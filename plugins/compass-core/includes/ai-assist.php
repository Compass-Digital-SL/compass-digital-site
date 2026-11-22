<?php
/**
 * AI drafting assistant for the Projects dashboard (admin only).
 *
 * Exposes a single authenticated REST route, `compass/v1/ai/draft`, that drafts
 * copy for a case study field (problem, approach, solution, results, summary,
 * body or SEO meta) from the values the editor has typed so far. It reuses the
 * same provider adapter as the public chat — compass_chat_complete() in
 * chat-providers.php — so the Gemini API key is read server-side via
 * compass_core_secret() and never reaches the browser.
 *
 * This route is capability-gated (`edit_posts`) and therefore deliberately NOT
 * subject to the public chat's per-IP / whole-site caps: those protect the free
 * quota from anonymous abuse, which does not apply to signed-in editors. A light
 * per-user throttle still guards against a runaway loop.
 *
 * @package CompassCore
 */

defined( 'ABSPATH' ) || exit;

const COMPASS_AI_RL_PREFIX = 'compass_ai_rl_';
const COMPASS_AI_RL_MAX    = 40;            // Drafts per user per window.
const COMPASS_AI_RL_WINDOW = 600;           // 10 minutes.

/**
 * Register the drafting route.
 */
function compass_ai_register_route() {
	register_rest_route(
		'compass/v1',
		'/ai/draft',
		array(
			'methods'             => 'POST',
			'callback'            => 'compass_ai_handle',
			'permission_callback' => 'compass_ai_permission',
			'args'                => array(
				'field'   => array(
					'required' => true,
					'type'     => 'string',
				),
				'context' => array(
					'required' => false,
					'type'     => 'object',
				),
			),
		)
	);
}
add_action( 'rest_api_init', 'compass_ai_register_route' );

/**
 * Only signed-in users who can edit posts may draft. REST cookie auth has
 * already verified the wp_rest nonce to populate the current user, so a missing
 * or stale nonce leaves no capability and is rejected here.
 *
 * @return bool|WP_Error
 */
function compass_ai_permission() {
	if ( ! current_user_can( 'edit_posts' ) ) {
		return new WP_Error(
			'compass_ai_forbidden',
			__( 'You are not allowed to use the AI assistant.', 'compass-core' ),
			array( 'status' => 403 )
		);
	}
	return true;
}

/**
 * Fields the assistant can draft.
 *
 * @return array<string,string> field key => short human label.
 */
function compass_ai_fields() {
	return array(
		'problem'  => __( 'Problem', 'compass-core' ),
		'approach' => __( 'Approach', 'compass-core' ),
		'solution' => __( 'Solution', 'compass-core' ),
		'results'  => __( 'Results', 'compass-core' ),
		'summary'  => __( 'Summary', 'compass-core' ),
		'body'     => __( 'Case study body', 'compass-core' ),
		'meta'     => __( 'SEO meta description', 'compass-core' ),
	);
}

/**
 * Handle a draft request.
 *
 * @param WP_REST_Request $request Request.
 * @return WP_REST_Response
 */
function compass_ai_handle( $request ) {
	$field = sanitize_key( (string) $request->get_param( 'field' ) );
	if ( ! array_key_exists( $field, compass_ai_fields() ) ) {
		return new WP_REST_Response(
			array(
				'ok'      => false,
				'message' => __( 'Unknown field.', 'compass-core' ),
			),
			400
		);
	}

	// --- Per-user throttle.
	$rl_key = COMPASS_AI_RL_PREFIX . get_current_user_id();
	$hits   = (int) get_transient( $rl_key );
	if ( $hits >= COMPASS_AI_RL_MAX ) {
		return new WP_REST_Response(
			array(
				'ok'      => false,
				'message' => __( 'Too many drafts in a short time. Please pause for a few minutes.', 'compass-core' ),
			),
			429
		);
	}

	// --- Collect and clean the context the editor has typed.
	$raw     = $request->get_param( 'context' );
	$context = compass_ai_sanitize_context( is_array( $raw ) ? $raw : array() );

	if ( '' === trim( $context['title'] ) && '' === trim( $context['client'] ) ) {
		return new WP_REST_Response(
			array(
				'ok'      => false,
				'message' => __( 'Add a project title or client first, so the draft has something to work from.', 'compass-core' ),
			),
			400
		);
	}

	list( $system, $user_text ) = compass_ai_build_prompt( $field, $context );

	// Count before the call so a slow/failed provider still spends the budget.
	set_transient( $rl_key, $hits + 1, COMPASS_AI_RL_WINDOW );

	$result = compass_chat_complete(
		$system,
		array(
			array(
				'role' => 'user',
				'text' => $user_text,
			),
		)
	);

	if ( empty( $result['ok'] ) ) {
		$code    = isset( $result['code'] ) ? $result['code'] : 'error';
		$status  = ( 'quota' === $code ) ? 429 : 502;
		$message = ( 'no_key' === $code )
			? __( 'No Gemini API key is set. Add one under Settings → Site Settings.', 'compass-core' )
			: __( 'The AI assistant could not draft this right now. Please try again.', 'compass-core' );
		if ( 'no_key' === $code ) {
			$status = 503;
		}
		return new WP_REST_Response(
			array(
				'ok'      => false,
				'message' => $message,
			),
			$status
		);
	}

	return new WP_REST_Response(
		array(
			'ok'   => true,
			'text' => compass_ai_clean_output( $field, (string) $result['text'] ),
		),
		200
	);
}

