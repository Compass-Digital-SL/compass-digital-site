<?php
/**
 * AI sales assistant — server-side proxy + front-end wiring.
 *
 * A REST route (compass/v1/chat) that proxies to an AI provider (default:
 * Google Gemini free tier) with a grounded, brand-voiced sales-rep system
 * prompt. The API key is read server-side only (never sent to the browser).
 * Per-IP rate limiting with transients protects the free quota and blocks
 * abuse; the route returns plain text and degrades gracefully to the quote
 * form / WhatsApp when the provider errors or the quota is hit.
 *
 * The provider itself lives in chat-providers.php (one module to swap). The
 * knowledge the prompt is built from lives in data/chat-knowledge.php (editable
 * data, no code).
 *
 * Front end: a tiny launcher button is printed in the footer; the heavy chat
 * panel is a lazy Vite chunk the theme fetches on first open, so initial page
 * budgets are untouched. The launcher only appears after cookie consent.
 *
 * @package CompassCore
 */

defined( 'ABSPATH' ) || exit;

require_once COMPASS_CORE_DIR . 'includes/chat-providers.php';
require_once COMPASS_CORE_DIR . 'data/chat-knowledge.php';

const COMPASS_CHAT_RL_PREFIX = 'compass_chat_rl_';
const COMPASS_CHAT_MAX_LEN   = 1000; // Max chars per visitor message.
const COMPASS_CHAT_MAX_TURNS = 12;   // Max history turns we forward.
const COMPASS_CHAT_RL_MAX    = 20;   // Messages per window, per IP.
const COMPASS_CHAT_RL_WINDOW = 600;  // Rate-limit window, seconds (10 min).

const COMPASS_CHAT_DAILY_MAX    = 600;                 // Whole-site messages per day (free-quota guard).
const COMPASS_CHAT_DAILY_PREFIX = 'compass_chat_day_'; // Daily counter transient prefix.

/**
 * Is the assistant configured and switched on?
 *
 * @return bool
 */
function compass_chat_enabled() {
	$key = function_exists( 'compass_core_secret' ) ? compass_core_secret( 'gemini_api_key' ) : '';
	return '' !== $key;
}

/**
 * Register the REST route.
 */
function compass_chat_register_route() {
	register_rest_route(
		'compass/v1',
		'/chat',
		array(
			'methods'             => 'POST',
			'callback'            => 'compass_chat_handle',
			'permission_callback' => 'compass_chat_permission',
			'args'                => array(
				'message' => array(
					'required' => true,
					'type'     => 'string',
				),
				'history' => array(
					'required' => false,
					'type'     => 'array',
				),
			),
		)
	);
}
add_action( 'rest_api_init', 'compass_chat_register_route' );

/**
 * Permission callback: a valid same-origin REST nonce is required.
 *
 * The widget sends the wp_rest nonce in the X-WP-Nonce header, which WordPress
 * verifies to populate the current user. This blocks naive cross-site abuse of
 * the proxy while keeping the route usable by anonymous visitors.
 *
 * @param WP_REST_Request $request Request.
 * @return bool|WP_Error
 */
function compass_chat_permission( $request ) {
	$nonce = $request->get_header( 'x-wp-nonce' );
	if ( ! $nonce || ! wp_verify_nonce( $nonce, 'wp_rest' ) ) {
		return new WP_Error(
			'compass_chat_forbidden',
			__( 'Invalid or missing session token.', 'compass-core' ),
			array( 'status' => 403 )
		);
	}
	return true;
}

/**
 * Handle a chat message.
 *
 * @param WP_REST_Request $request Request.
 * @return WP_REST_Response
 */
