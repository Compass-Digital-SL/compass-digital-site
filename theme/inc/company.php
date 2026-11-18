<?php
/**
 * Company pages (Task 14): About, Process and the global FAQ.
 *
 * One slug-keyed source drives the copy; the templates (page-about.php,
 * page-process.php, page-faq.php) and the JSON-LD read from here, so the visible
 * page and its structured data never drift. Copy follows docs/BRAND.md (plain,
 * confident, warm — no jargon, no hype). No prices anywhere: every CTA leads to
 * "Request a quote".
 *
 * Reuses the shared JSON-LD helpers from inc/services.php (compass_faq_schema,
 * compass_breadcrumb_schema, compass_print_schema, compass_quote_url). Like the
 * services wiring, this is scoped to these three templates and unhooks cleanly
 * when Task 16 generalises titles, metas and schema site-wide.
 *
 * @package CompassDigital
 */

defined( 'ABSPATH' ) || exit;

/**
 * Is the current request one of the company templates? Returns the template
 * basename ('page-about.php' etc.) or ''.
 *
 * @return string
 */
function compass_current_company_template() {
	if ( ! is_page() ) {
		return '';
	}
	foreach ( array( 'page-about.php', 'page-process.php', 'page-faq.php' ) as $tpl ) {
		if ( is_page_template( $tpl ) ) {
			return $tpl;
		}
	}
	return '';
}

/**
 * The four-step "how we work" process, in depth.
 *
 * Mirrors the short version in compass_service_process() (Listen, Plan, Build,
 * Support) so the story is one story everywhere, and expands each step with what
 * happens, what we need from the client, and a realistic time range.
 *
 * @return array<int,array<string,mixed>>
 */
function compass_process_steps() {
	return array(
		array(
			'title'    => __( 'Listen', 'compass-digital' ),
			'summary'  => __( 'We start with your business, not a template.', 'compass-digital' ),
			'body'     => array(
				__( 'Before we talk websites or software, we want to understand how you actually work — who your customers are, how they find you, where the day gets stuck, and what a genuine win looks like for you. A faster billing queue is a different job from more enquiries, and the two lead to different builds.', 'compass-digital' ),
				__( 'This is a conversation, not a sales pitch. We ask plain questions and listen to the answers. If a cheaper or simpler option would serve you better, we will say so — even when it means a smaller project for us.', 'compass-digital' ),
			),
			'need'     => array(
				__( 'An honest picture of the problem you are trying to fix', 'compass-digital' ),
				__( 'Access to your current site, accounts or system, if any', 'compass-digital' ),
				__( 'A rough sense of your budget, so we scope the right thing', 'compass-digital' ),
			),
			'timeline' => __( '2–5 days', 'compass-digital' ),
		),
		array(
			'title'    => __( 'Plan', 'compass-digital' ),
			'summary'  => __( 'A clear scope, a fixed quote and a realistic timeline.', 'compass-digital' ),
			'body'     => array(
				__( 'We turn the conversation into a written plan: what we will build, what it includes, what it will cost, and when it will be ready. No hourly surprises and nothing padded in that you do not need. You read it in plain language and sign off before any work starts.', 'compass-digital' ),
				__( 'If the project is large, we break it into stages so you can see value early and spread the work. You always know what the next step is and what it costs.', 'compass-digital' ),
			),
			'need'     => array(
				__( 'Your feedback on the scope — add, trim or ask questions', 'compass-digital' ),
				__( 'Sign-off on the quote and timeline so we can book you in', 'compass-digital' ),
			),
			'timeline' => __( '3–7 days', 'compass-digital' ),
		),
		array(
			'title'    => __( 'Build', 'compass-digital' ),
			'summary'  => __( 'We build in the open and share progress as we go.', 'compass-digital' ),
			'body'     => array(
				__( 'You see the work take shape on a real preview link, not in a black box. We share progress at regular checkpoints so you can steer early — a small change midway is easy, a big reveal that misses the mark is not. We build lean and test on real phones, because that is where most of your customers are.', 'compass-digital' ),
				__( 'This is where content matters most. The sooner we have your words, photos and product details, the faster the build moves. We can help with copy and images if you would rather we did.', 'compass-digital' ),
			),
			'need'     => array(
				__( 'Your content: words, photos, logo and product or menu details', 'compass-digital' ),
				__( 'Prompt feedback at each checkpoint so momentum holds', 'compass-digital' ),
				__( 'A decision-maker available for quick questions', 'compass-digital' ),
			),
			'timeline' => __( '2–6 weeks', 'compass-digital' ),
		),
		array(
			'title'    => __( 'Support', 'compass-digital' ),
			'summary'  => __( 'We stay on after launch — and it is always yours.', 'compass-digital' ),
			'body'     => array(
				__( 'Launch is the start, not the end. We hand over your site or system with a short training session and plain-language notes, so your team is confident running it. The domain, hosting and accounts are in your name from day one — nothing is locked to us, and you can take the work anywhere.', 'compass-digital' ),
				__( 'After that we are a message away for fixes, updates and the next thing you want to grow. No developer who vanishes after the invoice clears.', 'compass-digital' ),
			),
			'need'     => array(
				__( 'An hour for a handover and training walkthrough', 'compass-digital' ),
				__( 'A note whenever something needs fixing or you want to grow', 'compass-digital' ),
			),
			'timeline' => __( 'Ongoing', 'compass-digital' ),
		),
	);
}

