<?php
/**
 * Work archive — /work/ (post-type archive for `work`).
 *
 * A filterable grid of case studies. Filter chips (by service and by industry)
 * are real links that reload with a `?service=` / `?industry=` query arg, so the
 * list is filtered server-side and every filtered URL is shareable and works
 * with JavaScript off. work-filter.js enhances the chips to filter the cards in
 * place and keep the URL in sync via the History API.
 *
 * Cards come from the shared template-parts/work-card.php, the same component
 * the home "Recent work" preview uses. CollectionPage + BreadcrumbList JSON-LD
 * is emitted at the end.
 *
 * @package CompassDigital
 */

defined( 'ABSPATH' ) || exit;

get_header();

$facets          = compass_work_filter_facets();
$active_service  = compass_work_active_filter( 'service' );
$active_industry = compass_work_active_filter( 'industry' );
$active_filters  = array_filter(
	array(
		'service'  => $active_service,
		'industry' => $active_industry,
	)
);
$archive_url     = get_post_type_archive_link( 'work' );
?>
<article class="work-archive">

	<header class="service-hero">
		<div class="container">
			<?php get_template_part( 'template-parts/breadcrumbs' ); ?>
			<div class="service-hero__inner" data-reveal>
				<p class="service-hero__eyebrow"><?php esc_html_e( 'Our work', 'compass-digital' ); ?></p>
				<h1 class="service-hero__title"><?php esc_html_e( 'Real businesses, real results.', 'compass-digital' ); ?></h1>
				<p class="service-hero__lede"><?php esc_html_e( 'A look at what we have built for Sri Lankan businesses — the problem, the work, and the numbers that followed. Filter by what you need.', 'compass-digital' ); ?></p>
			</div>
		</div>
	</header>

	<div class="service-body container">

		<?php if ( $facets ) : ?>
			<form
				class="work-filter"
				method="get"
				action="<?php echo esc_url( $archive_url ); ?>"
				data-work-filter
				aria-label="<?php esc_attr_e( 'Filter case studies', 'compass-digital' ); ?>"
			>
				<?php foreach ( $facets as $facet_tax => $facet ) : ?>
					<?php
					$group_active = 'service' === $facet_tax ? $active_service : $active_industry;
					// The other facet's arg is preserved on every chip in this group.
					$other   = 'service' === $facet_tax ? array( 'industry' => $active_industry ) : array( 'service' => $active_service );
					$other   = array_filter( $other );
					$all_url = $other ? add_query_arg( $other, $archive_url ) : $archive_url;
					?>
					<div class="work-filter__group" role="group" aria-label="<?php echo esc_attr( $facet['label'] ); ?>" data-filter-group="<?php echo esc_attr( $facet_tax ); ?>">
						<span class="work-filter__label"><?php echo esc_html( $facet['label'] ); ?></span>
						<a
							class="work-chip<?php echo '' === $group_active ? ' is-active' : ''; ?>"
							href="<?php echo esc_url( $all_url ); ?>"
							data-filter-value=""
							<?php echo '' === $group_active ? 'aria-current="true"' : ''; ?>
						><?php esc_html_e( 'All', 'compass-digital' ); ?></a>
						<?php foreach ( $facet['terms'] as $facet_term ) : ?>
							<?php
							$on       = $group_active === $facet_term->slug;
							$chip_url = add_query_arg(
								array_filter( array_merge( $other, array( $facet_tax => $on ? '' : $facet_term->slug ) ) ),
								$archive_url
							);
							?>
							<a
								class="work-chip<?php echo $on ? ' is-active' : ''; ?>"
								href="<?php echo esc_url( $chip_url ); ?>"
								data-filter-value="<?php echo esc_attr( $facet_term->slug ); ?>"
								<?php echo $on ? 'aria-current="true"' : ''; ?>
							><?php echo esc_html( $facet_term->name ); ?></a>
						<?php endforeach; ?>
					</div>
				<?php endforeach; ?>
				<noscript><button type="submit" class="btn btn--ghost work-filter__submit"><?php esc_html_e( 'Apply', 'compass-digital' ); ?></button></noscript>
			</form>
		<?php endif; ?>

		<?php if ( have_posts() ) : ?>
			<div class="work-grid" data-work-grid data-reveal>
				<?php
				while ( have_posts() ) :
					the_post();
					get_template_part( 'template-parts/work-card' );
				endwhile;
				?>
			</div>

			<p class="work-grid__empty" data-work-empty hidden>
				<?php esc_html_e( 'No case studies match those filters yet.', 'compass-digital' ); ?>
				<a class="link" href="<?php echo esc_url( $archive_url ); ?>"><?php esc_html_e( 'Clear filters', 'compass-digital' ); ?></a>
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
			<p class="work-grid__empty">
				<?php
				if ( $active_filters ) {
					esc_html_e( 'No case studies match those filters yet.', 'compass-digital' );
					echo ' ';
					printf(
						'<a class="link" href="%s">%s</a>',
						esc_url( $archive_url ),
						esc_html__( 'Clear filters', 'compass-digital' )
					);
				} else {
					esc_html_e( 'Case studies are on the way. Check back soon.', 'compass-digital' );
				}
				?>
			</p>
		<?php endif; ?>

		<section class="service-section service-cta" aria-labelledby="work-cta-title" data-reveal>
			<div class="service-cta__inner">
				<h2 id="work-cta-title"><?php esc_html_e( 'Want results like these?', 'compass-digital' ); ?></h2>
				<p><?php esc_html_e( 'Tell us what you are trying to fix. We will send a clear, honest quote and a plan to get there.', 'compass-digital' ); ?></p>
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
		'name' => __( 'Work', 'compass-digital' ),
		'url'  => $archive_url,
	),
);
compass_print_schema( compass_breadcrumb_schema( $breadcrumbs ) );

get_footer();
