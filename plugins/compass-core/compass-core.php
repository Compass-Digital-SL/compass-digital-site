<?php
/**
 * Plugin Name:       Compass Core
 * Plugin URI:        https://compassdigital.lk
 * Description:       Content types and site settings for Compass Digital — Work (case studies), Testimonials, Quote Requests, the Industry & Service taxonomies, and a Site Settings page the theme reads. No page builder, no ACF: native meta boxes only.
 * Version:           1.0.0
 * Requires at least: 6.4
 * Requires PHP:      8.1
 * Author:            Compass Digital
 * Text Domain:       compass-core
 * Domain Path:       /languages
 *
 * Keeping this in a plugin (not the theme) means the client's data — case
 * studies, testimonials, quote requests, business details — survives a theme
 * change. The theme only ever reads it.
 *
 * @package CompassCore
 */

defined( 'ABSPATH' ) || exit;

define( 'COMPASS_CORE_VERSION', '1.0.0' );
define( 'COMPASS_CORE_FILE', __FILE__ );
define( 'COMPASS_CORE_DIR', plugin_dir_path( __FILE__ ) );

require_once COMPASS_CORE_DIR . 'includes/taxonomies.php';
require_once COMPASS_CORE_DIR . 'includes/post-types.php';
require_once COMPASS_CORE_DIR . 'includes/meta-boxes.php';
require_once COMPASS_CORE_DIR . 'includes/quote-requests.php';
require_once COMPASS_CORE_DIR . 'includes/settings.php';
require_once COMPASS_CORE_DIR . 'includes/quote-handler.php';

if ( defined( 'WP_CLI' ) && WP_CLI ) {
	require_once COMPASS_CORE_DIR . 'includes/class-compass-core-cli.php';
}

/**
 * Register post types and taxonomies, then flush rewrite rules once.
 *
 * Rewrite rules are expensive, so we only flush on (de)activation — not on
 * every load.
 */
function compass_core_activate() {
	compass_core_register_taxonomies();
	compass_core_register_post_types();
	flush_rewrite_rules();
}
register_activation_hook( __FILE__, 'compass_core_activate' );

/**
 * Clean up rewrite rules on deactivation.
 */
function compass_core_deactivate() {
	flush_rewrite_rules();
}
register_deactivation_hook( __FILE__, 'compass_core_deactivate' );

/**
 * Load the plugin text domain.
 */
function compass_core_load_textdomain() {
	load_plugin_textdomain( 'compass-core', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
}
add_action( 'init', 'compass_core_load_textdomain' );