function compass_chat_handle( $request ) {
	if ( ! compass_chat_enabled() ) {
		return compass_chat_response( compass_chat_fallback_text(), 503, 'disabled' );
	}

	// --- Per-IP rate limit.
	$ip_key = COMPASS_CHAT_RL_PREFIX . md5( compass_core_client_ip() );
	$hits   = (int) get_transient( $ip_key );
	if ( $hits >= COMPASS_CHAT_RL_MAX ) {
		return compass_chat_response(
			__( 'You have sent a lot of messages in a short time. Please take a short break, or reach us on the quote form or WhatsApp.', 'compass-core' ),
			429,
			'rate_limited'
		);
	}

	// --- Whole-site daily cap. Protects the provider's free quota from a
	// distributed caller (many IPs, each under the per-IP limit). Filterable.
	$daily_key = COMPASS_CHAT_DAILY_PREFIX . gmdate( 'Ymd' );
	$daily_max = (int) apply_filters( 'compass_chat_daily_max', COMPASS_CHAT_DAILY_MAX );
	$daily     = (int) get_transient( $daily_key );
	if ( $daily_max > 0 && $daily >= $daily_max ) {
		return compass_chat_response( compass_chat_fallback_text(), 429, 'busy' );
	}

	// --- Validate the incoming message (reject non-chat payloads).
	$message = (string) $request->get_param( 'message' );
	$message = trim( wp_strip_all_tags( $message ) );
	if ( '' === $message ) {
		return compass_chat_response( __( 'Please type a message.', 'compass-core' ), 400, 'empty' );
	}
	if ( mb_strlen( $message ) > COMPASS_CHAT_MAX_LEN ) {
		$message = mb_substr( $message, 0, COMPASS_CHAT_MAX_LEN );
	}

	// --- Normalise history into clean {role, text} turns.
	$history = $request->get_param( 'history' );
	$turns   = compass_chat_sanitize_history( is_array( $history ) ? $history : array() );
	$turns[] = array(
		'role' => 'user',
		'text' => $message,
	);

	// Count this message toward the per-IP window and the whole-site day before
	// calling out, so a failed/slow provider call still consumes the budget.
	set_transient( $ip_key, $hits + 1, COMPASS_CHAT_RL_WINDOW );
	set_transient( $daily_key, $daily + 1, DAY_IN_SECONDS );

	// --- Call the provider.
	$result = compass_chat_complete( compass_chat_system_prompt(), $turns );

	if ( empty( $result['ok'] ) ) {
		$code   = isset( $result['code'] ) ? $result['code'] : 'error';
		$status = ( 'quota' === $code ) ? 429 : 502;
		if ( 'no_key' === $code ) {
			$status = 503;
		}
		return compass_chat_response( compass_chat_fallback_text(), $status, $code );
	}

	$reply = (string) $result['text'];
	return compass_chat_response( $reply, 200, 'ok', compass_chat_sign( compass_chat_normalize_text( $reply ) ) );
}

/**
 * Build a plain-text REST response.
 *
 * @param string $reply  Assistant reply / message.
 * @param int    $status HTTP status.
 * @param string $state  Short machine state for the client.
 * @param string $sig    Optional HMAC signature the client echoes back.
 * @return WP_REST_Response
 */
function compass_chat_response( $reply, $status, $state, $sig = '' ) {
	$data = array(
		'reply' => $reply,
		'state' => $state,
	);
	// On a successful reply, hand the client a signature it must echo back with
	// that turn (see compass_chat_sanitize_history) so forged assistant turns
	// cannot be injected into later requests.
	if ( '' !== $sig ) {
		$data['sig'] = $sig;
	}
	return new WP_REST_Response( $data, $status );
}

/**
 * Normalise a message/turn to the canonical form used for storage and signing.
 *
 * @param string $text Raw text.
 * @return string Stripped, trimmed, length-capped text.
 */
function compass_chat_normalize_text( $text ) {
	$text = trim( wp_strip_all_tags( (string) $text ) );
	if ( mb_strlen( $text ) > COMPASS_CHAT_MAX_LEN ) {
		$text = mb_substr( $text, 0, COMPASS_CHAT_MAX_LEN );
	}
	return $text;
}

/**
 * HMAC-sign an assistant reply so the client cannot forge one.
 *
 * Keyed on a site salt (never leaves the server). The signature travels to the
 * browser with the reply and must come back unchanged for the turn to be
 * trusted as prior context.
 *
 * @param string $text Normalised assistant text.
 * @return string Hex signature.
 */