/**
 * The company values (from docs/BRAND.md), with a sentence of substance each.
 *
 * @return array<int,array<string,string>>
 */
function compass_company_values() {
	return array(
		array(
			'title' => __( 'Clarity', 'compass-digital' ),
			'text'  => __( 'We explain things in plain language and quote a fixed price up front. You always know what you are getting, what it costs, and why.', 'compass-digital' ),
		),
		array(
			'title' => __( 'Direction', 'compass-digital' ),
			'text'  => __( 'We help you choose the right next step, not the biggest one. Advice first, build second — even when the honest answer is "you do not need that yet".', 'compass-digital' ),
		),
		array(
			'title' => __( 'Craft', 'compass-digital' ),
			'text'  => __( 'We build the unglamorous parts well: fast pages, clean code, real accessibility. Work that holds up long after launch, not a pretty shell.', 'compass-digital' ),
		),
		array(
			'title' => __( 'Reliability', 'compass-digital' ),
			'text'  => __( 'We turn up, we finish, and we stay reachable. Your site, accounts and data are yours — no lock-in, no disappearing act.', 'compass-digital' ),
		),
	);
}

/**
 * The global FAQ: the questions Sri Lankan owners actually ask, grouped. Each
 * group has a label and a list of {q, a} pairs. The flat list of all pairs
 * drives the FAQPage JSON-LD.
 *
 * @return array<int,array<string,mixed>>
 */
function compass_faq_groups() {
	return array(
		array(
			'label' => __( 'Working with us', 'compass-digital' ),
			'faqs'  => array(
				array(
					'q' => __( 'Where are you based, and who do you work with?', 'compass-digital' ),
					'a' => __( 'We are a Sri Lankan software and digital services firm based in Colombo, working with businesses across the island — retail shops, restaurants and cafes, tour operators, ecommerce brands, and corporates and SMEs. Because we work remotely as standard, we also help Sri Lankan-owned businesses overseas.', 'compass-digital' ),
				),
				array(
					'q' => __( 'How do we get started?', 'compass-digital' ),
					'a' => __( 'Request a quote and tell us what you are trying to fix. We will have a short, no-pressure conversation to understand your business, then send a clear written plan with a fixed price and timeline. Nothing is charged until you sign off on that plan.', 'compass-digital' ),
				),
				array(
					'q' => __( 'What if I have been burned by a developer before?', 'compass-digital' ),
					'a' => __( 'A lot of our clients have. That is exactly why we put the scope, price and timeline in writing before we start, build on a preview link you can watch, and keep your domain, hosting and accounts in your name from day one. If you ever want to move on, the work goes with you.', 'compass-digital' ),
				),
			),
		),
		array(
			'label' => __( 'Cost and timelines', 'compass-digital' ),
			'faqs'  => array(
				array(
					'q' => __( 'How much will my project cost?', 'compass-digital' ),
					'a' => __( 'It depends on size and features, so we give a fixed quote on request before any work starts — no hourly surprises. A simple business website costs less than an online store or a custom system. Price mostly comes down to how many pages or features you need, whether you are selling online, and who supplies the words and photos.', 'compass-digital' ),
				),
				array(
					'q' => __( 'Why don’t you list prices on the site?', 'compass-digital' ),
					'a' => __( 'Because an honest price needs to fit your actual project, not a made-up average. A fixed quote after a ten-minute conversation is fairer to you than a headline number that balloons later. You will never be charged more than the figure you signed off.', 'compass-digital' ),
				),
				array(
					'q' => __( 'How long does a project take?', 'compass-digital' ),
					'a' => __( 'Most business websites take two to six weeks from first conversation to launch; POS rollouts and custom software vary with scope. We agree a realistic timeline up front and keep you updated at each checkpoint, so you are never left wondering where things stand.', 'compass-digital' ),
				),
				array(
					'q' => __( 'Do you take a deposit?', 'compass-digital' ),
					'a' => __( 'Yes. We usually split payment into clear stages tied to progress — a deposit to book you in, and the balance at agreed milestones. Everything is set out in the written quote before you commit.', 'compass-digital' ),
				),
			),
		),
		array(
			'label' => __( 'Websites and SEO', 'compass-digital' ),
			'faqs'  => array(
				array(
					'q' => __( 'Will my website work well on a phone?', 'compass-digital' ),
					'a' => __( 'Yes. We design mobile-first and test on real devices, because most Sri Lankan visitors arrive on a phone. Pages are built to load in under two seconds and stay easy to use on a small screen.', 'compass-digital' ),
				),
				array(
					'q' => __( 'Can I update the website myself?', 'compass-digital' ),
					'a' => __( 'Yes. We build on tools that are easy to edit and show you how during handover, so routine changes — text, images, prices — never need an invoice. When you do want a hand with something bigger, we are a message away.', 'compass-digital' ),
				),
				array(
					'q' => __( 'Will I actually show up on Google?', 'compass-digital' ),
					'a' => __( 'SEO is honest work, not a magic switch, so we will not promise a number-one ranking overnight. We build fast, well-structured pages, set up the local SEO basics, and tell you plainly what moves the needle for your business and what it will take.', 'compass-digital' ),
				),
			),
		),
		array(
			'label' => __( 'POS, software and support', 'compass-digital' ),
			'faqs'  => array(
				array(
					'q' => __( 'Does the POS work when the internet drops?', 'compass-digital' ),
					'a' => __( 'Billing needs to keep working during an outage, so we set up POS systems to keep taking sales offline and sync up again when the connection returns. We will walk you through exactly how it behaves before you commit.', 'compass-digital' ),
				),
				array(
					'q' => __( 'Do you build custom software, or only websites?', 'compass-digital' ),
					'a' => __( 'Both. Alongside websites, SEO and POS, we build bespoke business software and mobile apps around how your team actually works. If an off-the-shelf tool would serve you better and cheaper, we will tell you that instead.', 'compass-digital' ),
				),
				array(
					'q' => __( 'What happens after launch?', 'compass-digital' ),
					'a' => __( 'We hand over with training and plain-language notes, then stay on for fixes, updates and the next thing you want to grow. Your site, accounts and data are always yours — there is no lock-in and no disappearing act.', 'compass-digital' ),
				),
			),
		),
	);
}

