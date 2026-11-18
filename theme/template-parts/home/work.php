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
<section class="home-section work" id="work" aria-labelledby="work-title">
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

				$client    = get_post_meta( get_the_ID(), '_compass_client', true );
				$work_year = get_post_meta( get_the_ID(), '_compass_year', true );
				$results   = get_post_meta( get_the_ID(), '_compass_results', true );
				$result    = '';
				if ( $results ) {
					$lines  = preg_split( '/\r\n|\r|\n/', (string) $results );
					$result = trim( (string) reset( $lines ) );
				}
				if ( '' === $result ) {
					$result = wp_trim_words( get_the_excerpt(), 12 );
				}

				$meta_bits = array_filter( array( $client, $work_year ) );
				?>
				<a class="work-card" href="<?php the_permalink(); ?>">
					<div class="work-card__media">
						<?php
						if ( has_post_thumbnail() ) {
							the_post_thumbnail(
								'medium_large',
								array(
									'loading' => 'lazy',
									'alt'     => esc_attr( get_the_title() ),
								)
							);
						}
						?>
					</div>
					<div class="work-card__body">
						<?php if ( ! empty( $meta_bits ) ) : ?>
							<span class="work-card__meta"><?php echo esc_html( implode( ' · ', $meta_bits ) ); ?></span>
						<?php endif; ?>
						<h3 class="work-card__title"><?php the_title(); ?></h3>
						<?php if ( $result ) : ?>
							<p class="work-card__result"><?php echo esc_html( $result ); ?></p>
						<?php endif; ?>
					</div>
				</a>
				<?php
			endwhile;
			wp_reset_postdata();
			?>
		</div>
	</div>
</section>
