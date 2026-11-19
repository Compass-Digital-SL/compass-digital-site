<?php
/**
 * Template Name: Get a Quote
 *
 * The quote request: a friendly 3-step form that works as one long form without
 * JavaScript. The form markup + logic live in template-parts/quote-form.php and
 * the compass-core handler; this template is the page shell + reassurance copy.
 * Emits BreadcrumbList JSON-LD.
 *
 * @package CompassDigital
 */

defined( 'ABSPATH' ) || exit;

get_header();

$page_url = get_permalink();
?>
<article class="quote-page" data-quote-page>

	<header class="service-hero">
		<div class="container">
			<div class="service-hero__inner" data-reveal>
				<p class="service-hero__eyebrow"><?php esc_html_e( 'Request a quote', 'compass-digital' ); ?></p>
				<h1 class="service-hero__title"><?php esc_html_e( 'Tell us where you want to go.', 'compass-digital' ); ?></h1>
				<p class="service-hero__lede"><?php esc_html_e( 'Answer a few quick questions and we’ll send a clear, honest quote — no jargon, no pressure, and no obligation.', 'compass-digital' ); ?></p>
			</div>
		</div>
	</header>

	<div class="quote-layout container">
		<div class="quote-main">
			<?php get_template_part( 'template-parts/quote-form', null, array( 'form' => 'quote' ) ); ?>
		</div>

		<aside class="quote-aside" aria-label="<?php esc_attr_e( 'What to expect', 'compass-digital' ); ?>">
			<div class="card quote-aside__card">
				<h2 class="quote-aside__title"><?php esc_html_e( 'What happens next', 'compass-digital' ); ?></h2>
				<ol class="quote-aside__steps">
					<li><?php esc_html_e( 'A real person reads your request — no bots, no call centre.', 'compass-digital' ); ?></li>
					<li><?php esc_html_e( 'We reply within one business day with any quick questions.', 'compass-digital' ); ?></li>
					<li><?php esc_html_e( 'You get a clear scope and a fixed quote, in plain language.', 'compass-digital' ); ?></li>
				</ol>
				<p class="quote-aside__note"><?php esc_html_e( 'Prefer to talk first? Message us on WhatsApp.', 'compass-digital' ); ?></p>
				<p>
					<a class="btn btn--ghost" href="<?php echo esc_url( compass_whatsapp_url() ); ?>" rel="noopener" target="_blank"><?php esc_html_e( 'Chat on WhatsApp', 'compass-digital' ); ?></a>
				</p>
			</div>
		</aside>
	</div>
</article>
<?php
$breadcrumbs = array(
	array(
		'name' => __( 'Home', 'compass-digital' ),
		'url'  => home_url( '/' ),
	),
	array(
		'name' => __( 'Get a quote', 'compass-digital' ),
		'url'  => $page_url,
	),
);
compass_print_schema( compass_breadcrumb_schema( $breadcrumbs ) );

get_footer();
