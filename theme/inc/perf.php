<?php
/**
 * Front-end performance helpers.
 *
 * - Speculation Rules: prefetch the likely next page on hover / pointer-down so
 *   navigations feel instant, without the cost (or analytics double-counting) of
 *   full prerender.
 * - Image pipeline: allow modern upload formats (WebP / AVIF) and keep generated
 *   JPEG/WebP sizes lean.
 *
 * Render-blocking CSS, inlined critical CSS, font preloads and non-blocking
 * scripts live in inc/assets.php and inc/fonts.php.
 *
 * @package CompassDigital
 */

defined( 'ABSPATH' ) || exit;

/**
 * Speculation Rules — prefetch same-origin document links with "moderate"
 * eagerness (triggered on hover / pointer-down). Excludes wp-admin, the cart-less
 * logout/customiser links and anything marked rel="nofollow" or data-no-prefetch.
 * Browsers without support ignore the script; it adds no blocking work.
 */
add_action(
	'wp_footer',
	function () {
		if ( is_admin() || is_user_logged_in() ) {
			return;
		}
		$rules = array(
			'prefetch' => array(
				array(
					'source'    => 'document',
					'eagerness' => 'moderate',
					'where'     => array(
						'and' => array(
							array( 'href_matches' => '/*' ),
							array( 'not' => array( 'href_matches' => '/wp-admin/*' ) ),
							array( 'not' => array( 'href_matches' => '/wp-login.php*' ) ),
							array( 'not' => array( 'selector_matches' => '[rel~="nofollow"]' ) ),
							array( 'not' => array( 'selector_matches' => '[data-no-prefetch]' ) ),
						),
					),
				),
			),
		);
		printf(
			'<script type="speculationrules">%s</script>' . "\n",
			wp_json_encode( $rules ) // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- wp_json_encode output inside a script tag is safe JSON.
		);
	}
);

/**
 * Allow modern image formats to be uploaded (WebP is core since 5.8, AVIF since
 * 6.5; added defensively so owners can upload the lighter formats directly).
 *
 * @param array $mimes Allowed mime types.
 * @return array
 */
add_filter(
	'upload_mimes',
	function ( $mimes ) {
		$mimes['webp'] = 'image/webp';
		$mimes['avif'] = 'image/avif';
		return $mimes;
	}
);

/**
 * Keep generated image sizes lean. 82 is visually lossless for photos at these
 * dimensions while cutting bytes versus the WordPress default (82 for JPEG, but
 * this also pins WebP output quality).
 */
add_filter( 'jpeg_quality', fn() => 82 );
add_filter( 'wp_editor_set_quality', fn() => 82 );
