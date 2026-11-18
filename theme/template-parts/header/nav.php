<?php
/**
 * Primary navigation.
 *
 * @package CompassDigital
 */

defined( 'ABSPATH' ) || exit;

if ( ! has_nav_menu( 'primary' ) ) {
	return;
}
?>
<nav class="primary-nav" aria-label="<?php esc_attr_e( 'Primary', 'compass-digital' ); ?>">
	<button class="primary-nav__toggle" aria-expanded="false" aria-controls="primary-menu">
		<span class="screen-reader-text"><?php esc_html_e( 'Menu', 'compass-digital' ); ?></span>
		<span class="primary-nav__bars" aria-hidden="true"></span>
	</button>
	<?php
	wp_nav_menu(
		array(
			'theme_location' => 'primary',
			'container'      => false,
			'menu_id'        => 'primary-menu',
			'menu_class'     => 'primary-nav__menu',
			'depth'          => 2,
			'fallback_cb'    => false,
		)
	);
	?>
</nav>
