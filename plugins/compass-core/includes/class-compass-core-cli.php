<?php
/**
 * WP-CLI: `wp compass seed`.
 *
 * Seeds clearly-labelled placeholder content so the theme has something to
 * render in development. Every seeded item is marked with the `_compass_placeholder`
 * meta flag and a "[Sample]" title, so it is easy to spot and purge before launch.
 *
 * @package CompassCore
 */

defined( 'ABSPATH' ) || exit;

/**
 * Compass Digital sample-content commands.
 */
class Compass_Core_CLI {

	/**
	 * Seed sample Work and Testimonials.
	 *
	 * ## OPTIONS
	 *
	 * [--fresh]
	 * : Delete existing placeholder items first, then reseed.
	 *
	 * ## EXAMPLES
	 *
	 *     wp compass seed
	 *     wp compass seed --fresh
	 *
	 * @param array $args       Positional args (unused).
	 * @param array $assoc_args Flags.
	 */
	public function seed( $args, $assoc_args ) {
		$fresh = isset( $assoc_args['fresh'] );

		if ( $fresh ) {
			$this->purge();
		}

		compass_core_register_taxonomies();
		compass_core_register_post_types();

		$this->seed_work();
		$this->seed_testimonials();
		$this->seed_service_pages();
		$this->seed_industry_pages();

		WP_CLI::success( 'Seeded Work, Testimonials, Service and Industry pages. Remove placeholders with: wp compass seed --fresh (before next seed) or delete the "[Sample]" items.' );
	}

	/**
	 * Delete all placeholder items.
	 */
	private function purge() {
		$ids = get_posts(
			array(
				'post_type'      => array( 'work', 'testimonial' ),
				'post_status'    => 'any',
				'posts_per_page' => -1,
				'fields'         => 'ids',
				'meta_key'       => '_compass_placeholder', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key -- CLI, one-off.
				'meta_value'     => '1', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value -- CLI, one-off.
			)
		);
		foreach ( $ids as $id ) {
			wp_delete_post( $id, true );
		}
		WP_CLI::log( sprintf( 'Removed %d placeholder item(s).', count( $ids ) ) );
	}

	/**
	 * Ensure a term exists and return its ID.
	 *
	 * @param string $name     Term name.
	 * @param string $taxonomy Taxonomy.
	 * @return int
	 */
	private function term( $name, $taxonomy ) {
		$existing = term_exists( $name, $taxonomy );
		if ( $existing ) {
			return (int) $existing['term_id'];
		}
		$created = wp_insert_term( $name, $taxonomy );
		return is_wp_error( $created ) ? 0 : (int) $created['term_id'];
	}

	/**
	 * Find a placeholder post by title, or 0.
	 *
	 * @param string $title     Title.
	 * @param string $post_type Post type.
	 * @return int
	 */
	private function find( $title, $post_type ) {
		$found = get_posts(
			array(
				'post_type'      => $post_type,
				'post_status'    => 'any',
				'title'          => $title,
				'posts_per_page' => 1,
				'fields'         => 'ids',
			)
		);
		return $found ? (int) $found[0] : 0;
	}

