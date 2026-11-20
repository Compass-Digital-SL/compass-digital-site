<?php
/**
 * Vite asset pipeline for a classic WordPress theme.
 *
 * Dev: when `assets/dist/hot` exists (written by the Vite dev server), load the
 * client and entry from the dev server URL for HMR / live reload.
 * Prod: read `assets/dist/.vite/manifest.json` and enqueue hashed files.
 *
 * @package CompassDigital
 */

defined( 'ABSPATH' ) || exit;

/**
 * The single JS entry point, relative to the Vite root (assets/src).
 */
function compass_vite_entry() {
	return 'js/main.js';
}

/**
 * Absolute filesystem path to the built assets directory.
 *
 * @return string
 */
function compass_dist_dir() {
	return COMPASS_DIR . '/assets/dist';
}

/**
 * Public URL of the built assets directory.
 *
 * @return string
 */
function compass_dist_uri() {
	return COMPASS_URI . '/assets/dist';
}

/**
 * Dev server base URL from the hot file, or false in production.
 *
 * @return string|false
 */
function compass_vite_dev_server() {
	static $url = null;
	if ( null !== $url ) {
		return $url;
	}
	$hot = compass_dist_dir() . '/hot';
	if ( is_readable( $hot ) ) {
		$url = rtrim( (string) file_get_contents( $hot ), "/ \n\r\t" );
	} else {
		$url = false;
	}
	return $url;
}

/**
 * Cached Vite manifest array.
 *
 * @return array
 */
function compass_vite_manifest() {
	static $manifest = null;
	if ( null !== $manifest ) {
		return $manifest;
	}
	$file     = compass_dist_dir() . '/.vite/manifest.json';
	$manifest = is_readable( $file ) ? (array) json_decode( (string) file_get_contents( $file ), true ) : array();
	return $manifest;
}

/**
 * Read a theme CSS file once and cache it. Used to inline critical CSS.
 *
 * @param string $rel Path relative to the theme's assets directory.
 * @return string
 */
function compass_asset_contents( $rel ) {
	static $cache = array();
	if ( isset( $cache[ $rel ] ) ) {
		return $cache[ $rel ];
	}
	$path           = COMPASS_DIR . '/assets/' . ltrim( $rel, '/' );
	$cache[ $rel ]  = is_readable( $path ) ? (string) file_get_contents( $path ) : '';
	return $cache[ $rel ];
}

/**
 * Inline the @font-face rules with absolute woff2 URLs so there is no
 * render-blocking fonts stylesheet request. The metric-adjusted fallbacks ship
 * in the same block so the fallback text has the right metrics immediately (no
 * layout shift when the webfont swaps in). Paired with the preloads in
 * inc/fonts.php. Printed very early in the head.
 */
add_action(
	'wp_head',
	function () {
		$css = compass_asset_contents( 'fonts/fonts.css' );
		if ( '' === $css ) {
			return;
		}
		// Rewrite relative url("./x.woff2") to absolute so it resolves from the
		// document, not the (now absent) stylesheet.
		$css = str_replace( 'url("./', 'url("' . COMPASS_URI . '/assets/fonts/', $css );
		printf( "<style id=\"compass-fonts-inline\">%s</style>\n", $css ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- First-party CSS from the theme, no user input.
	},
	1
);

/**
 * Inline the critical, above-the-fold CSS (design tokens + base/header/hero) so
 * the first paint is correct with zero render-blocking stylesheets. The full
 * stylesheet is then loaded non-render-blocking (see the style_loader_tag
 * filter below). Printed after the fonts, still before wp_head CSS output.
 */
add_action(
	'wp_head',
	function () {
		// Built + minified by Vite (vite.config.js criticalCss). The source pair is
		// only a dev fallback: assets/src is not shipped in the deploy artifact.
		$critical = compass_asset_contents( 'dist/critical.css' );
		if ( '' === $critical ) {
			$critical = compass_asset_contents( 'src/css/tokens.css' ) . compass_asset_contents( 'critical.css' );
		}
		if ( '' === trim( $critical ) ) {
			return;
		}
		printf( "<style id=\"compass-critical\">%s</style>\n", $critical ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- First-party CSS from the theme, no user input.
	},
	2
);

add_action(
	'wp_enqueue_scripts',
	function () {
		$entry = compass_vite_entry();
		$dev   = compass_vite_dev_server();

		if ( $dev ) {
			// Vite dev server: ES module client + entry, no manifest.
			add_action(
				'wp_head',
				function () use ( $dev, $entry ) {
					printf( '<script type="module" src="%s/@vite/client"></script>' . "\n", esc_url( $dev ) );
					printf( '<script type="module" src="%s/%s"></script>' . "\n", esc_url( $dev ), esc_attr( $entry ) );
				},
				1
			);
			return;
		}

		$manifest = compass_vite_manifest();
		if ( empty( $manifest[ $entry ] ) ) {
			return;
		}
		$chunk = $manifest[ $entry ];

		// CSS imported by the entry.
		if ( ! empty( $chunk['css'] ) ) {
			foreach ( $chunk['css'] as $i => $css ) {
				wp_enqueue_style(
					'compass-main' . ( $i ? '-' . $i : '' ),
					compass_dist_uri() . '/' . $css,
					array(),
					null
				);
			}
		}

		// Main entry JS as a module.
		if ( ! empty( $chunk['file'] ) ) {
			wp_enqueue_script(
				'compass-main',
				compass_dist_uri() . '/' . $chunk['file'],
				array(),
				null,
				true
			);
		}
	}
);

/**
 * Load the main (below-the-fold) stylesheet non-render-blocking.
 *
 * The critical CSS is already inlined, so the full bundle can load without
 * holding up first paint: preload it as a stylesheet and swap rel on load, with
 * a <noscript> fallback for JS-off visitors. Only the theme's own
 * `compass-main*` handles are affected.
 */
add_filter(
	'style_loader_tag',
	function ( $tag, $handle, $href, $media ) {
		if ( 0 !== strpos( $handle, 'compass-main' ) ) {
			return $tag;
		}
		// phpcs:disable WordPress.WP.EnqueuedResources.NonEnqueuedStylesheet -- The style is enqueued; this filter only rewrites its tag to load non-blocking.
		return sprintf(
			'<link rel="preload" as="style" href="%1$s" media="%2$s" onload="this.onload=null;this.rel=\'stylesheet\';window.compassCssReady&&compassCssReady()" onerror="window.compassCssReady&&compassCssReady()">' . "\n"
			. '<noscript><link rel="stylesheet" href="%1$s" media="%2$s"></noscript>' . "\n",
			esc_url( $href ),
			esc_attr( $media )
		);
		// phpcs:enable WordPress.WP.EnqueuedResources.NonEnqueuedStylesheet
	},
	10,
	4
);

/**
 * Mark the theme's entry script as a native ES module.
 */
add_filter(
	'script_loader_tag',
	function ( $tag, $handle, $src ) {
		if ( 'compass-main' !== $handle ) {
			return $tag;
		}
		return sprintf( '<script type="module" src="%s"></script>' . "\n", esc_url( $src ) );
	},
	10,
	3
);