function compass_chat_sign( $text ) {
	return hash_hmac( 'sha256', $text, wp_salt( 'compass_chat_sig' ) );
}

/**
 * Sanitise forwarded history into safe {role, text} turns.
 *
 * @param array $history Raw history from the client.
 * @return array
 */
function compass_chat_sanitize_history( $history ) {
	$turns = array();
	foreach ( $history as $item ) {
		if ( ! is_array( $item ) ) {
			continue;
		}
		$role = isset( $item['role'] ) && 'assistant' === $item['role'] ? 'assistant' : 'user';
		$text = compass_chat_normalize_text( isset( $item['text'] ) ? $item['text'] : '' );
		if ( '' === $text ) {
			continue;
		}
		// An assistant turn is only trusted as context if it carries the server
		// signature we issued for that exact text. Unsigned or tampered assistant
		// turns are dropped, so the proxy cannot be steered with fabricated replies.
		if ( 'assistant' === $role ) {
			$sig = isset( $item['sig'] ) ? (string) $item['sig'] : '';
			if ( '' === $sig || ! hash_equals( compass_chat_sign( $text ), $sig ) ) {
				continue;
			}
		}
		$turns[] = array(
			'role' => $role,
			'text' => $text,
		);
	}

	// Keep only the most recent turns.
	if ( count( $turns ) > COMPASS_CHAT_MAX_TURNS ) {
		$turns = array_slice( $turns, -COMPASS_CHAT_MAX_TURNS );
	}
	return $turns;
}

/**
 * The fallback message shown when the provider is unavailable.
 *
 * @return string
 */
function compass_chat_fallback_text() {
	$quote = home_url( '/get-a-quote/' );
	return sprintf(
		/* translators: %s: quote page URL. */
		__( 'Sorry — I can\'t answer right now. The quickest way forward is to request a free quote at %s and the team will get straight back to you.', 'compass-core' ),
		$quote
	);
}

/**
 * Assemble the grounded sales-rep system prompt from the knowledge base.
 *
 * @return string
 */
function compass_chat_system_prompt() {
	$k        = compass_core_chat_knowledge();
	$persona  = isset( $k['persona'] ) ? $k['persona'] : array();
	$name     = isset( $persona['name'] ) ? $persona['name'] : 'the assistant';
	$company  = isset( $persona['company'] ) ? $persona['company'] : 'Compass Digital';
	$quote    = home_url( $k['handoff']['quote_path'] ?? '/get-a-quote/' );
	$whatsapp = ! empty( $k['handoff']['whatsapp'] ) ? ( 'https://wa.me/' . $k['handoff']['whatsapp'] ) : '';

	$lines   = array();
	$lines[] = sprintf( 'You are %s, %s. You chat with visitors on the %s website and your job is to help them and turn genuine interest into a quote request. Text chat only.', $name, $persona['role'] ?? 'a sales assistant', $company );
	$lines[] = sprintf( 'Company tagline: "%s".', $persona['tagline'] ?? '' );

	$lines[] = "\nVOICE:";
	foreach ( (array) ( $k['voice'] ?? array() ) as $v ) {
		$lines[] = '- ' . $v;
	}

	$lines[] = "\nABOUT THE COMPANY:";
	foreach ( (array) ( $k['about'] ?? array() ) as $a ) {
		$lines[] = '- ' . $a;
	}

	$lines[] = "\nSERVICES we offer (only these):";
	$lines[] = '- ' . implode( "\n- ", (array) ( $k['services'] ?? array() ) );

	$lines[] = "\nINDUSTRIES we work with:";
	$lines[] = '- ' . implode( "\n- ", (array) ( $k['industries'] ?? array() ) );

	if ( ! empty( $k['pricing_stance'] ) ) {
		$lines[] = "\nPRICING (critical): " . $k['pricing_stance'];
	}

	if ( ! empty( $k['faq'] ) ) {
		$lines[] = "\nFAQ you can answer directly:";
		foreach ( (array) $k['faq'] as $item ) {
			if ( isset( $item['q'], $item['a'] ) ) {
				$lines[] = sprintf( '- Q: %s A: %s', $item['q'], $item['a'] );
			}
		}
	}

	$lines[] = "\nSALES BEHAVIOUR:";
	$lines[] = '- Greet warmly, understand what the visitor is trying to achieve, and ask one short qualifying question at a time (what they need, their type of business, their goal).';
	$lines[] = sprintf( '- When there is real interest, invite them to request a free quote at %s. If they prefer to talk now and a WhatsApp link is available, offer it: %s', $quote, $whatsapp ? $whatsapp : '(no WhatsApp configured)' );
	$lines[] = '- Hand off to a human (via the quote form or WhatsApp) for anything you are unsure about or that needs real figures.';
	$lines[] = '- Write naturally for chat. Do not use markdown tables or headings. Short paragraphs, plain links.';

	$lines[] = "\nGUARDRAILS:";
	foreach ( (array) ( $k['guardrails'] ?? array() ) as $g ) {
		$lines[] = '- ' . $g;
	}

	$prompt = implode( "\n", $lines );

	/**
	 * Filter the final system prompt.
	 *
	 * @param string $prompt The assembled prompt.
	 * @param array  $k      The knowledge base.
	 */
	return (string) apply_filters( 'compass_chat_system_prompt', $prompt, $k );
}

