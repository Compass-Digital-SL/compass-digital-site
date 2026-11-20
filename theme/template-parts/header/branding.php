<?php
/**
 * Site branding: the wordmark, white on dark and black on light.
 *
 * Both images are in the markup; CSS shows only the one that matches the active
 * theme (display:none also hides the other from assistive tech, so the name is
 * announced once). These theme-aware wordmarks deliberately take priority over a
 * Customizer logo: a single uploaded image cannot switch between the dark and
 * light themes. The Customizer logo is used only if the theme files are missing.
 *
 * @package CompassDigital
 */

defined( 'ABSPATH' ) || exit;

$compass_brand_context = isset( $args['context'] ) ? $args['context'] : 'header';
$compass_has_wordmark  = is_readable( COMPASS_DIR . '/assets/brand/logo-white.webp' );
?>
<div class="site-branding">
	<?php if ( ! $compass_has_wordmark && has_custom_logo() ) : ?>
		<?php the_custom_logo(); ?>
	<?php else : ?>
		<a class="brand brand--<?php echo esc_attr( $compass_brand_context ); ?>" href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home">
			<?php foreach ( array( 'white' => 'dark', 'black' => 'light' ) as $compass_ink => $compass_mode ) : ?>
				<img
					class="brand__logo brand__logo--<?php echo esc_attr( $compass_mode ); ?>"
					src="<?php echo esc_url( COMPASS_URI . '/assets/brand/logo-' . $compass_ink . '.webp' ); ?>"
					width="466" height="72"
					alt="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>"
					decoding="async"
				>
			<?php endforeach; ?>
		</a>
	<?php endif; ?>
</div>
