<?php
/**
 * Quote + Contact form support (theme side).
 *
 * The server handler (saving, email, spam defence) lives in the compass-core
 * plugin so enquiry data survives a theme change. The theme owns only the
 * presentation: the option lists the form renders, a reader for the "flash"
 * (validation errors + the visitor's own input, replayed after a failed submit
 * so no typing is lost), and keeping the thank-you page out of search.
 *
 * @package CompassDigital
 */

defined( 'ABSPATH' ) || exit;

/**
 * Prefix shared with the plugin for the per-submission flash transient.
 */
if ( ! defined( 'COMPASS_QUOTE_FLASH_PREFIX' ) ) {
	define( 'COMPASS_QUOTE_FLASH_PREFIX', 'compass_quote_flash_' );
}

/**
 * Service options for the "what you need" multi-select.
 *
 * Keyed by slug (stored) => readable label (shown), sourced from the canonical
 * service content so the form, the pages and the saved enquiry never drift.
 *
 * @return array<string,string>
 */
function compass_quote_services() {
	$out = array();
	if ( function_exists( 'compass_services_data' ) ) {
		foreach ( compass_services_data() as $slug => $service ) {
			$out[ $slug ] = wp_strip_all_tags( $service['eyebrow'] );
		}
	}
	$out['something-else'] = __( 'Something else', 'compass-digital' );
	return $out;
}

/**
 * Industry options.
 *
 * @return array<string,string>
 */
function compass_quote_industries() {
	$out = array( '' => __( 'Select an industry (optional)', 'compass-digital' ) );
	if ( function_exists( 'compass_industries_data' ) ) {
		foreach ( compass_industries_data() as $slug => $industry ) {
			$out[ $slug ] = wp_strip_all_tags( $industry['eyebrow'] );
		}
	}
	$out['other'] = __( 'Other', 'compass-digital' );
	return $out;
}

/**
 * Timeline options.
 *
 * @return array<string,string>
 */
function compass_quote_timelines() {
	return array(
		''           => __( 'No fixed timeline', 'compass-digital' ),
		'asap'       => __( 'As soon as possible', 'compass-digital' ),
		'1-3-months' => __( 'In 1–3 months', 'compass-digital' ),
		'3-6-months' => __( 'In 3–6 months', 'compass-digital' ),
		'exploring'  => __( 'Just exploring for now', 'compass-digital' ),
	);
}

/**
 * Optional budget ranges, in LKR (our billing currency). Kept broad and
 * pressure-free. International visitors are told in the form hint they can give
 * a figure in any currency in their message, so we do not force a conversion
 * or re-price for a worldwide audience.
 *
 * @return array<string,string>
 */
function compass_quote_budgets() {
	return array(
		''           => __( 'Prefer not to say', 'compass-digital' ),
		'under-100k' => __( 'Under LKR 100,000', 'compass-digital' ),
		'100-300k'   => __( 'LKR 100,000 – 300,000', 'compass-digital' ),
		'300-750k'   => __( 'LKR 300,000 – 750,000', 'compass-digital' ),
		'over-750k'  => __( 'Over LKR 750,000', 'compass-digital' ),
		'not-sure'   => __( 'Not sure yet', 'compass-digital' ),
	);
}

/**
 * Preferred contact method options.
 *
 * @return array<string,string>
 */
function compass_quote_contact_methods() {
	return array(
		'email'    => __( 'Email', 'compass-digital' ),
		'phone'    => __( 'Phone call', 'compass-digital' ),
		'whatsapp' => __( 'WhatsApp', 'compass-digital' ),
	);
}

/**
 * Read (and clear) the flash for the current request.
 *
 * Returns array( 'errors' => array<field,string>, 'input' => array ) or null.
 * The token is a random key in the URL after a failed submit; the stored
 * transient holds the field errors and the visitor's own values to replay.
 *
 * @return array<string,array>|null
 */
