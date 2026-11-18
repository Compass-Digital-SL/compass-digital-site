<?php
/**
 * Home — services preview (6 items, hover motion).
 *
 * @package CompassDigital
 */

defined( 'ABSPATH' ) || exit;

$services = array(
	array(
		'title' => __( 'Website design & development', 'compass-digital' ),
		'text'  => __( 'Fast, modern business websites that look the part, work on a phone and are easy to update yourself.', 'compass-digital' ),
		'slug'  => 'website-design-and-development',
	),
	array(
		'title' => __( 'SEO', 'compass-digital' ),
		'text'  => __( 'Honest, measurable search work that gets you found in Sri Lanka — and brings in enquiries, not just traffic.', 'compass-digital' ),
		'slug'  => 'seo',
	),
	array(
		'title' => __( 'POS systems', 'compass-digital' ),
		'text'  => __( 'Quick billing, live stock and clear reports for shops and restaurants — and it keeps working offline.', 'compass-digital' ),
		'slug'  => 'pos-systems',
	),
	array(
		'title' => __( 'Mobile app development', 'compass-digital' ),
		'text'  => __( 'Clear, dependable Android and iOS apps built around how your business actually works.', 'compass-digital' ),
		'slug'  => 'mobile-app-development',
	),
	array(
		'title' => __( 'Ecommerce websites', 'compass-digital' ),
		'text'  => __( 'Secure online stores with local payments and easy stock control, so you can start selling sooner.', 'compass-digital' ),
		'slug'  => 'ecommerce-websites',
	),
	array(
		'title' => __( 'Custom software', 'compass-digital' ),
		'text'  => __( 'Bespoke systems built around how your team works — dependable, supported and genuinely yours.', 'compass-digital' ),
		'slug'  => 'custom-software',
	),
);
?>
<section class="home-section services" id="services" aria-labelledby="services-title">
	<div class="container">
		<div class="section-header" data-reveal>
			<span class="section-header__eyebrow"><?php esc_html_e( 'What we do', 'compass-digital' ); ?></span>
			<h2 id="services-title"><?php esc_html_e( 'Everything your digital presence needs, in one place.', 'compass-digital' ); ?></h2>
			<p class="section-header__lede"><?php esc_html_e( 'Websites, SEO, POS and software — built and supported by one team that explains things in plain language.', 'compass-digital' ); ?></p>
		</div>

		<div class="services__grid" data-reveal>
			<?php foreach ( $services as $i => $service ) : ?>
				<a class="card card--glass service-card" href="<?php echo esc_url( home_url( '/services/' . $service['slug'] . '/' ) ); ?>">
					<h3 class="service-card__title">
						<span class="service-card__num"><?php echo esc_html( sprintf( '%02d', $i + 1 ) ); ?></span>
						<?php echo esc_html( $service['title'] ); ?>
					</h3>
					<p class="service-card__text"><?php echo esc_html( $service['text'] ); ?></p>
					<span class="service-card__more"><?php esc_html_e( 'Learn more →', 'compass-digital' ); ?></span>
				</a>
			<?php endforeach; ?>
		</div>

		<p class="home-section__foot" data-reveal>
			<a class="link" href="<?php echo esc_url( home_url( '/services/' ) ); ?>"><?php esc_html_e( 'See all services', 'compass-digital' ); ?></a>
		</p>
	</div>
</section>
