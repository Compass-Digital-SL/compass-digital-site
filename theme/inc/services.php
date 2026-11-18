<?php
/**
 * Service-page head wiring: per-page document title, meta description, and the
 * shared JSON-LD helpers (Service, FAQPage, BreadcrumbList).
 *
 * These are intentionally scoped to the Services hub and service templates and
 * are easy to lift out: Task 16 (Technical SEO) generalises titles, metas and
 * schema site-wide, at which point `compass_services_head()` can be unhooked.
 *
 * @package CompassDigital
 */

defined( 'ABSPATH' ) || exit;

/**
 * Is the current request a single service page? Returns the service data entry
 * or false. A service page uses the shared `page-service.php` template and its
 * slug matches a key in compass_services_data().
 *
 * @return array<string,mixed>|false
 */
function compass_current_service() {
	if ( ! is_page() || ! is_page_template( 'page-service.php' ) ) {
		return false;
	}
	$slug = get_post_field( 'post_name', get_queried_object_id() );
	$data = compass_services_data();
	return isset( $data[ $slug ] ) ? $data[ $slug ] : false;
}

/**
 * Is the current request the Services hub?
 *
 * @return bool
 */
function compass_is_services_hub() {
	// The hub is auto-matched to page-services.php by slug, so it carries no
	// `_wp_page_template` meta — match on the page itself, not the template.
	return is_page() && 'services' === get_post_field( 'post_name', get_queried_object_id() ) && ! wp_get_post_parent_id( get_queried_object_id() );
}

/**
 * Override the document <title> for the hub and service pages with the
 * keyword-map titles from docs/SEO.md.
 *
 * @param string $title Default title.
 * @return string
 */
function compass_services_document_title( $title ) {
	$service = compass_current_service();
	if ( $service ) {
		return $service['title'];
	}
	if ( compass_is_services_hub() ) {
		return __( 'Our Services — Websites, SEO, POS & Software | Compass', 'compass-digital' );
	}
	return $title;
}
add_filter( 'pre_get_document_title', 'compass_services_document_title' );

/**
 * Print a meta description for the hub and service pages.
 */
function compass_services_head() {
	$description = '';

	$service = compass_current_service();
	if ( $service ) {
		$description = $service['meta'];
	} elseif ( compass_is_services_hub() ) {
		$description = __( 'Websites, SEO, POS systems, apps, ecommerce and custom software — built and supported by one Sri Lankan team. Quote on request.', 'compass-digital' );
	}

	if ( $description ) {
		printf( '<meta name="description" content="%s" />' . "\n", esc_attr( $description ) );
	}
}
add_action( 'wp_head', 'compass_services_head', 1 );

/**
 * Build a BreadcrumbList schema array from an ordered list of crumbs.
 *
 * @param array<int,array{name:string,url:string}> $crumbs Ordered crumbs; the last may omit url.
 * @return array<string,mixed>
 */
function compass_breadcrumb_schema( $crumbs ) {
	$items = array();
	foreach ( $crumbs as $i => $crumb ) {
		$item = array(
			'@type'    => 'ListItem',
			'position' => $i + 1,
			'name'     => $crumb['name'],
		);
		if ( ! empty( $crumb['url'] ) ) {
			$item['item'] = $crumb['url'];
		}
		$items[] = $item;
	}
	return array(
		'@context'        => 'https://schema.org',
		'@type'           => 'BreadcrumbList',
		'itemListElement' => $items,
	);
}

/**
 * Build the Service schema for one service entry.
 *
 * @param array<string,mixed> $service Service data entry.
 * @param string              $url     Canonical page URL.
 * @return array<string,mixed>
 */
function compass_service_schema( $service, $url ) {
	$offers = array();
	foreach ( (array) $service['offer_catalog'] as $name ) {
		$offers[] = array(
			'@type'       => 'Offer',
			'itemOffered' => array(
				'@type' => 'Service',
				'name'  => $name,
			),
		);
	}

	return array(
		'@context'        => 'https://schema.org',
		'@type'           => 'Service',
		'name'            => wp_strip_all_tags( $service['eyebrow'] ),
		'serviceType'     => $service['service_type'],
		'url'             => $url,
		'description'     => wp_strip_all_tags( $service['meta'] ),
		'provider'        => array(
			'@type' => 'Organization',
			'name'  => get_bloginfo( 'name' ),
			'url'   => home_url( '/' ),
		),
		'areaServed'      => array(
			'@type' => 'Country',
			'name'  => 'Sri Lanka',
		),
		'hasOfferCatalog' => array(
			'@type'           => 'OfferCatalog',
			'name'            => wp_strip_all_tags( $service['eyebrow'] ),
			'itemListElement' => $offers,
		),
	);
}

/**
 * Build the FAQPage schema from a list of {q,a} pairs.
 *
 * @param array<int,array{q:string,a:string}> $faqs FAQ pairs.
 * @return array<string,mixed>
 */
function compass_faq_schema( $faqs ) {
	$entities = array();
	foreach ( $faqs as $faq ) {
		$entities[] = array(
			'@type'          => 'Question',
			'name'           => wp_strip_all_tags( $faq['q'] ),
			'acceptedAnswer' => array(
				'@type' => 'Answer',
				'text'  => wp_strip_all_tags( $faq['a'] ),
			),
		);
	}
	return array(
		'@context'   => 'https://schema.org',
		'@type'      => 'FAQPage',
		'mainEntity' => $entities,
	);
}

/**
 * Print one JSON-LD script block.
 *
 * @param array<string,mixed> $schema Schema array.
 */
function compass_print_schema( $schema ) {
	printf(
		'<script type="application/ld+json">%s</script>' . "\n",
		wp_json_encode( $schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE )
	);
}

/**
 * URL to the quote form, pre-filled with a service name via the `service`
 * query arg (matches the `_compass_service` field; the front-end form reads it).
 *
 * @param string $service_name Readable service name.
 * @return string
 */
function compass_quote_url( $service_name = '' ) {
	$url = home_url( '/get-a-quote/' );
	if ( $service_name ) {
		$url = add_query_arg( 'service', rawurlencode( $service_name ), $url );
	}
	return $url;
}
