<?php
/**
 * Single case study — single-work.php.
 *
 * One template for every case study, built from the Work post's title, excerpt,
 * featured image and `_compass_*` meta (client, year, live URL, tech, problem,
 * approach, solution, results, gallery, testimonial). Structure:
 * problem → approach → solution → results (with numbers) → tech → gallery →
 * testimonial → next project → quote CTA.
 *
 * Gallery images are lazy-loaded with width/height set (no layout shift) and
 * serve whatever modern format (AVIF/WebP) the owner uploaded. CreativeWork and
 * BreadcrumbList JSON-LD are emitted at the end. Works with JavaScript off.
 *
 * @package CompassDigital
 */

defined( 'ABSPATH' ) || exit;

get_header();

while ( have_posts() ) :
	the_post();

	$work_id   = get_the_ID();
	$page_url  = get_permalink();
	$client    = get_post_meta( $work_id, '_compass_client', true );
	$work_year = get_post_meta( $work_id, '_compass_year', true );
	$live_url  = get_post_meta( $work_id, '_compass_live_url', true );
	$tech      = get_post_meta( $work_id, '_compass_tech', true );
	$problem   = get_post_meta( $work_id, '_compass_problem', true );
	$approach  = get_post_meta( $work_id, '_compass_approach', true );
	$solution  = get_post_meta( $work_id, '_compass_solution', true );
	$results   = get_post_meta( $work_id, '_compass_results', true );
	$quote     = get_post_meta( $work_id, '_compass_quote', true );
	$author    = get_post_meta( $work_id, '_compass_quote_author', true );
	$gallery   = get_post_meta( $work_id, '_compass_gallery', true );

	$meta_bits    = array_filter( array( $client, $work_year ) );
	$tech_items   = $tech ? array_filter( array_map( 'trim', explode( ',', (string) $tech ) ) ) : array();
	$result_items = $results ? array_filter( array_map( 'trim', preg_split( '/\r\n|\r|\n/', (string) $results ) ) ) : array();
	$gallery_ids  = $gallery ? array_filter( array_map( 'absint', explode( ',', (string) $gallery ) ) ) : array();

	// Quote pre-filled with the case study's service. Pass the term slug: it
	// matches the form's option keys, whereas term names can differ from labels.
	$svc_slugs = wp_get_post_terms( $work_id, 'service', array( 'fields' => 'slugs' ) );
	$svc_slug  = ( ! is_wp_error( $svc_slugs ) && $svc_slugs ) ? reset( $svc_slugs ) : '';
	$quote_url = compass_quote_url( $svc_slug );

	// Next case study (newest-first order, wrapping to the first).
	$next_post = get_adjacent_post( false, '', true );
	if ( ! $next_post ) {
		$older = new WP_Query(
			array(
				'post_type'           => 'work',
				'posts_per_page'      => 1,
				'post__not_in'        => array( $work_id ),
				'orderby'             => 'date',
				'order'               => 'DESC',
				'no_found_rows'       => true,
				'ignore_sticky_posts' => true,
			)
		);
		if ( $older->have_posts() ) {
			$next_post = $older->posts[0];
		}
		wp_reset_postdata();
	}
	?>