	/**
	 * Seed three sample case studies.
	 */
	private function seed_work() {
		$items = array(
			array(
				'title'    => '[Sample] Ella Spice Café — new website & bookings',
				'client'   => 'Ella Spice Café',
				'industry' => 'Restaurants & cafes',
				'service'  => 'Website design and development',
				'year'     => '2025',
				'live_url' => 'https://example.com/ella-spice',
				'tech'     => 'WordPress, Vite, WooCommerce',
				'problem'  => 'A hillside café relied on social posts for bookings and lost walk-ins to larger rivals online.',
				'solution' => 'A fast one-page site with menu, photos and a WhatsApp booking button, built on the house design system.',
				'results'  => "Bookings up 40% in three months\nPage loads under 1 second on mobile\nRanked first for \"Ella café\"",
				'featured' => '1',
				'excerpt'  => 'A fast bookings-first site for a hillside café.',
				'content'  => 'Placeholder case study. Replace the copy and images before launch.',
			),
			array(
				'title'    => '[Sample] Pearl Retail — POS rollout across 4 shops',
				'client'   => 'Pearl Retail',
				'industry' => 'Retail shops',
				'service'  => 'POS systems',
				'year'     => '2024',
				'live_url' => 'https://example.com/pearl-retail',
				'tech'     => 'POS, Inventory sync, Reporting',
				'problem'  => 'Four shops counted stock by hand and could not see sales across branches.',
				'solution' => 'A single POS with shared inventory, barcode scanning and daily sales reports per branch.',
				'results'  => "Stock checks cut from a day to an hour\nOne dashboard for all four shops\nFewer out-of-stock surprises",
				'featured' => '1',
				'excerpt'  => 'One POS, shared stock, four shops.',
				'content'  => 'Placeholder case study. Replace the copy and images before launch.',
			),
			array(
				'title'    => '[Sample] Serendib Tours — travel site & enquiries',
				'client'   => 'Serendib Tours',
				'industry' => 'Travel and tourism',
				'service'  => 'SEO',
				'year'     => '2025',
				'live_url' => 'https://example.com/serendib-tours',
				'tech'     => 'WordPress, Schema, Analytics',
				'problem'  => 'A tour operator had a slow site that few travellers found on Google.',
				'solution' => 'A rebuilt, SEO-first site with tour pages, rich snippets and a clear enquiry flow.',
				'results'  => "Organic traffic up 3x in six months\nEnquiries doubled\nTop-three rankings for key tours",
				'featured' => '0',
				'excerpt'  => 'An SEO-first rebuild for a tour operator.',
				'content'  => 'Placeholder case study. Replace the copy and images before launch.',
			),
		);

		foreach ( $items as $item ) {
			$id      = $this->find( $item['title'], 'work' );
			$postarr = array(
				'post_type'    => 'work',
				'post_status'  => 'publish',
				'post_title'   => $item['title'],
				'post_excerpt' => $item['excerpt'],
				'post_content' => $item['content'],
			);
			if ( $id ) {
				$postarr['ID'] = $id;
				wp_update_post( $postarr );
			} else {
				$id = wp_insert_post( $postarr );
			}
			if ( ! $id || is_wp_error( $id ) ) {
				continue;
			}

			update_post_meta( $id, '_compass_placeholder', '1' );
			foreach ( array( 'client', 'year', 'live_url', 'tech', 'problem', 'solution', 'results', 'featured' ) as $key ) {
				update_post_meta( $id, '_compass_' . $key, $item[ $key ] );
			}

			$ind = $this->term( $item['industry'], 'industry' );
			$svc = $this->term( $item['service'], 'service' );
			if ( $ind ) {
				wp_set_object_terms( $id, array( $ind ), 'industry' );
			}
			if ( $svc ) {
				wp_set_object_terms( $id, array( $svc ), 'service' );
			}

			WP_CLI::log( 'Work: ' . $item['title'] );
		}
	}

	/**
	 * Seed three sample testimonials.
	 */
	private function seed_testimonials() {
		$items = array(
			array(
				'title'    => '[Sample] Testimonial — Ella Spice Café',
				'quote'    => 'Our new site is quick and easy, and bookings have climbed since launch. Compass just got it.',
				'name'     => 'Nimali Perera',
				'role'     => 'Owner, Ella Spice Café',
				'rating'   => '5',
				'featured' => '1',
			),
			array(
				'title'    => '[Sample] Testimonial — Pearl Retail',
				'quote'    => 'The POS finally gives us one clear view of every shop. Stock-takes that took all day now take an hour.',
				'name'     => 'Rohan Fernando',
				'role'     => 'Director, Pearl Retail',
				'rating'   => '5',
				'featured' => '1',
			),
			array(
				'title'    => '[Sample] Testimonial — Serendib Tours',
				'quote'    => 'More travellers find us on Google now, and the enquiries keep coming. Clear work, no fuss.',
				'name'     => 'Dilani Jayawardena',
				'role'     => 'Manager, Serendib Tours',
				'rating'   => '5',
				'featured' => '0',
			),
		);

		foreach ( $items as $item ) {
			$id      = $this->find( $item['title'], 'testimonial' );
			$postarr = array(
				'post_type'    => 'testimonial',
				'post_status'  => 'publish',
				'post_title'   => $item['title'],
				'post_content' => $item['quote'],
			);
			if ( $id ) {
				$postarr['ID'] = $id;
				wp_update_post( $postarr );
			} else {
				$id = wp_insert_post( $postarr );
			}
			if ( ! $id || is_wp_error( $id ) ) {
				continue;
			}

			update_post_meta( $id, '_compass_placeholder', '1' );
			update_post_meta( $id, '_compass_author_name', $item['name'] );
			update_post_meta( $id, '_compass_author_role', $item['role'] );
			update_post_meta( $id, '_compass_rating', $item['rating'] );
			update_post_meta( $id, '_compass_featured', $item['featured'] );

			WP_CLI::log( 'Testimonial: ' . $item['title'] );
		}
	}

