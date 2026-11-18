<?php
/**
 * Home — proof: numbers, testimonials and honest guarantees.
 *
 * @package CompassDigital
 */

defined( 'ABSPATH' ) || exit;

$stats = array(
	array(
		'num'    => 95,
		'suffix' => '+',
		'prefix' => '',
		'label'  => __( 'Lighthouse score we build every site to', 'compass-digital' ),
	),
	array(
		'num'    => 2,
		'suffix' => 's',
		'prefix' => '<',
		'label'  => __( 'Page load we design for on mobile', 'compass-digital' ),
	),
	array(
		'num'    => 6,
		'suffix' => '',
		'prefix' => '',
		'label'  => __( 'Services, one team to call', 'compass-digital' ),
	),
	array(
		'num'    => 100,
		'suffix' => '%',
		'prefix' => '',
		'label'  => __( 'Yours to own and take anywhere', 'compass-digital' ),
	),
);

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
<section class="home-section proof" id="proof" aria-labelledby="proof-title">
	<div class="container">
		<div class="section-header" data-reveal>
			<span class="section-header__eyebrow"><?php esc_html_e( 'Why trust us', 'compass-digital' ); ?></span>
			<h2 id="proof-title"><?php esc_html_e( 'Proof, not promises.', 'compass-digital' ); ?></h2>
			<p class="section-header__lede"><?php esc_html_e( 'Clear standards we hold ourselves to, and the plain guarantees that come with every project.', 'compass-digital' ); ?></p>
		</div>

		<ul class="proof__stats" role="list" data-reveal>
			<?php foreach ( $stats as $stat ) : ?>
				<?php
				$counter_attrs = 'data-counter="' . esc_attr( $stat['num'] ) . '"';
				if ( '' !== $stat['prefix'] ) {
					$counter_attrs .= ' data-prefix="' . esc_attr( $stat['prefix'] ) . '"';
				}
				if ( '' !== $stat['suffix'] ) {
					$counter_attrs .= ' data-suffix="' . esc_attr( $stat['suffix'] ) . '"';
				}
				?>
				<li class="proof__stat">
					<span class="proof__num" <?php echo $counter_attrs; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- attribute values escaped with esc_attr above. ?>><?php echo esc_html( $stat['prefix'] . $stat['num'] . $stat['suffix'] ); ?></span>
					<span class="proof__label"><?php echo esc_html( $stat['label'] ); ?></span>
				</li>
			<?php endforeach; ?>
		</ul>

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
