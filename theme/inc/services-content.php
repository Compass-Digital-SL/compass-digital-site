<?php
/**
 * Service-page content.
 *
 * One slug-keyed array is the single source of truth for the six service pages
 * and the Services hub. Both `page-service.php` and `page-services.php` read it,
 * and the Service / FAQPage JSON-LD is generated from the very same copy, so the
 * visible page and the structured data can never drift apart.
 *
 * Copy follows docs/BRAND.md (plain, confident, warm — no jargon, no hype) and
 * weaves the primary + secondary keywords from docs/SEO.md naturally. No prices
 * anywhere: every page says "quote on request" and what affects the price.
 *
 * @package CompassDigital
 */

defined( 'ABSPATH' ) || exit;

/**
 * The shared "how we work" process, identical across services (matches the home
 * page process so the story is one story everywhere).
 *
 * @return array<int,array<string,string>>
 */
function compass_service_process() {
	return array(
		array(
			'title' => __( 'Listen', 'compass-digital' ),
			'text'  => __( 'We start with your business, not a template — how you work, who your customers are, and what a win actually looks like for you.', 'compass-digital' ),
		),
		array(
			'title' => __( 'Plan', 'compass-digital' ),
			'text'  => __( 'We agree a clear scope, a fixed quote and a realistic timeline, in plain language, with nothing in it you don’t need.', 'compass-digital' ),
		),
		array(
			'title' => __( 'Build', 'compass-digital' ),
			'text'  => __( 'We build in the open and share progress as we go, so you can steer early and there are no big reveals that miss the mark.', 'compass-digital' ),
		),
		array(
			'title' => __( 'Support', 'compass-digital' ),
			'text'  => __( 'We stay on after launch to fix, update and grow what we built — and it’s always yours to take anywhere.', 'compass-digital' ),
		),
	);
}

/**
 * All six services, keyed by slug (matches docs/SEO.md URL + keyword map).
 *
 * @return array<string,array<string,mixed>>
 */
