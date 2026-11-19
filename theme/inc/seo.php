<?php
/**
 * Site-wide technical SEO (Task 16).
 *
 * One place that owns the document head's SEO output so there is a single source
 * of truth and no duplicate tags:
 *
 *   - Meta description (centralised; the per-section printers in services.php,
 *     industries.php, insights.php, work.php and company.php are unhooked here
 *     and their copy is resolved by compass_meta_description()).
 *   - Canonical URL.
 *   - Open Graph and X (Twitter) cards, with generated share images.
 *   - robots directives: staging and local output noindex, production does not;
 *     thin and utility pages are noindex everywhere.
 *   - robots.txt body and the XML sitemap trimming (users, private types,
 *     utility pages).
 *   - 301 redirect map and trailing-slash consistency.
 *
 * Titles stay with the per-section `pre_get_document_title` filters (the theme
 * uses `add_theme_support( 'title-tag' )`); this file reads the finished title
 * via wp_get_document_title() for the og:title / twitter:title.
 *
 * Rank Math: when the plugin is used its Titles & Meta, Open Graph and Schema
 * modules are turned off (see docs/seo/rank-math-settings.json) because the
 * theme owns them, so the two never emit duplicate tags. Everything here is the
 * theme's own output and carries no dependency on the plugin.
 *
 * @package CompassDigital
 */

defined( 'ABSPATH' ) || exit;

/**
 * Absolute URL to a generated share image in the theme's og/ folder.
 *
 * @param string $name File basename without extension.
 * @return string
 */
function compass_og_image_url( $name ) {
	return COMPASS_URI . '/assets/og/' . $name . '.png';
}

/**
 * The contact page's meta description (keyword map, docs/SEO.md §3).
 *
 * @return string
 */
function compass_is_contact_page() {
	return is_page() && is_page_template( 'page-contact.php' );
}

/**
 * Resolve the meta description for the current request.
 *
 * Mirrors the copy that the per-section head printers used before they were
 * centralised here, then falls back to an excerpt and finally the site tagline,
 * so every URL has a description. Returned raw; escape at the point of output.
 *
 * @return string
 */
function compass_meta_description() {
	$description = '';
	$service     = function_exists( 'compass_current_service' ) ? compass_current_service() : false;
	$industry    = function_exists( 'compass_current_industry' ) ? compass_current_industry() : false;
	$company_tpl = function_exists( 'compass_current_company_template' ) ? compass_current_company_template() : '';

	if ( is_front_page() ) {
		$description = __( 'Sri Lankan web design, SEO and POS systems that are clear, fast and built around your business. Find your way forward. Request a free quote.', 'compass-digital' );
	} elseif ( $service ) {
		$description = $service['meta'];
	} elseif ( function_exists( 'compass_is_services_hub' ) && compass_is_services_hub() ) {
		$description = __( 'Websites, SEO, POS systems, apps, ecommerce and custom software — built and supported by one Sri Lankan team. Quote on request.', 'compass-digital' );
	} elseif ( $industry ) {
		$description = $industry['meta'];
	} elseif ( function_exists( 'compass_is_industries_hub' ) && compass_is_industries_hub() ) {
		$description = __( 'Websites, POS, ecommerce and software built for Sri Lankan retail, restaurants, travel, ecommerce brands and SMEs. Quote on request.', 'compass-digital' );
	} elseif ( $company_tpl ) {
		$meta        = compass_company_meta();
		$description = isset( $meta[ $company_tpl ] ) ? $meta[ $company_tpl ]['meta'] : '';
	} elseif ( compass_is_contact_page() ) {
		$description = __( 'Talk to Compass Digital about your website, SEO, POS or software. Based in Colombo, serving all of Sri Lanka. Request a free quote.', 'compass-digital' );
	} elseif ( is_home() ) {
		$description = __( 'Practical, jargon-free advice on websites, SEO, POS and selling online — written for Sri Lankan business owners by Compass Digital.', 'compass-digital' );
	} elseif ( is_category() || is_tag() ) {
		$description = wp_strip_all_tags( get_the_archive_description() );
		if ( ! $description ) {
			/* translators: %s: term name. */
			$description = sprintf( __( 'Insights on %s from Compass Digital — plain-language advice for Sri Lankan businesses.', 'compass-digital' ), wp_strip_all_tags( get_the_archive_title() ) );
		}
	} elseif ( is_post_type_archive( 'work' ) ) {
		$description = __( 'Real case studies from Compass Digital — websites, SEO, POS and software for Sri Lankan businesses, with the problem, the work and the results.', 'compass-digital' );
	} elseif ( is_singular( array( 'post', 'work' ) ) ) {
		$id          = get_queried_object_id();
		$description = has_excerpt( $id ) ? get_the_excerpt( $id ) : wp_trim_words( wp_strip_all_tags( get_post_field( 'post_content', $id ) ), 30 );
	} elseif ( is_singular() ) {
		$id          = get_queried_object_id();
		$description = has_excerpt( $id ) ? get_the_excerpt( $id ) : '';
	}

	if ( ! $description ) {
		$description = get_bloginfo( 'description', 'display' );
	}

	/**
	 * Filter the resolved meta description for the current request.
	 *
	 * @param string $description Resolved description (raw).
	 */
	return trim( (string) apply_filters( 'compass_meta_description', $description ) );
}