/**
 * Flatten the FAQ groups into a single list of {q, a} pairs for the schema.
 *
 * @return array<int,array{q:string,a:string}>
 */
function compass_faq_all() {
	$all = array();
	foreach ( compass_faq_groups() as $group ) {
		foreach ( $group['faqs'] as $faq ) {
			$all[] = $faq;
		}
	}
	return $all;
}

/**
 * Per-page titles and meta descriptions for the company templates.
 *
 * @return array<string,array{title:string,meta:string}>
 */
function compass_company_meta() {
	return array(
		'page-about.php'   => array(
			'title' => __( 'About Compass Digital — Your Digital Partner, Sri Lanka', 'compass-digital' ),
			'meta'  => __( 'A Sri Lankan software and digital services firm built on clarity, craft and reliability. Meet the team behind Compass Digital.', 'compass-digital' ),
		),
		'page-process.php' => array(
			'title' => __( 'Our Process — How We Work | Compass Digital', 'compass-digital' ),
			'meta'  => __( 'How we work, step by step: listen, plan, build, support. What we need from you and realistic timelines — all in plain language.', 'compass-digital' ),
		),
		'page-faq.php'     => array(
			'title' => __( 'FAQ — Websites, SEO, POS & Software | Compass Digital', 'compass-digital' ),
			'meta'  => __( 'Straight answers on cost, timelines, hosting, SEO, POS and support for Sri Lankan businesses — the questions owners actually ask.', 'compass-digital' ),
		),
	);
}

/**
 * Override the document <title> for the company pages.
 *
 * @param string $title Default title.
 * @return string
 */
function compass_company_document_title( $title ) {
	$tpl = compass_current_company_template();
	if ( $tpl ) {
		$meta = compass_company_meta();
		if ( isset( $meta[ $tpl ] ) ) {
			return $meta[ $tpl ]['title'];
		}
	}
	return $title;
}
add_filter( 'pre_get_document_title', 'compass_company_document_title' );

/**
 * Print the meta description for the company pages.
 */
function compass_company_head() {
	$tpl = compass_current_company_template();
	if ( ! $tpl ) {
		return;
	}
	$meta = compass_company_meta();
	if ( isset( $meta[ $tpl ] ) ) {
		printf( '<meta name="description" content="%s" />' . "\n", esc_attr( $meta[ $tpl ]['meta'] ) );
	}
}
add_action( 'wp_head', 'compass_company_head', 1 );
