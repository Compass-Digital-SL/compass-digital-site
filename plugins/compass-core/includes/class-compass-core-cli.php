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
		$this->seed_company_pages();
		$this->seed_insights();
		$this->seed_quote_pages();

		flush_rewrite_rules();

		WP_CLI::success( 'Seeded Work, Testimonials, Service, Industry and Company pages, the Insights blog and the Quote / Contact / Thank-you pages. Remove placeholders with: wp compass seed --fresh (before next seed) or delete the "[Sample]" items.' );
	}

	/**
	 * Delete all placeholder items.
	 */
	private function purge() {
		$ids = get_posts(
			array(
				'post_type'      => array( 'work', 'testimonial', 'post' ),
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
				'title'        => '[Sample] Ella Spice Café — new website & bookings',
				'client'       => 'Ella Spice Café',
				'industry'     => 'Restaurants & cafes',
				'service'      => 'Website design and development',
				'year'         => '2025',
				'live_url'     => 'https://example.com/ella-spice',
				'tech'         => 'WordPress, Vite, WooCommerce',
				'problem'      => 'A hillside café relied on social posts for bookings and lost walk-ins to larger rivals online.',
				'approach'     => 'We mapped the booking journey, kept it to one page, and designed around a single WhatsApp call-to-action.',
				'solution'     => 'A fast one-page site with menu, photos and a WhatsApp booking button, built on the house design system.',
				'results'      => "Bookings up 40% in three months\nPage loads under 1 second on mobile\nRanked first for \"Ella café\"",
				'quote'        => 'Our new site is quick and easy, and bookings have climbed since launch. Compass just got it.',
				'quote_author' => 'Nimali Perera, Owner — Ella Spice Café',
				'featured'     => '1',
				'excerpt'      => 'A fast, bookings-first website for a hillside café — clear menus, simple table reservations and pages that load quickly on any phone.',
				'content'      => 'Placeholder case study. Replace the copy and images before launch.',
			),
			array(
				'title'        => '[Sample] Pearl Retail — POS rollout across 4 shops',
				'client'       => 'Pearl Retail',
				'industry'     => 'Retail shops',
				'service'      => 'POS systems',
				'year'         => '2024',
				'live_url'     => 'https://example.com/pearl-retail',
				'tech'         => 'POS, Inventory sync, Reporting',
				'problem'      => 'Four shops counted stock by hand and could not see sales across branches.',
				'approach'     => 'We piloted the POS in one shop, trained the team, then rolled it out branch by branch with shared stock.',
				'solution'     => 'A single POS with shared inventory, barcode scanning and daily sales reports per branch.',
				'results'      => "Stock checks cut from a day to an hour\nOne dashboard for all four shops\nFewer out-of-stock surprises",
				'quote'        => 'The POS finally gives us one clear view of every shop. Stock-takes that took all day now take an hour.',
				'quote_author' => 'Rohan Fernando, Director — Pearl Retail',
				'featured'     => '1',
				'excerpt'      => 'One POS tying four shops together: shared live stock, quick billing at every counter and clear sales reports the owner can read at a glance.',
				'content'      => 'Placeholder case study. Replace the copy and images before launch.',
			),
			array(
				'title'        => '[Sample] Serendib Tours — travel site & enquiries',
				'client'       => 'Serendib Tours',
				'industry'     => 'Travel and tourism',
				'service'      => 'SEO',
				'year'         => '2025',
				'live_url'     => 'https://example.com/serendib-tours',
				'tech'         => 'WordPress, Schema, Analytics',
				'problem'      => 'A tour operator had a slow site that few travellers found on Google.',
				'approach'     => 'We audited rankings, restructured the site around tour intent, and added schema for rich results.',
				'solution'     => 'A rebuilt, SEO-first site with tour pages, rich snippets and a clear enquiry flow.',
				'results'      => "Organic traffic up 3x in six months\nEnquiries doubled\nTop-three rankings for key tours",
				'quote'        => 'More travellers find us on Google now, and the enquiries keep coming. Clear work, no fuss.',
				'quote_author' => 'Dilani Jayawardena, Manager — Serendib Tours',
				'featured'     => '0',
				'excerpt'      => 'An SEO-first rebuild for a tour operator — faster pages, clearer trip details and a structure built to rank for the searches that matter.',
				'content'      => 'Placeholder case study. Replace the copy and images before launch.',
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
			foreach ( array( 'client', 'year', 'live_url', 'tech', 'problem', 'approach', 'solution', 'results', 'quote', 'quote_author', 'featured' ) as $key ) {
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
	 * Create the About, Process and FAQ pages (idempotent).
	 *
	 * Each is a top-level page assigned the matching theme template. Copy is
	 * driven by the theme (inc/company.php), so the pages hold no body content.
	 */
	private function seed_company_pages() {
		$pages = array(
			'about'   => array( __( 'About', 'compass-core' ), 'page-about.php' ),
			'process' => array( __( 'Process', 'compass-core' ), 'page-process.php' ),
			'faq'     => array( __( 'FAQ', 'compass-core' ), 'page-faq.php' ),
		);
		foreach ( $pages as $slug => $page ) {
			$id = $this->ensure_page( $slug, $page[0], 0, $page[1] );
			if ( $id ) {
				WP_CLI::log( 'Page: ' . $page[0] );
			}
		}
	}

	/**
	 * Set up the Insights blog (idempotent):
	 *  - a static front page (Home → front-page.php) and Posts page (Insights),
	 *  - post permalinks under /insights/<slug>/,
	 *  - topic categories, and three starter articles.
	 */
	private function seed_insights() {
		// Static front + posts pages so /insights/ is the blog index.
		$home_id     = $this->ensure_page( 'home', __( 'Home', 'compass-core' ), 0, '' );
		$insights_id = $this->ensure_page( 'insights', __( 'Insights', 'compass-core' ), 0, '' );
		if ( $home_id ) {
			update_option( 'show_on_front', 'page' );
			update_option( 'page_on_front', $home_id );
		}
		if ( $insights_id ) {
			update_option( 'page_for_posts', $insights_id );
			WP_CLI::log( 'Page: Insights (blog index)' );
		}

		// Posts live at /insights/<slug>/ (matches docs/SEO.md).
		// Go through WP_Rewrite, not a bare update_option(): in a single WP-CLI
		// process the rewrite engine is bootstrapped with the previous structure
		// (setup.sh sets /%postname%/), and update_option() does not refresh it.
		// The seed()-level flush would then emit rules for the old structure, so
		// /insights/ (the posts page) still resolves but /insights/<slug>/ 404s.
		// set_permalink_structure() keeps the option and the in-memory engine in
		// sync so the flush regenerates the correct /insights/%postname%/ rules.
		global $wp_rewrite;
		if ( '/insights/%postname%/' !== $wp_rewrite->permalink_structure ) {
			$wp_rewrite->set_permalink_structure( '/insights/%postname%/' );
		}

		// Topic categories.
		$cats = array(
			'Websites'       => $this->term( 'Websites', 'category' ),
			'SEO'            => $this->term( 'SEO', 'category' ),
			'POS & Payments' => $this->term( 'POS & Payments', 'category' ),
		);

		$articles = array(
			array(
				'title'    => 'How much does a website cost in Sri Lanka?',
				'slug'     => 'website-cost-sri-lanka',
				'excerpt'  => 'A plain, honest look at what a business website really costs in Sri Lanka — what you pay for, what moves the price, and the two costly mistakes to avoid.',
				'category' => $cats['Websites'],
				'content'  => $this->article_website_cost(),
			),
			array(
				'title'    => 'How to get your business found on Google in Sri Lanka',
				'slug'     => 'get-found-on-google-sri-lanka',
				'excerpt'  => 'No jargon, no magic — a practical guide to showing up on Google for Sri Lankan businesses, and the handful of things that actually move the needle.',
				'category' => $cats['SEO'],
				'content'  => $this->article_get_found_google(),
			),
			array(
				'title'    => 'Choosing a POS system for a Sri Lankan shop: a plain guide',
				'slug'     => 'choosing-a-pos-system-sri-lanka',
				'excerpt'  => 'Billing, stock, reports and the questions nobody tells you to ask. A straightforward guide to picking a POS system that fits a Sri Lankan shop or restaurant.',
				'category' => $cats['POS & Payments'],
				'content'  => $this->article_choosing_pos(),
			),
		);

		$author = $this->author_id();
		$offset = 0;
		foreach ( $articles as $article ) {
			$existing = get_page_by_path( $article['slug'], OBJECT, 'post' );
			$postarr  = array(
				'post_type'    => 'post',
				'post_status'  => 'publish',
				'post_title'   => $article['title'],
				'post_name'    => $article['slug'],
				'post_excerpt' => $article['excerpt'],
				'post_content' => $article['content'],
				'post_author'  => $author,
				// Space the publish dates a few days apart for a natural order.
				'post_date'    => gmdate( 'Y-m-d H:i:s', strtotime( '-' . $offset . ' days' ) ),
			);
			if ( $existing ) {
				$postarr['ID'] = $existing->ID;
				$post_id       = wp_update_post( $postarr );
			} else {
				$post_id = wp_insert_post( $postarr );
			}
			if ( ! $post_id || is_wp_error( $post_id ) ) {
				continue;
			}
			update_post_meta( $post_id, '_compass_placeholder', '1' );
			if ( $article['category'] ) {
				wp_set_object_terms( $post_id, array( (int) $article['category'] ), 'category' );
			}
			WP_CLI::log( 'Article: ' . $article['title'] );
			$offset += 4;
		}
	}

	/**
	 * The first administrator's ID, or 1 as a fallback, for article authorship.
	 *
	 * @return int
	 */
	private function author_id() {
		$admins = get_users(
			array(
				'role'    => 'administrator',
				'number'  => 1,
				'fields'  => 'ID',
				'orderby' => 'ID',
				'order'   => 'ASC',
			)
		);
		return $admins ? (int) $admins[0] : 1;
	}

	/**
	 * Article body: "How much does a website cost in Sri Lanka?".
	 *
	 * @return string
	 */
	private function article_website_cost() {
		$website = esc_url( home_url( '/services/website-design-and-development/' ) );
		$quote   = esc_url( home_url( '/get-a-quote/' ) );
		return '
<p>It is the first question almost everyone asks, and the hardest one to get a straight answer to. Ask three companies what a website costs in Sri Lanka and you will get three wildly different numbers, usually with no explanation attached. This guide gives you the honest version: what you are actually paying for, what pushes the price up or down, and how to avoid the two mistakes that cost people the most.</p>

<h2>Why nobody will quote you a flat price</h2>
<p>A website is not one thing. A single-page site for a café and a twenty-page store with online payments are both "websites", but they are as different as a push-bike and a van. Anyone who gives you a firm price before understanding your business is either padding it to be safe or planning to cut corners later. A fair quote comes <em>after</em> a short conversation, not before.</p>
<p>That said, you deserve a sense of the landscape before you talk to anyone. Broadly, projects in Sri Lanka fall into a few bands: a simple, well-built business site; a larger site with more pages and custom design; and an online store or custom system. The gap between them is mostly about how much needs to be designed, built and connected — not about how "fancy" it looks.</p>

<h2>What you are actually paying for</h2>
<p>When you buy a website, the visible design is the small part. Most of the value — and the cost — is in the parts you cannot see on day one.</p>
<ul>
<li><strong>Design and build.</strong> Someone has to lay out every page, make it work on a phone, and write the code. A custom design costs more than a stock template, but it is yours and it loads faster.</li>
<li><strong>Speed and structure.</strong> A page that loads in under two seconds and is built so Google can read it is worth real money — it is the difference between being found and being invisible.</li>
<li><strong>Content.</strong> Words and photos either come from you or get written and sourced for you. If you supply them, you save; if we write them, that is time on the invoice.</li>
<li><strong>Setup you only do once.</strong> Domain, hosting, analytics, a contact form that actually reaches you, and the basic on-page SEO. Unglamorous, and essential.</li>
</ul>

<h3>The running costs, so there are no surprises</h3>
<p>A site is not a one-off purchase. Budget a small amount each year for the domain name and hosting, and set aside a little for occasional updates. Beware anyone who bundles these into a monthly fee you can never escape — you should own your domain and hosting outright, in your own name.</p>

<blockquote>A cheap site that loses customers is the most expensive thing you can buy. A fair-priced site that brings in enquiries pays for itself.</blockquote>

<h2>What makes the price go up or down</h2>
<p>If you want to steer the cost, these are the levers that actually move it:</p>
<ul>
<li><strong>Number of pages and sections.</strong> Five pages is a different job from twenty-five.</li>
<li><strong>Selling online.</strong> A store needs products, a cart, payments and stock — all of which add work. A simple business site does not.</li>
<li><strong>Who writes the words and takes the photos.</strong> This is the single biggest thing you control. Good content ready to go keeps the price down and the project fast.</li>
<li><strong>Bookings, logins, or systems to connect.</strong> Anything that talks to another tool — a CRM, a payment gateway, a booking calendar — adds time.</li>
<li><strong>How much custom design you want.</strong> A refined, bespoke look costs more than a clean, template-led one. Both can be excellent.</li>
</ul>

<h2>The two mistakes that cost the most</h2>
<p><strong>The first mistake is buying on price alone.</strong> The lowest quote often leaves out hosting, training, or the SEO basics, and the gaps appear as extra invoices later — or worse, as a slow site that quietly loses you customers. Cheap and expensive are not about the headline number; they are about what you get for it.</p>
<p><strong>The second mistake is getting locked in.</strong> Some builders keep your domain, hosting or site in their own account, so you cannot leave without losing everything. Always insist that the domain, hosting and accounts are in your name from day one. If a provider will not agree to that, walk away.</p>

<h2>Start small or do it all at once?</h2>
<p>You do not have to build everything on day one. For many businesses the smartest move is to launch a focused, excellent site that covers the essentials — who you are, what you offer, proof, and an easy way to get in touch — and then grow it as the business does. This keeps the first invoice manageable and gets you online sooner, which matters more than any feature you could add later.</p>
<p>The trick is to build on foundations that can grow. A site made on a solid, well-structured base can have pages, a blog, bookings or an online store added later without starting over. A cheap site cobbled together to look fine today often has to be thrown away the moment you need it to do more — which is how people end up paying twice. Ask any prospective builder a simple question: "If I want to add an online store in a year, do we extend this or rebuild it?" The answer tells you a lot.</p>
<p>A sensible way to think about budget is to tie it to what the site needs to earn back. If a new website brings in even one or two extra customers a month, a fair-priced build pays for itself quickly. Judged that way, the real question is not "what is the cheapest site I can get?" but "what is the site that will actually bring me enquiries?" — and those are rarely the same thing.</p>

<h2>How to get a quote you can trust</h2>
<p>A trustworthy quote has three things: a clear list of what is included, a fixed price rather than an open-ended hourly rate, and a realistic timeline. You should be able to read it in plain language and know exactly what you are getting. If a quote is one vague line with a big number, ask for the detail — a good partner will happily give it.</p>
<p>At Compass Digital we quote this way on purpose. We have a short conversation about your business, send a written plan with a fixed price, and nothing is charged until you sign off. You can read more about our <a href="' . $website . '">website design and development</a> service, or simply <a href="' . $quote . '">request a quote</a> and tell us what you are trying to fix. Either way, you will get a straight answer — which is where every good website should start.</p>
';
	}

	/**
	 * Article body: "How to get your business found on Google in Sri Lanka".
	 *
	 * @return string
	 */
	private function article_get_found_google() {
		$seo   = esc_url( home_url( '/services/seo/' ) );
		$quote = esc_url( home_url( '/get-a-quote/' ) );
		return '
<p>"Nobody finds us on Google" is one of the most common things we hear, and one of the most fixable. Getting found is not magic and it is not luck — it is a handful of sensible things done in the right order. This guide walks through them in plain language, so you know what matters, what does not, and what to expect.</p>

<h2>First, understand what Google is trying to do</h2>
<p>Google has one job: give the person searching the most useful, trustworthy answer, fast. Everything about showing up follows from that. If your website genuinely helps someone looking for what you offer, loads quickly, and looks legitimate, you are most of the way there. The tricks people used to chase have mostly stopped working; usefulness has not.</p>

<h2>The things that actually move the needle</h2>
<p>You could spend forever on SEO. Most Sri Lankan businesses only need to get a short list right.</p>

<h3>1. A fast, mobile-friendly website</h3>
<p>Most of your customers search on a phone, and so does Google when it decides where to rank you. A slow site, or one that is awkward on a small screen, loses on both counts. Aim for pages that load in under two seconds and are easy to use with a thumb. If your current site fails this, fixing it is usually the highest-value thing you can do.</p>

<h3>2. Pages that match what people search for</h3>
<p>People do not search for your company name — they search for what they need: "restaurant POS Colombo", "wedding photographer Kandy", "AC repair near me". You need a clear page for each main thing you offer, written in the words your customers actually use. One cramped page trying to cover everything will beat nothing, but a focused page for each service will beat that.</p>

<h3>3. Google Business Profile — your single biggest local win</h3>
<p>If you serve people in a place, a complete, verified Google Business Profile is the most valuable free thing you can do. It is what puts you on the map, shows your hours and phone number, and collects reviews. Fill in every field, add real photos, and keep it current.</p>
<ul>
<li>Use your exact business name, address and phone number — and make them identical everywhere online.</li>
<li>Choose the most specific category that fits.</li>
<li>Add photos of your actual shop, food, work or team.</li>
<li>Reply to every review, good or bad, politely.</li>
</ul>

<h3>4. Reviews, steadily</h3>
<p>Reviews do two jobs: they reassure the person reading them, and they tell Google you are a real, active business. You do not need hundreds. A steady trickle of honest reviews, replied to thoughtfully, beats a sudden burst that looks staged. The simplest system is to ask every happy customer, in person, with a direct link ready on your phone.</p>

<blockquote>Local SEO is mostly consistency and patience: the same details everywhere, useful pages, and steady reviews. There is no switch — but there is a reliable path.</blockquote>

<h3>5. A few good links and mentions</h3>
<p>When other trustworthy local sites mention or link to you — a tourism directory, a supplier, a local news piece, a partner — Google treats it as a vote of confidence. You do not need to chase hundreds. A handful of genuine, relevant mentions is worth more than a pile of junk ones, which can actually hurt.</p>

<h3>6. The quiet technical basics</h3>
<p>A few invisible things help Google understand and trust your site. You do not need to become an expert in them, but they should be in place: a sitemap so Google can find all your pages, clean page titles and descriptions that read well in search results, and structured data (schema) that spells out what your business is, where it is, and what it offers. A well-built site handles these for you from the start. If yours does not, it is leaving easy wins on the table.</p>

<h2>Write for people, and Google follows</h2>
<p>The single most sustainable way to get found is to be genuinely useful on the exact topics your customers search for. A café that writes a short, honest guide to the best spots for a quiet coffee, a builder who explains what a renovation really costs, a tour operator who answers the questions travellers actually ask — each of these earns rankings that adverts cannot buy, because they answer a real question better than the competition. This is slower than it sounds and more powerful than it looks: one good page can bring in enquiries for years. You do not need to write constantly. A handful of genuinely helpful pages, each aimed at one real question, will do more than a blog full of thin posts nobody reads.</p>

<h2>What to ignore</h2>
<p>Plenty of money gets wasted on SEO that does nothing. Be wary of anyone promising a number-one ranking by next week, selling bundles of low-quality backlinks, or stuffing your pages with repeated keywords until they read like a robot wrote them. These either do nothing or get you penalised. Honest SEO is slower and duller, and it lasts.</p>

<h2>How long does it take?</h2>
<p>This is the hard truth: SEO is a compounding investment, not an instant result. Technical fixes like speed can help within weeks. Rankings for competitive searches build over months as your pages prove useful and your reviews grow. Anyone who promises overnight results is selling something that will not last. The businesses that win are the ones that start, keep the basics tidy, and stay patient.</p>

<h2>Where to start this week</h2>
<p>If you do nothing else, do these three: claim and complete your Google Business Profile, make sure your website is fast on a phone, and ask your next five happy customers for a review. That alone puts you ahead of most of your competitors.</p>
<p>If you would rather have someone handle it properly — the technical fixes, the pages, the local setup, all measured so you can see what is working — that is exactly what our <a href="' . $seo . '">SEO service</a> does. It is honest, measurable work, with no empty promises. When you are ready, <a href="' . $quote . '">request a free SEO review</a> and we will tell you plainly where you stand and what would move you up.</p>
';
	}

	/**
	 * Article body: "Choosing a POS system for a Sri Lankan shop: a plain guide".
	 *
	 * @return string
	 */
	private function article_choosing_pos() {
		$pos   = esc_url( home_url( '/services/pos-systems/' ) );
		$quote = esc_url( home_url( '/get-a-quote/' ) );
		return '
<p>A good point-of-sale system is the quiet engine of a busy shop or restaurant: it speeds up billing, keeps your stock honest, and tells you what is really selling. A bad one slows the queue, loses track of stock, and locks your data away where you cannot use it. This guide walks through how to choose well, in plain language, with the questions most sellers forget to ask.</p>

<h2>What a POS is really for</h2>
<p>At its simplest, a POS rings up a sale and prints a bill. But the reason to invest in a proper one is everything around that moment:</p>
<ul>
<li><strong>Faster billing</strong> so the queue does not back up at your busiest times.</li>
<li><strong>Live stock</strong> that goes down as you sell, so you know what you have without counting by hand.</li>
<li><strong>Clear reports</strong> that show what is selling, what is sitting, and what each day actually brought in.</li>
<li><strong>Fewer mistakes</strong> because prices and totals are not being typed from memory.</li>
</ul>
<p>If a system does not make at least the first three noticeably better, it is not worth the switch.</p>

<h2>The questions most people forget to ask</h2>

<h3>Does it keep working when the internet drops?</h3>
<p>In Sri Lanka this is not a small detail. If your POS stops taking sales every time the connection wobbles, it will cost you money and patience at the worst moments. A good system keeps billing offline and syncs up when the connection returns. Ask to see exactly how it behaves during an outage before you commit.</p>

<h3>Whose data is it?</h3>
<p>Your sales history and stock data are among the most valuable things your business owns. Make sure you can export them, and that you are not locked into one provider forever. If moving away means losing years of records, that is a trap, not a feature.</p>

<h3>How hard is it to train someone on?</h3>
<p>Staff turnover is a fact of life. A POS that takes a week to learn is a POS that causes mistakes every time someone new starts. The best systems can be taught to a new cashier in an afternoon. If a demo leaves you confused, your team will be too.</p>

<h3>Does it fit how you actually work?</h3>
<p>A grocery, a pharmacy, a clothing shop and a restaurant all sell differently. A restaurant needs table management and kitchen tickets; a retail shop needs barcodes and variants; a pharmacy needs batch and expiry tracking. A general system that does none of these well will frustrate you. Match the tool to your trade.</p>

<blockquote>The right POS is not the one with the longest feature list. It is the one your team can run without thinking, that keeps working when the internet does not, and that hands your data back whenever you ask.</blockquote>

<h2>Hardware: keep it simple</h2>
<p>You do not need to spend a fortune on hardware to start. Many Sri Lankan shops run perfectly well on a tablet or a modest computer, a receipt printer, and a cash drawer, adding a barcode scanner when it earns its place. Buy for the shop you have, not the chain you imagine. You can always add terminals as you grow — which is exactly why shared stock across branches matters if you plan to expand.</p>

<h3>Watch the ongoing costs</h3>
<p>As with websites, the sticker price is only part of the story. Ask about monthly fees, charges per terminal, support costs, and what happens if you stop paying — some systems simply switch off. A fair arrangement is one you understand completely before you sign.</p>

<h2>Restaurant or retail? The difference matters</h2>
<p>The word "POS" hides two quite different jobs, and buying the wrong flavour is a common, costly mistake.</p>
<p>A <strong>restaurant or café</strong> lives or dies on the flow between the table and the kitchen. You want to take an order at the table, send it straight to the kitchen as a clear ticket, split or merge bills without a fuss, and handle takeaway and delivery orders alongside dine-in. Features like table management, kitchen tickets and modifiers ("no chilli", "extra cheese") are not luxuries here — they are the whole point.</p>
<p>A <strong>retail shop</strong> is about stock and speed at the counter. You want fast barcode scanning, product variants (size, colour), quick price lookups, and stock that updates live so you are never caught short on a best-seller. A pharmacy adds batch and expiry tracking; a clothing shop adds variants; a grocery adds weighing. The best system for one of these is rarely the best for another, so insist on seeing the features that match <em>your</em> trade in the demo, not a generic walk-through.</p>

<h2>Common mistakes when switching</h2>
<p>Most POS regrets come not from the software but from the switch. The usual culprits: entering stock in a rush so the numbers are wrong from day one; going live during your busiest week instead of a quiet one; and skipping training because "the staff will pick it up". Each of these turns a good system into a daily headache. Give the changeover the time it deserves and it pays you back every shift after.</p>

<h2>A simple way to choose</h2>
<p>Cut through the sales pitches with a short checklist. A POS worth buying should:</p>
<ul>
<li>Speed up your billing noticeably, not just match what you do now.</li>
<li>Keep working offline and sync when the connection returns.</li>
<li>Track stock in the way your trade actually needs.</li>
<li>Give you plain reports you will actually read.</li>
<li>Be learnable by a new staff member in an afternoon.</li>
<li>Let you export your own data, with no lock-in.</li>
</ul>
<p>If a system misses more than one of these, keep looking.</p>

<h2>Getting it set up right</h2>
<p>Even a great POS fails if it is rolled out badly — stock entered wrong, staff untrained, no plan for the first busy day. The smoothest rollouts start in one shop or one counter, train the team properly, iron out the quirks, then expand. Done that way, the change feels like a relief rather than a disruption.</p>
<p>We set up POS systems for Sri Lankan shops and restaurants with exactly that in mind: quick billing, live stock, clear reports, and sensible behaviour when the internet drops. If you would like a hand choosing or installing one, read about our <a href="' . $pos . '">POS systems</a> service, or <a href="' . $quote . '">request a demo quote</a> and tell us how your shop runs. We will recommend what fits — even when that is simpler than you expected.</p>
';
	}

	/**
	 * Ensure the quote flow pages exist with their templates assigned:
	 * Get a quote, Contact, and a noindex Thank-you page.
	 */
	private function seed_quote_pages() {
		$pages = array(
			'get-a-quote' => array( __( 'Get a quote', 'compass-core' ), 'page-get-a-quote.php' ),
			'contact'     => array( __( 'Contact', 'compass-core' ), 'page-contact.php' ),
			'thank-you'   => array( __( 'Thank you', 'compass-core' ), 'page-thank-you.php' ),
		);
		foreach ( $pages as $slug => $page ) {
			$id = $this->ensure_page( $slug, $page[0], 0, $page[1] );
			if ( $id ) {
				WP_CLI::log( 'Page: ' . $page[0] );
			}
		}
	}

	/**
	 * Ensure a page exists at the given slug (optionally under a parent) with the
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
