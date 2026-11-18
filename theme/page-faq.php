<?php
/**
 * Template Name: FAQ
 *
 * The global FAQ — grouped questions in an accessible accordion. Copy is
 * theme-driven (inc/company.php). Panels are visible without JavaScript (the
 * accordion enhances them with aria-expanded / hidden), so every answer is in
 * the DOM and the FAQPage JSON-LD matches visible content. Emits FAQPage +
 * BreadcrumbList JSON-LD.
 *
 * @package CompassDigital
 */

defined( 'ABSPATH' ) || exit;

get_header();

$page_url  = get_permalink();
$quote_url = compass_quote_url();
$groups    = compass_faq_groups();
$q_index   = 0;
?>
<article class="faq-page">

	<header class="service-hero">
		<div class="container">
			<div class="service-hero__inner" data-reveal>
				<p class="service-hero__eyebrow"><?php esc_html_e( 'FAQ', 'compass-digital' ); ?></p>
				<h1 class="service-hero__title"><?php esc_html_e( 'The questions owners actually ask.', 'compass-digital' ); ?></h1>
				<p class="service-hero__lede"><?php esc_html_e( 'Straight answers on cost, timelines, hosting, SEO, POS and support — in plain language. Can’t find yours? Ask us directly.', 'compass-digital' ); ?></p>
				<p class="service-hero__actions">
					<a class="btn btn--primary" href="<?php echo esc_url( $quote_url ); ?>"><?php esc_html_e( 'Request a quote', 'compass-digital' ); ?></a>
					<a class="btn btn--ghost" href="<?php echo esc_url( home_url( '/contact/' ) ); ?>"><?php esc_html_e( 'Ask a question', 'compass-digital' ); ?></a>
				</p>
			</div>
		</div>
	</header>

	<div class="service-body container">

		<?php foreach ( $groups as $group ) : ?>
			<section class="service-section faq-group" aria-labelledby="faq-group-<?php echo esc_attr( sanitize_title( $group['label'] ) ); ?>">
				<h2 id="faq-group-<?php echo esc_attr( sanitize_title( $group['label'] ) ); ?>" class="faq-group__title" data-reveal><?php echo esc_html( $group['label'] ); ?></h2>
				<div class="accordion faq-accordion" data-reveal>
					<?php foreach ( $group['faqs'] as $faq ) : ?>
						<?php
						++$q_index;
						$panel_id   = 'faq-panel-' . $q_index;
						$trigger_id = 'faq-trigger-' . $q_index;
						?>
						<div class="accordion__item">
							<h3 class="accordion__heading">
								<button
									class="accordion__trigger"
									id="<?php echo esc_attr( $trigger_id ); ?>"
									aria-expanded="false"
									aria-controls="<?php echo esc_attr( $panel_id ); ?>"
								>
									<span><?php echo esc_html( $faq['q'] ); ?></span>
									<span class="accordion__icon" aria-hidden="true"></span>
								</button>
							</h3>
							<div
								class="accordion__panel"
								id="<?php echo esc_attr( $panel_id ); ?>"
								role="region"
								aria-labelledby="<?php echo esc_attr( $trigger_id ); ?>"
							>
								<p><?php echo esc_html( $faq['a'] ); ?></p>
							</div>
						</div>
					<?php endforeach; ?>
				</div>
			</section>
		<?php endforeach; ?>

		<section class="service-section service-cta" aria-labelledby="faq-cta-title" data-reveal>
			<div class="service-cta__inner">
				<h2 id="faq-cta-title"><?php esc_html_e( 'Still have a question?', 'compass-digital' ); ?></h2>
				<p><?php esc_html_e( 'Tell us what you are trying to fix and we will answer plainly — and send a clear, honest quote if you want one.', 'compass-digital' ); ?></p>
				<p><a class="btn btn--primary" href="<?php echo esc_url( $quote_url ); ?>"><?php esc_html_e( 'Request a quote', 'compass-digital' ); ?></a></p>
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
		'name' => __( 'FAQ', 'compass-digital' ),
		'url'  => $page_url,
	),
);
compass_print_schema( compass_breadcrumb_schema( $breadcrumbs ) );
compass_print_schema( compass_faq_schema( compass_faq_all() ) );

get_footer();
