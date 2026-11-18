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

function compass_dist_dir() {
	return COMPASS_DIR . '/assets/dist';
}

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

add_action(
	'wp_enqueue_scripts',
	function () {
		// Static, un-hashed fonts stylesheet (loaded in dev and prod).
		$fonts_css = COMPASS_DIR . '/assets/fonts/fonts.css';
		if ( is_readable( $fonts_css ) ) {
			wp_enqueue_style(
				'compass-fonts',
				COMPASS_URI . '/assets/fonts/fonts.css',
				array(),
				(string) filemtime( $fonts_css )
			);
		}

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