/**
 * Resolve the canonical URL for the current request, including pagination.
 *
 * @return string
 */
function compass_canonical_url() {
	$url = '';

	if ( is_front_page() ) {
		$url = home_url( '/' );
	} elseif ( is_singular() ) {
		$url = wp_get_canonical_url( get_queried_object_id() );
	} elseif ( is_home() ) {
		$page_for_posts = (int) get_option( 'page_for_posts' );
		$url            = $page_for_posts ? get_permalink( $page_for_posts ) : home_url( '/' );
	} elseif ( is_post_type_archive() ) {
		$url = get_post_type_archive_link( get_query_var( 'post_type' ) );
	} elseif ( is_category() || is_tag() || is_tax() ) {
		$term = get_queried_object();
		if ( $term && ! is_wp_error( $term ) ) {
			$term_link = get_term_link( $term );
			$url       = is_wp_error( $term_link ) ? '' : $term_link;
		}
	} elseif ( is_author() ) {
		$url = get_author_posts_url( get_queried_object_id() );
	} elseif ( is_search() ) {
		$url = get_search_link();
	}

	if ( $url && ! is_singular() ) {
		$paged = (int) get_query_var( 'paged' );
		if ( $paged > 1 ) {
			$url = user_trailingslashit( trailingslashit( $url ) . 'page/' . $paged );
		}
	}

	/**
	 * Filter the canonical URL for the current request.
	 *
	 * @param string $url Canonical URL (may be empty).
	 */
	return (string) apply_filters( 'compass_canonical_url', $url );
}

/**
 * Pick a generated share image for the current request.
 *
 * Posts and case studies with a featured image use it; everything else maps to
 * a section image, falling back to the default. Returns url + dimensions.
 *
 * @return array{url:string,width:int,height:int,alt:string}
 */
function compass_share_image() {
	$name = 'og-default';
	$alt  = get_bloginfo( 'name' );

	if ( is_singular( array( 'post', 'work' ) ) && has_post_thumbnail() ) {
		$id  = get_post_thumbnail_id();
		$src = wp_get_attachment_image_src( $id, 'full' );
		if ( $src ) {
			$alt_text = get_post_meta( $id, '_wp_attachment_image_alt', true );
			return array(
				'url'    => $src[0],
				'width'  => (int) $src[1],
				'height' => (int) $src[2],
				'alt'    => $alt_text ? $alt_text : get_the_title(),
			);
		}
	}

	if ( is_front_page() ) {
		$name = 'og-home';
	} elseif ( ( function_exists( 'compass_current_service' ) && compass_current_service() ) || ( function_exists( 'compass_is_services_hub' ) && compass_is_services_hub() ) ) {
		$name = 'og-services';
	} elseif ( ( function_exists( 'compass_current_industry' ) && compass_current_industry() ) || ( function_exists( 'compass_is_industries_hub' ) && compass_is_industries_hub() ) ) {
		$name = 'og-industries';
	} elseif ( is_post_type_archive( 'work' ) || is_singular( 'work' ) ) {
		$name = 'og-work';
	} elseif ( is_home() || is_singular( 'post' ) || is_category() || is_tag() ) {
		$name = 'og-insights';
	} elseif ( compass_is_contact_page() ) {
		$name = 'og-contact';
	} elseif ( function_exists( 'compass_current_company_template' ) && compass_current_company_template() ) {
		$name = 'og-about';
	}

	return array(
		'url'    => compass_og_image_url( $name ),
		'width'  => 1200,
		'height' => 630,
		'alt'    => $alt,
	);
}

