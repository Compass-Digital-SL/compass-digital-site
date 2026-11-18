<?php
/**
 * Home — industries strip (5 items).
 *
 * @package CompassDigital
 */

defined( 'ABSPATH' ) || exit;

$industries = array(
	array(
		'label' => __( 'Retail shops', 'compass-digital' ),
		'slug'  => 'retail-shops',
	),
	array(
		'label' => __( 'Restaurants & cafes', 'compass-digital' ),
		'slug'  => 'restaurants-and-cafes',
	),
	array(
		'label' => __( 'Travel & tourism', 'compass-digital' ),
		'slug'  => 'travel-and-tourism',
	),
	array(
		'label' => __( 'Ecommerce brands', 'compass-digital' ),
		'slug'  => 'ecommerce-brands',
	),
	array(
		'label' => __( 'Corporates & SMEs', 'compass-digital' ),
		'slug'  => 'corporates-and-smes',
	),
);
?>
<section class="home-section home-section--tight industries" id="industries" aria-labelledby="industries-title">
	<div class="container">
		<div class="section-header" data-reveal>
			<span class="section-header__eyebrow"><?php esc_html_e( 'Who we help', 'compass-digital' ); ?></span>
			<h2 id="industries-title"><?php esc_html_e( 'We know how your business runs.', 'compass-digital' ); ?></h2>
			<p class="section-header__lede"><?php esc_html_e( 'We build for Sri Lankan businesses day in, day out. Find the kind of work that fits yours.', 'compass-digital' ); ?></p>
		</div>

		<ul class="industries__strip" role="list" data-reveal>
			<?php foreach ( $industries as $industry ) : ?>
				<li>
					<a class="industry-chip" href="<?php echo esc_url( home_url( '/industries/' . $industry['slug'] . '/' ) ); ?>">
						<?php echo esc_html( $industry['label'] ); ?>
					</a>
				</li>
			<?php endforeach; ?>
		</ul>
	</div>
</section>
