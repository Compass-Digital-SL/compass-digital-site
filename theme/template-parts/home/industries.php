<?php
/**
 * Home — industries: five tiles with a line icon and what we handle for each.
 *
 * @package CompassDigital
 */

defined( 'ABSPATH' ) || exit;

// Decorative 36x36 line icons (stroke = currentColor).
$compass_icons = array(
	'retail'     => '<path d="M5 14h26l-2-7H7zM7 14v15h22V14M14 29v-8h8v8"/>',
	'restaurant' => '<path d="M8 20h18a9 9 0 0 1-18 0zM26 22h2a3 3 0 0 0 0-6h-2v4M12 6c0 3 2 3 2 6M17 6c0 3 2 3 2 6"/>',
	'travel'     => '<circle cx="18" cy="18" r="12"/><path d="M6 18h24M18 6c4 4 4 20 0 24M18 6c-4 4-4 20 0 24"/>',
	'ecommerce'  => '<path d="M5 8h4l3 15h15l3-10H11"/><circle cx="14" cy="28" r="1.6"/><circle cx="25" cy="28" r="1.6"/>',
	'corporate'  => '<path d="M8 30V8h12v22M20 14h8v16M5 30h26M12 13h4M12 18h4M12 23h4"/>',
);

$industries = array(
	array(
		'label' => __( 'Retail shops', 'compass-digital' ),
		'desc'  => __( 'Billing, stock and repeat customers', 'compass-digital' ),
		'slug'  => 'retail-shops',
		'icon'  => 'retail',
	),
	array(
		'label' => __( 'Restaurants & cafes', 'compass-digital' ),
		'desc'  => __( 'Kitchen tickets, tables and menus', 'compass-digital' ),
		'slug'  => 'restaurants-and-cafes',
		'icon'  => 'restaurant',
	),
	array(
		'label' => __( 'Travel & tourism', 'compass-digital' ),
		'desc'  => __( 'Itineraries, enquiries and bookings', 'compass-digital' ),
		'slug'  => 'travel-and-tourism',
		'icon'  => 'travel',
	),
	array(
		'label' => __( 'Ecommerce brands', 'compass-digital' ),
		'desc'  => __( 'Checkout, payments and delivery', 'compass-digital' ),
		'slug'  => 'ecommerce-brands',
		'icon'  => 'ecommerce',
	),
	array(
		'label' => __( 'Corporates & SMEs', 'compass-digital' ),
		'desc'  => __( 'Systems, portals and internal tools', 'compass-digital' ),
		'slug'  => 'corporates-and-smes',
		'icon'  => 'corporate',
	),
);
?>
<section class="home-section industries" id="industries" aria-labelledby="industries-title" data-waypoint data-label="<?php esc_attr_e( 'Industries', 'compass-digital' ); ?>">
	<div class="container">
		<div class="section-header" data-reveal>
			<span class="section-header__eyebrow"><?php esc_html_e( 'Who we work with', 'compass-digital' ); ?></span>
			<h2 id="industries-title"><?php esc_html_e( 'Built for the way', 'compass-digital' ); ?> <em><?php esc_html_e( 'your trade', 'compass-digital' ); ?></em> <?php esc_html_e( 'works.', 'compass-digital' ); ?></h2>
			<p class="section-header__lede"><?php esc_html_e( 'Every industry has its own rush hour. We design around yours.', 'compass-digital' ); ?></p>
		</div>

		<ul class="ind-grid" role="list" data-reveal>
			<?php foreach ( $industries as $industry ) : ?>
				<li>
					<a class="ind-grid__tile" href="<?php echo esc_url( home_url( '/industries/' . $industry['slug'] . '/' ) ); ?>">
						<svg class="ind-grid__icon" viewBox="0 0 36 36" fill="none" stroke="currentColor" stroke-width="1.2" aria-hidden="true" focusable="false"><?php echo $compass_icons[ $industry['icon'] ]; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Static SVG paths defined above. ?></svg>
						<span class="ind-grid__body">
							<span class="ind-grid__title"><?php echo esc_html( $industry['label'] ); ?></span>
							<span class="ind-grid__desc"><?php echo esc_html( $industry['desc'] ); ?></span>
						</span>
					</a>
				</li>
			<?php endforeach; ?>
		</ul>
	</div>
</section>
