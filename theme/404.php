<?php
/**
 * 404 — not found.
 *
 * @package CompassDigital
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>
<section class="error-404" aria-labelledby="error-404-title">
	<h1 id="error-404-title"><?php esc_html_e( 'Page not found', 'compass-digital' ); ?></h1>
	<p><?php esc_html_e( 'The page you are looking for has moved or no longer exists.', 'compass-digital' ); ?></p>
	<p>
		<a class="button button--primary" href="<?php echo esc_url( home_url( '/' ) ); ?>">
			<?php esc_html_e( 'Back to home', 'compass-digital' ); ?>
		</a>
	</p>
	<?php get_search_form(); ?>
</section>
<?php
get_footer();
