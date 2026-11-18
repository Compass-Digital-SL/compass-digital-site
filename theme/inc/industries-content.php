<?php
/**
 * Industry-page content.
 *
 * One slug-keyed array is the single source of truth for the five industry pages
 * and the Industries hub. Both `page-industry.php` and `page-industries.php` read
 * it, and the FAQPage / BreadcrumbList JSON-LD is generated from the same copy,
 * so the visible page and the structured data can never drift apart.
 *
 * Copy follows docs/BRAND.md (plain, confident, warm — no jargon, no hype) and
 * weaves the primary + secondary keywords from docs/SEO.md naturally. No prices
 * anywhere. Each page has 500+ words of unique copy. `services` entries cross-link
 * to /services/<slug>/ and `work_term` queries the `industry` taxonomy for proof.
 *
 * @package CompassDigital
 */

defined( 'ABSPATH' ) || exit;

/**
 * All five industries, keyed by slug (matches docs/SEO.md URL + keyword map).
 *
 * @return array<string,array<string,mixed>>
 */
function compass_industries_data() {
	static $data = null;
	if ( null !== $data ) {
		return $data;
	}

	$data = array(

		/* ================================================================ */
		'retail-shops'          => array(
			'work_term' => 'Retail shops',
			'title'     => __( 'Websites & POS for Retail Shops in Sri Lanka | Compass', 'compass-digital' ),
			'meta'      => __( 'Billing, stock and a website that brings shoppers in — built for Sri Lankan retail shops. Fast, clear, dependable. Request a quote.', 'compass-digital' ),
			'eyebrow'   => __( 'Retail shops', 'compass-digital' ),
			'headline'  => __( 'Sell faster at the counter — and get found before customers leave home.', 'compass-digital' ),
			'lede'      => __( 'A retail POS system and a website that work together for Sri Lankan shops: quick billing, stock you can trust, and a presence on Google that brings new shoppers through the door.', 'compass-digital' ),
			'signs'     => array(
				__( 'The queue backs up at busy times because billing is slow.', 'compass-digital' ),
				__( 'You don’t really know what’s selling and what’s just sitting on the shelf.', 'compass-digital' ),
				__( 'People nearby can’t find you on Google, so they walk into the shop next door.', 'compass-digital' ),
			),
			'intro'     => array(
				__( 'A retail shop lives and dies by small moments: how fast you ring up a sale, whether the price on the shelf matches the till, and whether a customer two streets away can find you at all. When any of those break down, you feel it in the day’s takings. We build the digital side of a shop — the retail POS system and the website — so those moments go your way instead of against you.', 'compass-digital' ),
				__( 'Start with the counter. A good retail billing software setup for Sri Lanka should take seconds per sale, handle cash and card without fuss, and keep working when the internet drops. Ours does. Barcodes, quick-select buttons for your top lines, and a receipt your customer trusts — the queue moves, the busy hour stops being a scramble, and your staff aren’t fighting the system while people wait.', 'compass-digital' ),
				__( 'Behind the till, a proper shop management system quietly tracks your stock. You see what’s selling, what’s slow, and what’s about to run out before a customer asks for it. That inventory system turns guesswork into decisions: you reorder the right things, stop money sitting dead on shelves, and spot the quiet best-seller you’d never have noticed from memory alone.', 'compass-digital' ),
				__( 'Then there’s getting found. Most people check Google before they visit a shop, even a local one. With retail website design and local SEO, we make sure that when someone nearby searches for what you sell, your shop shows up — with your hours, your location on the map, your photos and your phone number one tap away. A simple, fast site also gives you somewhere to show your range, run an offer, or point a WhatsApp enquiry, without paying for every little change.', 'compass-digital' ),
				__( 'It all joins up. The POS, the stock and the website come from one Sri Lankan team who explain everything in plain language and stay on afterwards. You own it all — your data, your accounts, your domain — and you’re never locked into a system you can’t get out of.', 'compass-digital' ),
			),
			'pains'     => array(
				array(
					'quote' => __( '“Billing is slow and the queue backs up at busy times.”', 'compass-digital' ),
					'fix'   => __( 'A fast POS with barcodes and quick-select buttons takes seconds per sale, and keeps working offline when the internet drops.', 'compass-digital' ),
				),
				array(
					'quote' => __( '“I don’t know what’s selling and what’s just sitting there.”', 'compass-digital' ),
					'fix'   => __( 'Live stock and simple sales reports show your best-sellers and dead stock, so you reorder the right things and free up cash.', 'compass-digital' ),
				),
				array(
					'quote' => __( '“People nearby can’t find us on Google.”', 'compass-digital' ),
					'fix'   => __( 'A fast local website plus Google Business Profile and maps put your shop in front of shoppers searching close by.', 'compass-digital' ),
				),
			),
			'services'  => array(
				array(
					'slug' => 'pos-systems',
					'text' => __( 'A retail POS and billing system that’s quick at the counter, works offline, and tracks stock so you always know what to reorder.', 'compass-digital' ),
				),
				array(
					'slug' => 'website-design-and-development',
					'text' => __( 'A fast, mobile-first shop website that shows your range, hours and location — and that you can update yourself.', 'compass-digital' ),
				),
				array(
					'slug' => 'seo',
					'text' => __( 'Local SEO and Google Business Profile so nearby shoppers find you first when they search for what you sell.', 'compass-digital' ),
				),
			),
			'faqs'      => array(
				array(
					'q' => __( 'Do you build POS systems for retail shops in Sri Lanka?', 'compass-digital' ),
					'a' => __( 'Yes. We set up retail POS and billing software tuned to how your shop actually runs — barcodes, quick buttons for your top lines, cash and card, and stock tracking. It’s fast at the counter and keeps working when the internet drops.', 'compass-digital' ),
				),
				array(
					'q' => __( 'Will the POS keep working if the internet goes down?', 'compass-digital' ),
					'a' => __( 'Yes. We build billing to keep running offline so you never stop selling during an outage, then sync your sales and stock back up once the connection returns.', 'compass-digital' ),
				),
				array(
					'q' => __( 'Can my POS and stock talk to each other?', 'compass-digital' ),
					'a' => __( 'That’s the point of a proper shop management system. Every sale updates stock automatically, so your reports show what’s selling, what’s slow and what’s about to run out — no separate spreadsheet to keep.', 'compass-digital' ),
				),
				array(
					'q' => __( 'Do I need a website as well as a POS?', 'compass-digital' ),
					'a' => __( 'Most shops benefit from both. The POS runs the counter; the website and local SEO bring new shoppers in by making sure people searching nearby find your hours, location and range before they visit.', 'compass-digital' ),
				),
				array(
					'q' => __( 'How much does a retail setup cost?', 'compass-digital' ),
					'a' => __( 'It depends on how many tills you need, whether you want a website too, and how much stock you carry. We give a fixed quote on request before any work starts — no hourly surprises, and no prices listed because every shop is different.', 'compass-digital' ),
				),
			),
		),

		/* ================================================================ */
		'restaurants-and-cafes' => array(
			'work_term' => 'Restaurants & cafes',
			'title'     => __( 'POS & Websites for Restaurants in Sri Lanka | Compass', 'compass-digital' ),
			'meta'      => __( 'Quick table billing, kitchen tickets, online orders and a website that fills seats — for Sri Lankan restaurants and cafes. Get a quote.', 'compass-digital' ),
			'eyebrow'   => __( 'Restaurants & cafes', 'compass-digital' ),
			'headline'  => __( 'Turn tables faster, take orders online, and fill more seats.', 'compass-digital' ),
			'lede'      => __( 'A restaurant POS system that handles tables, bills and kitchen tickets without the hand-written chaos — plus a website and online ordering that bring Sri Lankan diners to your door.', 'compass-digital' ),
			'signs'     => array(
				__( 'Tables and bills are handled by hand, and mistakes creep in at the rush.', 'compass-digital' ),
				__( 'Orders come in on WhatsApp with no proper way to collect them.', 'compass-digital' ),
				__( 'Your menu online is a blurry photo, if it’s anywhere at all.', 'compass-digital' ),
			),
			'intro'     => array(
				__( 'In a restaurant or cafe, the difference between a good night and a stressful one is often the system behind the service. When tables and bills are handled by hand, orders get lost, the kitchen falls behind, and a full house becomes a scramble. We build the digital backbone — a restaurant POS, kitchen tickets, online ordering and a website — so service stays calm even when every table is taken.', 'compass-digital' ),
				__( 'A cafe billing system should match the pace of service. With a proper POS you open a table, add items as people order, split or merge bills, and print a clean cheque in seconds. When the order is sent, a KOT system fires the ticket straight to the kitchen or bar — no runner, no misread handwriting, no “did that table order the rice or not?” The floor and the kitchen finally speak the same language.', 'compass-digital' ),
				__( 'Plenty of your custom now starts on a phone. People look you up before they decide where to eat, and they message on WhatsApp to ask if you’re open or to place an order. A restaurant website design that loads fast, shows a clear menu with real prices, and puts your location, hours and a WhatsApp button one tap away turns that browsing into bookings and walk-ins. Add online food ordering and you capture the orders that currently arrive as messy chat messages — collected properly, with nothing lost.', 'compass-digital' ),
				__( 'Getting found matters as much as looking good. We set up your Google Business Profile and local SEO so that when someone nearby searches for a cafe or a cuisine, you’re on the map with photos, reviews and directions. For a cafe or restaurant, those “near me” searches are some of the most valuable custom you can win — people who are hungry, close by, and deciding right now.', 'compass-digital' ),
				__( 'Everything comes from one Sri Lankan team who explain things plainly and stay on after launch. The POS, the website and the ordering join up, your data and accounts are yours, and you’re never tied into something you can’t leave.', 'compass-digital' ),
			),
			'pains'     => array(
				array(
					'quote' => __( '“Tables and bills are handled by hand, and it’s chaos at the rush.”', 'compass-digital' ),
					'fix'   => __( 'A restaurant POS opens tables, splits bills and prints clean cheques in seconds, so busy nights stay calm and accurate.', 'compass-digital' ),
				),
				array(
					'quote' => __( '“Orders reach the kitchen on scraps of paper and get misread.”', 'compass-digital' ),
					'fix'   => __( 'A KOT system sends each order straight to the kitchen or bar — no runner, no handwriting, nothing lost between floor and pass.', 'compass-digital' ),
				),
				array(
					'quote' => __( '“I get orders on WhatsApp but no proper way to collect them.”', 'compass-digital' ),
					'fix'   => __( 'Online ordering plus a clear menu and WhatsApp button capture those orders properly, with your location and hours one tap away.', 'compass-digital' ),
				),
			),
			'services'  => array(
				array(
					'slug' => 'pos-systems',
					'text' => __( 'A restaurant POS with table management, bill splitting and kitchen tickets (KOT) that keeps the floor and kitchen in sync.', 'compass-digital' ),
				),
				array(
					'slug' => 'website-design-and-development',
					'text' => __( 'A fast website with a clear menu, real prices, photos and a WhatsApp button — built to turn a phone search into a seat filled.', 'compass-digital' ),
				),
				array(
					'slug' => 'seo',
					'text' => __( 'Local SEO, Google Business Profile and reviews so hungry diners nearby find you first on the map.', 'compass-digital' ),
				),
			),
			'faqs'      => array(
				array(
					'q' => __( 'Do you build restaurant POS systems in Sri Lanka?', 'compass-digital' ),
					'a' => __( 'Yes. We set up restaurant and cafe POS with table management, bill splitting, and kitchen tickets (KOT), tuned to how your service runs. It keeps billing fast and accurate even when every table is full.', 'compass-digital' ),
				),
				array(
					'q' => __( 'What is a KOT system and do I need one?', 'compass-digital' ),
					'a' => __( 'A KOT (Kitchen Order Ticket) system sends each order straight from the floor to the kitchen or bar as a printed ticket or screen. It ends misread handwriting and lost orders, and it’s worth it the moment service gets busy.', 'compass-digital' ),
				),
				array(
					'q' => __( 'Can customers order online from my restaurant?', 'compass-digital' ),
					'a' => __( 'Yes. We can add online food ordering to your website so the orders that currently arrive as messy WhatsApp messages are collected properly — with your menu, prices and delivery or pickup options all in one place.', 'compass-digital' ),
				),
				array(
					'q' => __( 'Will my menu look good on a phone?', 'compass-digital' ),
					'a' => __( 'Yes. We design mobile-first, because most diners look you up on a phone. Your menu loads fast, reads clearly, shows real prices, and sits next to your location, hours and a WhatsApp button.', 'compass-digital' ),
				),
				array(
					'q' => __( 'How much does a restaurant setup cost?', 'compass-digital' ),
					'a' => __( 'It depends on how many tills and printers you need, whether you want online ordering, and the size of your website. We give a fixed quote on request before any work starts — no hourly surprises, and no prices listed because every venue is different.', 'compass-digital' ),
				),
			),
		),

		/* ================================================================ */
		'travel-and-tourism'    => array(
			'work_term' => 'Travel and tourism',
			'title'     => __( 'Websites for Travel & Tourism in Sri Lanka | Compass', 'compass-digital' ),
			'meta'      => __( 'Beautiful, fast websites and SEO for Sri Lankan tour operators and hotels — win more direct bookings. See our work and request a quote.', 'compass-digital' ),
			'eyebrow'   => __( 'Travel & tourism', 'compass-digital' ),
			'headline'  => __( 'Win direct bookings from travellers before an agency takes the cut.', 'compass-digital' ),
			'lede'      => __( 'Fast, beautiful website design for travel companies in Sri Lanka — tour operators and hotels — with booking enquiries, multilingual readiness, maps, reviews and WhatsApp, all built mobile-first for travellers on the move.', 'compass-digital' ),
			'signs'     => array(
				__( 'Tourists can’t find you, or can’t easily contact you once they do.', 'compass-digital' ),
				__( 'Bookings come through agencies and platforms that take a big cut.', 'compass-digital' ),
				__( 'Your site looks dated and slow on the phones travellers actually use.', 'compass-digital' ),
			),
			'intro'     => array(
				__( 'For a Sri Lankan tour operator or hotel, the website is the whole shopfront. A traveller planning a trip from abroad can’t walk past your office — they find you on Google, judge you in seconds, and either enquire or move on. If your site is slow, hard to use on a phone, or impossible to contact from, you lose the booking to someone easier to deal with, often an agency or platform that then takes a cut of what should have been yours.', 'compass-digital' ),
				__( 'We build travel and tour operator websites to win those direct bookings. First, the booking enquiry flow: a clear, simple way for a traveller to tell you dates, group size and what they want, so a real enquiry lands in your inbox — not a half-finished form they gave up on. We make the path from “this looks good” to “here are my details” short and obvious, because every extra step loses people.', 'compass-digital' ),
				__( 'Travellers arrive from everywhere, so we build for multilingual readiness from the start — a structure that can carry English today and add other languages cleanly as you grow, without rebuilding the site. Everything is mobile-first, because your visitors are on phones, often on patchy connections, mid-journey. Pages load fast, images don’t eat their data, and the layout works one-handed on a small screen.', 'compass-digital' ),
				__( 'Trust is what converts a traveller who’s never met you. We put Google Maps front and centre so people can see exactly where you are and how to reach you, and we surface your reviews so new visitors see that others had a good trip. For enquiries, a WhatsApp button is often the single most effective thing on the page — travellers are used to messaging, it crosses borders and time zones, and it turns a browse into a conversation in one tap.', 'compass-digital' ),
				__( 'Then there’s travel SEO: being found when someone searches for a tour, a region or a type of stay in Sri Lanka. We handle the technical foundations, the local signals, and content built around the trips people actually search for, so you show up at the moment they’re planning. It all comes from one Sri Lankan team, in plain language, and it’s all yours — your site, your bookings, your data — with no lock-in.', 'compass-digital' ),
			),
			'pains'     => array(
				array(
					'quote' => __( '“Tourists can’t find us, or can’t easily contact us.”', 'compass-digital' ),
					'fix'   => __( 'Travel SEO and Google Maps get you found, and a clear booking enquiry form plus a WhatsApp button make contacting you effortless.', 'compass-digital' ),
				),
				array(
					'quote' => __( '“Most of our bookings go through agencies that take a big cut.”', 'compass-digital' ),
					'fix'   => __( 'A fast site with a simple enquiry flow and visible reviews wins direct bookings, so more of the value stays with you.', 'compass-digital' ),
				),
				array(
					'quote' => __( '“Our site is slow and awkward on the phones travellers use.”', 'compass-digital' ),
					'fix'   => __( 'We build mobile-first and fast, so pages load on patchy connections and work one-handed on a small screen.', 'compass-digital' ),
				),
			),
			'services'  => array(
				array(
					'slug' => 'website-design-and-development',
					'text' => __( 'A beautiful, fast, mobile-first website with a clear booking enquiry flow, Google Maps, visible reviews, WhatsApp and multilingual readiness.', 'compass-digital' ),
				),
				array(
					'slug' => 'seo',
					'text' => __( 'Travel SEO so travellers find you when they search for tours, regions and stays in Sri Lanka — and book direct instead of through an agency.', 'compass-digital' ),
				),
				array(
					'slug' => 'custom-software',
					'text' => __( 'When you’re ready, booking or itinerary tools that fit how you actually run trips, connected to the enquiries your site brings in.', 'compass-digital' ),
				),
			),
			'faqs'      => array(
				array(
					'q' => __( 'Do you design websites for tour operators and hotels in Sri Lanka?', 'compass-digital' ),
					'a' => __( 'Yes. Travel and tourism is one of our core industries. We build fast, beautiful tour operator and hotel websites with booking enquiry flows, maps, reviews and WhatsApp, all designed to win direct bookings.', 'compass-digital' ),
				),
				array(
					'q' => __( 'Can the website handle booking enquiries?', 'compass-digital' ),
					'a' => __( 'Yes. We build a clear booking enquiry flow where a traveller gives their dates, group size and what they want, and a complete enquiry lands in your inbox. We keep the path short so people finish it, and can add a WhatsApp button alongside it.', 'compass-digital' ),
				),
				array(
					'q' => __( 'Can you build the site in more than one language?', 'compass-digital' ),
					'a' => __( 'We build with multilingual readiness from the start, so the site can carry English now and add other languages cleanly as you grow, without a rebuild. Travellers come from everywhere, so this is worth planning for early.', 'compass-digital' ),
				),
				array(
					'q' => __( 'Will travellers be able to reach us on WhatsApp?', 'compass-digital' ),
					'a' => __( 'Yes, and we recommend it. A WhatsApp button is often the most effective thing on a travel site — it crosses borders and time zones and turns a browse into a conversation in one tap. We place it where travellers will see it on mobile.', 'compass-digital' ),
				),
				array(
					'q' => __( 'How do we get more direct bookings instead of going through agencies?', 'compass-digital' ),
					'a' => __( 'A fast, trustworthy website that’s easy to find and easy to enquire from is the heart of it: travel SEO brings travellers in, Google Maps and reviews build trust, and a simple enquiry flow plus WhatsApp converts them — so more of the booking value stays with you.', 'compass-digital' ),
				),
			),
		),

		/* ================================================================ */
		'ecommerce-brands'      => array(
			'work_term' => 'Ecommerce brands',
			'title'     => __( 'Ecommerce for Growing Brands in Sri Lanka | Compass', 'compass-digital' ),
			'meta'      => __( 'Online stores built to sell and to rank — for Sri Lankan ecommerce brands. Faster pages, local payments, more orders. Request a quote.', 'compass-digital' ),
			'eyebrow'   => __( 'Ecommerce brands', 'compass-digital' ),
			'headline'  => __( 'An online store built to sell — fast, findable, and yours to grow.', 'compass-digital' ),
			'lede'      => __( 'Ecommerce development for Sri Lankan brands that want to sell more online: fast pages, local payments, SEO that brings buyers in, and a store you own outright instead of renting.', 'compass-digital' ),
			'signs'     => array(
				__( 'You want to sell online but don’t know where to start.', 'compass-digital' ),
				__( 'Your store is slow, and buyers leave before the page even loads.', 'compass-digital' ),
				__( 'You’re paying monthly for a platform and can’t fully control it.', 'compass-digital' ),
			),
			'intro'     => array(
				__( 'An online store only grows if two things are true: people can find it, and buying is effortless once they do. Plenty of ecommerce brands in Sri Lanka lose sales on one or both. We build stores that fix both — ecommerce development focused on speed, findability and conversion, so the traffic you earn actually turns into orders.', 'compass-digital' ),
				__( 'Speed comes first, because it quietly decides everything. A slow store bleeds buyers before the page even loads, and it drags down your Google ranking too. We build lean, mobile-first stores that load fast on a phone, so the shopper who tapped your link stays long enough to buy. Clean product pages, a checkout with as few steps as possible, and local payment methods your customers already trust all remove the friction that loses a sale at the last moment.', 'compass-digital' ),
				__( 'Being found is the other half. Ecommerce SEO — fast pages, a clean structure, and product and category pages built around what buyers actually search — brings people in without paying for every click. For a growing brand that’s the difference between renting your customers from ads forever and building traffic that keeps working. We set the foundations so your store earns its own visitors over time.', 'compass-digital' ),
				__( 'We also believe you should own your store, not just rent it. If a hosted platform’s monthly fees, limits and lock-in frustrate you, we build on tools you control — a genuine Shopify alternative in Sri Lanka where the store, the data and the customer list are yours. You can change anything, move anywhere, and you’re never held hostage by a plan that keeps getting more expensive as you grow.', 'compass-digital' ),
				__( 'And because a brand is more than a storefront, we build with room to grow — the store connects cleanly to the marketing, content and custom tools you add later. It all comes from one Sri Lankan team who explain things in plain language and stay on afterwards, so growing online sales feels like progress, not a fight with your own website.', 'compass-digital' ),
			),
			'pains'     => array(
				array(
					'quote' => __( '“I want to sell online but I don’t know where to start.”', 'compass-digital' ),
					'fix'   => __( 'We guide you from first idea to a live store — products, payments and shipping set up properly, explained in plain language.', 'compass-digital' ),
				),
				array(
					'quote' => __( '“My store is so slow buyers leave before it loads.”', 'compass-digital' ),
					'fix'   => __( 'We build lean, mobile-first stores that load fast on a phone, so shoppers stay long enough to actually buy.', 'compass-digital' ),
				),
				array(
					'quote' => __( '“I’m scared of being locked into a platform I can’t get out of.”', 'compass-digital' ),
					'fix'   => __( 'We build on tools you own — your store, data and customer list are yours to keep, change or move at any time.', 'compass-digital' ),
				),
			),
			'services'  => array(
				array(
					'slug' => 'ecommerce-websites',
					'text' => __( 'A fast online store built to convert — clean product pages, a short checkout, local payments, and full ownership of your store and data.', 'compass-digital' ),
				),
				array(
					'slug' => 'seo',
					'text' => __( 'Ecommerce SEO so your products and categories rank and bring in buyers, instead of renting every customer from ads.', 'compass-digital' ),
				),
				array(
					'slug' => 'custom-software',
					'text' => __( 'When you outgrow off-the-shelf, custom tools and integrations that fit how your brand actually sells and ships.', 'compass-digital' ),
				),
			),
			'faqs'      => array(
				array(
					'q' => __( 'Do you build online stores for brands in Sri Lanka?', 'compass-digital' ),
					'a' => __( 'Yes. We handle ecommerce development end to end for Sri Lankan brands — products, local payments, shipping and a fast, mobile-first store built to sell. Whether you’re starting out or replacing a store you’ve outgrown, we guide the whole way.', 'compass-digital' ),
				),
				array(
					'q' => __( 'Can you set up local payment methods?', 'compass-digital' ),
					'a' => __( 'Yes. We set up the local payment options your customers already trust, alongside card payments, so checkout feels familiar and more buyers finish their order.', 'compass-digital' ),
				),
				array(
					'q' => __( 'Is this a good alternative to Shopify?', 'compass-digital' ),
					'a' => __( 'For many brands, yes. We build on tools you own rather than rent, so you avoid rising monthly fees and platform lock-in. The store, data and customer list are yours to keep, change or move — a genuine Shopify alternative that grows with you instead of charging more as you do.', 'compass-digital' ),
				),
				array(
					'q' => __( 'Will my store show up on Google?', 'compass-digital' ),
					'a' => __( 'That’s what ecommerce SEO is for. We build fast pages and a clean structure, and optimise your product and category pages around what buyers actually search, so your store earns its own traffic over time instead of relying only on ads.', 'compass-digital' ),
				),
				array(
					'q' => __( 'How much does an online store cost?', 'compass-digital' ),
					'a' => __( 'It depends on how many products you sell, the payment and shipping setup, and how custom you want the store. We give a fixed quote on request before any work starts — no hourly surprises, and no prices listed because every brand is different.', 'compass-digital' ),
				),
			),
		),

		/* ================================================================ */
		'corporates-and-smes'   => array(
			'work_term' => 'Corporates & SMEs',
			'title'     => __( 'Websites & Software for SMEs in Sri Lanka | Compass', 'compass-digital' ),
			'meta'      => __( 'Professional websites and dependable business software for Sri Lankan corporates and SMEs. Clear, reliable, built to last. Get a quote.', 'compass-digital' ),
			'eyebrow'   => __( 'Corporates & SMEs', 'compass-digital' ),
			'headline'  => __( 'A professional website and software your business can rely on.', 'compass-digital' ),
			'lede'      => __( 'Business website design and dependable software for Sri Lankan corporates and SMEs: a credible online presence, and the custom tools to run the parts of your business that spreadsheets have outgrown.', 'compass-digital' ),
			'signs'     => array(
				__( 'Your website doesn’t reflect the quality of the business you run.', 'compass-digital' ),
				__( 'Work runs on spreadsheets and manual steps that no longer scale.', 'compass-digital' ),
				__( 'The last developer disappeared, and you’re wary of being left stranded again.', 'compass-digital' ),
			),
			'intro'     => array(
				__( 'For a corporate or an established SME, your website is your credibility. Clients, partners and good hires look you up before they deal with you, and a dated or slow site makes a strong business look uncertain. A professional business website design — clear, fast, and easy to keep current — makes sure your online presence matches the quality of the work you actually do.', 'compass-digital' ),
				__( 'A good company website in Colombo or anywhere in Sri Lanka does a few things well: it explains what you do without jargon, it earns trust with real proof, and it makes the next step obvious, whether that’s an enquiry, a call or a download. We build it mobile-first and fast, with a clean structure search engines can read, and we hand it over so your own team can update news, services and people without paying for every small change.', 'compass-digital' ),
				__( 'Beyond the website, growing businesses hit a wall where spreadsheets and manual steps stop scaling. Quotes, approvals, bookings, stock, reporting — the work that lives in a tangle of files and WhatsApp messages and someone’s memory. That’s where business software for SMEs earns its keep. We build custom software that fits how you already work, automates the repetitive parts, and gives you a single, reliable place for the information you currently chase.', 'compass-digital' ),
				__( 'We’re deliberate about this: we don’t over-build. We start with the one process that’s hurting most, solve it properly, and grow from there, so you get value early rather than waiting on a big system that may miss the mark. Everything is built to last — maintainable, documented, and connected to the tools you already use — so it keeps serving the business instead of becoming the next thing you have to replace.', 'compass-digital' ),
				__( 'Most of all, we’re the team that doesn’t disappear. If you’ve been burned by a developer who vanished after taking the money, you’ll feel the difference: plain-language updates, honest scoping, full ownership of your site, software and data, and support that’s still there after launch. One Sri Lankan partner for the website and the software, so the whole thing holds together.', 'compass-digital' ),
			),
			'pains'     => array(
				array(
					'quote' => __( '“Our website doesn’t reflect the quality of what we do.”', 'compass-digital' ),
					'fix'   => __( 'A clean, fast, professional site that earns trust and makes the next step obvious — and that your team can keep current.', 'compass-digital' ),
				),
				array(
					'quote' => __( '“Everything runs on spreadsheets and manual steps that don’t scale.”', 'compass-digital' ),
					'fix'   => __( 'Custom software that fits how you work, automates the repetitive parts, and gives you one reliable place for the information you chase.', 'compass-digital' ),
				),
				array(
					'quote' => __( '“The last developer disappeared after taking the money.”', 'compass-digital' ),
					'fix'   => __( 'Honest scoping, plain-language updates, full ownership of everything we build, and support that stays on after launch.', 'compass-digital' ),
				),
			),
			'services'  => array(
				array(
					'slug' => 'website-design-and-development',
					'text' => __( 'A professional, fast corporate website that reflects your business, ranks well, and your team can update without a developer.', 'compass-digital' ),
				),
				array(
					'slug' => 'custom-software',
					'text' => __( 'Business software built around your real processes — quotes, approvals, bookings, reporting — to replace the spreadsheets you’ve outgrown.', 'compass-digital' ),
				),
				array(
					'slug' => 'seo',
					'text' => __( 'SEO so the right clients and partners find your business when they search for what you do.', 'compass-digital' ),
				),
			),
			'faqs'      => array(
				array(
					'q' => __( 'Do you build corporate websites in Sri Lanka?', 'compass-digital' ),
					'a' => __( 'Yes. We design professional, fast business and corporate websites that reflect the quality of your work, earn trust, and make the next step clear. They’re built mobile-first, structured to rank, and easy for your own team to keep current.', 'compass-digital' ),
				),
				array(
					'q' => __( 'Can you build custom software for our business?', 'compass-digital' ),
					'a' => __( 'Yes. We build business software for SMEs around how you actually work — automating quotes, approvals, bookings, stock or reporting — to replace the spreadsheets and manual steps that stop scaling. We start with the process that hurts most and grow from there.', 'compass-digital' ),
				),
				array(
					'q' => __( 'We were let down by a developer before. How are you different?', 'compass-digital' ),
					'a' => __( 'We scope honestly, update you in plain language, and stay on after launch. Crucially, you own everything we build — your website, software, domain and data — so you’re never locked in or left stranded if you want to move.', 'compass-digital' ),
				),
				array(
					'q' => __( 'Can the website and software work together?', 'compass-digital' ),
					'a' => __( 'Yes, and it’s an advantage of using one partner. Your site can connect to the tools we build and the systems you already use, so information flows instead of being re-keyed between a tangle of files.', 'compass-digital' ),
				),
				array(
					'q' => __( 'How much does a website or software project cost?', 'compass-digital' ),
					'a' => __( 'It depends on the size of the site and the scope of any software. We give a fixed quote on request before any work starts — no hourly surprises, and no prices listed because every business is different.', 'compass-digital' ),
				),
			),
		),
	);

	return $data;
}

/**
 * The ordered industry slugs, used by the hub and for navigation.
 *
 * @return string[]
 */
function compass_industries_order() {
	return array(
		'retail-shops',
		'restaurants-and-cafes',
		'travel-and-tourism',
		'ecommerce-brands',
		'corporates-and-smes',
	);
}
