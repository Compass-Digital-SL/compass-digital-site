<?php
/**
 * Custom post types: Work, Testimonial, Quote Request.
 *
 * - Work (`work`): public case studies, has a single + archive URL.
 * - Testimonial (`testimonial`): public, surfaced by the theme (no own archive).
 * - Quote Request (`quote_request`): PRIVATE — no public URL, admin only. These
 *   hold enquiries from the quote form and must never be indexed or front-end
 *   viewable.
 *
 * @package CompassCore
 */

defined( 'ABSPATH' ) || exit;

/**
 * Register all three post types.
 */
function compass_core_register_post_types() {
	// Work — case studies.
	register_post_type(
		'work',
		array(
			'labels'        => array(
				'name'               => _x( 'Work', 'post type general name', 'compass-core' ),
				'singular_name'      => _x( 'Case Study', 'post type singular name', 'compass-core' ),
				'menu_name'          => __( 'Work', 'compass-core' ),
				'add_new'            => __( 'Add New', 'compass-core' ),
				'add_new_item'       => __( 'Add New Case Study', 'compass-core' ),
				'edit_item'          => __( 'Edit Case Study', 'compass-core' ),
				'new_item'           => __( 'New Case Study', 'compass-core' ),
				'view_item'          => __( 'View Case Study', 'compass-core' ),
				'search_items'       => __( 'Search Work', 'compass-core' ),
				'not_found'          => __( 'No case studies found', 'compass-core' ),
				'not_found_in_trash' => __( 'No case studies in Trash', 'compass-core' ),
			),
			'public'        => true,
			'has_archive'   => 'work',
			'menu_icon'     => 'dashicons-portfolio',
			'menu_position' => 20,
			'supports'      => array( 'title', 'editor', 'thumbnail', 'excerpt', 'revisions' ),
			'taxonomies'    => array( 'industry', 'service' ),
			'rewrite'       => array(
				'slug'       => 'work',
				'with_front' => false,
			),
			'show_in_rest'  => true,
		)
	);

	// Testimonial.
	register_post_type(
		'testimonial',
		array(
			'labels'              => array(
				'name'               => _x( 'Testimonials', 'post type general name', 'compass-core' ),
				'singular_name'      => _x( 'Testimonial', 'post type singular name', 'compass-core' ),
				'menu_name'          => __( 'Testimonials', 'compass-core' ),
				'add_new_item'       => __( 'Add New Testimonial', 'compass-core' ),
				'edit_item'          => __( 'Edit Testimonial', 'compass-core' ),
				'new_item'           => __( 'New Testimonial', 'compass-core' ),
				'search_items'       => __( 'Search Testimonials', 'compass-core' ),
				'not_found'          => __( 'No testimonials found', 'compass-core' ),
				'not_found_in_trash' => __( 'No testimonials in Trash', 'compass-core' ),
			),
			'public'              => false,
			'show_ui'             => true,
			'show_in_nav_menus'   => false,
			'has_archive'         => false,
			'exclude_from_search' => true,
			'menu_icon'           => 'dashicons-format-quote',
			'menu_position'       => 21,
			'supports'            => array( 'title', 'editor', 'thumbnail' ),
			'show_in_rest'        => true,
		)
	);

	// Quote Request — PRIVATE enquiries, no public URL.
	register_post_type(
		'quote_request',
		array(
			'labels'              => array(
				'name'               => _x( 'Quote Requests', 'post type general name', 'compass-core' ),
				'singular_name'      => _x( 'Quote Request', 'post type singular name', 'compass-core' ),
				'menu_name'          => __( 'Quote Requests', 'compass-core' ),
				'add_new_item'       => __( 'Add Quote Request', 'compass-core' ),
				'edit_item'          => __( 'Quote Request', 'compass-core' ),
				'search_items'       => __( 'Search Quote Requests', 'compass-core' ),
				'not_found'          => __( 'No quote requests found', 'compass-core' ),
				'not_found_in_trash' => __( 'No quote requests in Trash', 'compass-core' ),
			),
			'public'              => false,
			'publicly_queryable'  => false,
			'exclude_from_search' => true,
			'show_ui'             => true,
			'show_in_nav_menus'   => false,
			'show_in_rest'        => false,
			'has_archive'         => false,
			'rewrite'             => false,
			'query_var'           => false,
			'menu_icon'           => 'dashicons-email-alt',
			'menu_position'       => 22,
			'supports'            => array( 'title' ),
			'capability_type'     => 'post',
			'map_meta_cap'        => true,
		)
	);
}
add_action( 'init', 'compass_core_register_post_types' );
