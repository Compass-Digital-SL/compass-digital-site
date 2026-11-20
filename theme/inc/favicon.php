<?php
/**
 * Browser tab icon (favicon) and home-screen icon.
 *
 * The compass mark sits on a white disc with an ink ring, so it stays clear on
 * both light and dark browser tab bars. Files live in assets/brand/. These
 * replace any Customizer Site Icon, so the tab always shows the compass mark
 * (a full wordmark is unreadable at 16px).
 *
 * @package CompassDigital
 */

defined( 'ABSPATH' ) || exit;

add_action(
	'init',
	function () {
		remove_action( 'wp_head', 'wp_site_icon', 99 );
		remove_action( 'login_head', 'wp_site_icon', 99 );
	}
);

/**
 * Print the icon links.
 */
function compass_favicon_links() {
	$base = COMPASS_URI . '/assets/brand/';
	printf( "<link rel=\"icon\" href=\"%s\" sizes=\"32x32\">\n", esc_url( $base . 'favicon.ico' ) );
	printf( "<link rel=\"icon\" href=\"%s\" type=\"image/svg+xml\">\n", esc_url( $base . 'favicon.svg' ) );
	printf( "<link rel=\"apple-touch-icon\" href=\"%s\">\n", esc_url( $base . 'apple-touch-icon.png' ) );
}
add_action( 'wp_head', 'compass_favicon_links', 2 );
add_action( 'login_head', 'compass_favicon_links' );

/**
 * Serve our icon at /favicon.ico (WordPress otherwise redirects to the Site Icon
 * or its own logo).
 */
add_action(
	'do_faviconico',
	function () {
		header( 'Content-Type: image/x-icon' );
		header( 'Cache-Control: public, max-age=604800' );
		readfile( COMPASS_DIR . '/assets/brand/favicon.ico' ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_readfile -- Static theme file.
		exit;
	},
	1
);
