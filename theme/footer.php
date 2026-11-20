<?php
/**
 * Site footer: full sitemap, contact, social, registration.
 *
 * @package CompassDigital
 */

defined( 'ABSPATH' ) || exit;

$compass_map     = compass_nav_map();
$compass_contact = compass_contact_details();

// Columns of the sitemap, in order.
$compass_columns = array( $compass_map['services'], $compass_map['industries'], $compass_map['company'] );
?>
</main><!-- #main -->

<footer class="site-footer" role="contentinfo">
	<div class="site-footer__inner">
		<div class="site-footer__top">
			<div class="site-footer__brand-col">
				<?php get_template_part( 'template-parts/header/branding', null, array( 'context' => 'footer' ) ); ?>
				<p class="site-footer__tagline"><?php esc_html_e( 'Websites, SEO and POS systems for businesses worldwide.', 'compass-digital' ); ?></p>
				<ul class="site-footer__contact">
					<?php if ( ! empty( $compass_contact['phone'] ) ) : ?>
						<li>
							<a href="tel:<?php echo esc_attr( preg_replace( '/[^\d+]/', '', $compass_contact['phone'] ) ); ?>">
								<?php echo esc_html( $compass_contact['phone'] ); ?>
							</a>
						</li>
					<?php endif; ?>
					<?php if ( ! empty( $compass_contact['whatsapp'] ) ) : ?>
						<li><a href="<?php echo esc_url( compass_whatsapp_url() ); ?>" rel="noopener" target="_blank"><?php esc_html_e( 'WhatsApp', 'compass-digital' ); ?></a></li>
					<?php endif; ?>
					<?php if ( ! empty( $compass_contact['email'] ) ) : ?>
						<li><a href="mailto:<?php echo esc_attr( $compass_contact['email'] ); ?>"><?php echo esc_html( $compass_contact['email'] ); ?></a></li>
					<?php endif; ?>
					<?php // No street address in the footer: the business is positioned worldwide (Task 24). ?>
				</ul>

				<?php
				$compass_social = array_filter( $compass_contact['social'] );
				if ( ! empty( $compass_social ) ) :
					?>
					<ul class="site-footer__social" aria-label="<?php esc_attr_e( 'Social', 'compass-digital' ); ?>">
						<?php foreach ( $compass_social as $name => $url ) : ?>
							<li><a href="<?php echo esc_url( $url ); ?>" rel="noopener" target="_blank"><?php echo esc_html( $name ); ?></a></li>
						<?php endforeach; ?>
					</ul>
				<?php endif; ?>
			</div>

			<nav class="site-footer__sitemap" aria-label="<?php esc_attr_e( 'Sitemap', 'compass-digital' ); ?>">
				<?php foreach ( $compass_columns as $group ) : ?>
					<div class="site-footer__col">
						<h2 class="site-footer__col-title">
							<?php if ( ! empty( $group['url'] ) ) : ?>
								<a href="<?php echo esc_url( $group['url'] ); ?>"><?php echo esc_html( $group['label'] ); ?></a>
							<?php else : ?>
								<?php echo esc_html( $group['label'] ); ?>
							<?php endif; ?>
						</h2>
						<ul>
							<?php foreach ( $group['items'] as $item ) : ?>
								<li><a href="<?php echo esc_url( $item['url'] ); ?>"><?php echo esc_html( $item['label'] ); ?></a></li>
							<?php endforeach; ?>
						</ul>
					</div>
				<?php endforeach; ?>
				<div class="site-footer__col">
					<h2 class="site-footer__col-title"><?php esc_html_e( 'Legal', 'compass-digital' ); ?></h2>
					<ul>
						<li><a href="<?php echo esc_url( compass_nav_url( 'privacy/' ) ); ?>"><?php esc_html_e( 'Privacy', 'compass-digital' ); ?></a></li>
						<li><a href="<?php echo esc_url( compass_nav_url( 'terms/' ) ); ?>"><?php esc_html_e( 'Terms', 'compass-digital' ); ?></a></li>
						<li><a href="<?php echo esc_url( compass_nav_url( 'cookie-policy/' ) ); ?>"><?php esc_html_e( 'Cookie Policy', 'compass-digital' ); ?></a></li>
						<li><button type="button" class="site-footer__consent-btn" data-consent-open><?php esc_html_e( 'Cookie preferences', 'compass-digital' ); ?></button></li>
					</ul>
				</div>
			</nav>
		</div>

		<div class="site-footer__bottom">
			<p class="site-footer__copy">
				&copy; <?php echo esc_html( gmdate( 'Y' ) ); ?>
				<?php echo esc_html( ! empty( $compass_contact['reg_name'] ) ? $compass_contact['reg_name'] : get_bloginfo( 'name' ) ); ?>.
				<?php if ( ! empty( $compass_contact['reg_no'] ) ) : ?>
					<span class="site-footer__reg"><?php esc_html_e( 'Reg. No.', 'compass-digital' ); ?> <?php echo esc_html( $compass_contact['reg_no'] ); ?></span>
				<?php endif; ?>
			</p>
			<p class="site-footer__motto"><?php esc_html_e( 'Find your way forward.', 'compass-digital' ); ?></p>
		</div>
	</div>
</footer>

<?php
// Decorative scroll-driven compass watermark, on every template except home.
if ( ! is_front_page() ) {
	get_template_part( 'template-parts/compass-watermark' );
}
?>

<?php wp_footer(); ?>
</body>
</html>
