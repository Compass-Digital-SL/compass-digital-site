<?php
/**
 * Archive listing — Insights category/tag/date archives.
 *
 * The Insights index itself is home.php (the static Posts page); this handles
 * the secondary archives (a category, a tag, a date). It reuses the Insights
 * hero and the shared post card, and links back to the full Insights index.
 *
 * @package CompassDigital
 */

defined( 'ABSPATH' ) || exit;

get_header();

$insights_url = home_url( '/insights/' );
$categories   = compass_insights_categories();
$current_cat  = is_category() ? (int) get_queried_object_id() : 0;
?>
<article class="insights-archive">

	<header class="service-hero">
		<div class="container">
			<div class="service-hero__inner" data-reveal>
				<p class="service-hero__eyebrow"><?php esc_html_e( 'Insights', 'compass-digital' ); ?></p>
				<h1 class="service-hero__title"><?php the_archive_title(); ?></h1>
				<?php
				$compass_desc = get_the_archive_description();
				if ( $compass_desc ) :
					?>
					<div class="service-hero__lede"><?php echo wp_kses_post( $compass_desc ); ?></div>
				<?php endif; ?>
			</div>
		</div>
	</header>

	<div class="service-body container">

		<?php if ( $categories ) : ?>
			<nav class="insights-filter" aria-label="<?php esc_attr_e( 'Filter articles by topic', 'compass-digital' ); ?>">
				<span class="insights-filter__label"><?php esc_html_e( 'Topic', 'compass-digital' ); ?></span>
				<a class="post-chip" href="<?php echo esc_url( $insights_url ); ?>"><?php esc_html_e( 'All', 'compass-digital' ); ?></a>
				<?php foreach ( $categories as $topic ) : ?>
					<a
						class="post-chip<?php echo $current_cat === (int) $topic->term_id ? ' is-active' : ''; ?>"
						href="<?php echo esc_url( get_category_link( $topic ) ); ?>"
						<?php echo $current_cat === (int) $topic->term_id ? 'aria-current="true"' : ''; ?>
					><?php echo esc_html( $topic->name ); ?></a>
				<?php endforeach; ?>
			</nav>
		<?php endif; ?>

		<?php if ( have_posts() ) : ?>
			<div class="post-grid" data-reveal>
				<?php
				while ( have_posts() ) :
					the_post();
					get_template_part( 'template-parts/post-card' );
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
			<p class="post-grid__empty">
				<?php esc_html_e( 'Nothing here yet.', 'compass-digital' ); ?>
				<a class="link" href="<?php echo esc_url( $insights_url ); ?>"><?php esc_html_e( 'Back to all insights', 'compass-digital' ); ?></a>
			</p>
		<?php endif; ?>

	</div>
</article>
<?php
get_footer();