/**
 * Sanitise and cap the editor-supplied context.
 *
 * @param array $raw Raw context map.
 * @return array<string,string> Clean context with every expected key present.
 */
function compass_ai_sanitize_context( $raw ) {
	$keys  = array( 'title', 'client', 'year', 'tech', 'service', 'industry', 'problem', 'approach', 'solution', 'results' );
	$clean = array();
	foreach ( $keys as $key ) {
		$value         = isset( $raw[ $key ] ) ? (string) $raw[ $key ] : '';
		$value         = sanitize_textarea_field( wp_unslash( $value ) );
		$clean[ $key ] = mb_substr( $value, 0, 1500 );
	}
	return $clean;
}

/**
 * Build the system + user prompt for a field.
 *
 * @param string $field   Field key (validated).
 * @param array  $context Clean context map.
 * @return array{0:string,1:string} [ system prompt, user text ].
 */
function compass_ai_build_prompt( $field, $context ) {
	$voice = implode(
		' ',
		array(
			'You write case-study copy for Compass Digital, a software and digital services firm (websites, SEO, POS systems, apps, ecommerce and custom software) working with clients worldwide.',
			'Voice: plain, confident, warm. Short sentences, active voice. No jargon, no hype.',
			'Never use these words: cutting-edge, synergy, revolutionary, world-class. No exclamation marks.',
			'Write in British English. Do not invent client names, figures or facts that are not in the brief.',
			'Return only the requested copy — no preamble, no labels, no markdown fences.',
		)
	);

	$instructions = array(
		'problem'  => 'Write ONE short paragraph (2–3 sentences) describing the problem the client faced before the project.',
		'approach' => 'Write ONE short paragraph (2–3 sentences) describing how Compass Digital planned to tackle it — the thinking before the build.',
		'solution' => 'Write ONE short paragraph (2–4 sentences) describing what was built and delivered.',
		'results'  => 'Write 3–5 result lines, ONE per line, no bullet characters. Lead with a concrete number where the brief supports it; do not invent figures.',
		'summary'  => 'Write a single-sentence summary of about 140 characters (max 160) for a project card and the page excerpt.',
		'body'     => 'Write the full case-study body as simple HTML using only <h2> and <p> tags. Cover, in order: the problem, the approach, the solution, and the results. Keep it to roughly 180–300 words.',
		'meta'     => 'Write one SEO meta description of about 150 characters (strict max 160). It must read naturally and mention the kind of work and the client type.',
	);

	$system = $voice . ' ' . $instructions[ $field ];

	// --- Compose the brief from whatever the editor has filled in.
	$labels = array(
		'title'    => 'Project title',
		'client'   => 'Client',
		'year'     => 'Year',
		'tech'     => 'Tech used',
		'service'  => 'Service',
		'industry' => 'Industry',
		'problem'  => 'Problem',
		'approach' => 'Approach',
		'solution' => 'Solution',
		'results'  => 'Results',
	);

	$lines = array();
	foreach ( $labels as $key => $label ) {
		// When drafting a field, don't feed that same field back in as a hint.
		if ( $key === $field ) {
			continue;
		}
		if ( '' !== trim( $context[ $key ] ) ) {
			$lines[] = $label . ': ' . $context[ $key ];
		}
	}

	$brief = $lines ? implode( "\n", $lines ) : 'No details provided yet.';

	$user_text = "Project brief:\n" . $brief . "\n\nNow write the " . compass_ai_fields()[ $field ] . '.';

	return array( $system, $user_text );
}

/**
 * Tidy the model output for the target field.
 *
 * @param string $field Field key.
 * @param string $text  Raw model text.
 * @return string
 */
function compass_ai_clean_output( $field, $text ) {
	$text = trim( $text );

	// Strip any stray markdown code fences the model may add.
	$text = preg_replace( '/^```[a-z]*\s*|\s*```$/i', '', $text );

	if ( 'body' === $field ) {
		return wp_kses_post( $text );
	}

	// Non-body fields are plain text; collapse to safe text but keep newlines
	// for the multi-line "results" field.
	$text = wp_strip_all_tags( $text );
	return trim( $text );
}
