<?php
/**
 * Front page.
 *
 * Section scaffolding only; real home-page content lands in Task 10.
 *
 * @package CompassDigital
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>
<div class="front-page">
	<section class="hero" aria-labelledby="hero-title">
		<div class="hero__inner">
			<h1 id="hero-title" class="hero__title"><?php bloginfo( 'name' ); ?></h1>
			<p class="hero__tagline"><?php esc_html_e( 'Find your way forward.', 'compass-digital' ); ?></p>
			<p class="hero__descriptor"><?php esc_html_e( 'Websites, SEO and POS systems.', 'compass-digital' ); ?></p>
			<p class="hero__cta">
				<a class="button button--primary" href="<?php echo esc_url( home_url( '/get-a-quote/' ) ); ?>">
					<?php esc_html_e( 'Request a quote', 'compass-digital' ); ?>
				</a>
			</p>
		</div>
	</section>

	<?php
	// If an editor has added content to the Home page, show it below the hero.
	if ( have_posts() ) :
		while ( have_posts() ) :
			the_post();
			if ( '' !== trim( get_the_content() ) ) :
				?>
				<section class="front-page__content">
					<div class="entry-content">
						<?php the_content(); ?>
					</div>
				</section>
				<?php
			endif;
		endwhile;
	endif;
	?>
</div>
<?php
get_footer();
