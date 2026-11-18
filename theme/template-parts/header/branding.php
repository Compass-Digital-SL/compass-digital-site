<?php
/**
 * Site branding (logo or name).
 *
 * @package CompassDigital
 */

defined( 'ABSPATH' ) || exit;
?>
<div class="site-branding">
	<?php if ( has_custom_logo() ) : ?>
		<?php the_custom_logo(); ?>
	<?php else : ?>
		<a class="site-branding__name" href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home">
			<?php bloginfo( 'name' ); ?>
		</a>
	<?php endif; ?>
</div>
