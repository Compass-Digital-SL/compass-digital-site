<?php
/**
 * 404 — not found. A calm "off the map" page with real ways back.
 *
 * @package CompassDigital
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>
<section class="error-page" aria-labelledby="error-404-title">
	<div class="container error-page__inner">
		<svg class="error-page__rose" viewBox="0 0 200 200" fill="none" aria-hidden="true" focusable="false">
			<circle cx="100" cy="100" r="92" stroke="currentColor" stroke-opacity=".14"/>
			<circle cx="100" cy="100" r="62" stroke="currentColor" stroke-opacity=".08" stroke-dasharray="2 6"/>
			<g class="error-page__needle">
				<path d="M100 30 106 100 94 100Z" fill="var(--accent-fill)"/>
				<path d="M100 170 106 100 94 100Z" fill="currentColor" fill-opacity=".7"/>
				<circle cx="100" cy="100" r="3.5" fill="var(--bg)" stroke="currentColor" stroke-opacity=".6"/>
			</g>
		</svg>
		<p class="eyebrow"><?php esc_html_e( 'Error 404', 'compass-digital' ); ?></p>
		<h1 id="error-404-title"><?php esc_html_e( 'This page is', 'compass-digital' ); ?> <em><?php esc_html_e( 'off the map.', 'compass-digital' ); ?></em></h1>
		<p class="error-page__lede"><?php esc_html_e( 'The page you are looking for has moved or no longer exists. Here are some ways back.', 'compass-digital' ); ?></p>
		<p class="error-page__actions">
			<a class="btn btn--primary" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Back to home', 'compass-digital' ); ?> <span class="btn__arrow" aria-hidden="true">→</span></a>
			<a class="btn btn--ghost" href="<?php echo esc_url( home_url( '/services/' ) ); ?>"><?php esc_html_e( 'See our services', 'compass-digital' ); ?></a>
			<button type="button" class="btn btn--ghost" data-command-open aria-haspopup="dialog" aria-controls="command-menu"><?php esc_html_e( 'Search the site', 'compass-digital' ); ?></button>
		</p>
	</div>
</section>
<?php
get_footer();
