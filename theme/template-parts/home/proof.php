<?php
/**
 * Home — proof: our promise, testimonials and honest guarantees.
 *
 * The headline numbers live in the hero spec strip, so they are not repeated here.
 *
 * @package CompassDigital
 */

defined( 'ABSPATH' ) || exit;

$guarantees = array(
	array(
		'title' => __( 'Support after launch', 'compass-digital' ),
		'text'  => __( 'We don’t vanish once the invoice clears. We stay on to fix, update and grow what we built.', 'compass-digital' ),
	),
	array(
		'title' => __( 'You own your site', 'compass-digital' ),
		'text'  => __( 'Your site, your domain, your accounts, your content — all in your name. No hostage situations.', 'compass-digital' ),
	),
	array(
		'title' => __( 'No lock-in', 'compass-digital' ),
		'text'  => __( 'Built on open, standard tools you could hand to any other developer. If you ever leave, you leave with everything.', 'compass-digital' ),
	),
	array(
		'title' => __( 'Fixed, clear quotes', 'compass-digital' ),
		'text'  => __( 'You get a fixed price and scope up front, in plain language. No creeping fees, no surprises.', 'compass-digital' ),
	),
);

$testimonials = new WP_Query(
	array(
		'post_type'           => 'testimonial',
		'posts_per_page'      => 2,
		'post_status'         => 'publish',
		'ignore_sticky_posts' => true,
		'no_found_rows'       => true,
		'orderby'             => array(
			'meta_value_num' => 'DESC',
			'date'           => 'DESC',
		),
		'meta_key'            => '_compass_featured', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
	)
);
?>
<section class="home-section proof" id="proof" aria-labelledby="proof-title" data-waypoint data-label="<?php esc_attr_e( 'Promise', 'compass-digital' ); ?>">
	<div class="container">
		<div class="section-header" data-reveal>
			<span class="section-header__eyebrow"><?php esc_html_e( 'Our promise', 'compass-digital' ); ?></span>
			<h2 id="proof-title" class="proof__promise"><?php esc_html_e( 'You should always know what you’re paying for,', 'compass-digital' ); ?> <em><?php esc_html_e( 'what happens next,', 'compass-digital' ); ?></em> <?php esc_html_e( 'and that it’s yours.', 'compass-digital' ); ?></h2>
			<p class="section-header__lede"><?php esc_html_e( 'The plain guarantees that come with every project, and what our clients say.', 'compass-digital' ); ?></p>
		</div>


		<?php if ( $testimonials->have_posts() ) : ?>
			<div class="proof__quotes" data-reveal>
				<?php
				while ( $testimonials->have_posts() ) :
					$testimonials->the_post();
					$author      = get_post_meta( get_the_ID(), '_compass_author_name', true );
					$author_role = get_post_meta( get_the_ID(), '_compass_author_role', true );
					if ( '' === $author ) {
						$author = get_the_title();
					}
					?>
					<figure class="proof__quote">
						<blockquote><?php echo wp_kses_post( wpautop( get_the_content() ) ); ?></blockquote>
						<figcaption class="proof__cite">
							<strong><?php echo esc_html( $author ); ?></strong>
							<?php echo esc_html( $author_role ); ?>
						</figcaption>
					</figure>
					<?php
				endwhile;
				wp_reset_postdata();
				?>
			</div>
		<?php endif; ?>

		<?php
		$reviews_url = function_exists( 'compass_get_setting' ) ? compass_get_setting( 'reviews_url' ) : '';
		if ( $reviews_url ) :
			?>
			<p class="proof__reviews" data-reveal>
				<a class="proof__reviews-link" href="<?php echo esc_url( $reviews_url ); ?>" rel="noopener" target="_blank">
					<?php esc_html_e( 'Read more reviews on Google', 'compass-digital' ); ?>
					<span class="screen-reader-text"><?php esc_html_e( '(opens in a new tab)', 'compass-digital' ); ?></span>
				</a>
			</p>
		<?php endif; ?>

		<ul class="guarantees" role="list" data-reveal>
			<?php foreach ( $guarantees as $guarantee ) : ?>
				<li class="guarantee">
					<h3><?php echo esc_html( $guarantee['title'] ); ?></h3>
					<p><?php echo esc_html( $guarantee['text'] ); ?></p>
				</li>
			<?php endforeach; ?>
		</ul>
	</div>
</section>
