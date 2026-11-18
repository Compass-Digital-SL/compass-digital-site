<?php
/**
 * Taxonomies: Industry and Service.
 *
 * Both classify Work case studies (and are available for reuse by content
 * tasks). Public, so archive pages resolve once templates land.
 *
 * @package CompassCore
 */

defined( 'ABSPATH' ) || exit;

/**
 * Register the Industry and Service taxonomies.
 */
function compass_core_register_taxonomies() {
	register_taxonomy(
		'industry',
		array( 'work' ),
		array(
			'labels'            => array(
				'name'          => _x( 'Industries', 'taxonomy general name', 'compass-core' ),
				'singular_name' => _x( 'Industry', 'taxonomy singular name', 'compass-core' ),
				'search_items'  => __( 'Search Industries', 'compass-core' ),
				'all_items'     => __( 'All Industries', 'compass-core' ),
				'edit_item'     => __( 'Edit Industry', 'compass-core' ),
				'update_item'   => __( 'Update Industry', 'compass-core' ),
				'add_new_item'  => __( 'Add New Industry', 'compass-core' ),
				'new_item_name' => __( 'New Industry Name', 'compass-core' ),
				'menu_name'     => __( 'Industries', 'compass-core' ),
			),
			'hierarchical'      => true,
			'public'            => true,
			'show_admin_column' => true,
			'show_in_rest'      => true,
			'rewrite'           => array( 'slug' => 'industry' ),
		)
	);

	register_taxonomy(
		'service',
		array( 'work' ),
		array(
			'labels'            => array(
				'name'          => _x( 'Services', 'taxonomy general name', 'compass-core' ),
				'singular_name' => _x( 'Service', 'taxonomy singular name', 'compass-core' ),
				'search_items'  => __( 'Search Services', 'compass-core' ),
				'all_items'     => __( 'All Services', 'compass-core' ),
				'edit_item'     => __( 'Edit Service', 'compass-core' ),
				'update_item'   => __( 'Update Service', 'compass-core' ),
				'add_new_item'  => __( 'Add New Service', 'compass-core' ),
				'new_item_name' => __( 'New Service Name', 'compass-core' ),
				'menu_name'     => __( 'Services', 'compass-core' ),
			),
			'hierarchical'      => true,
			'public'            => true,
			'show_admin_column' => true,
			'show_in_rest'      => true,
			'rewrite'           => array( 'slug' => 'service' ),
		)
	);
}
add_action( 'init', 'compass_core_register_taxonomies' );