function compass_services_data() {
	static $data = null;
	if ( null !== $data ) {
		return $data;
	}

	$data = array(

		/* ---------------------------------------------------------------- */
		'website-design-and-development' => array(
			'tax_name'      => 'Website design and development',
			'service_type'  => 'Website design and development',
			'title'         => __( 'Website Design & Development | Compass Digital', 'compass-digital' ),
			'meta'          => __( 'Fast, modern business websites for clients worldwide — mobile-first, easy to update, built to rank. See our work and request a quote.', 'compass-digital' ),
			'eyebrow'       => __( 'Website design & development', 'compass-digital' ),
			'headline'      => __( 'A website you’re proud to share — fast, modern and easy to run.', 'compass-digital' ),
			'lede'          => __( 'We design and build business websites that load in under two seconds, look right on a phone, and you can update yourself. Website design that works as hard as you do.', 'compass-digital' ),
			'who'           => array(
				__( 'Owners embarrassed by a slow, dated site they’d rather not share.', 'compass-digital' ),
				__( 'Businesses still relying on a Facebook page who need a proper home online.', 'compass-digital' ),
				__( 'Anyone burned by a developer who vanished after taking the money.', 'compass-digital' ),
			),
			'body'          => array(
				__( 'Your website is usually the first thing a customer sees before they ever call. If it’s slow, awkward on a phone or looks like it was built a decade ago, you lose the enquiry before you hear from them. Good website design and development fixes that quietly: the site loads fast, reads clearly, and makes the next step obvious.', 'compass-digital' ),
				__( 'We build every business website as a custom WordPress theme — no bloated page builders, no plugin for every little thing. That keeps your pages light, which is exactly what both your customers and Google reward. As website designers we care about the unglamorous parts: clean code, real accessibility, images that don’t blow your data, and a content structure search engines can actually read.', 'compass-digital' ),
				__( 'You get a site that’s genuinely yours. The domain, hosting and accounts are in your name, the design comes from a proper system rather than a stock theme, and you can edit text, images and prices yourself without paying us for every small change. When you do want a hand, we’re a message away — not a mystery you can’t reach.', 'compass-digital' ),
				__( 'Affordable website design doesn’t mean cheap or cut-corner. It means we scope honestly, build the right thing once, and leave you with something fast and durable instead of a pretty site that falls over in six months.', 'compass-digital' ),
			),
			'pains'         => array(
				array(
					'quote' => __( '“Our website looks old and I’m embarrassed to share it.”', 'compass-digital' ),
					'fix'   => __( 'We design a clean, modern site that reflects the business you’ve actually built — so sending the link feels good, not like an apology.', 'compass-digital' ),
				),
				array(
					'quote' => __( '“My site is so slow customers leave before it loads.”', 'compass-digital' ),
					'fix'   => __( 'We build lean and measure it, aiming for a sub-two-second load and a 95+ Lighthouse score on mobile.', 'compass-digital' ),
				),
				array(
					'quote' => __( '“I can’t update it myself — I pay someone for every little change.”', 'compass-digital' ),
					'fix'   => __( 'We build on tools that are easy to edit and show you how, so routine updates never need an invoice.', 'compass-digital' ),
				),
			),
			'included'      => array(
				array(
					'title' => __( 'Custom design', 'compass-digital' ),
					'text'  => __( 'A layout built around your business and brand, not a stock theme everyone else is using.', 'compass-digital' ),
				),
				array(
					'title' => __( 'Mobile-first build', 'compass-digital' ),
					'text'  => __( 'Designed for the phone first, because that’s where most of your visitors actually are.', 'compass-digital' ),
				),
				array(
					'title' => __( 'Easy editing', 'compass-digital' ),
					'text'  => __( 'A simple way to change words, images and prices yourself, with a short handover so you’re confident.', 'compass-digital' ),
				),
				array(
					'title' => __( 'Built to be found', 'compass-digital' ),
					'text'  => __( 'Clean structure, fast pages and the on-page SEO basics done right from day one.', 'compass-digital' ),
				),
			),
			'tech'          => array( 'WordPress (classic theme)', 'Vite build', 'WooCommerce (when selling)', 'Self-hosted fonts', 'Core Web Vitals tuned' ),
			'deliverables'  => array(
				__( 'A responsive, custom-built website on your own domain and hosting', 'compass-digital' ),
				__( 'Mobile, tablet and desktop layouts tested on real devices', 'compass-digital' ),
				__( 'On-page SEO basics, sitemap and analytics connected', 'compass-digital' ),
				__( 'A short training session and plain-language handover notes', 'compass-digital' ),
			),
			'price_factors' => array(
				__( 'How many pages and sections you need', 'compass-digital' ),
				__( 'Whether you’re selling online or taking bookings', 'compass-digital' ),
				__( 'Who writes the words and supplies the photos', 'compass-digital' ),
				__( 'Any systems to connect, like a CRM or payment gateway', 'compass-digital' ),
			),
			'industries'    => array( 'corporates-and-smes', 'travel-and-tourism', 'restaurants-and-cafes' ),
			'offer_catalog' => array( 'Business website design', 'Website redesign', 'Landing pages', 'Website care and support' ),
			'faqs'          => array(
				array(
					'q' => __( 'How much does a website cost?', 'compass-digital' ),
					'a' => __( 'It depends on size and features, so we give a fixed quote on request before any work starts — no hourly surprises. A simple business site costs less than an online store. Price mostly comes down to how many pages you need, whether you’re selling online, and who supplies the words and photos.', 'compass-digital' ),
				),
				array(
					'q' => __( 'How long does it take to build a website?', 'compass-digital' ),
					'a' => __( 'Most business websites take a few weeks from first conversation to launch. We agree a realistic timeline up front and keep you updated, so you’re never left wondering where things stand.', 'compass-digital' ),
				),
				array(
					'q' => __( 'Will my website work well on a phone?', 'compass-digital' ),
					'a' => __( 'Yes. We design mobile-first and test on real devices, because most visitors arrive on a phone. Pages are built to load fast and stay easy to use on a small screen.', 'compass-digital' ),
				),
				array(
					'q' => __( 'Can I update the website myself after launch?', 'compass-digital' ),
					'a' => __( 'Yes. We build on tools that are easy to edit and show you how to change text, images and prices. You’ll never have to pay us for a small tweak, and we’re here when you want help with something bigger.', 'compass-digital' ),
				),
				array(
					'q' => __( 'Do I own my website and domain?', 'compass-digital' ),
					'a' => __( 'Completely. The domain, hosting and accounts are set up in your name, and the site is yours to keep, move or hand to another developer at any time. We never lock you in.', 'compass-digital' ),
				),
			),
		),

		/* ---------------------------------------------------------------- */
		'seo'                            => array(
			'tax_name'      => 'SEO',
			'service_type'  => 'Search engine optimization',
			'title'         => __( 'SEO Services | Get Found on Google | Compass Digital', 'compass-digital' ),
			'meta'          => __( 'Get found on Google, wherever your customers are. Honest, measurable SEO — local rankings, faster pages, more enquiries. Request a free SEO review.', 'compass-digital' ),
			'eyebrow'       => __( 'SEO', 'compass-digital' ),
			'headline'      => __( 'Get found on Google — by the customers who are already looking.', 'compass-digital' ),
			'lede'          => __( 'Honest, measurable SEO services. We fix the technical basics, win the rankings that matter in your market, and turn searches into real enquiries — then show you exactly what’s working in plain language.', 'compass-digital' ),
			'who'           => array(
				__( 'Businesses whose competitors show up first on Google and don’t know why.', 'compass-digital' ),
				__( 'Owners paying for ads but getting no calls, who want traffic that lasts.', 'compass-digital' ),
				__( 'Anyone tired of SEO jargon and vanity numbers that mean nothing.', 'compass-digital' ),
			),
			'body'          => array(
				__( 'Most people searching for what you sell never scroll past the first few results. If a competitor sits above you, they get the call and you don’t — however good you actually are. Search engine optimization is simply the work of earning that spot, and keeping it, so the right customers find you at the moment they’re ready to buy.', 'compass-digital' ),
				__( 'We start with the unglamorous foundations: a site that loads fast, pages Google can read, a clean structure, and your Google Business Profile and local listings set up properly. For local SEO that groundwork is half the battle — it’s what gets you into the “near me” results in the places you serve.', 'compass-digital' ),
				__( 'Then we build around the words your customers actually type, not the ones a keyword tool guessed. As an SEO team we write pages that answer real questions plainly, earn links the honest way, and keep your content fresh. No tricks, no black-hat shortcuts that get you penalised six months later.', 'compass-digital' ),
				__( 'Above all, you’ll know what you’re paying for. Every month you get a report you can actually read — rankings, enquiries and exactly what we did — so your Google ranking stops being a mystery and starts being something you can see improving.', 'compass-digital' ),
			),
			'pains'         => array(
				array(
					'quote' => __( '“Competitors show up above me on Google and I don’t know why.”', 'compass-digital' ),
					'fix'   => __( 'We audit what’s holding you back, fix the technical basics, and build pages that out-rank them for the searches that matter.', 'compass-digital' ),
				),
				array(
					'quote' => __( '“I’m paying for ads but getting no calls.”', 'compass-digital' ),
					'fix'   => __( 'We earn rankings that keep working after the ad budget stops, and make sure the traffic we bring is ready to enquire.', 'compass-digital' ),
				),
				array(
					'quote' => __( '“Nobody explains anything — it’s all jargon.”', 'compass-digital' ),
					'fix'   => __( 'You get a plain-English monthly report: what moved, what we did, and what’s next. No vanity metrics.', 'compass-digital' ),
				),
			),
			'included'      => array(
				array(
					'title' => __( 'Technical fixes', 'compass-digital' ),
					'text'  => __( 'Speed, crawlability, mobile and structure sorted, so Google can find and trust your pages.', 'compass-digital' ),
				),
				array(
					'title' => __( 'Local SEO', 'compass-digital' ),
					'text'  => __( 'Google Business Profile, maps and local listings set up to win “near me” searches where you trade.', 'compass-digital' ),
				),
				array(
					'title' => __( 'Content that ranks', 'compass-digital' ),
					'text'  => __( 'Pages built around the questions your customers actually search, written to be useful first.', 'compass-digital' ),
				),
				array(
					'title' => __( 'Clear reporting', 'compass-digital' ),
					'text'  => __( 'A monthly report in plain language — rankings, enquiries and the work behind them.', 'compass-digital' ),
				),
			),
			'tech'          => array( 'Google Search Console', 'Google Business Profile', 'Analytics', 'Schema / structured data', 'Core Web Vitals' ),
			'deliverables'  => array(
				__( 'A technical SEO audit and a prioritised fix list', 'compass-digital' ),
				__( 'Local listings and Google Business Profile set up or cleaned up', 'compass-digital' ),
				__( 'Keyword-mapped, optimised pages and ongoing content', 'compass-digital' ),
				__( 'A monthly plain-language report of rankings and enquiries', 'compass-digital' ),
			),
			'price_factors' => array(
				__( 'How competitive your market and keywords are', 'compass-digital' ),
				__( 'The state of your current site and content', 'compass-digital' ),
				__( 'Whether it’s a one-off cleanup or ongoing work', 'compass-digital' ),
				__( 'How many locations or service areas you cover', 'compass-digital' ),
			),
			'industries'    => array( 'travel-and-tourism', 'ecommerce-brands', 'corporates-and-smes' ),
			'offer_catalog' => array( 'Technical SEO audit', 'Local SEO', 'On-page SEO', 'Ongoing SEO and content' ),
			'faqs'          => array(
				array(
					'q' => __( 'How long does SEO take to work?', 'compass-digital' ),
					'a' => __( 'Technical fixes can help within weeks, but ranking well for competitive terms usually takes a few months of steady work. We set honest expectations up front and show progress every month, so you can see it building rather than taking it on faith.', 'compass-digital' ),
				),
				array(
					'q' => __( 'What does local SEO include?', 'compass-digital' ),
					'a' => __( 'Setting up and optimising your Google Business Profile, getting your name, address and phone consistent across local listings, earning reviews, and building location pages — so you show up in “near me” searches in your city.', 'compass-digital' ),
				),
				array(
					'q' => __( 'Do you guarantee the number one spot on Google?', 'compass-digital' ),
					'a' => __( 'No honest SEO company can, and anyone who promises it is best avoided. Google’s results aren’t for sale. What we guarantee is sound, white-hat work and clear reporting, so your rankings and enquiries improve steadily and safely.', 'compass-digital' ),
				),
				array(
					'q' => __( 'Is SEO better than paying for ads?', 'compass-digital' ),
					'a' => __( 'They do different jobs. Ads bring instant traffic but stop the moment you stop paying; SEO builds rankings that keep working. Many businesses do both — ads for the short term while SEO grows underneath. We’ll advise what fits your goals.', 'compass-digital' ),
				),
				array(
					'q' => __( 'How much do SEO services cost?', 'compass-digital' ),
					'a' => __( 'It’s quote on request, because it depends on how competitive your market is and whether you want a one-off cleanup or ongoing work. We’ll give you a clear monthly or project price before anything starts, with no lock-in.', 'compass-digital' ),
				),
			),
		),

		/* ---------------------------------------------------------------- */
		'pos-systems'                    => array(
			'tax_name'      => 'POS systems',
			'service_type'  => 'Point of sale system',
			'title'         => __( 'POS Systems — Billing & Stock | Compass Digital', 'compass-digital' ),
			'meta'          => __( 'Fast, reliable POS for shops and restaurants — quick billing, live stock, clear reports, works offline. Request a demo quote.', 'compass-digital' ),
			'eyebrow'       => __( 'POS systems', 'compass-digital' ),
			'headline'      => __( 'Bill in seconds, know your stock, and keep selling even when the internet drops.', 'compass-digital' ),
			'lede'          => __( 'A fast, reliable POS system for shops and restaurants — quick billing, live stock, clear reports, and point of sale that keeps working offline when the connection doesn’t.', 'compass-digital' ),
			'who'           => array(
				__( 'Shops and restaurants where the queue backs up at the busiest times.', 'compass-digital' ),
				__( 'Owners who can’t tell what’s really selling and what’s just sitting there.', 'compass-digital' ),
				__( 'Anyone whose POS and stock don’t talk to each other.', 'compass-digital' ),
			),
			'body'          => array(
				__( 'At your busiest moment the till shouldn’t be the bottleneck. A good POS system bills in seconds, takes any payment your customers use, and prints a kitchen or counter ticket without a second thought — so the queue keeps moving and nobody walks out.', 'compass-digital' ),
				__( 'Behind the counter, the point of sale quietly keeps your stock honest. Every sale updates live inventory, so you stop guessing what’s on the shelf and start seeing what actually sells. Clear daily reports tell you your best lines, your slow movers and your real takings — the billing software gives you the numbers to make decisions, not just a receipt.', 'compass-digital' ),
				__( 'We know connections drop. That’s why our retail POS keeps billing through an outage and syncs the moment you’re back online. A lost connection never costs you a sale, and you’re never stuck apologising to a customer at the counter.', 'compass-digital' ),
				__( 'Whether you run one shop or several, we set it up around how you actually work — your products, your prices, your tax, your receipts. Our restaurant POS handles tables, KOT and takeaway; retail handles barcodes, variants and multi-branch stock. Either way it’s dependable, supported, and built to make a busy day calmer.', 'compass-digital' ),
			),
			'pains'         => array(
				array(
					'quote' => __( '“Billing is slow and the queue backs up when we’re busy.”', 'compass-digital' ),
					'fix'   => __( 'Fast, keyboard- and scanner-friendly billing that takes seconds per sale, so the line keeps moving at peak.', 'compass-digital' ),
				),
				array(
					'quote' => __( '“I don’t know what’s selling and what’s just sitting there.”', 'compass-digital' ),
					'fix'   => __( 'Live stock and clear daily reports show your best and slowest lines at a glance.', 'compass-digital' ),
				),
				array(
					'quote' => __( '“I need something that works offline when the internet drops.”', 'compass-digital' ),
					'fix'   => __( 'Our POS keeps billing during an outage and syncs automatically when you’re back online.', 'compass-digital' ),
				),
			),
			'included'      => array(
				array(
					'title' => __( 'Fast billing', 'compass-digital' ),
					'text'  => __( 'Quick sales with barcode scanning, split payments and printed or digital receipts.', 'compass-digital' ),
				),
				array(
					'title' => __( 'Live stock', 'compass-digital' ),
					'text'  => __( 'Inventory that updates with every sale, with low-stock alerts and multi-branch support.', 'compass-digital' ),
				),
				array(
					'title' => __( 'Works offline', 'compass-digital' ),
					'text'  => __( 'Billing carries on through outages and syncs the moment the connection returns.', 'compass-digital' ),
				),
				array(
					'title' => __( 'Clear reports', 'compass-digital' ),
					'text'  => __( 'Daily takings, best sellers and slow movers in reports you can read at a glance.', 'compass-digital' ),
				),
			),
			'tech'          => array( 'Offline-first sync', 'Barcode / scanner support', 'Thermal receipt & KOT printing', 'Multi-branch inventory', 'Role-based access' ),
			'deliverables'  => array(
				__( 'A POS set up with your products, prices, tax and receipts', 'compass-digital' ),
				__( 'Billing, inventory and reporting configured for how you work', 'compass-digital' ),
				__( 'Hardware guidance for tills, scanners and printers', 'compass-digital' ),
				__( 'Staff training and after-launch support', 'compass-digital' ),
			),
			'price_factors' => array(
				__( 'How many tills and branches you run', 'compass-digital' ),
				__( 'Retail, restaurant or both', 'compass-digital' ),
				__( 'Whether you need new hardware', 'compass-digital' ),
				__( 'Any systems to connect, like accounting or your website', 'compass-digital' ),
			),
			'industries'    => array( 'retail-shops', 'restaurants-and-cafes', 'corporates-and-smes' ),
			'offer_catalog' => array( 'Retail POS', 'Restaurant POS', 'Inventory management', 'Multi-branch POS' ),
			'faqs'          => array(
				array(
					'q' => __( 'Does the POS work without internet?', 'compass-digital' ),
					'a' => __( 'Yes. It’s built offline-first, so billing keeps working during an outage and syncs automatically the moment you’re back online. A dropped connection never stops a sale or holds up your queue.', 'compass-digital' ),
				),
				array(
					'q' => __( 'Can it handle more than one shop or branch?', 'compass-digital' ),
					'a' => __( 'Yes. You can run several branches with shared products and see stock and sales across all of them from one place, while each branch still has its own till and reports.', 'compass-digital' ),
				),
				array(
					'q' => __( 'Is this POS for restaurants or retail shops?', 'compass-digital' ),
					'a' => __( 'Both. For restaurants and cafes it handles tables, kitchen tickets (KOT) and takeaway; for retail it handles barcodes, product variants and stock. We set it up around your kind of business.', 'compass-digital' ),
				),
				array(
					'q' => __( 'Do I need to buy new hardware?', 'compass-digital' ),
					'a' => __( 'Often not — it runs on hardware you may already have, and we’ll tell you honestly what works and what doesn’t. If you do need a till, scanner or printer, we’ll guide you to sensible, affordable options.', 'compass-digital' ),
				),
				array(
					'q' => __( 'How much does a POS system cost?', 'compass-digital' ),
					'a' => __( 'Quote on request. It depends mainly on how many tills and branches you run, whether you’re retail or restaurant, and whether you need hardware. We’ll give you a clear price and a demo before you commit.', 'compass-digital' ),
				),
			),
		),

		/* ---------------------------------------------------------------- */
		'mobile-app-development'         => array(
			'tax_name'      => 'Mobile app development',
			'service_type'  => 'Mobile app development',
			'title'         => __( 'Mobile App Development | Compass Digital', 'compass-digital' ),
			'meta'          => __( 'Android and iOS apps — clear, dependable and built around how you work. See our work and request a quote.', 'compass-digital' ),
			'eyebrow'       => __( 'Mobile app development', 'compass-digital' ),
			'headline'      => __( 'An app your customers understand on the first try.', 'compass-digital' ),
			'lede'          => __( 'Clear, dependable Android and iOS apps — designed around how your business actually works, not a template, and supported long after launch.', 'compass-digital' ),
			'who'           => array(
				__( 'Businesses whose customers live on their phones and want an easier way to order, book or return.', 'compass-digital' ),
				__( 'Owners who need an app built around their real workflow, not a generic one.', 'compass-digital' ),
				__( 'Anyone who wants a dependable partner who won’t disappear after launch.', 'compass-digital' ),
			),
			'body'          => array(
				__( 'An app only earns its place if it makes something genuinely easier — ordering, booking, loyalty, a service your customers use often. We don’t build an app for the sake of it. Mobile app development should start with a simple question: what would make your customers’ lives easier, and is an app really the best way to do it? Sometimes the honest answer is a good website, and we’ll tell you so.', 'compass-digital' ),
				__( 'When an app is the right call, we build it around how you actually work. As app developers we design the screens so a first-time user understands them without a manual, keep the flow short, and make sure it feels fast on the mid-range phones most people carry — not just the latest flagship.', 'compass-digital' ),
				__( 'We handle Android app development and iOS app development together, so you reach everyone without paying twice for the same work. Behind the screens we connect the app to the systems you already use — your website, your POS, your payments — so it’s one joined-up business, not another island of data.', 'compass-digital' ),
				__( 'And we stay. A custom mobile app needs updates as phones and app stores change, so we support, maintain and grow it after launch. You get a dependable app and a team you can still reach a year later.', 'compass-digital' ),
			),
			'pains'         => array(
				array(
					'quote' => __( '“My customers live on their phones — and I’ve got nothing for them there.”', 'compass-digital' ),
					'fix'   => __( 'We build the app that makes ordering, booking or returning genuinely easy — the reason they’ll keep it installed.', 'compass-digital' ),
				),
				array(
					'quote' => __( '“Off-the-shelf apps don’t fit how we actually work.”', 'compass-digital' ),
					'fix'   => __( 'We design around your real workflow, so the app matches your business instead of forcing you to match it.', 'compass-digital' ),
				),
				array(
					'quote' => __( '“The developer disappeared after taking the money.”', 'compass-digital' ),
					'fix'   => __( 'We support and update the app after launch, and the code and store accounts are yours.', 'compass-digital' ),
				),
			),
			'included'      => array(
				array(
					'title' => __( 'Android & iOS', 'compass-digital' ),
					'text'  => __( 'One well-built app for both platforms, so you reach every customer without double the cost.', 'compass-digital' ),
				),
				array(
					'title' => __( 'Built around you', 'compass-digital' ),
					'text'  => __( 'Screens and flows designed for your workflow, clear enough to use on the first try.', 'compass-digital' ),
				),
				array(
					'title' => __( 'Connected', 'compass-digital' ),
					'text'  => __( 'Linked to your website, POS or payments so your business stays joined up.', 'compass-digital' ),
				),
				array(
					'title' => __( 'Supported', 'compass-digital' ),
					'text'  => __( 'Maintenance and updates after launch as phones and app stores change.', 'compass-digital' ),
				),
			),
			'tech'          => array( 'Cross-platform (Android & iOS)', 'Secure APIs', 'Local & online payments', 'Push notifications', 'App Store & Play Store release' ),
			'deliverables'  => array(
				__( 'A published Android and iOS app in your own store accounts', 'compass-digital' ),
				__( 'A workflow designed and tested around your business', 'compass-digital' ),
				__( 'Connections to your website, POS or payment systems', 'compass-digital' ),
				__( 'Post-launch support, updates and the source code', 'compass-digital' ),
			),
			'price_factors' => array(
				__( 'How many features and screens the app needs', 'compass-digital' ),
				__( 'Android, iOS or both', 'compass-digital' ),
				__( 'Whether it connects to other systems or payments', 'compass-digital' ),
				__( 'How much ongoing support you want', 'compass-digital' ),
			),
			'industries'    => array( 'restaurants-and-cafes', 'retail-shops', 'travel-and-tourism' ),
			'offer_catalog' => array( 'Android app development', 'iOS app development', 'Cross-platform apps', 'App maintenance' ),
			'faqs'          => array(
				array(
					'q' => __( 'Do you build for both Android and iOS?', 'compass-digital' ),
					'a' => __( 'Yes. We build cross-platform, so one codebase serves both Android and iOS. That means you reach every customer without paying to build the same app twice.', 'compass-digital' ),
				),
				array(
					'q' => __( 'Do I actually need an app, or is a website enough?', 'compass-digital' ),
					'a' => __( 'Often a fast website is enough, and we’ll tell you honestly if it is. An app makes sense when customers use you often — ordering, bookings or loyalty — and a home-screen icon genuinely makes that easier.', 'compass-digital' ),
				),
				array(
					'q' => __( 'How long does it take to build an app?', 'compass-digital' ),
					'a' => __( 'A focused first version usually takes a couple of months; bigger apps take longer. We start with the core that delivers value, launch it, then grow it — rather than spending a year before anyone uses it.', 'compass-digital' ),
				),
				array(
					'q' => __( 'Will you publish it to the app stores for me?', 'compass-digital' ),
					'a' => __( 'Yes. We handle the Play Store and App Store submission and set the accounts up in your name, so you own the listings. We also guide you through the store requirements so approval goes smoothly.', 'compass-digital' ),
				),
				array(
					'q' => __( 'What does mobile app development cost?', 'compass-digital' ),
					'a' => __( 'Quote on request. It depends on how many features you need, whether it’s Android, iOS or both, and what it connects to. We scope it with you and give a clear price before building.', 'compass-digital' ),
				),
			),
		),

		/* ---------------------------------------------------------------- */
		'ecommerce-websites'             => array(
			'tax_name'      => 'Ecommerce websites',
			'service_type'  => 'Ecommerce website development',
			'title'         => __( 'Ecommerce Website Development | Compass Digital', 'compass-digital' ),
			'meta'          => __( 'Start selling online. Fast, secure ecommerce stores with the right payments for your market and easy stock control. Request a quote today.', 'compass-digital' ),
			'eyebrow'       => __( 'Ecommerce websites', 'compass-digital' ),
			'headline'      => __( 'Start selling online — without needing a tech team to run it.', 'compass-digital' ),
			'lede'          => __( 'Fast, secure ecommerce websites with the payment options your market uses and stock control that’s genuinely easy to run — so you can sell online from day one.', 'compass-digital' ),
			'who'           => array(
				__( 'Brands with products people want and no proper way to take an order.', 'compass-digital' ),
				__( 'Owners collecting enquiries on WhatsApp who are ready to sell online properly.', 'compass-digital' ),
				__( 'Anyone who wants an online store they can run themselves.', 'compass-digital' ),
			),
			'body'          => array(
				__( 'You’ve got products people want and enquiries landing in your WhatsApp — what’s missing is a proper way to take the order and get paid. An ecommerce website closes that gap: customers browse, choose and check out on their own, at any hour, without you typing out another price list.', 'compass-digital' ),
				__( 'We build online stores that are fast and secure, because a slow or shaky checkout is where sales quietly leak away. Pages load quickly, the cart is simple, and the whole thing works beautifully on a phone — which is where most shoppers buy. Selling online shouldn’t feel fragile, and ours doesn’t.', 'compass-digital' ),
				__( 'Crucially, it’s set up for how your customers actually pay. We connect the payment gateways they trust, and cash on delivery where it’s expected, so checkout works for your customers from the first day instead of turning them away. Stock control is built in and genuinely easy, so what’s sold out online matches what’s sold out in real life.', 'compass-digital' ),
				__( 'As WooCommerce developers we build on tools you can run yourself — add products, change prices, run an offer — without calling a developer for every change. And because it’s built to be found as well as to sell, your store has a real chance of ranking, not just existing. Ecommerce web design that earns its keep.', 'compass-digital' ),
			),
			'pains'         => array(
				array(
					'quote' => __( '“I want to sell online but I don’t know where to start.”', 'compass-digital' ),
					'fix'   => __( 'We handle the whole setup — store, payments, stock and launch — and show you how to run it in plain language.', 'compass-digital' ),
				),
				array(
					'quote' => __( '“I get enquiries on WhatsApp but no proper way to collect orders.”', 'compass-digital' ),
					'fix'   => __( 'A real checkout takes orders and payment around the clock, so you stop re-typing prices into chats.', 'compass-digital' ),
				),
				array(
					'quote' => __( '“I’m scared of being locked into a system I can’t get out of.”', 'compass-digital' ),
					'fix'   => __( 'We build on open tools you own, so your store, data and customers are always yours to take anywhere.', 'compass-digital' ),
				),
			),
			'included'      => array(
				array(
					'title' => __( 'Fast, secure store', 'compass-digital' ),
					'text'  => __( 'A quick, mobile-first storefront with a simple checkout that doesn’t lose sales.', 'compass-digital' ),
				),
				array(
					'title' => __( 'Local payments', 'compass-digital' ),
					'text'  => __( 'The gateways your customers trust, and cash on delivery where it’s expected.', 'compass-digital' ),
				),
				array(
					'title' => __( 'Easy stock control', 'compass-digital' ),
					'text'  => __( 'Add products, set prices and manage inventory yourself, without a developer.', 'compass-digital' ),
				),
				array(
					'title' => __( 'Built to rank', 'compass-digital' ),
					'text'  => __( 'Product and category pages structured to be found on Google, not just to exist.', 'compass-digital' ),
				),
			),
			'tech'          => array( 'WordPress + WooCommerce', 'Local payment gateways', 'Cash on delivery', 'Shipping & tax rules', 'Product SEO' ),
			'deliverables'  => array(
				__( 'A live online store on your own domain and hosting', 'compass-digital' ),
				__( 'Payments, shipping and tax configured for your market', 'compass-digital' ),
				__( 'Your products loaded and stock control set up', 'compass-digital' ),
				__( 'Training so you can run and grow the store yourself', 'compass-digital' ),
			),
			'price_factors' => array(
				__( 'How many products and categories you sell', 'compass-digital' ),
				__( 'Which payment and delivery options you need', 'compass-digital' ),
				__( 'Whether products and photos are ready', 'compass-digital' ),
				__( 'Any systems to connect, like your POS or accounting', 'compass-digital' ),
			),
			'industries'    => array( 'ecommerce-brands', 'retail-shops', 'corporates-and-smes' ),
			'offer_catalog' => array( 'WooCommerce store', 'Online store setup', 'Payment gateway integration', 'Ecommerce redesign' ),
			'faqs'          => array(
				array(
					'q' => __( 'Which payment methods can my store accept?', 'compass-digital' ),
					'a' => __( 'We connect the payment gateways your customers already trust for cards and online payment, and we can add cash on delivery too. Your customers pay the way they’re comfortable, which means fewer abandoned carts at checkout.', 'compass-digital' ),
				),
				array(
					'q' => __( 'Can I manage products and stock myself?', 'compass-digital' ),
					'a' => __( 'Yes. We build on WooCommerce, which is genuinely easy to run — you can add products, change prices, run offers and manage stock yourself. We train you at handover, and we’re here if you want help later.', 'compass-digital' ),
				),
				array(
					'q' => __( 'Do I own my online store?', 'compass-digital' ),
					'a' => __( 'Completely. It’s built on open tools, hosted in your name, and your store, data and customer list are always yours to keep or move. You’re never locked into a system you can’t get out of.', 'compass-digital' ),
				),
				array(
					'q' => __( 'Can the store connect to my POS?', 'compass-digital' ),
					'a' => __( 'Often yes. If you run a shop as well, we can line up your online store and your POS so stock and orders stay in step, instead of counting two different sets of numbers.', 'compass-digital' ),
				),
				array(
					'q' => __( 'How much does an ecommerce website cost?', 'compass-digital' ),
					'a' => __( 'Quote on request. It depends on how many products you sell, which payment and delivery options you need, and whether your products and photos are ready. We’ll give you a clear price before any work starts.', 'compass-digital' ),
				),
			),
		),

		/* ---------------------------------------------------------------- */
		'custom-software'                => array(
			'tax_name'      => 'Custom software',
			'service_type'  => 'Custom software development',
			'title'         => __( 'Custom Software Development | Compass Digital', 'compass-digital' ),
			'meta'          => __( 'Bespoke business software — dependable systems built around how your team actually works. Request a quote.', 'compass-digital' ),
			'eyebrow'       => __( 'Custom software', 'compass-digital' ),
			'headline'      => __( 'Software built around how your team actually works.', 'compass-digital' ),
			'lede'          => __( 'Bespoke business software — dependable systems shaped around your real workflow, so the tool fits the team instead of the team bending around the tool.', 'compass-digital' ),
			'who'           => array(
				__( 'Businesses outgrowing spreadsheets and off-the-shelf tools that don’t quite fit.', 'compass-digital' ),
				__( 'Teams stitching several apps together by hand and losing time to it.', 'compass-digital' ),
				__( 'Owners who want a dependable system and a developer who stays.', 'compass-digital' ),
			),
			'body'          => array(
				__( 'Most businesses reach a point where the usual tools stop fitting. The spreadsheet has become a monster, three different apps don’t talk to each other, and someone spends half a day re-typing the same numbers. Custom software development exists for exactly that moment: a system built around how your team actually works, not how a generic product assumes you should.', 'compass-digital' ),
				__( 'We start by understanding the real workflow — the steps, the exceptions, the bits everyone complains about — before writing a line of code. As a software team we’d rather build the right smaller thing than an impressive system nobody uses. Bespoke software only pays off if it removes work, so that’s the test we hold it to.', 'compass-digital' ),
				__( 'What we build is dependable and genuinely yours. Clean, documented code, your data in your hands, and no lock-in to a black box you can’t escape. Business software development should leave you freer, not more dependent, so we hand over properly and you own what we make.', 'compass-digital' ),
				__( 'And we don’t vanish. Good software grows with the business, so our developers stay on to support it, fix it and extend it as you change. You get a system that fits today and can keep fitting tomorrow — and a team that’s still there when you need them.', 'compass-digital' ),
			),
			'pains'         => array(
				array(
					'quote' => __( '“Off-the-shelf tools don’t fit how we actually work.”', 'compass-digital' ),
					'fix'   => __( 'We build around your real workflow, so the software matches your process instead of forcing you to change it.', 'compass-digital' ),
				),
				array(
					'quote' => __( '“My systems don’t talk to each other.”', 'compass-digital' ),
					'fix'   => __( 'We connect what you use into one joined-up system, so data stops being re-typed from app to app.', 'compass-digital' ),
				),
				array(
					'quote' => __( '“The developer disappeared after taking the money.”', 'compass-digital' ),
					'fix'   => __( 'We hand over clean, documented code you own, and stay on to support and grow it.', 'compass-digital' ),
				),
			),
			'included'      => array(
				array(
					'title' => __( 'Workflow-led design', 'compass-digital' ),
					'text'  => __( 'We map how your team really works first, then build the system to fit it.', 'compass-digital' ),
				),
				array(
					'title' => __( 'Built to connect', 'compass-digital' ),
					'text'  => __( 'Links to the tools and data you already use, so everything stays joined up.', 'compass-digital' ),
				),
				array(
					'title' => __( 'Yours to own', 'compass-digital' ),
					'text'  => __( 'Clean, documented code and your data in your hands — no black box, no lock-in.', 'compass-digital' ),
				),
				array(
					'title' => __( 'Supported', 'compass-digital' ),
					'text'  => __( 'We stay on to maintain, fix and extend the system as your business changes.', 'compass-digital' ),
				),
			),
			'tech'          => array( 'Web-based systems', 'Secure APIs & integrations', 'Role-based access', 'Reporting & dashboards', 'Cloud or on-premise' ),
			'deliverables'  => array(
				__( 'A bespoke system built around your workflow', 'compass-digital' ),
				__( 'Integrations with the tools and data you already use', 'compass-digital' ),
				__( 'Documentation, training and your own source code', 'compass-digital' ),
				__( 'Ongoing support and a roadmap to grow it', 'compass-digital' ),
			),
			'price_factors' => array(
				__( 'How complex the workflow and features are', 'compass-digital' ),
				__( 'How many users and roles you need', 'compass-digital' ),
				__( 'What it has to integrate with', 'compass-digital' ),
				__( 'Whether you want ongoing support and new features', 'compass-digital' ),
			),
			'industries'    => array( 'corporates-and-smes', 'retail-shops', 'travel-and-tourism' ),
			'offer_catalog' => array( 'Bespoke business software', 'System integration', 'Internal tools & dashboards', 'Software support' ),
			'faqs'          => array(
				array(
					'q' => __( 'Custom software or off-the-shelf — which is right for me?', 'compass-digital' ),
					'a' => __( 'If a ready-made tool fits, use it — it’s cheaper and faster. Custom software is worth it when nothing off-the-shelf matches how you work, or when stitching several apps together is costing you real time. We’ll give you an honest view.', 'compass-digital' ),
				),
				array(
					'q' => __( 'Can it connect to the systems I already use?', 'compass-digital' ),
					'a' => __( 'Usually yes. We build integrations so your new system talks to the tools and data you already have — your website, POS, accounting or others — so nobody has to re-type the same numbers across apps.', 'compass-digital' ),
				),
				array(
					'q' => __( 'Do I own the software and the code?', 'compass-digital' ),
					'a' => __( 'Yes. You own the source code and your data, with proper documentation at handover. There’s no black box and no lock-in, so another developer could pick it up if they ever needed to.', 'compass-digital' ),
				),
				array(
					'q' => __( 'What happens after the software is built?', 'compass-digital' ),
					'a' => __( 'We stay on. Good software grows with the business, so we support, fix and extend it as you change. You’re never left with a system nobody can maintain.', 'compass-digital' ),
				),
				array(
					'q' => __( 'How much does custom software cost?', 'compass-digital' ),
					'a' => __( 'Quote on request. It depends on how complex the workflow is, how many users and roles you need, and what it integrates with. We scope it carefully with you and give a clear price before building.', 'compass-digital' ),
				),
			),
		),
	);

	return $data;
}

/**
 * The ordered slugs, used by the hub and for navigation.
 *
 * @return string[]
 */
function compass_services_order() {
	return array(
		'website-design-and-development',
		'seo',
		'pos-systems',
		'mobile-app-development',
		'ecommerce-websites',
		'custom-software',
	);
}

/**
 * Short label for a service (the eyebrow), used in cross-links and lists.
 *
 * @param string $slug Service slug.
 * @return string
 */
function compass_service_label( $slug ) {
	$data = compass_services_data();
	return isset( $data[ $slug ] ) ? $data[ $slug ]['eyebrow'] : '';
}

/**
 * Industry display labels, keyed by slug (for related-industry cross-links).
 *
 * @return array<string,string>
 */
function compass_industry_labels() {
	return array(
		'retail-shops'          => __( 'Retail shops', 'compass-digital' ),
		'restaurants-and-cafes' => __( 'Restaurants & cafes', 'compass-digital' ),
		'travel-and-tourism'    => __( 'Travel & tourism', 'compass-digital' ),
		'ecommerce-brands'      => __( 'Ecommerce brands', 'compass-digital' ),
		'corporates-and-smes'   => __( 'Corporates & SMEs', 'compass-digital' ),
	);
}
