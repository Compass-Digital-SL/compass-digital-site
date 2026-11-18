<?php
/**
 * Compass Digital theme bootstrap.
 *
 * @package CompassDigital
 */

defined( 'ABSPATH' ) || exit;

define( 'COMPASS_VERSION', '0.1.0' );
define( 'COMPASS_DIR', get_template_directory() );
define( 'COMPASS_URI', get_template_directory_uri() );

/**
 * Load an include file from inc/.
 */
foreach ( array( 'setup', 'cleanup', 'assets', 'fonts' ) as $compass_inc ) {
	require COMPASS_DIR . "/inc/{$compass_inc}.php";
}
unset( $compass_inc );