function compass_quote_flash() {
	static $flash = false;
	if ( false !== $flash ) {
		return $flash;
	}
	$flash = null;

	$token = isset( $_GET['quote_error'] ) ? sanitize_key( wp_unslash( $_GET['quote_error'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only replay token, not a trusted action.
	if ( '' === $token ) {
		return $flash;
	}

	$data = get_transient( COMPASS_QUOTE_FLASH_PREFIX . $token );
	if ( is_array( $data ) ) {
		delete_transient( COMPASS_QUOTE_FLASH_PREFIX . $token );
		$flash = wp_parse_args(
			$data,
			array(
				'errors' => array(),
				'input'  => array(),
			)
		);
	}
	return $flash;
}

/**
 * Error message for one field, if the last submit failed on it.
 *
 * @param string $field Field name.
 * @return string
 */
function compass_quote_error( $field ) {
	$flash = compass_quote_flash();
	return ( $flash && isset( $flash['errors'][ $field ] ) ) ? (string) $flash['errors'][ $field ] : '';
}

/**
 * Replay value for a field: the visitor's last input, else a URL pre-fill,
 * else the given default. Always returned raw; escape at the point of output.
 *
 * @param string $field    Field name.
 * @param string $fallback Fallback value.
 * @return string
 */
function compass_quote_value( $field, $fallback = '' ) {
	$flash = compass_quote_flash();
	if ( $flash && isset( $flash['input'][ $field ] ) && ! is_array( $flash['input'][ $field ] ) ) {
		return (string) $flash['input'][ $field ];
	}
	if ( isset( $_GET[ $field ] ) && ! is_array( $_GET[ $field ] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only pre-fill of a public form.
		return sanitize_text_field( wp_unslash( $_GET[ $field ] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only pre-fill of a public form.
	}
	return $fallback;
}

/**
 * Is a given option currently selected (replayed input or URL pre-fill)?
 *
 * Handles both single values and the services[] multi-select. Service and
 * industry accept either the slug or the readable label in the URL.
 *
 * @param string $field Field name (e.g. 'services', 'industry').
 * @param string $slug  Option slug.
 * @param string $label Option label (for human-friendly URL pre-fill).
 * @return bool
 */
function compass_quote_checked( $field, $slug, $label = '' ) {
	$flash    = compass_quote_flash();
	$selected = array();

	if ( $flash && isset( $flash['input'][ $field ] ) ) {
		$selected = (array) $flash['input'][ $field ];
	} elseif ( isset( $_GET[ $field ] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only pre-fill.
		$raw      = $_GET[ $field ]; // phpcs:ignore WordPress.Security.NonceVerification.Recommended, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.ValidatedSanitizedInput.MissingUnslash -- sanitised on the next line.
		$selected = is_array( $raw ) ? array_map( 'sanitize_text_field', wp_unslash( $raw ) ) : array( sanitize_text_field( wp_unslash( $raw ) ) );
	}

	// The site-wide CTAs link with the singular `?service=<name>` (see
	// compass_quote_url()); fold that into the services multi-select pre-fill.
	if ( 'services' === $field && isset( $_GET['service'] ) && ! is_array( $_GET['service'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only pre-fill.
		$selected[] = sanitize_text_field( wp_unslash( $_GET['service'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	}

	if ( ! $selected ) {
		return false;
	}
	$selected = array_map( 'strtolower', $selected );
	if ( in_array( strtolower( $slug ), $selected, true ) ) {
		return true;
	}
	return '' !== $label && in_array( strtolower( $label ), $selected, true );
}

/**
 * Is the current request the thank-you page template?
 */
function compass_is_thank_you() {
	return is_page() && is_page_template( 'page-thank-you.php' );
}

/**
 * Keep the thank-you page out of search and the sitemap.
 */
add_filter(
	'wp_robots',
	function ( $robots ) {
		if ( compass_is_thank_you() ) {
			$robots['noindex']  = true;
			$robots['nofollow'] = true;
			unset( $robots['max-image-preview'] );
		}
		return $robots;
	},
	20
);

add_filter(
	'wp_sitemaps_posts_query_args',
	function ( $args, $post_type ) {
		if ( 'page' !== $post_type ) {
			return $args;
		}
		$ids = get_posts(
			array(
				'post_type'      => 'page',
				'fields'         => 'ids',
				'posts_per_page' => -1,
				'meta_key'       => '_wp_page_template', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key -- small page set, runs on sitemap build.
				'meta_value'     => 'page-thank-you.php', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
				'no_found_rows'  => true,
			)
		);
		if ( $ids ) {
			$args['post__not_in'] = array_merge( isset( $args['post__not_in'] ) ? $args['post__not_in'] : array(), $ids );
		}
		return $args;
	},
	10,
	2
);

/**
 * Pages that carry the quote/contact form (a nonce that expires) or show a
 * per-visit result must never be served from a page cache: a stale nonce makes
 * every submission fail with "session expired". nocache_headers() sends
 * no-store headers that host and plugin caches respect.
 */
add_action(
	'template_redirect',
	function () {
		if ( is_page_template( array( 'page-contact.php', 'page-get-a-quote.php', 'page-thank-you.php' ) ) ) {
			nocache_headers();
		}
	}
);