/**
 * The Open Graph object type for the current request.
 *
 * @return string
 */
function compass_og_type() {
	if ( is_singular( array( 'post', 'work' ) ) ) {
		return 'article';
	}
	return 'website';
}

/**
 * Print the centralised SEO head: description, canonical, Open Graph, X cards.
 *
 * Runs early (priority 1) so the description sits near the top of <head>.
 */
function compass_seo_head() {
	$description = compass_meta_description();
	$canonical   = compass_canonical_url();
	$title       = wp_get_document_title();
	$image       = compass_share_image();
	$site_name   = get_bloginfo( 'name' );

	if ( $description ) {
		printf( '<meta name="description" content="%s" />' . "\n", esc_attr( $description ) );
	}
	if ( $canonical ) {
		printf( '<link rel="canonical" href="%s" />' . "\n", esc_url( $canonical ) );
	}

	// Open Graph.
	printf( '<meta property="og:type" content="%s" />' . "\n", esc_attr( compass_og_type() ) );
	printf( '<meta property="og:site_name" content="%s" />' . "\n", esc_attr( $site_name ) );
	printf( '<meta property="og:locale" content="%s" />' . "\n", esc_attr( 'en_US' ) );
	printf( '<meta property="og:title" content="%s" />' . "\n", esc_attr( $title ) );
	if ( $description ) {
		printf( '<meta property="og:description" content="%s" />' . "\n", esc_attr( $description ) );
	}
	if ( $canonical ) {
		printf( '<meta property="og:url" content="%s" />' . "\n", esc_url( $canonical ) );
	}
	printf( '<meta property="og:image" content="%s" />' . "\n", esc_url( $image['url'] ) );
	printf( '<meta property="og:image:width" content="%d" />' . "\n", (int) $image['width'] );
	printf( '<meta property="og:image:height" content="%d" />' . "\n", (int) $image['height'] );
	printf( '<meta property="og:image:alt" content="%s" />' . "\n", esc_attr( $image['alt'] ) );

	if ( is_singular( array( 'post', 'work' ) ) ) {
		$published = get_the_date( DATE_W3C );
		$modified  = get_the_modified_date( DATE_W3C );
		printf( '<meta property="article:published_time" content="%s" />' . "\n", esc_attr( $published ) );
		printf( '<meta property="article:modified_time" content="%s" />' . "\n", esc_attr( $modified ) );
	}

	// X (Twitter) card.
	printf( '<meta name="twitter:card" content="%s" />' . "\n", esc_attr( 'summary_large_image' ) );
	printf( '<meta name="twitter:title" content="%s" />' . "\n", esc_attr( $title ) );
	if ( $description ) {
		printf( '<meta name="twitter:description" content="%s" />' . "\n", esc_attr( $description ) );
	}
	printf( '<meta name="twitter:image" content="%s" />' . "\n", esc_url( $image['url'] ) );
	printf( '<meta name="twitter:image:alt" content="%s" />' . "\n", esc_attr( $image['alt'] ) );
}
add_action( 'wp_head', 'compass_seo_head', 1 );

/**
 * Unhook the per-section meta-description printers now that the description is
 * resolved centrally in compass_meta_description(). The title filters stay.
 */
add_action(
	'wp_loaded',
	function () {
		foreach ( array( 'compass_services_head', 'compass_industries_head', 'compass_insights_head', 'compass_work_head', 'compass_company_head' ) as $hook ) {
			remove_action( 'wp_head', $hook, 1 );
		}
	}
);

/**
 * Robots directives via the core wp_robots API (WP 5.7+).
 *
 * Staging and local output noindex,nofollow site-wide so non-production copies
 * never rank; production gets index + large previews. Thin and utility pages
 * (search, 404, author and date archives, paginated subpages) are noindex in
 * every environment. Pages that opt out on their own (styleguide, thank-you)
 * keep doing so through their own filters.
 *
 * @param array $robots Robots directives.
 * @return array
 */
function compass_seo_robots( $robots ) {
	if ( 'production' !== wp_get_environment_type() ) {
		$robots['noindex']  = true;
		$robots['nofollow'] = true;
		unset( $robots['max-image-preview'], $robots['max-snippet'], $robots['max-video-preview'] );
		return $robots;
	}

	if ( is_search() || is_404() || is_author() || is_date() || is_paged() ) {
		$robots['noindex'] = true;
		$robots['follow']  = true;
		unset( $robots['index'] );
	}

	if ( empty( $robots['noindex'] ) ) {
		$robots['max-image-preview'] = 'large';
		$robots['max-snippet']       = -1;
		$robots['max-video-preview'] = -1;
	}

	return $robots;
}
add_filter( 'wp_robots', 'compass_seo_robots', 20 );

