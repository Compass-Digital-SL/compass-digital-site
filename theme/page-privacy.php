<?php
/**
 * Template Name: Privacy Policy
 *
 * Applies to the page with slug `privacy` (linked from the footer and the
 * cookie consent bar). Drafted to match what the site actually collects — the
 * quote/contact form, Google Analytics 4 (only after consent), and email
 * delivery — and marked DRAFT for the owner's legal review. Registration
 * details and the firm's trading name are placeholders ([…]).
 *
 * @package CompassDigital
 */

defined( 'ABSPATH' ) || exit;

get_header();

$page_url  = get_permalink();
$quote_url = compass_quote_url();
$contact   = function_exists( 'compass_contact_details' ) ? compass_contact_details() : array();
$email     = ! empty( $contact['email'] ) ? $contact['email'] : 'info@compassdigital.com';
$reg_name  = ! empty( $contact['reg_name'] ) ? $contact['reg_name'] : __( '[Registered company name]', 'compass-digital' );
$address   = ! empty( $contact['address'] ) ? $contact['address'] : __( '[Registered address]', 'compass-digital' );
$updated   = gmdate( 'j F Y' );
?>
<article class="legal-page">

	<header class="service-hero">
		<div class="container">
			<div class="service-hero__inner">
				<p class="service-hero__eyebrow"><?php esc_html_e( 'Legal', 'compass-digital' ); ?></p>
				<h1 class="service-hero__title"><?php esc_html_e( 'Privacy Policy', 'compass-digital' ); ?></h1>
				<p class="service-hero__lede"><?php esc_html_e( 'How Compass Digital collects, uses and protects your information when you use this website.', 'compass-digital' ); ?></p>
			</div>
		</div>
	</header>

	<div class="service-body container">
		<div class="service-prose legal-prose">

			<p class="legal-draft" role="note">
				<strong><?php esc_html_e( 'DRAFT — for legal review.', 'compass-digital' ); ?></strong>
				<?php esc_html_e( 'This policy reflects what the site technically collects. It is a starting draft, not legal advice. Have it reviewed and complete the placeholders in square brackets before launch.', 'compass-digital' ); ?>
			</p>
			<?php /* translators: %s: date the policy was last updated. */ ?>
			<p class="legal-meta"><?php printf( esc_html__( 'Last updated: %s', 'compass-digital' ), esc_html( $updated ) ); ?></p>

			<h2><?php esc_html_e( 'Who we are', 'compass-digital' ); ?></h2>
			<p>
				<?php
				printf(
					/* translators: 1: registered name, 2: address, 3: contact email. */
					esc_html__( 'This site is operated by %1$s (“Compass Digital”, “we”, “us”), a software and digital services firm based in %2$s. For any question about this policy or your data, contact us at %3$s.', 'compass-digital' ),
					esc_html( $reg_name ),
					esc_html( $address ),
					esc_html( $email )
				);
				?>
			</p>

			<h2><?php esc_html_e( 'What we collect and why', 'compass-digital' ); ?></h2>
			<h3><?php esc_html_e( 'Information you give us (quote and contact forms)', 'compass-digital' ); ?></h3>
			<p><?php esc_html_e( 'When you request a quote or contact us, we collect what you enter: your name, email address, phone number (if given), the services and industry you select, your timeline and optional budget range, and your message. We use this only to respond to your enquiry and to prepare a quote. We ask for your preferred contact method so we reach you the way you want.', 'compass-digital' ); ?></p>
			<p><?php esc_html_e( 'We keep these enquiries on our website’s database and, where configured, send a copy by email so we can reply. We do not sell this information or use it for advertising.', 'compass-digital' ); ?></p>

			<h3><?php esc_html_e( 'Analytics (only with your consent)', 'compass-digital' ); ?></h3>
			<p><?php esc_html_e( 'With your consent, we use Google Analytics 4 to understand how the site is used — which pages are visited and how people move through them — so we can improve it. Analytics loads only after you accept cookies in the banner, and you can decline or change your choice at any time through “Cookie preferences” in the footer. If you decline, no analytics cookies are set.', 'compass-digital' ); ?></p>
			<p><?php esc_html_e( 'Analytics data is handled by Google and may be processed outside Sri Lanka. See our Cookie Policy for the specific cookies and durations.', 'compass-digital' ); ?></p>

			<h3><?php esc_html_e( 'Technical information', 'compass-digital' ); ?></h3>
			<p><?php esc_html_e( 'Like most websites, our server and security measures briefly process technical details such as your IP address and browser type to keep the site secure and working (for example, to limit spam and abuse on the forms). This is a legitimate interest in protecting the site and is not used to identify you.', 'compass-digital' ); ?></p>
			<p><?php esc_html_e( 'The forms use a honeypot, a timing check and, where enabled, Cloudflare Turnstile to block spam. Turnstile is a privacy-friendly check that does not track you across sites.', 'compass-digital' ); ?></p>

			<h2><?php esc_html_e( 'How long we keep it', 'compass-digital' ); ?></h2>
			<p><?php esc_html_e( 'We keep quote and contact enquiries only as long as needed to deal with them and for a reasonable period afterwards for our records, then delete them. [Confirm the retention period you want, e.g. 24 months.] You can ask us to delete your enquiry sooner.', 'compass-digital' ); ?></p>

			<h2><?php esc_html_e( 'Who we share it with', 'compass-digital' ); ?></h2>
			<p><?php esc_html_e( 'We share your information only with the services that help us run the site and respond to you: our website hosting provider, our email delivery provider, and — with your consent — Google Analytics. Each handles data on our behalf under their own terms. We do not sell your data or share it for third-party marketing.', 'compass-digital' ); ?></p>

			<h2><?php esc_html_e( 'Your rights', 'compass-digital' ); ?></h2>
			<p><?php esc_html_e( 'You can ask us for a copy of the information we hold about you, ask us to correct or delete it, or withdraw your consent to analytics. To do any of these, email us at the address above and we will respond within a reasonable time. [Confirm the rights and complaint route under the applicable law — e.g. Sri Lanka’s Personal Data Protection Act, No. 9 of 2022, and any other jurisdiction you serve.]', 'compass-digital' ); ?></p>

			<h2><?php esc_html_e( 'Keeping your data secure', 'compass-digital' ); ?></h2>
			<p><?php esc_html_e( 'The site is served over HTTPS and we apply security measures including spam defences, access controls and regular backups. No website can be completely secure, but we take reasonable steps to protect your information.', 'compass-digital' ); ?></p>

			<h2><?php esc_html_e( 'Changes to this policy', 'compass-digital' ); ?></h2>
			<p><?php esc_html_e( 'We may update this policy from time to time. The “last updated” date above shows when it last changed.', 'compass-digital' ); ?></p>

			<h2><?php esc_html_e( 'Contact', 'compass-digital' ); ?></h2>
			<p>
				<?php
				printf(
					/* translators: %s: contact email. */
					esc_html__( 'Questions about your privacy? Email %s.', 'compass-digital' ),
					'<a href="mailto:' . esc_attr( $email ) . '">' . esc_html( $email ) . '</a>'
				);
				?>
			</p>

			<p class="legal-related">
				<a href="<?php echo esc_url( compass_nav_url( 'cookie-policy/' ) ); ?>"><?php esc_html_e( 'Cookie Policy', 'compass-digital' ); ?></a>
				· <a href="<?php echo esc_url( compass_nav_url( 'terms/' ) ); ?>"><?php esc_html_e( 'Terms', 'compass-digital' ); ?></a>
			</p>
		</div>
	</div>
</article>
<?php
$breadcrumbs = array(
	array(
		'name' => __( 'Home', 'compass-digital' ),
		'url'  => home_url( '/' ),
	),
	array(
		'name' => __( 'Privacy Policy', 'compass-digital' ),
		'url'  => $page_url,
	),
);
compass_print_schema( compass_breadcrumb_schema( $breadcrumbs ) );

get_footer();
