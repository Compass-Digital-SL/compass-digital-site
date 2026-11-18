<?php
/**
 * Theme setup: supports, menus, image sizes.
 *
 * @package CompassDigital
 */

defined( 'ABSPATH' ) || exit;

add_action(
	'after_setup_theme',
	function () {
		load_theme_textdomain( 'compass-digital', COMPASS_DIR . '/languages' );

		add_theme_support( 'title-tag' );
		add_theme_support( 'post-thumbnails' );
		add_theme_support( 'automatic-feed-links' );
		add_theme_support( 'html5', array( 'search-form', 'gallery', 'caption', 'style', 'script', 'navigation-widgets' ) );
		add_theme_support(
			'custom-logo',
			array(
				'height'      => 48,
				'width'       => 200,
				'flex-width'  => true,
				'flex-height' => true,
			)
		);
		add_theme_support( 'responsive-embeds' );

		register_nav_menus(
			array(
				'primary' => __( 'Primary', 'compass-digital' ),
				'footer'  => __( 'Footer', 'compass-digital' ),
			)
		);
	}
);

/**
 * Trim the default content width for embeds.
 */
add_action(
	'after_setup_theme',
	function () {
		$GLOBALS['content_width'] = 720;
	},
	0
);