/**
 * Robots.txt body.
 *
 * On production: allow crawling, block only wp-admin (bar admin-ajax) and the
 * quote/thank-you funnels, and advertise the sitemap. On any non-production
 * environment: disallow everything, so staging and local are never indexed.
 *
 * @param string $output    Default robots.txt.
 * @param bool   $is_public Whether the site is set to public.
 * @return string
 */
function compass_robots_txt( $output, $is_public ) {
	if ( ! $is_public || 'production' !== wp_get_environment_type() ) {
		return "User-agent: *\nDisallow: /\n";
	}

	$lines = array(
		'User-agent: *',
		'Disallow: /wp-admin/',
		'Allow: /wp-admin/admin-ajax.php',
		'Disallow: /get-a-quote/',
		'Disallow: /thank-you/',
		'Disallow: /?s=',
		'Disallow: /search/',
		'',
		'Sitemap: ' . home_url( '/wp-sitemap.xml' ),
	);

	return implode( "\n", $lines ) . "\n";
}
add_filter( 'robots_txt', 'compass_robots_txt', 10, 2 );

/**
 * Trim the core XML sitemap: drop the author (users) sitemap by returning false
 * for that provider. Utility pages (styleguide, thank-you) remove themselves in
 * their own includes, and the private quote_request type is handled below.
 *
 * @param WP_Sitemaps_Provider $provider Provider instance.
 * @param string               $name     Provider name.
 * @return WP_Sitemaps_Provider|false
 */
function compass_sitemap_provider_filter( $provider, $name ) {
	return 'users' === $name ? false : $provider;
}
add_filter( 'wp_sitemaps_add_provider', 'compass_sitemap_provider_filter', 10, 2 );

/**
 * Keep non-public types (quote requests) out of the sitemap, belt-and-braces.
 *
 * @param array $post_types Post type objects keyed by name.
 * @return array
 */
function compass_sitemap_post_types( $post_types ) {
	unset( $post_types['quote_request'] );
	return $post_types;
}
add_filter( 'wp_sitemaps_post_types', 'compass_sitemap_post_types' );

/**
 * The 301 redirect map: old path (path only, leading slash) => new URL or path.
 *
 * Empty by default; the owner adds entries as URLs change, or a must-use plugin
 * filters `compass_redirects`. Keeping it here means redirects live in the repo
 * and survive a plugin being toggled off.
 *
 * @return array<string,string>
 */
function compass_redirects() {
	$redirects = array(
		// Example — superseded service slug:
		// '/services/seo-services/' => '/services/seo/',.
	);

	/**
	 * Filter the redirect map.
	 *
	 * @param array<string,string> $redirects Map of old path => new path/URL.
	 */
	return (array) apply_filters( 'compass_redirects', $redirects );
}

/**
 * Apply the 301 redirect map early in the request.
 */
function compass_do_redirects() {
	if ( is_admin() || wp_doing_ajax() || wp_doing_cron() ) {
		return;
	}

	$redirects = compass_redirects();
	if ( ! $redirects ) {
		return;
	}

	$request = isset( $_SERVER['REQUEST_URI'] ) ? esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '';
	$path    = '/' . trim( (string) wp_parse_url( $request, PHP_URL_PATH ), '/' ) . '/';
	$path    = '//' === $path ? '/' : $path;

	if ( isset( $redirects[ $path ] ) ) {
		$target = $redirects[ $path ];
		$target = preg_match( '#^https?://#', $target ) ? $target : home_url( $target );
		wp_safe_redirect( $target, 301 );
		exit;
	}
}
add_action( 'template_redirect', 'compass_do_redirects', 0 );

/*
 * Site-wide organisation schema (Organization, WebSite, LocalBusiness).
 *
 * Per-page schema (Service, Article, CreativeWork, FAQPage, BreadcrumbList) is
 * printed by the templates. These three identity nodes are emitted once, in the
 * head, on the pages where they belong: Organization + WebSite + LocalBusiness
 * on the front page, and LocalBusiness on the Contact page. Stable @id anchors
 * let the per-page nodes reference them without repeating the data.
 */

/**
 * Non-empty social profile URLs from the contact details, for sameAs.
 *
 * @return array<int,string>
 */
