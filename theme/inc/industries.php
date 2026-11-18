<?php
/**
 * Industry-page head wiring: per-page document title and meta description for
 * the Industries hub and the five industry pages.
 *
 * Scoped to these pages and easy to lift out: Task 16 (Technical SEO)
 * generalises titles, metas and schema site-wide, at which point
 * `compass_industries_head()` and the title filter can be unhooked. The
 * JSON-LD helpers (FAQPage, BreadcrumbList) are shared with the service pages
 * and live in inc/services.php.
 *
 * @package CompassDigital
 */

defined( 'ABSPATH' ) || exit;

/**
 * Is the current request a single industry page? Returns the industry data
 * entry or false. An industry page uses the shared `page-industry.php` template
 * and its slug matches a key in compass_industries_data().
 *
 * @return array<string,mixed>|false
 */
function compass_current_industry() {
	if ( ! is_page() || ! is_page_template( 'page-industry.php' ) ) {
		return false;
	}
	$slug = get_post_field( 'post_name', get_queried_object_id() );
	$data = compass_industries_data();
	return isset( $data[ $slug ] ) ? $data[ $slug ] : false;
}

/**
 * Is the current request the Industries hub?
 *
 * @return bool
 */
function compass_is_industries_hub() {
	// The hub is auto-matched to page-industries.php by slug, so it carries no
	// `_wp_page_template` meta — match on the page itself, not the template.
	return is_page() && 'industries' === get_post_field( 'post_name', get_queried_object_id() ) && ! wp_get_post_parent_id( get_queried_object_id() );
}

/**
 * Override the document <title> for the hub and industry pages with the
 * keyword-map titles from docs/SEO.md.
 *
 * @param string $title Default title.
 * @return string
 */
function compass_industries_document_title( $title ) {
	$industry = compass_current_industry();
	if ( $industry ) {
		return $industry['title'];
	}
	if ( compass_is_industries_hub() ) {
		return __( 'Industries We Serve — Sri Lanka | Compass Digital', 'compass-digital' );
	}
	return $title;
}
add_filter( 'pre_get_document_title', 'compass_industries_document_title' );

/**
 * Print a meta description for the hub and industry pages.
 */
function compass_industries_head() {
	$description = '';

	$industry = compass_current_industry();
	if ( $industry ) {
		$description = $industry['meta'];
	} elseif ( compass_is_industries_hub() ) {
		$description = __( 'Websites, POS, ecommerce and software built for Sri Lankan retail, restaurants, travel, ecommerce brands and SMEs. Quote on request.', 'compass-digital' );
	}

	if ( $description ) {
		printf( '<meta name="description" content="%s" />' . "\n", esc_attr( $description ) );
	}
}
add_action( 'wp_head', 'compass_industries_head', 1 );
