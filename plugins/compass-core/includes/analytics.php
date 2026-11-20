<?php
/**
 * Analytics, Search Console verification, cookie consent and event tracking.
 *
 * All IDs come from the Site Settings page (or PHP constants) — nothing is
 * hard-coded. Google Analytics 4 loads only after the visitor accepts cookies
 * and once the page is idle (see assets/consent.js), so it never blocks the
 * first paint and respects consent. Search Console verification is a plain meta
 * tag and sets no cookies, so it is printed unconditionally.
 *
 * Tracked events (fired by consent.js): quote_submitted, whatsapp_click,
 * phone_click, service_view, command_menu_used.
 *
 * The consent bar and its "Cookie preferences" re-open link are owned here (not
 * the theme) so consent survives a theme change. Styling uses the theme's
 * design tokens via CSS custom properties — no hard-coded colours.
 *
 * @package CompassCore
 */

defined( 'ABSPATH' ) || exit;

const COMPASS_CONSENT_COOKIE = 'compass_consent';

/**
 * The GA4 Measurement ID (constant COMPASS_GA4_ID overrides the stored value).
 *
 * @return string
 */
function compass_core_ga4_id() {
	return function_exists( 'compass_core_secret' ) ? compass_core_secret( 'ga4_id' ) : (string) compass_get_setting( 'ga4_id' );
}

/**
 * The Search Console verification token (constant COMPASS_GSC_VERIFICATION wins).
 *
 * @return string
 */
function compass_core_gsc_token() {
	return function_exists( 'compass_core_secret' ) ? compass_core_secret( 'gsc_verification' ) : (string) compass_get_setting( 'gsc_verification' );
}

/**
 * Print the Google Search Console verification meta tag.
 *
 * No cookies, so this is not gated on consent.
 */
function compass_core_gsc_head() {
	$token = compass_core_gsc_token();
	if ( $token ) {
		printf( '<meta name="google-site-verification" content="%s" />' . "\n", esc_attr( $token ) );
	}
}
add_action( 'wp_head', 'compass_core_gsc_head', 2 );

/**
 * The page-level analytics event for the current request, or null.
 *
 * Service pages fire `service_view`; the thank-you page fires `quote_submitted`.
 * consent.js fires it once, after consent.
 *
 * @return array{name:string,params:array}|null
 */
function compass_core_page_event() {
	if ( function_exists( 'compass_is_thank_you' ) && compass_is_thank_you() ) {
		return array(
			'name'   => 'quote_submitted',
			'params' => array(),
		);
	}

	if ( function_exists( 'compass_current_service' ) ) {
		$service = compass_current_service();
		if ( $service ) {
			$slug = get_post_field( 'post_name', get_queried_object_id() );
			return array(
				'name'   => 'service_view',
				'params' => array( 'service' => $slug ? $slug : '' ),
			);
		}
	}

	return null;
}

/**
 * Register and enqueue the consent + analytics script with its config.
 *
 * The script is enqueued even when no GA4 ID is set, so the consent bar and its
 * event wiring still work; it simply loads no analytics.
 */
function compass_core_enqueue_consent() {
	if ( is_admin() ) {
		return;
	}

	$path = COMPASS_CORE_DIR . 'assets/consent.js';
	$ver  = file_exists( $path ) ? (string) filemtime( $path ) : COMPASS_CORE_VERSION;

	wp_register_script(
		'compass-consent',
		plugins_url( 'assets/consent.js', COMPASS_CORE_FILE ),
		array(),
		$ver,
		array(
			'in_footer' => true,
			'strategy'  => 'defer',
		)
	);

	$config = array(
		'ga4'       => compass_core_ga4_id(),
		'cookie'    => COMPASS_CONSENT_COOKIE,
		'pageEvent' => compass_core_page_event(),
		'policyUrl' => home_url( '/privacy/' ),
	);

	wp_add_inline_script(
		'compass-consent',
		'window.CompassConsent=' . wp_json_encode( $config ) . ';',
		'before'
	);

	wp_enqueue_script( 'compass-consent' );
}
add_action( 'wp_enqueue_scripts', 'compass_core_enqueue_consent' );

