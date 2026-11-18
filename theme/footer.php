<?php
/**
 * Site footer and document close.
 *
 * @package CompassDigital
 */

defined( 'ABSPATH' ) || exit;
?>
</main><!-- #main -->

<footer class="site-footer" role="contentinfo">
	<div class="site-footer__inner">
		<p class="site-footer__brand"><?php bloginfo( 'name' ); ?></p>
		<?php
		if ( has_nav_menu( 'footer' ) ) {
			wp_nav_menu(
				array(
					'theme_location' => 'footer',
					'container'      => 'nav',
					'menu_class'     => 'footer-nav',
					'depth'          => 1,
					'fallback_cb'    => false,
				)
			);
		}
		?>
		<p class="site-footer__copy">
			&copy; <?php echo esc_html( gmdate( 'Y' ) ); ?> <?php bloginfo( 'name' ); ?>.
			<?php esc_html_e( 'Find your way forward.', 'compass-digital' ); ?>
		</p>
	</div>
</footer>

<?php wp_footer(); ?>
</body>
</html>
