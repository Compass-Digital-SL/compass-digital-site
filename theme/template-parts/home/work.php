<?php
/**
 * Home — "Recent work" preview: 3 featured case studies as a scroll-snap
 * carousel, falling back to the latest Work if none are flagged featured.
 *
 * @package CompassDigital
 */

defined( 'ABSPATH' ) || exit;

$work_args = array(
	'post_type'           => 'work',
	'posts_per_page'      => 3,
	'post_status'         => 'publish',
	'ignore_sticky_posts' => true,
	'no_found_rows'       => true,
	'meta_query'          => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
		array(
			'key'   => '_compass_featured',
			'value' => '1',
		),
	),
);

$work = new WP_Query( $work_args );

// Fallback: no featured case studies yet — show the three most recent.
if ( ! $work->have_posts() ) {
	unset( $work_args['meta_query'] );
	$work = new WP_Query( $work_args );
}

if ( ! $work->have_posts() ) {
	wp_reset_postdata();
	return;
}
?>
<section class="home-section work" id="work" aria-labelledby="work-title" data-waypoint data-label="<?php esc_attr_e( 'Work', 'compass-digital' ); ?>">
	<div class="container">
		<div class="work__header section-header" data-reveal>
			<div>
				<span class="section-header__eyebrow"><?php esc_html_e( 'Recent work', 'compass-digital' ); ?></span>
				<h2 id="work-title"><?php esc_html_e( 'Real businesses, real results.', 'compass-digital' ); ?></h2>
			</div>
			<a class="link" href="<?php echo esc_url( home_url( '/work/' ) ); ?>"><?php esc_html_e( 'See all work', 'compass-digital' ); ?></a>
		</div>

		<div class="work__rail" data-reveal>
			<?php
			while ( $work->have_posts() ) :
				$work->the_post();
				get_template_part( 'template-parts/work-card' );
			endwhile;
			wp_reset_postdata();
			?>
		</div>
	</div>
</section>
