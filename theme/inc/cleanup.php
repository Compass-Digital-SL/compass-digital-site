<?php
/**
 * Strip WordPress front-end bloat.
 *
 * Removes emojis, oEmbed discovery, unused block-library CSS, jQuery on the
 * front end, generator tags, RSD/WLW manifest links, shortlink and extra feed
 * links, and REST/oEmbed <head> link tags we do not need.
 *
 * @package CompassDigital
 */

defined( 'ABSPATH' ) || exit;

add_action(
	'init',
	function () {
		// Emojis.
		remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
		remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
		remove_action( 'wp_print_styles', 'print_emoji_styles' );
		remove_action( 'admin_print_styles', 'print_emoji_styles' );
		remove_filter( 'the_content_feed', 'wp_staticize_emoji' );
		remove_filter( 'comment_text_rss', 'wp_staticize_emoji' );
		remove_filter( 'wp_mail', 'wp_staticize_emoji_for_email' );
		add_filter( 'emoji_svg_url', '__return_false' );
		add_filter(
			'tiny_mce_plugins',
			function ( $plugins ) {
				return is_array( $plugins ) ? array_diff( $plugins, array( 'wpemoji' ) ) : array();
			}
		);

		// Head cruft: generator, RSD, WLW manifest, shortlink, extra feeds, adjacent rel.
		remove_action( 'wp_head', 'wp_generator' );
		remove_action( 'wp_head', 'rsd_link' );
		remove_action( 'wp_head', 'wlwmanifest_link' );
		remove_action( 'wp_head', 'wp_shortlink_wp_head' );
		remove_action( 'wp_head', 'feed_links_extra', 3 );
		// Comments are off site-wide, so do not advertise an empty comments feed.
		add_filter( 'feed_links_show_comments_feed', '__return_false' );
		// No template renders comments, so refuse them too (closes the
		// wp-comments-post.php spam door).
		add_filter( 'comments_open', '__return_false', 20 );
		add_filter( 'pings_open', '__return_false', 20 );
		remove_action( 'wp_head', 'adjacent_posts_rel_link_wp_head', 10 );

		// REST API and oEmbed discovery links in <head> and headers.
		remove_action( 'wp_head', 'rest_output_link_wp_head' );
		remove_action( 'wp_head', 'wp_oembed_add_discovery_links' );
		remove_action( 'wp_head', 'wp_oembed_add_host_js' );
		remove_action( 'template_redirect', 'rest_output_link_header', 11 );
	}
);

// Drop the X-Pingback header.
add_filter(
	'wp_headers',
	function ( $headers ) {
		unset( $headers['X-Pingback'] );
		return $headers;
	}
);

// Remove the generator meta anywhere it is filtered in.
add_filter( 'the_generator', '__return_empty_string' );

/**
 * Dequeue unused block-library and classic-theme CSS on the front end.
 */
add_action(
	'wp_enqueue_scripts',
	function () {
		if ( is_admin() ) {
			return;
		}
		wp_dequeue_style( 'wp-block-library' );
		wp_dequeue_style( 'wp-block-library-theme' );
		wp_dequeue_style( 'wc-blocks-style' );
		wp_dequeue_style( 'global-styles' );
		wp_dequeue_style( 'classic-theme-styles' );
	},
	100
);

/**
 * Deregister jQuery on the front end (kept in admin).
 */
add_action(
	'wp_enqueue_scripts',
	function () {
		if ( is_admin() ) {
			return;
		}
		wp_deregister_script( 'jquery' );
		wp_deregister_script( 'jquery-core' );
		wp_deregister_script( 'jquery-migrate' );
	},
	100
);

// Disable self-pingbacks and keep emoji DNS prefetch out of resource hints.
add_filter(
	'wp_resource_hints',
	function ( $hints, $relation_type ) {
		if ( 'dns-prefetch' === $relation_type ) {
			$hints = array_filter(
				$hints,
				function ( $hint ) {
					return false === strpos( (string) $hint, 's.w.org' );
				}
			);
		}
		return $hints;
	},
	10,
	2
);
