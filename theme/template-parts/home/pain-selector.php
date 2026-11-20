<?php
/**
 * Home — "Where are you stuck?" pain-point selector.
 *
 * Each of the six cards is an in-page anchor to a tailored panel whose id
 * matches a hero bearing (seo, websites, pos, app, ecommerce, travel). Without
 * JavaScript every panel is rendered and visible, so the section works as plain
 * anchored sections; ui.js (initPainSelector) enhances it into a swap-in-place
 * selector driven by the cards, the hero bearings and the URL hash.
 *
 * @package CompassDigital
 */

defined( 'ABSPATH' ) || exit;

$pain_points = array(
	array(
		'id'        => 'seo',
		'quote'     => __( '“Nobody finds us on Google.”', 'compass-digital' ),
		'tag'       => __( 'Getting found', 'compass-digital' ),
		'problem'   => __( 'You know you’re good at what you do — but when someone searches, a competitor shows up first and you have no idea why.', 'compass-digital' ),
		'fix'       => __( 'We fix the technical basics, set up your Google Business Profile and local listings properly, and build pages around the words your customers actually type. Then we show you, in plain English, what’s moving and what isn’t.', 'compass-digital' ),
		'proof'     => __( 'Measurable', 'compass-digital' ),
		'proof_txt' => __( 'You get a monthly report you can actually read — rankings, enquiries and what we did. No jargon, no vanity numbers.', 'compass-digital' ),
		'link'      => '/services/seo/',
		'link_txt'  => __( 'See SEO services', 'compass-digital' ),
	),
	array(
		'id'        => 'websites',
		'quote'     => __( '“Our website looks old and I’m embarrassed to share it.”', 'compass-digital' ),
		'tag'       => __( 'Looking the part', 'compass-digital' ),
		'problem'   => __( 'Your site is slow, awkward on a phone, and doesn’t look like the business you’ve built. Sharing the link feels like an apology.', 'compass-digital' ),
		'fix'       => __( 'We design and build a fast, modern site that works beautifully on a phone, loads in under two seconds, and that you can update yourself without calling a developer for every change.', 'compass-digital' ),
		'proof'     => __( 'Lighthouse 95+', 'compass-digital' ),
		'proof_txt' => __( 'Every site we ship is built to score 95+ on Google’s own performance checks — because speed is what customers and search engines both reward.', 'compass-digital' ),
		'link'      => '/services/website-design-and-development/',
		'link_txt'  => __( 'See website design', 'compass-digital' ),
	),
	array(
		'id'        => 'pos',
		'quote'     => __( '“Billing is slow and the queue backs up when we’re busy.”', 'compass-digital' ),
		'tag'       => __( 'Selling smarter', 'compass-digital' ),
		'problem'   => __( 'At the busiest moments your till is the bottleneck, your stock counts never match, and you can’t tell what’s really selling.', 'compass-digital' ),
		'fix'       => __( 'We set up a point-of-sale system that bills in seconds, keeps live stock, prints kitchen or counter tickets, and gives you clear reports — and it keeps working when the internet drops.', 'compass-digital' ),
		'proof'     => __( 'Works offline', 'compass-digital' ),
		'proof_txt' => __( 'Our POS keeps billing during an outage and syncs the moment you’re back online, so a dropped connection never stops a sale.', 'compass-digital' ),
		'link'      => '/services/pos-systems/',
		'link_txt'  => __( 'See POS systems', 'compass-digital' ),
	),
	array(
		'id'        => 'app',
		'quote'     => __( '“My customers live on their phones — and I’ve got nothing for them there.”', 'compass-digital' ),
		'tag'       => __( 'Going mobile', 'compass-digital' ),
		'problem'   => __( 'Your customers would happily order, book or come back more often — if only tapping an app made it easy. Right now it isn’t.', 'compass-digital' ),
		'fix'       => __( 'We build clear, dependable Android and iOS apps around the way you actually work — ordering, bookings, loyalty or whatever moves your business — and we support them after launch.', 'compass-digital' ),
		'proof'     => __( 'Built around you', 'compass-digital' ),
		'proof_txt' => __( 'We start with how your business runs, not a template. You get an app your customers understand on the first try.', 'compass-digital' ),
		'link'      => '/services/mobile-app-development/',
		'link_txt'  => __( 'See app development', 'compass-digital' ),
	),
	array(
		'id'        => 'ecommerce',
		'quote'     => __( '“I want to sell online but I don’t know where to start.”', 'compass-digital' ),
		'tag'       => __( 'Selling online', 'compass-digital' ),
		'problem'   => __( 'You’ve got products people want, enquiries landing on WhatsApp, and no proper way to take an order and get paid.', 'compass-digital' ),
		'fix'       => __( 'We build a fast, secure online store with the right payment options for your market and stock control that’s genuinely easy to run, so you can start selling without needing a tech team.', 'compass-digital' ),
		'proof'     => __( 'Right payments', 'compass-digital' ),
		'proof_txt' => __( 'Set up with the gateways your customers already trust in your market, so checkout works for them from day one.', 'compass-digital' ),
		'link'      => '/services/ecommerce-websites/',
		'link_txt'  => __( 'See ecommerce stores', 'compass-digital' ),
	),
	array(
		'id'        => 'travel',
		'quote'     => __( '“Travellers book with whoever looks trustworthy — and that’s rarely us.”', 'compass-digital' ),
		'tag'       => __( 'Welcoming travellers', 'compass-digital' ),
		'problem'   => __( 'Visitors compare you to polished international sites and decide in seconds. A slow, dated site loses the booking before you ever hear from them.', 'compass-digital' ),
		'fix'       => __( 'We build fast, trustworthy travel and tourism sites with clear itineraries, easy enquiries or bookings, and SEO that reaches people planning their trip — wherever in the world they are.', 'compass-digital' ),
		'proof'     => __( 'Found by travellers', 'compass-digital' ),
		'proof_txt' => __( 'We build for the searches travellers actually make before they arrive, so you’re in the running when they’re choosing.', 'compass-digital' ),
		'link'      => '/industries/travel-and-tourism/',
		'link_txt'  => __( 'See travel & tourism', 'compass-digital' ),
	),
);

