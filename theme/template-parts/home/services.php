<?php
/**
 * Home — services preview: a numbered editorial list (6 items).
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
		'text'  => __( 'Honest, measurable search work that gets you found, and brings in enquiries, not just traffic.', 'compass-digital' ),
		'slug'  => 'seo',
	),
	array(
		'title' => __( 'POS systems', 'compass-digital' ),
		'text'  => __( 'Quick billing, live stock and clear reports for shops and restaurants. It keeps working offline.', 'compass-digital' ),
		'slug'  => 'pos-systems',
	),
	array(
		'title' => __( 'Mobile apps', 'compass-digital' ),
		'text'  => __( 'Clear, dependable Android and iOS apps built around how your business actually works.', 'compass-digital' ),
		'slug'  => 'mobile-app-development',
	),
	array(
		'title' => __( 'Ecommerce websites', 'compass-digital' ),
		'text'  => __( 'Secure online stores with the payment options your customers already use, so you can start selling sooner.', 'compass-digital' ),
		'slug'  => 'ecommerce-websites',
	),
	array(
		'title' => __( 'Custom software', 'compass-digital' ),
		'text'  => __( 'Systems built around how your team works. Dependable, supported and genuinely yours.', 'compass-digital' ),
		'slug'  => 'custom-software',
	),
);
?>
<section class="home-section services" id="services" aria-labelledby="services-title" data-waypoint data-label="<?php esc_attr_e( 'Services', 'compass-digital' ); ?>">
	<div class="container">
		<div class="section-header" data-reveal>
			<span class="section-header__eyebrow"><?php esc_html_e( 'What we do', 'compass-digital' ); ?></span>
			<h2 id="services-title"><?php esc_html_e( 'Everything your business needs online,', 'compass-digital' ); ?> <em><?php esc_html_e( 'in one place.', 'compass-digital' ); ?></em></h2>
			<p class="section-header__lede"><?php esc_html_e( 'Websites, SEO, POS and software, built and supported by one team that explains things in plain language.', 'compass-digital' ); ?></p>
		</div>

		<ul class="svc-list" role="list" data-reveal>
			<?php foreach ( $services as $i => $service ) : ?>
				<li>
					<a class="svc-list__row" href="<?php echo esc_url( home_url( '/services/' . $service['slug'] . '/' ) ); ?>">
						<span class="svc-list__num" aria-hidden="true"><?php echo esc_html( sprintf( '%02d', $i + 1 ) ); ?></span>
						<h3 class="svc-list__title"><?php echo esc_html( $service['title'] ); ?></h3>
						<p class="svc-list__text"><?php echo esc_html( $service['text'] ); ?></p>
						<span class="svc-list__go" aria-hidden="true">→</span>
					</a>
				</li>
			<?php endforeach; ?>
		</ul>

		<p class="home-section__foot" data-reveal>
			<a class="link" href="<?php echo esc_url( home_url( '/services/' ) ); ?>"><?php esc_html_e( 'See all services', 'compass-digital' ); ?></a>
		</p>
	</div>
</section>
