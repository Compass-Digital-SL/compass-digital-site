<?php
/**
 * Template Name: Service
 *
 * One template, six pages. The current page's slug selects its entry in
 * compass_services_data() (see inc/services-content.php), and every section —
 * plus the Service, FAQPage and BreadcrumbList JSON-LD — is rendered from that
 * single source, so the copy and the structured data never drift apart.
 *
 * Works with JavaScript off: the FAQ accordion shows all answers, and reveals
 * are progressive enhancement only. No prices anywhere — "quote on request".
 *
 * @package CompassDigital
 */

defined( 'ABSPATH' ) || exit;

get_header();

$compass_slug = get_post_field( 'post_name', get_queried_object_id() );
$compass_data = compass_services_data();
$service      = isset( $compass_data[ $compass_slug ] ) ? $compass_data[ $compass_slug ] : null;

if ( null === $service ) {
	// A page using this template whose slug isn't a known service: render its
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

$page_url        = get_permalink();
$quote_url       = compass_quote_url( $service['eyebrow'] );
$industry_labels = compass_industry_labels();

// Related work: case studies tagged with this service's taxonomy term.
$related_work = new WP_Query(
	array(
		'post_type'              => 'work',
		'posts_per_page'         => 3,
		'no_found_rows'          => true,
		'ignore_sticky_posts'    => true,
		'update_post_meta_cache' => false,
		'tax_query'              => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query -- small, cached page query.
			array(
				'taxonomy' => 'service',
				'field'    => 'name',
				'terms'    => $service['tax_name'],
			),
		),
	)
);
?>
<article class="service-page">

	<header class="service-hero">
		<div class="container">
			<?php get_template_part( 'template-parts/breadcrumbs' ); ?>
			<div class="service-hero__inner" data-reveal>
				<p class="service-hero__eyebrow"><?php echo esc_html( $service['eyebrow'] ); ?></p>
				<h1 class="service-hero__title"><?php echo esc_html( $service['headline'] ); ?></h1>
				<p class="service-hero__lede"><?php echo esc_html( $service['lede'] ); ?></p>
				<p class="service-hero__actions">
					<a class="btn btn--primary" href="<?php echo esc_url( $quote_url ); ?>">
						<?php esc_html_e( 'Get a quote for this', 'compass-digital' ); ?>
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
				<?php foreach ( (array) $service['body'] as $paragraph ) : ?>
					<p><?php echo esc_html( $paragraph ); ?></p>
				<?php endforeach; ?>
			</div>
			<aside class="service-who card" data-reveal aria-labelledby="who-title">
				<h2 id="who-title" class="service-who__title"><?php esc_html_e( 'Who it’s for', 'compass-digital' ); ?></h2>
				<ul class="service-who__list" role="list">
					<?php foreach ( (array) $service['who'] as $who ) : ?>
						<li><?php echo esc_html( $who ); ?></li>
					<?php endforeach; ?>
				</ul>
			</aside>
		</section>

		<section class="service-section service-pains" aria-labelledby="pains-title">
			<div class="section-header" data-reveal>
				<span class="section-header__eyebrow"><?php esc_html_e( 'Pains we solve', 'compass-digital' ); ?></span>
				<h2 id="pains-title"><?php esc_html_e( 'Sound familiar?', 'compass-digital' ); ?></h2>
			</div>
			<ul class="service-pains__list" role="list" data-reveal>
				<?php foreach ( (array) $service['pains'] as $pain ) : ?>
					<li class="service-pain card">
						<p class="service-pain__quote"><?php echo esc_html( $pain['quote'] ); ?></p>
						<p class="service-pain__fix"><?php echo esc_html( $pain['fix'] ); ?></p>
					</li>
				<?php endforeach; ?>
			</ul>
		</section>

		<section class="service-section service-included" aria-labelledby="included-title">
			<div class="section-header" data-reveal>
				<span class="section-header__eyebrow"><?php esc_html_e( 'What’s included', 'compass-digital' ); ?></span>
				<h2 id="included-title"><?php esc_html_e( 'What you get', 'compass-digital' ); ?></h2>
			</div>
			<ul class="service-included__grid" role="list" data-reveal>
				<?php foreach ( (array) $service['included'] as $item ) : ?>
					<li class="service-feature card">
						<h3 class="service-feature__title"><?php echo esc_html( $item['title'] ); ?></h3>
						<p class="service-feature__text"><?php echo esc_html( $item['text'] ); ?></p>
					</li>
				<?php endforeach; ?>
			</ul>
		</section>

		<section class="service-section service-process" aria-labelledby="process-title">
			<div class="section-header" data-reveal>
				<span class="section-header__eyebrow"><?php esc_html_e( 'How we work', 'compass-digital' ); ?></span>
				<h2 id="process-title"><?php esc_html_e( 'From first chat to launch and beyond', 'compass-digital' ); ?></h2>
			</div>
			<ol class="service-process__steps" data-reveal>
				<?php foreach ( compass_service_process() as $i => $step ) : ?>
					<li class="service-step">
						<span class="service-step__num"><?php echo esc_html( sprintf( '%02d', $i + 1 ) ); ?></span>
						<h3 class="service-step__title"><?php echo esc_html( $step['title'] ); ?></h3>
						<p class="service-step__text"><?php echo esc_html( $step['text'] ); ?></p>
					</li>
				<?php endforeach; ?>
			</ol>
		</section>

		<section class="service-section service-tech" aria-labelledby="tech-title">
			<div class="section-header" data-reveal>
				<span class="section-header__eyebrow"><?php esc_html_e( 'Tech & deliverables', 'compass-digital' ); ?></span>
				<h2 id="tech-title"><?php esc_html_e( 'What we build with, and what you’ll have', 'compass-digital' ); ?></h2>
			</div>
			<div class="service-tech__grid" data-reveal>
				<div class="service-tech__col">
					<h3 class="service-tech__subtitle"><?php esc_html_e( 'Technology', 'compass-digital' ); ?></h3>
					<ul class="service-tags" role="list">
						<?php foreach ( (array) $service['tech'] as $tech ) : ?>
							<li class="tag"><?php echo esc_html( $tech ); ?></li>
						<?php endforeach; ?>
					</ul>
				</div>
				<div class="service-tech__col">
					<h3 class="service-tech__subtitle"><?php esc_html_e( 'Deliverables', 'compass-digital' ); ?></h3>
					<ul class="service-deliverables" role="list">
						<?php foreach ( (array) $service['deliverables'] as $deliverable ) : ?>
							<li><?php echo esc_html( $deliverable ); ?></li>
						<?php endforeach; ?>
					</ul>
				</div>
			</div>
		</section>

		<section class="service-section service-pricing card" aria-labelledby="pricing-title" data-reveal>
			<div class="service-pricing__inner">
				<div>
					<span class="section-header__eyebrow"><?php esc_html_e( 'Pricing', 'compass-digital' ); ?></span>
					<h2 id="pricing-title" class="service-pricing__title"><?php esc_html_e( 'Quote on request — no surprises.', 'compass-digital' ); ?></h2>
					<p class="service-pricing__lede"><?php esc_html_e( 'We don’t list prices because every project is different. You get a fixed quote before any work starts — no hourly surprises. What shapes the price:', 'compass-digital' ); ?></p>
					<ul class="service-pricing__factors" role="list">
						<?php foreach ( (array) $service['price_factors'] as $factor ) : ?>
							<li><?php echo esc_html( $factor ); ?></li>
						<?php endforeach; ?>
					</ul>
				</div>
				<p class="service-pricing__cta">
					<a class="btn btn--primary" href="<?php echo esc_url( $quote_url ); ?>"><?php esc_html_e( 'Get a quote for this', 'compass-digital' ); ?></a>
				</p>
			</div>
		</section>

		<?php
		// Related industries — cross-links that build the industry + service map.
		$related_industries = array_filter(
			(array) $service['industries'],
			static function ( $slug ) use ( $industry_labels ) {
				return isset( $industry_labels[ $slug ] );
			}
		);
		if ( $related_industries ) :
			?>
			<section class="service-section service-industries" aria-labelledby="industries-title">
				<div class="section-header" data-reveal>
					<span class="section-header__eyebrow"><?php esc_html_e( 'Related industries', 'compass-digital' ); ?></span>
					<h2 id="industries-title"><?php esc_html_e( 'Who we build this for', 'compass-digital' ); ?></h2>
				</div>
				<ul class="service-industries__strip" role="list" data-reveal>
					<?php foreach ( $related_industries as $slug ) : ?>
						<li>
							<a class="industry-chip" href="<?php echo esc_url( home_url( '/industries/' . $slug . '/' ) ); ?>">
								<?php echo esc_html( $industry_labels[ $slug ] ); ?>
							</a>
						</li>
					<?php endforeach; ?>
				</ul>
			</section>
		<?php endif; ?>

		<?php if ( $related_work->have_posts() ) : ?>
			<section class="service-section service-work" aria-labelledby="work-title">
				<div class="section-header" data-reveal>
					<span class="section-header__eyebrow"><?php esc_html_e( 'Related work', 'compass-digital' ); ?></span>
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
				<?php foreach ( (array) $service['faqs'] as $i => $faq ) : ?>
					<?php $panel_id = 'svc-faq-panel-' . $i; ?>
					<div class="accordion__item">
						<h3 class="accordion__heading">
							<button
								type="button"
								class="accordion__trigger"
								aria-expanded="false"
								aria-controls="<?php echo esc_attr( $panel_id ); ?>"
								id="svc-faq-trigger-<?php echo esc_attr( $i ); ?>"
							>
								<span><?php echo esc_html( $faq['q'] ); ?></span>
								<span class="accordion__icon" aria-hidden="true"></span>
							</button>
						</h3>
						<div
							class="accordion__panel"
							id="<?php echo esc_attr( $panel_id ); ?>"
							role="region"
							aria-labelledby="svc-faq-trigger-<?php echo esc_attr( $i ); ?>"
						>
							<p><?php echo esc_html( $faq['a'] ); ?></p>
						</div>
					</div>
				<?php endforeach; ?>
			</div>
		</section>

		<section class="service-section service-cta" aria-labelledby="service-cta-title" data-reveal>
			<div class="service-cta__inner">
				<h2 id="service-cta-title"><?php esc_html_e( 'Ready to get started?', 'compass-digital' ); ?></h2>
				<p><?php esc_html_e( 'Tell us what you need and we’ll send a clear, honest quote — no pressure, no jargon.', 'compass-digital' ); ?></p>
				<p>
					<a class="btn btn--primary" href="<?php echo esc_url( $quote_url ); ?>"><?php esc_html_e( 'Get a quote for this', 'compass-digital' ); ?></a>
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
		'name' => __( 'Services', 'compass-digital' ),
		'url'  => home_url( '/services/' ),
	),
	array(
		'name' => wp_strip_all_tags( $service['eyebrow'] ),
		'url'  => $page_url,
	),
);
compass_print_schema( compass_service_schema( $service, $page_url ) );
compass_print_schema( compass_faq_schema( $service['faqs'] ) );
compass_print_schema( compass_breadcrumb_schema( $breadcrumbs ) );

get_footer();