$quote_url = esc_url( home_url( '/get-a-quote/' ) );
?>
<section class="home-section pain" id="pain" data-pain aria-labelledby="pain-title" data-waypoint data-label="<?php esc_attr_e( 'Your problem', 'compass-digital' ); ?>">
	<div class="container">
		<div class="section-header" data-reveal>
			<span class="section-header__eyebrow"><?php esc_html_e( 'Where are you stuck?', 'compass-digital' ); ?></span>
			<h2 id="pain-title"><?php esc_html_e( 'Pick the problem that', 'compass-digital' ); ?> <em><?php esc_html_e( 'sounds like you.', 'compass-digital' ); ?></em></h2>
			<p class="section-header__lede"><?php esc_html_e( 'Choose one and we’ll show you, in plain language, how we’d fix it, and where to go next.', 'compass-digital' ); ?></p>
		</div>

		<div class="pain__layout">
			<ul class="pain__nav" role="list" data-reveal>
				<?php foreach ( $pain_points as $point ) : ?>
					<li>
						<a class="pain__card" href="#<?php echo esc_attr( $point['id'] ); ?>" data-pain-link>
							<span class="pain__card-quote"><?php echo esc_html( $point['quote'] ); ?></span>
							<span class="pain__card-tag"><?php echo esc_html( $point['tag'] ); ?></span>
						</a>
					</li>
				<?php endforeach; ?>
			</ul>

			<div class="pain__panels">
				<?php foreach ( $pain_points as $point ) : ?>
					<article
						class="pain__panel"
						id="<?php echo esc_attr( $point['id'] ); ?>"
						data-pain-panel
						aria-label="<?php echo esc_attr( $point['tag'] ); ?>"
					>
						<p class="eyebrow pain__panel-tag"><?php echo esc_html( $point['tag'] ); ?></p>
						<p class="pain__problem"><?php echo esc_html( $point['quote'] ); ?></p>
						<p class="pain__fix"><?php echo esc_html( $point['problem'] ); ?></p>
						<p class="pain__fix"><strong><?php esc_html_e( 'How we fix it:', 'compass-digital' ); ?></strong> <?php echo esc_html( $point['fix'] ); ?></p>
						<div class="pain__actions">
							<a class="btn btn--primary btn--sm" href="<?php echo esc_url( home_url( $point['link'] ) ); ?>"><?php echo esc_html( $point['link_txt'] ); ?> <span class="btn__arrow" aria-hidden="true">→</span></a>
							<a class="btn btn--ghost btn--sm" href="<?php echo $quote_url; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>"><?php esc_html_e( 'Get a quote', 'compass-digital' ); ?></a>
						</div>
						<p class="pain__proof">
							<span class="pain__proof-stat"><?php echo esc_html( $point['proof'] ); ?></span>
							<span class="pain__proof-text"><?php echo esc_html( $point['proof_txt'] ); ?></span>
						</p>
					</article>
				<?php endforeach; ?>
			</div>
		</div>
	</div>
</section>