/**
 * Minimal, token-based styling for the consent bar.
 *
 * Kept with the markup (not the theme) so the bar is always styled; colours are
 * the theme's design tokens, never hard-coded values.
 */
function compass_core_consent_styles() {
	if ( is_admin() ) {
		return;
	}
	?>
	<style id="compass-consent-css">
		.compass-consent{position:fixed;inset:auto 0 0 0;z-index:1000;display:flex;gap:var(--space-4,1rem);align-items:center;flex-wrap:wrap;justify-content:center;padding:var(--space-4,1rem) var(--gutter,1rem);background:var(--surface-2,#0b2033);color:var(--text,#f6f2ea);border-top:1px solid var(--border-strong,rgba(246,242,234,.24));box-shadow:var(--shadow-2,0 8px 24px -8px rgba(5,12,20,.55));font-size:var(--fs-sm,.9rem);line-height:var(--lh-base,1.6)}
		.compass-consent[hidden]{display:none}
		.compass-consent__text{margin:0;max-width:52ch;flex:1 1 20rem}
		.compass-consent__text a{color:var(--link,#5fb5bd)}
		.compass-consent__actions{display:flex;gap:var(--space-2,.5rem);flex:0 0 auto}
		.compass-consent__btn{cursor:pointer;font:inherit;padding:var(--space-2,.5rem) var(--space-4,1rem);border-radius:var(--radius-pill,999px);border:1px solid var(--border-strong,rgba(246,242,234,.24));background:transparent;color:inherit}
		.compass-consent__btn--accept{background:var(--accent-fill,#1c8282);color:var(--accent-text,#fff);border-color:var(--accent-fill,#1c8282);font-weight:500}
		.compass-consent__btn:focus-visible{outline:3px solid var(--focus,#c9a24b);outline-offset:2px}
		.site-footer__consent-btn{cursor:pointer;background:none;border:0;padding:0;margin:0;font:inherit;color:var(--link,#5fb5bd);text-decoration:underline;text-underline-offset:.15em}
		@media (prefers-reduced-motion: no-preference){.compass-consent{animation:compass-consent-in var(--dur-mid,300ms) var(--ease-out,ease)}}
		@keyframes compass-consent-in{from{transform:translateY(100%)}to{transform:translateY(0)}}
	</style>
	<?php
}
add_action( 'wp_print_styles', 'compass_core_consent_styles' );

/**
 * Render the cookie consent bar in the footer.
 *
 * Server-rendered and hidden by default; consent.js shows it only when the
 * visitor has not yet chosen, and the footer "Cookie preferences" link reopens
 * it. Accessible: a labelled region with real buttons.
 */
function compass_core_consent_bar() {
	$policy = home_url( '/privacy/' );
	?>
	<div id="compass-consent" class="compass-consent" role="region" aria-label="<?php esc_attr_e( 'Cookie choices', 'compass-core' ); ?>" hidden>
		<p class="compass-consent__text">
			<?php
			printf(
				/* translators: %s: link to the privacy policy. */
				esc_html__( 'We use optional analytics cookies to understand how the site is used. You can accept or decline. %s', 'compass-core' ),
				'<a href="' . esc_url( $policy ) . '">' . esc_html__( 'Privacy policy', 'compass-core' ) . '</a>'
			);
			?>
		</p>
		<div class="compass-consent__actions">
			<button type="button" class="compass-consent__btn compass-consent__btn--decline" data-consent-action="decline"><?php esc_html_e( 'Decline', 'compass-core' ); ?></button>
			<button type="button" class="compass-consent__btn compass-consent__btn--accept" data-consent-action="accept"><?php esc_html_e( 'Accept', 'compass-core' ); ?></button>
		</div>
	</div>
	<?php
}
add_action( 'wp_footer', 'compass_core_consent_bar' );
