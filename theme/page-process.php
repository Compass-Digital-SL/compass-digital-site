<?php
/**
 * Template Name: Process
 *
 * How we work — the four steps (Listen, Plan, Build, Support) in depth, what we
 * need from the client at each one, and realistic timelines in ranges. Copy is
 * theme-driven (inc/company.php); the page holds no body content. Emits
 * HowTo + BreadcrumbList JSON-LD. Works with JavaScript off.
 *
 * @package CompassDigital
 */

defined( 'ABSPATH' ) || exit;

get_header();

$page_url  = get_permalink();
$quote_url = compass_quote_url();
$steps     = compass_process_steps();
?>
<article class="process-page">

	<header class="service-hero">
		<div class="container">
			<div class="service-hero__inner" data-reveal>
				<p class="service-hero__eyebrow"><?php esc_html_e( 'How we work', 'compass-digital' ); ?></p>
				<h1 class="service-hero__title"><?php esc_html_e( 'A clear path from first chat to launch — and after.', 'compass-digital' ); ?></h1>
				<p class="service-hero__lede"><?php esc_html_e( 'Four steps, no mystery. Here is exactly what happens, what we need from you, and how long each part takes — in honest ranges, not promises we can’t keep.', 'compass-digital' ); ?></p>
				<p class="service-hero__actions">
					<a class="btn btn--primary" href="<?php echo esc_url( $quote_url ); ?>"><?php esc_html_e( 'Request a quote', 'compass-digital' ); ?></a>
				</p>
			</div>
		</div>
	</header>

	<div class="service-body container">

		<ol class="process-steps" data-reveal>
			<?php foreach ( $steps as $i => $step ) : ?>
				<li class="process-step" id="step-<?php echo esc_attr( $i + 1 ); ?>">
					<div class="process-step__head">
						<span class="process-step__num"><?php echo esc_html( sprintf( '%02d', $i + 1 ) ); ?></span>
						<div>
							<h2 class="process-step__title"><?php echo esc_html( $step['title'] ); ?></h2>
							<p class="process-step__summary"><?php echo esc_html( $step['summary'] ); ?></p>
						</div>
						<span class="process-step__timeline" aria-label="<?php esc_attr_e( 'Typical time', 'compass-digital' ); ?>">
							<span class="process-step__timeline-label"><?php esc_html_e( 'Typical', 'compass-digital' ); ?></span>
							<?php echo esc_html( $step['timeline'] ); ?>
						</span>
					</div>
					<div class="process-step__body service-prose">
						<?php foreach ( $step['body'] as $para ) : ?>
							<p><?php echo esc_html( $para ); ?></p>
						<?php endforeach; ?>
					</div>
					<div class="process-step__need">
						<h3 class="process-step__need-title"><?php esc_html_e( 'What we need from you', 'compass-digital' ); ?></h3>
						<ul class="process-need__list" role="list">
							<?php foreach ( $step['need'] as $need ) : ?>
								<li><?php echo esc_html( $need ); ?></li>
							<?php endforeach; ?>
						</ul>
					</div>
				</li>
			<?php endforeach; ?>
		</ol>

		<section class="service-section" aria-labelledby="timeline-title">
			<div class="section-header" data-reveal>
				<span class="section-header__eyebrow"><?php esc_html_e( 'The honest version', 'compass-digital' ); ?></span>
				<h2 id="timeline-title"><?php esc_html_e( 'How long will it really take?', 'compass-digital' ); ?></h2>
				<p class="section-header__lede"><?php esc_html_e( 'Timelines depend on scope and how fast content comes back. These are typical ranges, not guarantees — we agree a firm timeline in your quote.', 'compass-digital' ); ?></p>
			</div>
			<ul class="process-ranges" role="list" data-reveal>
				<li class="process-range card">
					<span class="process-range__value"><?php esc_html_e( '2–4 weeks', 'compass-digital' ); ?></span>
					<span class="process-range__label"><?php esc_html_e( 'A focused business website', 'compass-digital' ); ?></span>
				</li>
				<li class="process-range card">
					<span class="process-range__value"><?php esc_html_e( '4–8 weeks', 'compass-digital' ); ?></span>
					<span class="process-range__label"><?php esc_html_e( 'An ecommerce store or larger site', 'compass-digital' ); ?></span>
				</li>
				<li class="process-range card">
					<span class="process-range__value"><?php esc_html_e( '3–10 weeks', 'compass-digital' ); ?></span>
					<span class="process-range__label"><?php esc_html_e( 'A POS rollout or custom software', 'compass-digital' ); ?></span>
				</li>
			</ul>
		</section>

		<section class="service-section service-cta" aria-labelledby="process-cta-title" data-reveal>
			<div class="service-cta__inner">
				<h2 id="process-cta-title"><?php esc_html_e( 'Ready for step one?', 'compass-digital' ); ?></h2>
				<p><?php esc_html_e( 'Step one is a conversation. Tell us what you are trying to fix and we will take it from there.', 'compass-digital' ); ?></p>
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
		'name' => __( 'Process', 'compass-digital' ),
		'url'  => $page_url,
	),
);
compass_print_schema( compass_breadcrumb_schema( $breadcrumbs ) );

$howto_steps = array();
foreach ( $steps as $i => $step ) {
	$howto_steps[] = array(
		'@type'    => 'HowToStep',
		'position' => $i + 1,
		'name'     => wp_strip_all_tags( $step['title'] ),
		'text'     => wp_strip_all_tags( $step['summary'] . ' ' . ( isset( $step['body'][0] ) ? $step['body'][0] : '' ) ),
		'url'      => $page_url . '#step-' . ( $i + 1 ),
	);
}
compass_print_schema(
	array(
		'@context'    => 'https://schema.org',
		'@type'       => 'HowTo',
		'name'        => __( 'How Compass Digital works', 'compass-digital' ),
		'description' => __( 'Our four-step process: listen, plan, build and support.', 'compass-digital' ),
		'step'        => $howto_steps,
	)
);

get_footer();
