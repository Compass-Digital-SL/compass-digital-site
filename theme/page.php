<?php
/**
 * Single page.
 *
 * @package CompassDigital
 */

defined( 'ABSPATH' ) || exit;

get_header();

while ( have_posts() ) :
	the_post();
	?>
	<article <?php post_class( 'page' ); ?>>
		<?php get_template_part( 'template-parts/content/entry-header' ); ?>
		<div class="entry-content">
			<?php
			the_content();
			wp_link_pages();
			?>
		</div>
	</article>
	<?php
endwhile;

get_footer();