	/**
	 * Create the Services hub and the six service pages (idempotent).
	 *
	 * The hub (slug `services`) uses page-services.php via the template hierarchy;
	 * each service page is a child at /services/<slug>/ assigned the shared
	 * `page-service.php` template. Copy is driven by the theme, so the pages hold
	 * no body content — only the right slug, parent and template.
	 */
	private function seed_service_pages() {
		if ( ! function_exists( 'compass_services_data' ) || ! function_exists( 'compass_services_order' ) ) {
			WP_CLI::warning( 'Service content not found (is the Compass theme active?). Skipped service pages.' );
			return;
		}

		$hub_id = $this->ensure_page( 'services', __( 'Services', 'compass-core' ), 0, '' );
		if ( ! $hub_id ) {
			WP_CLI::warning( 'Could not create the Services hub page.' );
			return;
		}
		WP_CLI::log( 'Page: Services (hub)' );

		$services = compass_services_data();
		foreach ( compass_services_order() as $slug ) {
			if ( empty( $services[ $slug ] ) ) {
				continue;
			}
			$title = wp_strip_all_tags( $services[ $slug ]['eyebrow'] );
			$id    = $this->ensure_page( $slug, $title, $hub_id, 'page-service.php' );
			if ( $id ) {
				WP_CLI::log( 'Page: Services / ' . $title );
			}
		}
	}

	/**
	 * Create the Industries hub and the five industry pages (idempotent).
	 *
	 * The hub (slug `industries`) uses page-industries.php via the template
	 * hierarchy; each industry page is a child at /industries/<slug>/ assigned
	 * the shared `page-industry.php` template. Copy is driven by the theme, so
	 * the pages hold no body content — only the right slug, parent and template.
	 */
	private function seed_industry_pages() {
		if ( ! function_exists( 'compass_industries_data' ) || ! function_exists( 'compass_industries_order' ) ) {
			WP_CLI::warning( 'Industry content not found (is the Compass theme active?). Skipped industry pages.' );
			return;
		}

		$hub_id = $this->ensure_page( 'industries', __( 'Industries', 'compass-core' ), 0, '' );
		if ( ! $hub_id ) {
			WP_CLI::warning( 'Could not create the Industries hub page.' );
			return;
		}
		WP_CLI::log( 'Page: Industries (hub)' );

		$industries = compass_industries_data();
		foreach ( compass_industries_order() as $slug ) {
			if ( empty( $industries[ $slug ] ) ) {
				continue;
			}
			$title = wp_strip_all_tags( $industries[ $slug ]['eyebrow'] );
			$id    = $this->ensure_page( $slug, $title, $hub_id, 'page-industry.php' );
			if ( $id ) {
				WP_CLI::log( 'Page: Industries / ' . $title );
			}
		}
	}

	/**
	 * Ensure a published page exists at the given slug and parent, with the
	 * given template. Returns the page ID.
	 *
	 * @param string $slug      Page slug.
	 * @param string $title     Page title (used only when creating).
	 * @param int    $parent_id Parent page ID (0 for top level).
	 * @param string $template  Page template file, or '' for the default.
	 * @return int
	 */
	private function ensure_page( $slug, $title, $parent_id, $template ) {
		$existing = get_page_by_path( $parent_id ? get_post_field( 'post_name', $parent_id ) . '/' . $slug : $slug );
		$id       = $existing ? (int) $existing->ID : 0;

		if ( ! $id ) {
			$id = wp_insert_post(
				array(
					'post_type'    => 'page',
					'post_status'  => 'publish',
					'post_title'   => $title,
					'post_name'    => $slug,
					'post_parent'  => $parent_id,
					'post_content' => '',
				)
			);
		}

		if ( ! $id || is_wp_error( $id ) ) {
			return 0;
		}

		if ( $template ) {
			update_post_meta( $id, '_wp_page_template', $template );
		}

		return (int) $id;
	}
}

WP_CLI::add_command( 'compass', 'Compass_Core_CLI' );
