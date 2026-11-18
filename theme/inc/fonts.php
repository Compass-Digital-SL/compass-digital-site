<?php
/**
 * Preload the critical self-hosted fonts.
 *
 * Only the fonts used above the fold are preloaded: the sans variable font
 * (body/UI) and the serif display variable font (headings). @font-face rules
 * and size-adjust fallbacks live in assets/src/css (see fonts.css).
 *
 * @package CompassDigital
 */

defined( 'ABSPATH' ) || exit;

add_action(
	'wp_head',
	function () {
		$fonts = array(
			'fonts/inter-latin-var.woff2',
			'fonts/fraunces-latin-var.woff2',
		);
		foreach ( $fonts as $rel ) {
			$path = COMPASS_DIR . '/assets/' . $rel;
			if ( ! is_readable( $path ) ) {
				continue;
			}
			printf(
				'<link rel="preload" href="%s" as="font" type="font/woff2" crossorigin>' . "\n",
				esc_url( COMPASS_URI . '/assets/' . $rel )
			);
		}
	},
	2
);
