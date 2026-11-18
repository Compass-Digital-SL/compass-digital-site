<?php
/**
 * Work (portfolio) wiring: the /work/ archive filters, per-page title/meta, the
 * filter-chip data source, and the CreativeWork JSON-LD for a case study.
 *
 * The archive is the `work` post-type archive (has_archive => 'work'); single
 * case studies render through single-work.php. Filtering is done server-side in
 * `pre_get_posts` from the `service` / `industry` query args so a shared URL
 * (e.g. /work/?service=seo&industry=retail-shops) returns the same filtered
 * list with JavaScript off; work-filter.js enhances it to filter in place.
 *
 * Reuses the shared JSON-LD printer and BreadcrumbList helper from inc/services.php.
 *
 * @package CompassDigital
 */

defined( 'ABSPATH' ) || exit;

/**
 * The filter query arg for a taxonomy (a single term slug per facet).
 *
 * @param string $taxonomy 'service' or 'industry'.
 * @return string Sanitised slug, or '' when absent.
 */
function compass_work_active_filter( $taxonomy ) {
	// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only, shareable GET filter.
	if ( empty( $_GET[ $taxonomy ] ) ) {
		return '';
	}
	// phpcs:ignore WordPress.Security.NonceVerification.Recommended
	return sanitize_title( wp_unslash( $_GET[ $taxonomy ] ) );
}

/**
 * Filter the Work archive's main query from the service/industry GET args.
 *
 * @param WP_Query $query The query.
 */
function compass_work_filter_archive( $query ) {
	if ( is_admin() || ! $query->is_main_query() || ! $query->is_post_type_archive( 'work' ) ) {
		return;
	}

	$tax_query = array();
	foreach ( array( 'service', 'industry' ) as $taxonomy ) {
		$slug = compass_work_active_filter( $taxonomy );
		if ( $slug && term_exists( $slug, $taxonomy ) ) {
			$tax_query[] = array(
				'taxonomy' => $taxonomy,
				'field'    => 'slug',
				'terms'    => $slug,
			);
		}
	}

	if ( $tax_query ) {
		if ( count( $tax_query ) > 1 ) {
			$tax_query['relation'] = 'AND';
		}
		$query->set( 'tax_query', $tax_query ); // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query -- small, indexed archive query.
	}

	$query->set( 'posts_per_page', 12 );
}
add_action( 'pre_get_posts', 'compass_work_filter_archive' );

/**
 * Filter facets for the archive: each taxonomy with the terms actually used by
 * published case studies (empty terms are hidden). Ordered by name.
 *
 * @return array<string,array{label:string,terms:WP_Term[]}>
 */
function compass_work_filter_facets() {
	$facets = array();
	foreach ( array(
		'service'  => __( 'Service', 'compass-digital' ),
		'industry' => __( 'Industry', 'compass-digital' ),
	) as $taxonomy => $label ) {
		$terms = get_terms(
			array(
				'taxonomy'   => $taxonomy,
				'hide_empty' => true,
				'orderby'    => 'name',
			)
		);
		if ( ! is_wp_error( $terms ) && $terms ) {
			$facets[ $taxonomy ] = array(
				'label' => $label,
				'terms' => $terms,
			);
		}
	}
	return $facets;
}

/**
 * Document title for the Work archive and single case studies.
 *
 * @param string $title Default title.
 * @return string
 */
function compass_work_document_title( $title ) {
	if ( is_post_type_archive( 'work' ) ) {
		return __( 'Our Work — Case Studies | Compass Digital', 'compass-digital' );
	}
	if ( is_singular( 'work' ) ) {
		$client = get_post_meta( get_queried_object_id(), '_compass_client', true );
		$name   = $client ? $client : get_the_title();
		/* translators: %s: case study / client name. */
		return sprintf( __( '%s — Case Study | Compass Digital', 'compass-digital' ), $name );
	}
	return $title;
}
add_filter( 'pre_get_document_title', 'compass_work_document_title' );

/**
 * Meta description for the Work archive and single case studies.
 */
function compass_work_head() {
	$description = '';

	if ( is_post_type_archive( 'work' ) ) {
		$description = __( 'Real case studies from Compass Digital — websites, SEO, POS and software for Sri Lankan businesses, with the problem, the work and the results.', 'compass-digital' );
	} elseif ( is_singular( 'work' ) ) {
		$description = has_excerpt() ? get_the_excerpt() : wp_trim_words( wp_strip_all_tags( get_post_field( 'post_content', get_queried_object_id() ) ), 30 );
	}

	if ( $description ) {
		printf( '<meta name="description" content="%s" />' . "\n", esc_attr( $description ) );
	}
}
add_action( 'wp_head', 'compass_work_head', 1 );

/**
 * Build the CreativeWork schema for one case study.
 *
 * @param int    $post_id Case study ID.
 * @param string $url     Canonical URL.
 * @return array<string,mixed>
 */
function compass_work_schema( $post_id, $url ) {
	$client   = get_post_meta( $post_id, '_compass_client', true );
	$year     = get_post_meta( $post_id, '_compass_year', true );
	$quote    = get_post_meta( $post_id, '_compass_quote', true );
	$author   = get_post_meta( $post_id, '_compass_quote_author', true );
	$services = wp_get_post_terms( $post_id, 'service', array( 'fields' => 'names' ) );

	$schema = array(
		'@context' => 'https://schema.org',
		'@type'    => 'CreativeWork',
		'name'     => get_the_title( $post_id ),
		'headline' => get_the_title( $post_id ),
		'url'      => $url,
		'creator'  => array(
			'@type' => 'Organization',
			'name'  => get_bloginfo( 'name' ),
			'url'   => home_url( '/' ),
		),
	);

	$excerpt = get_the_excerpt( $post_id );
	if ( $excerpt ) {
		$schema['abstract'] = wp_strip_all_tags( $excerpt );
	}
	if ( $client ) {
		$schema['about'] = array(
			'@type' => 'Organization',
			'name'  => $client,
		);
	}
	if ( $year && is_numeric( $year ) ) {
		$schema['dateCreated'] = (string) absint( $year );
	}
	if ( ! is_wp_error( $services ) && $services ) {
		$schema['keywords'] = implode( ', ', $services );
	}
	if ( has_post_thumbnail( $post_id ) ) {
		$img = wp_get_attachment_image_url( get_post_thumbnail_id( $post_id ), 'large' );
		if ( $img ) {
			$schema['image'] = $img;
		}
	}
	if ( $quote ) {
		$review = array(
			'@type'        => 'Review',
			'reviewBody'   => wp_strip_all_tags( $quote ),
			'itemReviewed' => array(
				'@type' => 'Organization',
				'name'  => get_bloginfo( 'name' ),
			),
		);
		if ( $author ) {
			$review['author'] = array(
				'@type' => 'Person',
				'name'  => wp_strip_all_tags( $author ),
			);
		}
		$schema['review'] = $review;
	}

	return $schema;
}
