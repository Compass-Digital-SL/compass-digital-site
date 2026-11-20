<?php
/**
 * Single Insights post.
 *
 * A reader-friendly article template: eyebrow meta (category · date · reading
 * time), a table of contents built from the post's own h2/h3 headings, the
 * article body, an author box, share links (plain URLs, no third-party scripts),
 * a soft "Request a quote" CTA, and related posts by category. Emits BlogPosting
 * + BreadcrumbList JSON-LD. Works with JavaScript off; insights.js enhances the
 * TOC (scroll-spy) and the copy-link button.
 *
 * @package CompassDigital
 */

defined( 'ABSPATH' ) || exit;

get_header();

while ( have_posts() ) :
	the_post();

	$insight_id = get_the_ID();
	$page_url   = get_permalink();
	$read_mins  = compass_reading_time( $insight_id );
	$cats       = get_the_category();
	$primary    = $cats ? $cats[0] : null;
	$author     = compass_post_author( $insight_id );
	$share      = compass_share_links( $page_url, get_the_title() );

	// Process the content once: add heading ids + collect the TOC.
	$processed = compass_process_post_content( apply_filters( 'the_content', get_the_content() ) );
	$toc       = $processed['toc'];
	$body      = $processed['content'];

	$related = compass_related_posts( $insight_id, 3 );
	?>
<article <?php post_class( 'insight' ); ?>>

	<header class="service-hero insight-hero">
		<div class="container">
			<div class="service-hero__inner" data-reveal>
				<?php if ( $primary ) : ?>
					<p class="service-hero__eyebrow">
						<a class="insight-hero__cat" href="<?php echo esc_url( get_category_link( $primary ) ); ?>"><?php echo esc_html( $primary->name ); ?></a>
					</p>
				<?php endif; ?>
				<h1 class="service-hero__title"><?php the_title(); ?></h1>
				<?php if ( has_excerpt() ) : ?>
					<p class="service-hero__lede"><?php echo esc_html( get_the_excerpt() ); ?></p>
				<?php endif; ?>
				<p class="insight-hero__meta">
					<span><?php echo esc_html( $author['name'] ); ?></span>
					<span aria-hidden="true">·</span>
					<time datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>"><?php echo esc_html( get_the_date() ); ?></time>
					<span aria-hidden="true">·</span>
					<span>
						<?php
						/* translators: %d: minutes to read. */
						printf( esc_html__( '%d min read', 'compass-digital' ), (int) $read_mins );
						?>
					</span>
				</p>
			</div>
		</div>
	</header>

	<?php if ( has_post_thumbnail() ) : ?>
		<figure class="insight__cover container" data-reveal>
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

	<div class="insight-body container">

		<?php if ( count( $toc ) > 1 ) : ?>
			<aside class="insight-toc" aria-labelledby="toc-title" data-insight-toc>
				<h2 id="toc-title" class="insight-toc__title"><?php esc_html_e( 'On this page', 'compass-digital' ); ?></h2>
				<nav aria-label="<?php esc_attr_e( 'Table of contents', 'compass-digital' ); ?>">
					<ol class="insight-toc__list">
						<?php foreach ( $toc as $item ) : ?>
							<li class="insight-toc__item insight-toc__item--h<?php echo esc_attr( $item['level'] ); ?>">
								<a href="#<?php echo esc_attr( $item['id'] ); ?>" data-toc-link="<?php echo esc_attr( $item['id'] ); ?>"><?php echo esc_html( $item['text'] ); ?></a>
							</li>
						<?php endforeach; ?>
					</ol>
				</nav>
			</aside>
		<?php endif; ?>

		<div class="insight-main">
			<div class="insight-prose">
				<?php
				echo $body; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- post content already run through the_content + heading-id pass.
				wp_link_pages();
				?>
			</div>

			<div class="insight-share" aria-label="<?php esc_attr_e( 'Share this article', 'compass-digital' ); ?>" data-insight-share>
				<span class="insight-share__label"><?php esc_html_e( 'Share', 'compass-digital' ); ?></span>
				<ul class="insight-share__list" role="list">
					<?php foreach ( $share as $share_link ) : ?>
						<li>
							<a
								class="insight-share__link insight-share__link--<?php echo esc_attr( $share_link['key'] ); ?>"
								href="<?php echo esc_url( $share_link['url'] ); ?>"
								<?php if ( 'copy' === $share_link['key'] ) : ?>
									data-copy-link
									data-copied-label="<?php esc_attr_e( 'Link copied', 'compass-digital' ); ?>"
								<?php elseif ( 'email' !== $share_link['key'] ) : ?>
									target="_blank" rel="noopener"
								<?php endif; ?>
							>
								<span class="screen-reader-text"><?php echo esc_html( $share_link['label'] ); ?></span>
								<span aria-hidden="true"><?php echo esc_html( $share_link['short'] ); ?></span>
							</a>
						</li>
					<?php endforeach; ?>
				</ul>
			</div>

			<aside class="insight-author card" aria-label="<?php esc_attr_e( 'About the author', 'compass-digital' ); ?>">
				<span class="insight-author__avatar" aria-hidden="true"></span>
				<div class="insight-author__body">
					<p class="insight-author__name"><?php echo esc_html( $author['name'] ); ?></p>
					<p class="insight-author__bio"><?php echo esc_html( $author['bio'] ); ?></p>
				</div>
			</aside>

			<section class="insight-cta" aria-labelledby="insight-cta-title">
				<div class="insight-cta__inner">
					<h2 id="insight-cta-title"><?php esc_html_e( 'Want this sorted for your business?', 'compass-digital' ); ?></h2>
					<p><?php esc_html_e( 'We build websites, SEO, POS and software for businesses worldwide — clear, fast and built around how you work. Tell us what you need.', 'compass-digital' ); ?></p>
					<p><a class="btn btn--primary" href="<?php echo esc_url( compass_quote_url() ); ?>"><?php esc_html_e( 'Request a quote', 'compass-digital' ); ?></a></p>
				</div>
			</section>
		</div>

	</div>

	<?php if ( $related ) : ?>
		<section class="insight-related container" aria-labelledby="related-title">
			<div class="section-header" data-reveal>
				<span class="section-header__eyebrow"><?php esc_html_e( 'Keep reading', 'compass-digital' ); ?></span>
				<h2 id="related-title"><?php esc_html_e( 'Related insights', 'compass-digital' ); ?></h2>
			</div>
			<div class="post-grid" data-reveal>
				<?php
				foreach ( $related as $rel_post ) :
					$GLOBALS['post'] = $rel_post; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited -- loop context for the shared card.
					setup_postdata( $rel_post );
					get_template_part( 'template-parts/post-card' );
				endforeach;
				wp_reset_postdata();
				?>
			</div>
		</section>
	<?php endif; ?>

</article>
	<?php
	compass_print_schema( compass_article_schema( $insight_id, $page_url ) );

	$breadcrumbs = array(
		array(
			'name' => __( 'Home', 'compass-digital' ),
			'url'  => home_url( '/' ),
		),
		array(
			'name' => __( 'Insights', 'compass-digital' ),
			'url'  => home_url( '/insights/' ),
		),
		array(
			'name' => get_the_title(),
			'url'  => $page_url,
		),
	);
	compass_print_schema( compass_breadcrumb_schema( $breadcrumbs ) );

endwhile;

get_footer();
