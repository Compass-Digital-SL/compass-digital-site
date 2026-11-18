<?php
/**
 * Fallback template.
 *
 * WordPress requires index.php. All real routes resolve to more specific
 * templates (front-page, page, single, archive, 404); this mirrors archive.
 *
 * @package CompassDigital
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>
<div class="archive">
	<?php if ( have_posts() ) : ?>
		<div class="archive__list">
			<?php
			while ( have_posts() ) :
				the_post();
				get_template_part( 'template-parts/content/entry-summary' );
			endwhile;
			?>
		</div>
		<?php the_posts_pagination(); ?>
	<?php else : ?>
		<p><?php esc_html_e( 'Nothing here yet.', 'compass-digital' ); ?></p>
	<?php endif; ?>
</div>
<?php
get_footer();
