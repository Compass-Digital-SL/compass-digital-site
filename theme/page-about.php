<?php
/**
 * Template Name: About
 *
 * The About page — who we are, an honest story, mission, vision, the values from
 * docs/BRAND.md, and team placeholders the owner swaps for real people. Copy is
 * theme-driven (inc/company.php); the page itself holds no body content.
 * Emits AboutPage + BreadcrumbList JSON-LD. Works with JavaScript off.
 *
 * @package CompassDigital
 */

defined( 'ABSPATH' ) || exit;

get_header();

$page_url  = get_permalink();
$quote_url = compass_quote_url();
$values    = compass_company_values();

// Team placeholders — the owner replaces names/photos before launch.
$team = array(
	array(
		'role' => __( 'Founder & lead developer', 'compass-digital' ),
		'bio'  => __( 'Sets the technical direction and builds the hard parts — fast, accessible sites and dependable systems.', 'compass-digital' ),
	),
	array(
		'role' => __( 'Design & front-end', 'compass-digital' ),
		'bio'  => __( 'Turns your business into a clear, modern interface that works as well on a phone as on a desktop.', 'compass-digital' ),
	),
	array(
		'role' => __( 'SEO & content', 'compass-digital' ),
		'bio'  => __( 'Makes sure the right people find you on Google, with pages that read plainly and rank honestly.', 'compass-digital' ),
	),
	array(
		'role' => __( 'Support & delivery', 'compass-digital' ),
		'bio'  => __( 'Keeps projects on track and stays reachable after launch, so nothing goes quiet once the invoice clears.', 'compass-digital' ),
	),
);
?>
<article class="about-page">

	<header class="service-hero">
		<div class="container">
			<div class="service-hero__inner" data-reveal>
				<p class="service-hero__eyebrow"><?php esc_html_e( 'About us', 'compass-digital' ); ?></p>
				<h1 class="service-hero__title"><?php esc_html_e( 'A digital partner you can actually reach.', 'compass-digital' ); ?></h1>
				<p class="service-hero__lede"><?php esc_html_e( 'Compass Digital is a software and digital services firm working with clients worldwide. We build websites, SEO, POS systems and custom software that are clear, dependable and built around how you work.', 'compass-digital' ); ?></p>
				<p class="service-hero__actions">
					<a class="btn btn--primary" href="<?php echo esc_url( $quote_url ); ?>"><?php esc_html_e( 'Request a quote', 'compass-digital' ); ?></a>
					<a class="btn btn--ghost" href="<?php echo esc_url( home_url( '/process/' ) ); ?>"><?php esc_html_e( 'See how we work', 'compass-digital' ); ?></a>
				</p>
			</div>
		</div>
	</header>

	<div class="service-body container">

		<section class="service-section" aria-labelledby="story-title">
			<div class="section-header" data-reveal>
				<span class="section-header__eyebrow"><?php esc_html_e( 'Our story', 'compass-digital' ); ?></span>
				<h2 id="story-title"><?php esc_html_e( 'Built because too many good businesses were being let down online.', 'compass-digital' ); ?></h2>
			</div>
			<div class="service-prose" data-reveal>
				<p><?php esc_html_e( 'We started Compass Digital after watching the same story play out again and again: a business owner pays for a website or a system, waits, and ends up with something slow, confusing, or half-finished — and a developer who stops replying. The work was never really theirs, and nobody explained anything in plain language.', 'compass-digital' ); ?></p>
				<p><?php esc_html_e( 'We thought that was a low bar to clear. So we built a firm around the opposite: quote honestly, build the right thing once, explain it as we go, and stay reachable afterwards. Our name is the promise — a compass helps you find your way forward, and that is the job. Not the biggest build, the right next step.', 'compass-digital' ); ?></p>
				<p><?php esc_html_e( 'We are a small, hands-on team, and we like it that way. You talk to the people doing the work, not an account manager relaying messages. We take on projects we can do well, say no to the ones we cannot, and treat your budget as if it were our own. That is not a slogan — it is just how we have chosen to run.', 'compass-digital' ); ?></p>
				<p><?php esc_html_e( 'We work with clients all over the world, and because we work remotely as standard, distance is never the reason something cannot get done. Whether you need a first website, a faster one, a POS that keeps up at the counter, or software built around your team, the starting point is the same conversation.', 'compass-digital' ); ?></p>
			</div>
		</section>

		<section class="service-section" aria-labelledby="mv-title">
			<h2 id="mv-title" class="screen-reader-text"><?php esc_html_e( 'Mission and vision', 'compass-digital' ); ?></h2>
			<div class="about-mv" data-reveal>
				<div class="card">
					<h3 class="card__title"><?php esc_html_e( 'Our mission', 'compass-digital' ); ?></h3>
					<p class="card__text"><?php esc_html_e( 'Help businesses find their way online with websites, search visibility and POS systems that are clear, dependable and built around how they work.', 'compass-digital' ); ?></p>
				</div>
				<div class="card">
					<h3 class="card__title"><?php esc_html_e( 'Our vision', 'compass-digital' ); ?></h3>
					<p class="card__text"><?php esc_html_e( 'To be the first name growing businesses think of when they need a trusted digital partner.', 'compass-digital' ); ?></p>
				</div>
			</div>
		</section>

		<section class="service-section" aria-labelledby="values-title">
			<div class="section-header" data-reveal>
				<span class="section-header__eyebrow"><?php esc_html_e( 'What we stand for', 'compass-digital' ); ?></span>
				<h2 id="values-title"><?php esc_html_e( 'Four values, and we actually use them.', 'compass-digital' ); ?></h2>
			</div>
			<ul class="about-values" role="list" data-reveal>
				<?php foreach ( $values as $value ) : ?>
					<li class="about-value card">
						<h3 class="about-value__title"><?php echo esc_html( $value['title'] ); ?></h3>
						<p class="about-value__text"><?php echo esc_html( $value['text'] ); ?></p>
					</li>
				<?php endforeach; ?>
			</ul>
		</section>

		<section class="service-section" aria-labelledby="team-title">
			<div class="section-header" data-reveal>
				<span class="section-header__eyebrow"><?php esc_html_e( 'The team', 'compass-digital' ); ?></span>
				<h2 id="team-title"><?php esc_html_e( 'The people who will actually do the work.', 'compass-digital' ); ?></h2>
				<p class="section-header__lede"><?php esc_html_e( 'A small, hands-on team — you talk to us, not a middleman.', 'compass-digital' ); ?></p>
			</div>
			<ul class="about-team" role="list" data-reveal>
				<?php foreach ( $team as $member ) : ?>
					<li class="about-member card">
						<span class="about-member__avatar" aria-hidden="true"></span>
						<p class="about-member__name"><?php esc_html_e( 'Name — coming soon', 'compass-digital' ); ?></p>
						<p class="about-member__role"><?php echo esc_html( $member['role'] ); ?></p>
						<p class="about-member__bio"><?php echo esc_html( $member['bio'] ); ?></p>
					</li>
				<?php endforeach; ?>
			</ul>
			<p class="service-section__foot about-team__note"><?php esc_html_e( 'Real names and photos go here before launch — we would rather leave an honest placeholder than invent a team.', 'compass-digital' ); ?></p>
		</section>

		<section class="service-section service-cta" aria-labelledby="about-cta-title" data-reveal>
			<div class="service-cta__inner">
				<h2 id="about-cta-title"><?php esc_html_e( 'Let’s find your way forward.', 'compass-digital' ); ?></h2>
				<p><?php esc_html_e( 'Tell us what you are trying to fix. We will send a clear, honest quote and a plan to get there — no pressure, no jargon.', 'compass-digital' ); ?></p>
				<p><a class="btn btn--primary" href="<?php echo esc_url( $quote_url ); ?>"><?php esc_html_e( 'Request a quote', 'compass-digital' ); ?></a></p>
			</div>
		</section>

	</div>
</article>
<?php
$breadcrumbs = array(
	array(
		'name' => __( 'Home', 'compass-digital' ),
		'url'  => home_url( '/' ),
	),
	array(
		'name' => __( 'About', 'compass-digital' ),
		'url'  => $page_url,
	),
);
compass_print_schema( compass_breadcrumb_schema( $breadcrumbs ) );
compass_print_schema(
	array(
		'@context'    => 'https://schema.org',
		'@type'       => 'AboutPage',
		'name'        => get_the_title(),
		'url'         => $page_url,
		'description' => 'A software and digital services firm built on clarity, direction, craft and reliability.',
		'publisher'   => array(
			'@type' => 'Organization',
			'name'  => get_bloginfo( 'name' ),
			'url'   => home_url( '/' ),
		),
	)
);

get_footer();
