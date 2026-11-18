<?php
/**
 * Template Name: Thank You
 *
 * The post-submit page the quote / contact handler redirects to. Confirms the
 * request, sets expectations (reply within one business day) and offers next
 * steps. Fires a `generate_lead` analytics event (dataLayer + gtag-safe) that
 * Task 17 consumes. Kept out of search (noindex via inc/quote.php).
 *
 * @package CompassDigital
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>
<article class="thankyou-page">
	<div class="container container--narrow">
		<div class="thankyou-inner" data-reveal>
			<p class="service-hero__eyebrow"><?php esc_html_e( 'Request received', 'compass-digital' ); ?></p>
			<h1 class="thankyou-title"><?php esc_html_e( 'Thank you — we’ve got your request.', 'compass-digital' ); ?></h1>
			<p class="thankyou-lede"><?php esc_html_e( 'A real person will read it and reply within one business day, usually sooner. We’ve also sent a confirmation to your email — if it’s not there in a few minutes, check your spam folder.', 'compass-digital' ); ?></p>

			<div class="thankyou-next">
				<h2><?php esc_html_e( 'While you wait', 'compass-digital' ); ?></h2>
				<ul>
					<li><a href="<?php echo esc_url( home_url( '/work/' ) ); ?>"><?php esc_html_e( 'See recent work we’ve delivered', 'compass-digital' ); ?></a></li>
					<li><a href="<?php echo esc_url( home_url( '/insights/' ) ); ?>"><?php esc_html_e( 'Read our guides on cost, SEO and POS', 'compass-digital' ); ?></a></li>
					<li><a href="<?php echo esc_url( home_url( '/process/' ) ); ?>"><?php esc_html_e( 'Learn how we work, step by step', 'compass-digital' ); ?></a></li>
				</ul>
			</div>

			<p class="thankyou-actions">
				<a class="btn btn--primary" href="<?php echo esc_url( compass_whatsapp_url() ); ?>" rel="noopener" target="_blank"><?php esc_html_e( 'Message us on WhatsApp', 'compass-digital' ); ?></a>
				<a class="btn btn--ghost" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Back to home', 'compass-digital' ); ?></a>
			</p>
		</div>
	</div>
</article>

<?php // Analytics: lead conversion event (consumed by Task 17). ?>
<script data-analytics-event="generate_lead">
	window.dataLayer = window.dataLayer || [];
	window.dataLayer.push({ event: "generate_lead", form: "quote" });
	if (typeof window.gtag === "function") {
		window.gtag("event", "generate_lead");
	}
</script>
<?php
get_footer();