/*
 * -------------------------------------------------------------------------
 * Front end
 * ----------------------------------------------------------------------
 */

/**
 * Hand the chat widget a fresh REST nonce (never cached).
 *
 * Served through admin-ajax so WordPress resolves the real cookie user: a REST
 * route called without a nonce runs as user 0, and its nonce would then fail
 * for a logged-in visitor. Nothing secret — the same value is in the page HTML.
 */
function compass_chat_ajax_nonce() {
	nocache_headers();
	if ( ! compass_chat_enabled() ) {
		wp_send_json_error( null, 503 );
	}
	wp_send_json_success( array( 'nonce' => wp_create_nonce( 'wp_rest' ) ) );
}
add_action( 'wp_ajax_compass_chat_nonce', 'compass_chat_ajax_nonce' );
add_action( 'wp_ajax_nopriv_compass_chat_nonce', 'compass_chat_ajax_nonce' );

/**
 * Expose the config the theme's JS needs to open the chat.
 *
 * Printed as window.CompassChat. Enqueued against the theme's main entry so the
 * lazy chat chunk (fetched on first open) can read it. Nothing secret here — the
 * REST nonce only authorises same-origin calls.
 */
function compass_chat_front_config() {
	if ( is_admin() || ! compass_chat_enabled() ) {
		return;
	}

	$k       = compass_core_chat_knowledge();
	$persona = $k['persona'] ?? array();

	$config = array(
		'endpoint' => esc_url_raw( rest_url( 'compass/v1/chat' ) ),
		'nonce'    => wp_create_nonce( 'wp_rest' ),
		// Page HTML may be cached for longer than a nonce lives (12–24 h), so the
		// widget asks for a fresh one from this uncached URL before sending.
		'nonceUrl' => esc_url_raw( admin_url( 'admin-ajax.php?action=compass_chat_nonce' ) ),
		'name'     => $persona['name'] ?? 'Assistant',
		'company'  => $persona['company'] ?? 'Compass Digital',
		'quoteUrl' => home_url( $k['handoff']['quote_path'] ?? '/get-a-quote/' ),
		'whatsapp' => ! empty( $k['handoff']['whatsapp'] )
			? 'https://wa.me/' . $k['handoff']['whatsapp'] . '?text=' . rawurlencode( $k['handoff']['whatsapp_text'] ?? '' )
			: '',
		'greeting' => sprintf(
			/* translators: %s: assistant name. */
			__( 'Hi, I\'m %s. How can I help with your project today?', 'compass-core' ),
			$persona['name'] ?? 'your assistant'
		),
		'maxLen'   => COMPASS_CHAT_MAX_LEN,
		'consent'  => COMPASS_CONSENT_COOKIE,
	);

	// Print the config as its own inline <script> in the footer, before the main
	// bundle runs (footer priority 4 < wp_print_footer_scripts at 20). We do NOT
	// attach it to the `compass-main` handle: the theme rewrites that handle into
	// a native ES module via `script_loader_tag`, which drops any inline "before"
	// script WordPress would normally print — so `window.CompassChat` would be
	// lost and the launcher could never open. main.js is a deferred module, so a
	// classic inline script placed earlier in the DOM always runs first.
	add_action(
		'wp_footer',
		function () use ( $config ) {
			printf( '<script id="compass-chat-config">window.CompassChat=%s;</script>' . "\n", wp_json_encode( $config ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- wp_json_encode output, no user input.
		},
		4
	);
}
add_action( 'wp_enqueue_scripts', 'compass_chat_front_config', 20 );

/**
 * Print the chat launcher button in the footer.
 *
 * Always visible. The heavy panel UI is a lazy chunk fetched on first open, so
 * this button is all that ships on initial load. Styled from design tokens only.
 */
function compass_chat_launcher() {
	if ( is_admin() || ! compass_chat_enabled() ) {
		return;
	}
	$label = __( 'Chat with us', 'compass-core' );
	?>
	<button type="button" id="compass-chat-launcher" class="compass-chat-launcher" data-compass-chat aria-label="<?php echo esc_attr( $label ); ?>" aria-haspopup="dialog" aria-expanded="false">
		<span class="compass-chat-launcher__icon" aria-hidden="true">
			<svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" focusable="false">
				<path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"/>
			</svg>
		</span>
		<span class="compass-chat-launcher__label"><?php esc_html_e( 'Chat', 'compass-core' ); ?></span>
	</button>
	<?php
}
add_action( 'wp_footer', 'compass_chat_launcher', 5 );

/**
 * Minimal, token-based styling for the launcher button only.
 *
 * Kept tiny and inline (like the consent bar) so the full panel CSS can stay in
 * the lazy chunk and never touch the initial CSS budget. Colours are design
 * tokens, never hard-coded values.
 */
function compass_chat_launcher_styles() {
	if ( is_admin() || ! compass_chat_enabled() ) {
		return;
	}
	?>
	<style id="compass-chat-launcher-css">
		.compass-chat-launcher{position:fixed;z-index:995;right:clamp(1rem,2vw,1.5rem);bottom:calc(clamp(1rem,2vw,1.5rem) + var(--compass-launcher-lift,0px));display:inline-flex;align-items:center;gap:var(--space-2,.5rem);padding:var(--space-3,.75rem) var(--space-4,1rem);font:inherit;font-size:var(--fs-sm,.9rem);font-weight:500;color:var(--accent-text,#fff);background:color-mix(in srgb,var(--accent-fill,#1c8282) 72%,transparent);-webkit-backdrop-filter:blur(var(--glass-blur,14px)) saturate(1.3);backdrop-filter:blur(var(--glass-blur,14px)) saturate(1.3);border:1px solid color-mix(in srgb,#fff 24%,transparent);border-radius:var(--radius-pill,999px);box-shadow:var(--shadow-2,0 10px 30px -10px rgba(5,12,20,.6)),inset 0 1px 0 color-mix(in srgb,#fff 22%,transparent);cursor:pointer}
		.compass-chat-launcher[hidden]{display:none}
		.compass-chat-launcher__icon{display:inline-flex}
		.compass-chat-launcher:hover{background:color-mix(in srgb,var(--accent-fill-2,#187070) 82%,transparent);border-color:color-mix(in srgb,#fff 34%,transparent)}
		.compass-chat-launcher:focus-visible{outline:2px solid var(--focus,#309393);outline-offset:3px}
		@media (max-width:480px){.compass-chat-launcher{bottom:calc(env(safe-area-inset-bottom,0) + 4.5rem + var(--compass-launcher-lift,0px))}}
		@media (prefers-reduced-motion:no-preference){.compass-chat-launcher{transition:background var(--dur-fast,150ms) var(--ease,ease),transform var(--dur-fast,150ms) var(--ease,ease),bottom var(--dur-mid,300ms) var(--ease,ease)}.compass-chat-launcher:hover{transform:translateY(-1px)}}
	</style>
	<?php
}
add_action( 'wp_print_styles', 'compass_chat_launcher_styles' );
