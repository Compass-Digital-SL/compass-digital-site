<?php
/**
 * Services hub (slug `services`).
 *
 * Auto-matched by the template hierarchy to the page with slug `services`. Lists
 * all six services (hub-and-spoke: hub → every service), explains the one way we
 * work, and points to a quote. BreadcrumbList JSON-LD is emitted at the end.
 *
 * @package CompassDigital
 */

defined( 'ABSPATH' ) || exit;

get_header();

$services      = compass_services_data();
$compass_order = compass_services_order();
?>
<article class="services-hub">

	<header class="service-hero">
		<div class="container">
			<?php get_template_part( 'template-parts/breadcrumbs' ); ?>
			<div class="service-hero__inner" data-reveal>
				<p class="service-hero__eyebrow"><?php esc_html_e( 'Services', 'compass-digital' ); ?></p>
				<h1 class="service-hero__title"><?php esc_html_e( 'Everything your business needs online, from one team.', 'compass-digital' ); ?></h1>
				<p class="service-hero__lede"><?php esc_html_e( 'Websites, SEO, POS systems, apps, ecommerce and custom software — designed, built and supported by people who explain things in plain language. Pick what you need; we’ll join it up.', 'compass-digital' ); ?></p>
				<p class="service-hero__actions">
					<a class="btn btn--primary" href="<?php echo esc_url( compass_quote_url() ); ?>"><?php esc_html_e( 'Request a quote', 'compass-digital' ); ?></a>
					<a class="btn btn--ghost" href="<?php echo esc_url( home_url( '/work/' ) ); ?>"><?php esc_html_e( 'See our work', 'compass-digital' ); ?></a>
				</p>
			</div>
		</div>
	</header>

	<div class="service-body container">

		<section class="service-section" aria-labelledby="services-list-title">
			<div class="section-header" data-reveal>
				<span class="section-header__eyebrow"><?php esc_html_e( 'What we do', 'compass-digital' ); ?></span>
				<h2 id="services-list-title"><?php esc_html_e( 'Six services, one joined-up partner.', 'compass-digital' ); ?></h2>
				<p class="section-header__lede"><?php esc_html_e( 'No prices listed — every project is different, so you get a fixed quote on request before any work starts.', 'compass-digital' ); ?></p>
			</div>

			<div class="services-hub__grid" data-reveal>
				<?php
				foreach ( $compass_order as $i => $slug ) :
					if ( empty( $services[ $slug ] ) ) {
						continue;
					}
					$service = $services[ $slug ];
					?>
					<a class="card card--glass service-card" href="<?php echo esc_url( home_url( '/services/' . $slug . '/' ) ); ?>">
						<h3 class="service-card__title">
							<span class="service-card__num"><?php echo esc_html( sprintf( '%02d', $i + 1 ) ); ?></span>
							<?php echo esc_html( $service['eyebrow'] ); ?>
						</h3>
						<p class="service-card__text"><?php echo esc_html( $service['lede'] ); ?></p>
						<span class="service-card__more"><?php esc_html_e( 'Learn more →', 'compass-digital' ); ?></span>
					</a>
				<?php endforeach; ?>
			</div>
		</section>

		<section class="service-section service-cta" aria-labelledby="hub-cta-title" data-reveal>
			<div class="service-cta__inner">
				<h2 id="hub-cta-title"><?php esc_html_e( 'Not sure which you need?', 'compass-digital' ); ?></h2>
				<p><?php esc_html_e( 'Tell us where you want your business to go. We’ll point you to the right service — or the right mix — and send a clear quote.', 'compass-digital' ); ?></p>
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
		'name' => __( 'Services', 'compass-digital' ),
		'url'  => home_url( '/services/' ),
	),
);
compass_print_schema( compass_breadcrumb_schema( $breadcrumbs ) );

get_footer();
