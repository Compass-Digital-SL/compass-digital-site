<?php
/**
 * Template Name: Industry
 *
 * One template, five pages. The current page's slug selects its entry in
 * compass_industries_data() (see inc/industries-content.php), and every section —
 * plus the FAQPage and BreadcrumbList JSON-LD — is rendered from that single
 * source, so the copy and the structured data never drift apart.
 *
 * Reuses the service-page CSS (assets/src/css/services.css) and the shared FAQ
 * accordion. Cross-links out to the services that fit and back to the Industries
 * hub. Works with JavaScript off. No prices anywhere — "quote on request".
 *
 * @package CompassDigital
 */

defined( 'ABSPATH' ) || exit;

get_header();

$compass_slug = get_post_field( 'post_name', get_queried_object_id() );
$compass_data = compass_industries_data();
$industry     = isset( $compass_data[ $compass_slug ] ) ? $compass_data[ $compass_slug ] : null;

if ( null === $industry ) {
	// A page using this template whose slug isn't a known industry: render its
	// editor content rather than a blank page.
	while ( have_posts() ) :
		the_post();
		?>
		<article class="page container container--narrow">
			<h1><?php the_title(); ?></h1>
			<div class="entry-content"><?php the_content(); ?></div>
		</article>
		<?php
	endwhile;
	get_footer();
	return;
}

$page_url  = get_permalink();
$quote_url = compass_quote_url( '', $compass_slug );

