<?php
/**
 * Home — final call to action.
 *
 * @package CompassDigital
 */

defined( 'ABSPATH' ) || exit;
?>
<section class="home-section home-cta" id="get-started" aria-labelledby="cta-title">
	<div class="container home-cta__inner">
		<div data-reveal>
			<h2 id="cta-title"><?php esc_html_e( 'Tell us where you want to go. We’ll send a quote.', 'compass-digital' ); ?></h2>
			<p class="home-cta__lede"><?php esc_html_e( 'No pressure, no jargon — just a clear, honest plan for getting your business where it needs to be.', 'compass-digital' ); ?></p>
			<div class="home-cta__actions">
				<a class="btn btn--primary" href="<?php echo esc_url( home_url( '/get-a-quote/' ) ); ?>"><?php esc_html_e( 'Request a quote', 'compass-digital' ); ?></a>
				<a class="btn btn--ghost" href="<?php echo esc_url( home_url( '/work/' ) ); ?>"><?php esc_html_e( 'See our work first', 'compass-digital' ); ?></a>
			</div>
		</div>
	</div>
</section>
