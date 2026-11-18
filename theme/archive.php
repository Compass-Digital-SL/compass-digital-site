<?php
/**
 * Archive (and blog home) listing.
 *
 * @package CompassDigital
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>
<div class="archive">
	<header class="archive__header">
		<h1 class="archive__title">
			<?php
			if ( is_home() && ! is_front_page() ) {
				single_post_title();
			} else {
				the_archive_title();
			}
			?>
		</h1>
		<?php
		$compass_desc = get_the_archive_description();
		if ( $compass_desc ) {
			echo '<div class="archive__description">' . wp_kses_post( $compass_desc ) . '</div>';
		}
		?>
	</header>

	<?php if ( have_posts() ) : ?>
		<div class="archive__list">
			<?php
			while ( have_posts() ) :
				the_post();
				get_template_part( 'template-parts/content/entry-summary' );
			endwhile;
			?>
		</div>
		<?php
		the_posts_pagination(
			array(
				'mid_size'  => 1,
				'prev_text' => __( 'Previous', 'compass-digital' ),
				'next_text' => __( 'Next', 'compass-digital' ),
			)
		);
		?>
	<?php else : ?>
		<p><?php esc_html_e( 'Nothing here yet.', 'compass-digital' ); ?></p>
	<?php endif; ?>
</div>
<?php
get_footer();
