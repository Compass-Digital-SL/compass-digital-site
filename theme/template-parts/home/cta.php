<?php
/**
 * Home — final call to action.
 *
 * @package CompassDigital
 */

defined( 'ABSPATH' ) || exit;
?>
<section class="home-section home-cta" id="get-started" aria-labelledby="cta-title" data-waypoint data-label="<?php esc_attr_e( 'Get a quote', 'compass-digital' ); ?>">
	<div class="home-cta__light" aria-hidden="true"></div>
	<div class="container home-cta__inner" data-reveal>
		<span class="section-header__eyebrow"><?php esc_html_e( 'Start here', 'compass-digital' ); ?></span>
		<h2 id="cta-title"><?php esc_html_e( 'Tell us where you want to go.', 'compass-digital' ); ?> <em><?php esc_html_e( 'We’ll send a quote.', 'compass-digital' ); ?></em></h2>
		<div class="home-cta__row">
			<p class="home-cta__lede"><?php esc_html_e( 'No pressure and no jargon. Just a clear plan for getting your business where it needs to be.', 'compass-digital' ); ?></p>
			<div class="home-cta__actions">
				<a class="btn btn--primary" href="<?php echo esc_url( home_url( '/get-a-quote/' ) ); ?>"><?php esc_html_e( 'Request a quote', 'compass-digital' ); ?> <span class="btn__arrow" aria-hidden="true">→</span></a>
				<a class="btn btn--ghost" href="<?php echo esc_url( home_url( '/work/' ) ); ?>"><?php esc_html_e( 'See our work first', 'compass-digital' ); ?></a>
			</div>
		</div>
	</div>
</section>