function compass_social_profiles() {
	$details = function_exists( 'compass_contact_details' ) ? compass_contact_details() : array();
	$social  = isset( $details['social'] ) ? (array) $details['social'] : array();
	return array_values( array_filter( array_map( 'strval', $social ) ) );
}

/**
 * The Organization identity node.
 *
 * @return array<string,mixed>
 */
function compass_organization_schema() {
	$details = function_exists( 'compass_contact_details' ) ? compass_contact_details() : array();

	$org = array(
		'@context' => 'https://schema.org',
		'@type'    => 'Organization',
		'@id'      => home_url( '/#organization' ),
		'name'     => get_bloginfo( 'name' ),
		'url'      => home_url( '/' ),
		'image'    => compass_og_image_url( 'og-home' ),
	);

	$logo = get_theme_mod( 'custom_logo' ) ? wp_get_attachment_image_url( (int) get_theme_mod( 'custom_logo' ), 'full' ) : '';
	if ( $logo ) {
		$org['logo'] = $logo;
	}

	$contact_point = array(
		'@type'       => 'ContactPoint',
		'contactType' => 'customer service',
	);
	if ( ! empty( $details['phone'] ) ) {
		$contact_point['telephone'] = $details['phone'];
	}
	if ( ! empty( $details['email'] ) ) {
		$contact_point['email'] = $details['email'];
	}
	$contact_point['areaServed']        = 'LK';
	$contact_point['availableLanguage'] = array( 'en', 'si', 'ta' );
	$org['contactPoint']                = $contact_point;

	$social = compass_social_profiles();
	if ( $social ) {
		$org['sameAs'] = $social;
	}

	return $org;
}

/**
 * The WebSite identity node, with a SearchAction for the built-in search.
 *
 * @return array<string,mixed>
 */
function compass_website_schema() {
	return array(
		'@context'        => 'https://schema.org',
		'@type'           => 'WebSite',
		'@id'             => home_url( '/#website' ),
		'name'            => get_bloginfo( 'name' ),
		'url'             => home_url( '/' ),
		'publisher'       => array( '@id' => home_url( '/#organization' ) ),
		'inLanguage'      => 'en',
		'potentialAction' => array(
			'@type'       => 'SearchAction',
			'target'      => array(
				'@type'       => 'EntryPoint',
				'urlTemplate' => home_url( '/?s={search_term_string}' ),
			),
			'query-input' => 'required name=search_term_string',
		),
	);
}

/**
 * The LocalBusiness / ProfessionalService identity node.
 *
 * Address is Colombo; area served is all of Sri Lanka. Geo coordinates and
 * precise opening hours are finalised in Task 17 (Local SEO).
 *
 * @return array<string,mixed>
 */
function compass_localbusiness_schema() {
	$details = function_exists( 'compass_contact_details' ) ? compass_contact_details() : array();

	$node = array(
		'@context'           => 'https://schema.org',
		'@type'              => 'ProfessionalService',
		'@id'                => home_url( '/#localbusiness' ),
		'name'               => get_bloginfo( 'name' ),
		'url'                => home_url( '/' ),
		'image'              => compass_og_image_url( 'og-home' ),
		'parentOrganization' => array( '@id' => home_url( '/#organization' ) ),
		'address'            => array(
			'@type'           => 'PostalAddress',
			'addressLocality' => 'Colombo',
			'addressCountry'  => 'LK',
		),
		'areaServed'         => array(
			'@type' => 'Country',
			'name'  => 'Sri Lanka',
		),
		'priceRange'         => '$$',
	);

	if ( ! empty( $details['phone'] ) ) {
		$node['telephone'] = $details['phone'];
	}
	if ( ! empty( $details['email'] ) ) {
		$node['email'] = $details['email'];
	}
	$social = compass_social_profiles();
	if ( $social ) {
		$node['sameAs'] = $social;
	}

	return $node;
}

/**
 * Print the identity schema in the head where it belongs.
 */
function compass_identity_schema_head() {
	if ( ! function_exists( 'compass_print_schema' ) ) {
		return;
	}

	if ( is_front_page() ) {
		compass_print_schema( compass_organization_schema() );
		compass_print_schema( compass_website_schema() );
		compass_print_schema( compass_localbusiness_schema() );
	} elseif ( compass_is_contact_page() ) {
		compass_print_schema( compass_localbusiness_schema() );
	}
}
add_action( 'wp_head', 'compass_identity_schema_head', 5 );
