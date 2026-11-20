<?php
/**
 * Insights index — the static Posts page at /insights/ (is_home).
 *
 * A filterable grid of articles. Category chips are real links to the category
 * archives, so filtering works with JavaScript off and every filtered URL is
 * shareable; insights-filter.js enhances them to filter the cards in place and
 * keep the URL in sync via the History API. Cards come from the shared
 * template-parts/post-card.php. Emits Blog + BreadcrumbList JSON-LD.
 *
 * @package CompassDigital
 */

defined( 'ABSPATH' ) || exit;

get_header();

$insights_url = home_url( '/insights/' );
$categories   = compass_insights_categories();
?>
<article class="insights-archive">

	<header class="service-hero">
		<div class="container">
			<div class="service-hero__inner" data-reveal>
				<p class="service-hero__eyebrow"><?php esc_html_e( 'Insights', 'compass-digital' ); ?></p>
				<h1 class="service-hero__title"><?php esc_html_e( 'Plain advice on getting your business online.', 'compass-digital' ); ?></h1>
				<p class="service-hero__lede"><?php esc_html_e( 'Practical, jargon-free guides on websites, SEO, POS and selling online — written for business owners, not developers.', 'compass-digital' ); ?></p>
			</div>
		</div>
	</header>

	<div class="service-body container">

		<?php if ( $categories ) : ?>
			<nav class="insights-filter" aria-label="<?php esc_attr_e( 'Filter articles by topic', 'compass-digital' ); ?>" data-insights-filter>
				<span class="insights-filter__label"><?php esc_html_e( 'Topic', 'compass-digital' ); ?></span>
				<a class="post-chip is-active" href="<?php echo esc_url( $insights_url ); ?>" data-filter-value="" aria-current="true"><?php esc_html_e( 'All', 'compass-digital' ); ?></a>
				<?php foreach ( $categories as $topic ) : ?>
					<a
						class="post-chip"
						href="<?php echo esc_url( get_category_link( $topic ) ); ?>"
						data-filter-value="<?php echo esc_attr( $topic->slug ); ?>"
					><?php echo esc_html( $topic->name ); ?></a>
				<?php endforeach; ?>
			</nav>
		<?php endif; ?>

		<?php if ( have_posts() ) : ?>
			<div class="post-grid" data-post-grid data-reveal>
				<?php
				while ( have_posts() ) :
					the_post();
					get_template_part( 'template-parts/post-card' );
				endwhile;
				?>
			</div>

			<p class="post-grid__empty" data-post-empty hidden>
				<?php esc_html_e( 'No articles match that topic yet.', 'compass-digital' ); ?>
				<a class="link" href="<?php echo esc_url( $insights_url ); ?>"><?php esc_html_e( 'Clear filter', 'compass-digital' ); ?></a>
			</p>

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
			<p class="post-grid__empty"><?php esc_html_e( 'Articles are on the way. Check back soon.', 'compass-digital' ); ?></p>
		<?php endif; ?>

		<section class="service-section service-cta" aria-labelledby="insights-cta-title" data-reveal>
			<div class="service-cta__inner">
				<h2 id="insights-cta-title"><?php esc_html_e( 'Rather just talk it through?', 'compass-digital' ); ?></h2>
				<p><?php esc_html_e( 'Reading up is a good start. When you are ready, tell us what you are trying to fix and we will send a clear, honest quote.', 'compass-digital' ); ?></p>
				<p><a class="btn btn--primary" href="<?php echo esc_url( compass_quote_url() ); ?>"><?php esc_html_e( 'Request a quote', 'compass-digital' ); ?></a></p>
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
		'name' => __( 'Insights', 'compass-digital' ),
		'url'  => $insights_url,
	),
);
compass_print_schema( compass_breadcrumb_schema( $breadcrumbs ) );
compass_print_schema(
	array(
		'@context'    => 'https://schema.org',
		'@type'       => 'Blog',
		'name'        => __( 'Compass Digital Insights', 'compass-digital' ),
		'url'         => $insights_url,
		'description' => __( 'Practical, jargon-free advice on websites, SEO, POS and selling online for growing businesses.', 'compass-digital' ),
		'publisher'   => array(
			'@type' => 'Organization',
			'name'  => get_bloginfo( 'name' ),
			'url'   => home_url( '/' ),
		),
	)
);

get_footer();