// Related work: case studies tagged with this industry's taxonomy term.
$related_work = new WP_Query(
	array(
		'post_type'              => 'work',
		'posts_per_page'         => 3,
		'no_found_rows'          => true,
		'ignore_sticky_posts'    => true,
		'update_post_meta_cache' => false,
		'tax_query'              => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query -- small, cached page query.
			array(
				'taxonomy' => 'industry',
				'field'    => 'name',
				'terms'    => $industry['work_term'],
			),
		),
	)
);
?>
<article class="service-page industry-page">

	<header class="service-hero">
		<div class="container">
			<?php get_template_part( 'template-parts/breadcrumbs' ); ?>
			<div class="service-hero__inner" data-reveal>
				<p class="service-hero__eyebrow"><?php echo esc_html( $industry['eyebrow'] ); ?></p>
				<h1 class="service-hero__title"><?php echo esc_html( $industry['headline'] ); ?></h1>
				<p class="service-hero__lede"><?php echo esc_html( $industry['lede'] ); ?></p>
				<p class="service-hero__actions">
					<a class="btn btn--primary" href="<?php echo esc_url( $quote_url ); ?>">
						<?php esc_html_e( 'Get a quote', 'compass-digital' ); ?>
					</a>
					<a class="btn btn--ghost" href="<?php echo esc_url( home_url( '/work/' ) ); ?>">
						<?php esc_html_e( 'See our work', 'compass-digital' ); ?>
					</a>
				</p>
			</div>
		</div>
	</header>

	<div class="service-body container">

		<section class="service-section service-intro" aria-label="<?php esc_attr_e( 'Overview', 'compass-digital' ); ?>">
			<div class="service-prose" data-reveal>
				<?php foreach ( (array) $industry['intro'] as $paragraph ) : ?>
					<p><?php echo esc_html( $paragraph ); ?></p>
				<?php endforeach; ?>
			</div>
			<aside class="service-who card" data-reveal aria-labelledby="signs-title">
				<h2 id="signs-title" class="service-who__title"><?php esc_html_e( 'Sounds like you if…', 'compass-digital' ); ?></h2>
				<ul class="service-who__list" role="list">
					<?php foreach ( (array) $industry['signs'] as $sign ) : ?>
						<li><?php echo esc_html( $sign ); ?></li>
					<?php endforeach; ?>
				</ul>
			</aside>
		</section>

		<section class="service-section service-pains" aria-labelledby="pains-title">
			<div class="section-header" data-reveal>
				<span class="section-header__eyebrow"><?php esc_html_e( 'Pains we solve', 'compass-digital' ); ?></span>
				<h2 id="pains-title"><?php esc_html_e( 'The daily frustrations — and how we fix them.', 'compass-digital' ); ?></h2>
			</div>
			<ul class="service-pains__list" role="list" data-reveal>
				<?php foreach ( (array) $industry['pains'] as $pain ) : ?>
					<li class="service-pain card">
						<p class="service-pain__quote"><?php echo esc_html( $pain['quote'] ); ?></p>
						<p class="service-pain__fix"><?php echo esc_html( $pain['fix'] ); ?></p>
					</li>
				<?php endforeach; ?>
			</ul>
		</section>

		<section class="service-section service-included" aria-labelledby="fit-title">
			<div class="section-header" data-reveal>
				<span class="section-header__eyebrow"><?php esc_html_e( 'Services that fit', 'compass-digital' ); ?></span>
				<h2 id="fit-title"><?php esc_html_e( 'What we’d build for you, and why.', 'compass-digital' ); ?></h2>
			</div>
			<ul class="service-included__grid" role="list" data-reveal>
				<?php
				foreach ( (array) $industry['services'] as $fit ) :
					$fit_slug  = $fit['slug'];
					$fit_label = compass_service_label( $fit_slug );
					if ( ! $fit_label ) {
						continue;
					}
					$fit_url = home_url( '/services/' . $fit_slug . '/' );
					?>
					<li class="service-feature industry-fit card">
						<h3 class="service-feature__title">
							<a class="industry-fit__link" href="<?php echo esc_url( $fit_url ); ?>"><?php echo esc_html( $fit_label ); ?></a>
						</h3>
						<p class="service-feature__text"><?php echo esc_html( $fit['text'] ); ?></p>
						<span class="industry-fit__more"><?php esc_html_e( 'Explore this service →', 'compass-digital' ); ?></span>
					</li>
				<?php endforeach; ?>
			</ul>
		</section>

		<?php if ( $related_work->have_posts() ) : ?>
			<section class="service-section service-work" aria-labelledby="work-title">
				<div class="section-header" data-reveal>
					<span class="section-header__eyebrow"><?php esc_html_e( 'Relevant work', 'compass-digital' ); ?></span>
					<h2 id="work-title"><?php esc_html_e( 'Proof, not promises', 'compass-digital' ); ?></h2>
				</div>
				<ul class="service-work__grid" role="list" data-reveal>
					<?php
					while ( $related_work->have_posts() ) :
						$related_work->the_post();
						$client = get_post_meta( get_the_ID(), '_compass_client', true );
						?>
						<li class="service-work-card card">
							<a href="<?php the_permalink(); ?>">
								<?php if ( $client ) : ?>
									<span class="service-work-card__client"><?php echo esc_html( $client ); ?></span>
								<?php endif; ?>
								<h3 class="service-work-card__title"><?php the_title(); ?></h3>
								<?php if ( has_excerpt() ) : ?>
									<p class="service-work-card__text"><?php echo esc_html( get_the_excerpt() ); ?></p>
								<?php endif; ?>
								<span class="service-work-card__more"><?php esc_html_e( 'Read the story →', 'compass-digital' ); ?></span>
							</a>
						</li>
						<?php
					endwhile;
					wp_reset_postdata();
					?>
				</ul>
				<p class="service-section__foot" data-reveal>
					<a class="link" href="<?php echo esc_url( home_url( '/work/' ) ); ?>"><?php esc_html_e( 'See all our work', 'compass-digital' ); ?></a>
				</p>
			</section>
		<?php endif; ?>

		<section class="service-section service-faq" aria-labelledby="faq-title">
			<div class="section-header" data-reveal>
				<span class="section-header__eyebrow"><?php esc_html_e( 'Questions', 'compass-digital' ); ?></span>
				<h2 id="faq-title"><?php esc_html_e( 'The things people usually ask.', 'compass-digital' ); ?></h2>
			</div>
			<div class="accordion faq" data-reveal>
				<?php foreach ( (array) $industry['faqs'] as $i => $faq ) : ?>
					<?php $panel_id = 'ind-faq-panel-' . $i; ?>
					<div class="accordion__item">
						<h3 class="accordion__heading">
							<button
								type="button"
								class="accordion__trigger"
								aria-expanded="false"
								aria-controls="<?php echo esc_attr( $panel_id ); ?>"
								id="ind-faq-trigger-<?php echo esc_attr( $i ); ?>"
							>
								<span><?php echo esc_html( $faq['q'] ); ?></span>
								<span class="accordion__icon" aria-hidden="true"></span>
							</button>
						</h3>
						<div
							class="accordion__panel"
							id="<?php echo esc_attr( $panel_id ); ?>"
							role="region"
							aria-labelledby="ind-faq-trigger-<?php echo esc_attr( $i ); ?>"
						>
							<p><?php echo esc_html( $faq['a'] ); ?></p>
						</div>
					</div>
				<?php endforeach; ?>
			</div>
		</section>

		<section class="service-section service-industries" aria-labelledby="related-services-title">
			<div class="section-header" data-reveal>
				<span class="section-header__eyebrow"><?php esc_html_e( 'Related services', 'compass-digital' ); ?></span>
				<h2 id="related-services-title"><?php esc_html_e( 'Explore the services behind this.', 'compass-digital' ); ?></h2>
			</div>
			<ul class="service-industries__strip" role="list" data-reveal>
				<?php
				foreach ( (array) $industry['services'] as $fit ) :
					$fit_label = compass_service_label( $fit['slug'] );
					if ( ! $fit_label ) {
						continue;
					}
					?>
					<li>
						<a class="industry-chip" href="<?php echo esc_url( home_url( '/services/' . $fit['slug'] . '/' ) ); ?>">
							<?php echo esc_html( $fit_label ); ?>
						</a>
					</li>
				<?php endforeach; ?>
				<li>
					<a class="industry-chip" href="<?php echo esc_url( home_url( '/industries/' ) ); ?>">
						<?php esc_html_e( 'All industries', 'compass-digital' ); ?>
					</a>
				</li>
			</ul>
		</section>

		<section class="service-section service-cta" aria-labelledby="industry-cta-title" data-reveal>
			<div class="service-cta__inner">
				<h2 id="industry-cta-title"><?php esc_html_e( 'Ready to fix this for good?', 'compass-digital' ); ?></h2>
				<p><?php esc_html_e( 'Tell us what’s slowing your business down and we’ll send a clear, honest quote — no pressure, no jargon.', 'compass-digital' ); ?></p>
				<p>
					<a class="btn btn--primary" href="<?php echo esc_url( $quote_url ); ?>"><?php esc_html_e( 'Get a quote', 'compass-digital' ); ?></a>
				</p>
			</div>
		</section>

	</div>
</article>
<?php
// Structured data, generated from the same copy shown above.
$breadcrumbs = array(
	array(
		'name' => __( 'Home', 'compass-digital' ),
		'url'  => home_url( '/' ),
	),
	array(
		'name' => __( 'Industries', 'compass-digital' ),
		'url'  => home_url( '/industries/' ),
	),
	array(
		'name' => wp_strip_all_tags( $industry['eyebrow'] ),
		'url'  => $page_url,
	),
);
compass_print_schema( compass_faq_schema( $industry['faqs'] ) );
compass_print_schema( compass_breadcrumb_schema( $breadcrumbs ) );

get_footer();
