<?php
/**
 * Template Name: Contact
 *
 * A simple contact page: the same enquiry form (reused), the business details,
 * a pre-filled WhatsApp link, and a clear response-time promise. The form posts
 * through the same compass-core handler as the quote page (marked source
 * "contact"), so every enquiry lands in Quote Requests. Emits ContactPage +
 * BreadcrumbList JSON-LD.
 *
 * @package CompassDigital
 */

defined( 'ABSPATH' ) || exit;

get_header();

$page_url = get_permalink();
$contact  = compass_contact_details();
$hours    = function_exists( 'compass_get_setting' ) ? compass_get_setting( 'hours' ) : '';
?>
<article class="contact-page" data-quote-page>

	<header class="service-hero">
		<div class="container">
			<div class="service-hero__inner" data-reveal>
				<p class="service-hero__eyebrow"><?php esc_html_e( 'Contact', 'compass-digital' ); ?></p>
				<h1 class="service-hero__title"><?php esc_html_e( 'Let’s talk about your project.', 'compass-digital' ); ?></h1>
				<p class="service-hero__lede"><?php esc_html_e( 'Send a message and a real person will reply within one business day. Prefer WhatsApp or a call? Those work too.', 'compass-digital' ); ?></p>
			</div>
		</div>
	</header>

	<div class="quote-layout container">
		<div class="quote-main">
			<?php
			get_template_part(
				'template-parts/quote-form',
				null,
				array(
					'form'   => 'contact',
					'submit' => __( 'Send message', 'compass-digital' ),
				)
			);
			?>
		</div>

		<aside class="quote-aside" aria-label="<?php esc_attr_e( 'Ways to reach us', 'compass-digital' ); ?>">
			<div class="card quote-aside__card">
				<h2 class="quote-aside__title"><?php esc_html_e( 'Other ways to reach us', 'compass-digital' ); ?></h2>

				<p class="quote-aside__promise"><?php esc_html_e( 'We answer every message within one business day — usually much sooner.', 'compass-digital' ); ?></p>

				<ul class="contact-list">
					<li>
						<a class="btn btn--primary contact-list__wa" href="<?php echo esc_url( compass_whatsapp_url() ); ?>" rel="noopener" target="_blank"><?php esc_html_e( 'Message us on WhatsApp', 'compass-digital' ); ?></a>
					</li>
					<?php if ( ! empty( $contact['phone'] ) ) : ?>
						<li>
							<span class="contact-list__label"><?php esc_html_e( 'Phone', 'compass-digital' ); ?></span>
							<a href="<?php echo esc_url( 'tel:' . preg_replace( '/[^\d+]/', '', $contact['phone'] ) ); ?>"><?php echo esc_html( $contact['phone'] ); ?></a>
						</li>
					<?php endif; ?>
					<?php if ( ! empty( $contact['email'] ) ) : ?>
						<li>
							<span class="contact-list__label"><?php esc_html_e( 'Email', 'compass-digital' ); ?></span>
							<a href="<?php echo esc_url( 'mailto:' . $contact['email'] ); ?>"><?php echo esc_html( $contact['email'] ); ?></a>
						</li>
					<?php endif; ?>
					<li>
						<span class="contact-list__label"><?php esc_html_e( 'Where we work', 'compass-digital' ); ?></span>
						<?php esc_html_e( 'Worldwide, remotely. Calls scheduled in your time zone.', 'compass-digital' ); ?>
					</li>
					<?php if ( $hours ) : ?>
						<li>
							<span class="contact-list__label"><?php esc_html_e( 'Hours', 'compass-digital' ); ?></span>
							<?php echo nl2br( esc_html( $hours ) ); ?>
						</li>
					<?php endif; ?>
				</ul>
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
		'name' => __( 'Contact', 'compass-digital' ),
		'url'  => $page_url,
	),
);

$contact_schema = array(
	'@context' => 'https://schema.org',
	'@type'    => 'ContactPage',
	'name'     => wp_get_document_title(),
	'url'      => $page_url,
);
compass_print_schema( $contact_schema );
compass_print_schema( compass_breadcrumb_schema( $breadcrumbs ) );

get_footer();
