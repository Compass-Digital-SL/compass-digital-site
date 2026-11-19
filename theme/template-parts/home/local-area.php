<?php
/**
 * Home — service area, NAP, embed-free map and reviews.
 *
 * Local-SEO block (Task 17): names the base city and the areas served, repeats
 * the single source of truth NAP (name, address, phone) from Site Settings so
 * it stays consistent everywhere, links out to Google Maps without a heavy
 * iframe, and surfaces reviews — ready for the real Google reviews link the
 * moment the Business Profile is live.
 *
 * @package CompassDigital
 */

defined( 'ABSPATH' ) || exit;

$compass_contact = compass_contact_details();
$area            = function_exists( 'compass_get_setting' ) ? compass_get_setting( 'service_area' ) : '';
$map_url         = function_exists( 'compass_get_setting' ) ? compass_get_setting( 'map_url' ) : '';
$reviews_url     = function_exists( 'compass_get_setting' ) ? compass_get_setting( 'reviews_url' ) : '';
$business_name   = ! empty( $compass_contact['reg_name'] ) ? $compass_contact['reg_name'] : get_bloginfo( 'name' );

// The cities we actively push for in local SEO (docs/SEO.md), plus the island.
$cities = array(
	__( 'Colombo', 'compass-digital' ),
	__( 'Kandy', 'compass-digital' ),
	__( 'Galle', 'compass-digital' ),
	__( 'Jaffna', 'compass-digital' ),
);
?>
<section
	class="home-section local-area"
	id="service-area"
	aria-labelledby="local-area-title"
	data-waypoint
	data-label="<?php esc_attr_e( 'Where we work', 'compass-digital' ); ?>"
>
	<div class="container">
		<div class="section-header" data-reveal>
			<span class="section-header__eyebrow"><?php esc_html_e( 'Where we work', 'compass-digital' ); ?></span>
			<h2 id="local-area-title"><?php esc_html_e( 'Based in Colombo, working across Sri Lanka.', 'compass-digital' ); ?></h2>
			<p class="section-header__lede"><?php esc_html_e( 'We build and support websites, SEO, POS and software for businesses island-wide — and remotely for Sri Lankan-owned businesses overseas.', 'compass-digital' ); ?></p>
		</div>

		<div class="local-area__grid">
			<div class="local-area__nap card" data-reveal>
				<h3 class="local-area__subtitle"><?php esc_html_e( 'Find us', 'compass-digital' ); ?></h3>
				<address class="local-area__address">
					<span class="local-area__name"><?php echo esc_html( $business_name ); ?></span>
					<?php if ( ! empty( $compass_contact['address'] ) ) : ?>
						<span class="local-area__line"><?php echo esc_html( $compass_contact['address'] ); ?></span>
					<?php endif; ?>
					<?php if ( ! empty( $compass_contact['phone'] ) ) : ?>
						<a class="local-area__line" href="<?php echo esc_url( 'tel:' . preg_replace( '/[^\d+]/', '', $compass_contact['phone'] ) ); ?>"><?php echo esc_html( $compass_contact['phone'] ); ?></a>
					<?php endif; ?>
					<?php if ( ! empty( $compass_contact['email'] ) ) : ?>
						<a class="local-area__line" href="<?php echo esc_url( 'mailto:' . $compass_contact['email'] ); ?>"><?php echo esc_html( $compass_contact['email'] ); ?></a>
					<?php endif; ?>
				</address>

				<?php if ( $map_url ) : ?>
					<a class="btn btn--ghost local-area__map-link" href="<?php echo esc_url( $map_url ); ?>" rel="noopener" target="_blank">
						<?php esc_html_e( 'Open in Google Maps', 'compass-digital' ); ?>
						<span class="screen-reader-text"><?php esc_html_e( '(opens in a new tab)', 'compass-digital' ); ?></span>
					</a>
				<?php endif; ?>
			</div>

			<div class="local-area__served" data-reveal>
				<h3 class="local-area__subtitle"><?php esc_html_e( 'Service area', 'compass-digital' ); ?></h3>
				<?php if ( $area ) : ?>
					<p class="local-area__area-note"><?php echo esc_html( $area ); ?></p>
				<?php endif; ?>
				<ul class="local-area__cities" role="list">
					<?php foreach ( $cities as $city ) : ?>
						<li class="local-area__city"><?php echo esc_html( $city ); ?></li>
					<?php endforeach; ?>
					<li class="local-area__city local-area__city--all"><?php esc_html_e( 'All of Sri Lanka', 'compass-digital' ); ?></li>
				</ul>

				<div class="local-area__reviews">
					<h3 class="local-area__subtitle"><?php esc_html_e( 'Reviews', 'compass-digital' ); ?></h3>
					<?php if ( $reviews_url ) : ?>
						<p><?php esc_html_e( 'See what our clients say, or leave a review of your own.', 'compass-digital' ); ?></p>
						<a class="local-area__reviews-link" href="<?php echo esc_url( $reviews_url ); ?>" rel="noopener" target="_blank">
							<?php esc_html_e( 'Read our Google reviews', 'compass-digital' ); ?>
							<span class="screen-reader-text"><?php esc_html_e( '(opens in a new tab)', 'compass-digital' ); ?></span>
						</a>
					<?php else : ?>
						<p class="local-area__reviews-soon"><?php esc_html_e( 'Our Google reviews go live with our Business Profile — check back soon.', 'compass-digital' ); ?></p>
					<?php endif; ?>
				</div>
			</div>
		</div>
	</div>
</section>
