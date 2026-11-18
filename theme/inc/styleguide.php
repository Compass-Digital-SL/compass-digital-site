<?php
/**
 * Hidden /styleguide/ page: keep it out of search and sitemaps.
 *
 * The page uses the "Styleguide" page template (page-styleguide.php). Wherever
 * that template is active we force noindex,nofollow and drop the page from the
 * core sitemap. It is never linked from nav or footer.
 *
 * @package CompassDigital
 */

defined( 'ABSPATH' ) || exit;

/**
 * Is the current request the styleguide page template?
 */
function compass_is_styleguide() {
	return is_page() && is_page_template( 'page-styleguide.php' );
}

/**
 * Force noindex,nofollow on the styleguide (core robots API, WP 5.7+).
 */
add_filter(
	'wp_robots',
	function ( $robots ) {
		if ( compass_is_styleguide() ) {
			$robots['noindex']  = true;
			$robots['nofollow'] = true;
			unset( $robots['max-image-preview'] );
		}
		return $robots;
	},
	20
);

/**
 * Exclude styleguide pages from the core WordPress sitemap.
 */
add_filter(
	'wp_sitemaps_posts_query_args',
	function ( $args, $post_type ) {
		if ( 'page' !== $post_type ) {
			return $args;
		}
		$ids = get_posts(
			array(
				'post_type'      => 'page',
				'fields'         => 'ids',
				'posts_per_page' => -1,
				'meta_key'       => '_wp_page_template',
				'meta_value'     => 'page-styleguide.php',
				'no_found_rows'  => true,
			)
		);
		if ( $ids ) {
			$args['post__not_in'] = array_merge( isset( $args['post__not_in'] ) ? $args['post__not_in'] : array(), $ids );
		}
		return $args;
	},
	10,
	2
);
