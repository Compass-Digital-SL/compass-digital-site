<?php
/**
 * Industries hub (slug `industries`).
 *
 * Auto-matched by the template hierarchy to the page with slug `industries`.
 * Lists all five industries (hub-and-spoke: hub → every industry), and points to
 * a quote. BreadcrumbList JSON-LD is emitted at the end. Reuses the service-page
 * CSS classes (see assets/src/css/services.css).
 *
 * @package CompassDigital
 */

defined( 'ABSPATH' ) || exit;

get_header();

$industries      = compass_industries_data();
$compass_order   = compass_industries_order();
$industry_labels = compass_industry_labels();
?>
<article class="services-hub">

	<header class="service-hero">
		<div class="container">
			<?php get_template_part( 'template-parts/breadcrumbs' ); ?>
			<div class="service-hero__inner" data-reveal>
				<p class="service-hero__eyebrow"><?php esc_html_e( 'Industries', 'compass-digital' ); ?></p>
				<h1 class="service-hero__title"><?php esc_html_e( 'Built for your line of work, not a one-size-fits-all template.', 'compass-digital' ); ?></h1>
				<p class="service-hero__lede"><?php esc_html_e( 'Retail, restaurants, travel, ecommerce and growing businesses each have their own headaches. We know them, and we build the websites, POS and software that solve them, wherever you are.', 'compass-digital' ); ?></p>
				<p class="service-hero__actions">
					<a class="btn btn--primary" href="<?php echo esc_url( compass_quote_url() ); ?>"><?php esc_html_e( 'Request a quote', 'compass-digital' ); ?></a>
					<a class="btn btn--ghost" href="<?php echo esc_url( home_url( '/services/' ) ); ?>"><?php esc_html_e( 'Browse services', 'compass-digital' ); ?></a>
				</p>
			</div>
		</div>
	</header>

	<div class="service-body container">

		<section class="service-section" aria-labelledby="industries-list-title">
			<div class="section-header" data-reveal>
				<span class="section-header__eyebrow"><?php esc_html_e( 'Who we work with', 'compass-digital' ); ?></span>
				<h2 id="industries-list-title"><?php esc_html_e( 'Five industries we know inside out.', 'compass-digital' ); ?></h2>
				<p class="section-header__lede"><?php esc_html_e( 'Pick the one that sounds like you. Each page shows the pains we solve, the services that fit, and real work — then points you to a quote.', 'compass-digital' ); ?></p>
			</div>

			<div class="services-hub__grid" data-reveal>
				<?php
				foreach ( $compass_order as $i => $slug ) :
					if ( empty( $industries[ $slug ] ) ) {
						continue;
					}
					$industry = $industries[ $slug ];
					?>
					<a class="card card--glass service-card" href="<?php echo esc_url( home_url( '/industries/' . $slug . '/' ) ); ?>">
						<h3 class="service-card__title">
							<span class="service-card__num"><?php echo esc_html( sprintf( '%02d', $i + 1 ) ); ?></span>
							<?php echo esc_html( $industry['eyebrow'] ); ?>
						</h3>
						<p class="service-card__text"><?php echo esc_html( $industry['lede'] ); ?></p>
						<span class="service-card__more"><?php esc_html_e( 'See how we help →', 'compass-digital' ); ?></span>
					</a>
				<?php endforeach; ?>
			</div>
		</section>

		<section class="service-section service-cta" aria-labelledby="hub-cta-title" data-reveal>
			<div class="service-cta__inner">
				<h2 id="hub-cta-title"><?php esc_html_e( 'Not sure where you fit?', 'compass-digital' ); ?></h2>
				<p><?php esc_html_e( 'Tell us about your business and what’s slowing you down. We’ll point you to the right mix of services and send a clear, honest quote.', 'compass-digital' ); ?></p>
				<p>
					<a class="btn btn--primary" href="<?php echo esc_url( compass_quote_url() ); ?>"><?php esc_html_e( 'Request a quote', 'compass-digital' ); ?></a>
				</p>
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
		'name' => __( 'Industries', 'compass-digital' ),
		'url'  => home_url( '/industries/' ),
	),
);
compass_print_schema( compass_breadcrumb_schema( $breadcrumbs ) );

get_footer();
