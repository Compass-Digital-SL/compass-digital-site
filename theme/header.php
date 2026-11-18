<?php
/**
 * Site header and document head.
 *
 * @package CompassDigital
 */

defined( 'ABSPATH' ) || exit;
?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<a class="skip-link" href="#main"><?php esc_html_e( 'Skip to content', 'compass-digital' ); ?></a>

<header class="site-header" role="banner">
	<div class="site-header__inner">
		<?php get_template_part( 'template-parts/header/branding' ); ?>
		<?php get_template_part( 'template-parts/header/nav' ); ?>
	</div>
</header>

<main id="main" class="site-main" tabindex="-1">