<article class="case-study">

	<header class="service-hero">
		<div class="container">
			<?php get_template_part( 'template-parts/breadcrumbs' ); ?>
			<div class="service-hero__inner" data-reveal>
				<?php if ( ! empty( $meta_bits ) ) : ?>
					<p class="service-hero__eyebrow"><?php echo esc_html( implode( ' · ', $meta_bits ) ); ?></p>
				<?php endif; ?>
				<h1 class="service-hero__title"><?php the_title(); ?></h1>
				<?php if ( has_excerpt() ) : ?>
					<p class="service-hero__lede"><?php echo esc_html( get_the_excerpt() ); ?></p>
				<?php endif; ?>
				<p class="service-hero__actions">
					<a class="btn btn--primary" href="<?php echo esc_url( $quote_url ); ?>"><?php esc_html_e( 'Start a project like this', 'compass-digital' ); ?></a>
					<?php if ( $live_url ) : ?>
						<a class="btn btn--ghost" href="<?php echo esc_url( $live_url ); ?>" target="_blank" rel="noopener"><?php esc_html_e( 'Visit the live site', 'compass-digital' ); ?></a>
					<?php endif; ?>
				</p>
			</div>
		</div>
	</header>

	<?php if ( has_post_thumbnail() ) : ?>
		<figure class="case-study__cover container" data-reveal>
			<?php
			the_post_thumbnail(
				'large',
				array(
					'loading'  => 'eager',
					'decoding' => 'async',
					'alt'      => esc_attr( get_the_title() ),
				)
			);
			?>
		</figure>
	<?php endif; ?>

	<div class="service-body container">

		<?php if ( $problem || $approach || $solution ) : ?>
			<section class="service-section case-study__story" aria-labelledby="story-title">
				<h2 id="story-title" class="screen-reader-text"><?php esc_html_e( 'The story', 'compass-digital' ); ?></h2>
				<div class="case-study__story-grid">
					<?php if ( $problem ) : ?>
						<div class="case-block card" data-reveal>
							<h3 class="case-block__label"><?php esc_html_e( 'The problem', 'compass-digital' ); ?></h3>
							<p><?php echo esc_html( $problem ); ?></p>
						</div>
					<?php endif; ?>
					<?php if ( $approach ) : ?>
						<div class="case-block card" data-reveal>
							<h3 class="case-block__label"><?php esc_html_e( 'Our approach', 'compass-digital' ); ?></h3>
							<p><?php echo esc_html( $approach ); ?></p>
						</div>
					<?php endif; ?>
					<?php if ( $solution ) : ?>
						<div class="case-block card" data-reveal>
							<h3 class="case-block__label"><?php esc_html_e( 'The solution', 'compass-digital' ); ?></h3>
							<p><?php echo esc_html( $solution ); ?></p>
						</div>
					<?php endif; ?>
				</div>
			</section>
		<?php endif; ?>

		<?php if ( get_the_content() ) : ?>
			<section class="service-section case-study__detail service-prose" data-reveal>
				<?php the_content(); ?>
			</section>
		<?php endif; ?>

		<?php if ( $result_items ) : ?>
			<section class="service-section case-study__results" aria-labelledby="results-title">
				<div class="section-header" data-reveal>
					<span class="section-header__eyebrow"><?php esc_html_e( 'Results', 'compass-digital' ); ?></span>
					<h2 id="results-title"><?php esc_html_e( 'The numbers that followed.', 'compass-digital' ); ?></h2>
				</div>
				<ul class="case-results__list" role="list" data-reveal>
					<?php foreach ( $result_items as $item ) : ?>
						<li class="case-result card"><?php echo esc_html( $item ); ?></li>
					<?php endforeach; ?>
				</ul>
			</section>
		<?php endif; ?>

		<?php if ( $tech_items ) : ?>
			<section class="service-section case-study__tech" aria-labelledby="tech-title">
				<div class="section-header" data-reveal>
					<span class="section-header__eyebrow"><?php esc_html_e( 'Under the hood', 'compass-digital' ); ?></span>
					<h2 id="tech-title"><?php esc_html_e( 'What we built it with.', 'compass-digital' ); ?></h2>
				</div>
				<ul class="case-tech__list" role="list" data-reveal>
					<?php foreach ( $tech_items as $item ) : ?>
						<li class="tag"><?php echo esc_html( $item ); ?></li>
					<?php endforeach; ?>
				</ul>
			</section>
		<?php endif; ?>

		<?php if ( $gallery_ids ) : ?>
			<section class="service-section case-study__gallery" aria-labelledby="gallery-title">
				<h2 id="gallery-title" class="screen-reader-text"><?php esc_html_e( 'Project gallery', 'compass-digital' ); ?></h2>
				<div class="case-gallery" data-reveal>
					<?php
					foreach ( $gallery_ids as $att_id ) {
						$img = wp_get_attachment_image(
							$att_id,
							'large',
							false,
							array(
								'class'    => 'case-gallery__img',
								'loading'  => 'lazy',
								'decoding' => 'async',
							)
						);
						if ( $img ) {
							echo '<figure class="case-gallery__item">' . $img . '</figure>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- wp_get_attachment_image returns escaped markup.
						}
					}
					?>
				</div>
			</section>
		<?php endif; ?>

		<?php if ( $quote ) : ?>
			<section class="service-section case-study__quote" aria-label="<?php esc_attr_e( 'Client testimonial', 'compass-digital' ); ?>">
				<figure class="case-quote card" data-reveal>
					<blockquote class="case-quote__text"><?php echo esc_html( $quote ); ?></blockquote>
					<?php if ( $author ) : ?>
						<figcaption class="case-quote__author"><?php echo esc_html( $author ); ?></figcaption>
					<?php endif; ?>
				</figure>
			</section>
		<?php endif; ?>

		<?php if ( $next_post ) : ?>
			<nav class="service-section case-study__next" aria-label="<?php esc_attr_e( 'Next case study', 'compass-digital' ); ?>" data-reveal>
				<a class="case-next" href="<?php echo esc_url( get_permalink( $next_post ) ); ?>">
					<span class="case-next__label"><?php esc_html_e( 'Next project', 'compass-digital' ); ?></span>
					<span class="case-next__title"><?php echo esc_html( get_the_title( $next_post ) ); ?></span>
					<span class="case-next__more"><?php esc_html_e( 'Read the story →', 'compass-digital' ); ?></span>
				</a>
			</nav>
		<?php endif; ?>

		<section class="service-section service-cta" aria-labelledby="case-cta-title" data-reveal>
			<div class="service-cta__inner">
				<h2 id="case-cta-title"><?php esc_html_e( 'Let’s build yours.', 'compass-digital' ); ?></h2>
				<p><?php esc_html_e( 'Every project starts with a clear, honest quote — no jargon, no surprises. Tell us what you need.', 'compass-digital' ); ?></p>
				<p>
					<a class="btn btn--primary" href="<?php echo esc_url( $quote_url ); ?>"><?php esc_html_e( 'Request a quote', 'compass-digital' ); ?></a>
					<a class="btn btn--ghost" href="<?php echo esc_url( get_post_type_archive_link( 'work' ) ); ?>"><?php esc_html_e( 'See more work', 'compass-digital' ); ?></a>
				</p>
			</div>
		</section>

	</div>
</article>
	<?php
	$breadcrumbs = array(
		array(
			'name' => __( 'Home', 'compass-digital' ),
			'url'  => home_url( '/' ),
		),
		array(
			'name' => __( 'Work', 'compass-digital' ),
			'url'  => get_post_type_archive_link( 'work' ),
		),
		array(
			'name' => get_the_title(),
			'url'  => $page_url,
		),
	);
	compass_print_schema( compass_work_schema( $work_id, $page_url ) );
	compass_print_schema( compass_breadcrumb_schema( $breadcrumbs ) );

endwhile;

get_footer();
