<?php
/**
 * Template Name: Terms
 *
 * Applies to the page with slug `terms` (linked from the footer). Drafted to
 * match how this site actually works — a marketing site where every call to
 * action leads to a quote request, with no online checkout or payment. Marked
 * DRAFT for the owner's legal review; registered details are placeholders.
 *
 * @package CompassDigital
 */

defined( 'ABSPATH' ) || exit;

get_header();

$page_url = get_permalink();
$contact  = function_exists( 'compass_contact_details' ) ? compass_contact_details() : array();
$email    = ! empty( $contact['email'] ) ? $contact['email'] : 'info@compassdigital.com';
$reg_name = ! empty( $contact['reg_name'] ) ? $contact['reg_name'] : __( '[Registered company name]', 'compass-digital' );
$address  = ! empty( $contact['address'] ) ? $contact['address'] : __( '[Registered address]', 'compass-digital' );
$updated  = gmdate( 'j F Y' );
?>
<article class="legal-page">

	<header class="service-hero">
		<div class="container">
			<div class="service-hero__inner">
				<p class="service-hero__eyebrow"><?php esc_html_e( 'Legal', 'compass-digital' ); ?></p>
				<h1 class="service-hero__title"><?php esc_html_e( 'Terms of Use', 'compass-digital' ); ?></h1>
				<p class="service-hero__lede"><?php esc_html_e( 'The terms on which you may use the Compass Digital website.', 'compass-digital' ); ?></p>
			</div>
		</div>
	</header>

	<div class="service-body container">
		<div class="service-prose legal-prose">

			<p class="legal-draft" role="note">
				<strong><?php esc_html_e( 'DRAFT — for legal review.', 'compass-digital' ); ?></strong>
				<?php esc_html_e( 'These terms describe how this website works today. They are a starting draft, not legal advice. Have them reviewed and complete the placeholders in square brackets before launch.', 'compass-digital' ); ?>
			</p>
			<?php /* translators: %s: date the terms were last updated. */ ?>
			<p class="legal-meta"><?php printf( esc_html__( 'Last updated: %s', 'compass-digital' ), esc_html( $updated ) ); ?></p>

			<h2><?php esc_html_e( 'About these terms', 'compass-digital' ); ?></h2>
			<p>
				<?php
				printf(
					/* translators: 1: registered name, 2: address. */
					esc_html__( 'This website is operated by %1$s (“Compass Digital”, “we”, “us”), based in %2$s. By using this site you agree to these terms. If you do not agree, please do not use the site.', 'compass-digital' ),
					esc_html( $reg_name ),
					esc_html( $address )
				);
				?>
			</p>

			<h2><?php esc_html_e( 'What this site is', 'compass-digital' ); ?></h2>
			<p><?php esc_html_e( 'This is a marketing and information website. It describes our services and lets you request a quote or contact us. It does not sell anything online: there is no checkout and no payment is taken on this site. Any work we do for you is governed by a separate written agreement, not by these terms.', 'compass-digital' ); ?></p>

			<h2><?php esc_html_e( 'Quotes and enquiries', 'compass-digital' ); ?></h2>
			<p><?php esc_html_e( 'When you request a quote or contact us, you agree to provide accurate information. A quote we send is an estimate for discussion, not a binding offer, and does not create a contract until both sides agree terms in writing. We may decline or withdraw a quote.', 'compass-digital' ); ?></p>

			<h2><?php esc_html_e( 'Acceptable use', 'compass-digital' ); ?></h2>
			<p><?php esc_html_e( 'You agree not to misuse the site: no attempts to break, overload, probe or gain unauthorised access to it; no automated scraping or spam through the forms; and nothing unlawful. We use spam and security measures and may block access that breaches these terms.', 'compass-digital' ); ?></p>

			<h2><?php esc_html_e( 'Our content', 'compass-digital' ); ?></h2>
			<p><?php esc_html_e( 'The text, design, logos and images on this site are owned by Compass Digital or used with permission, and are protected by copyright and trademark law. You may view and share pages for your own reference, but you may not copy, republish or reuse our content commercially without our written permission.', 'compass-digital' ); ?></p>

			<h2><?php esc_html_e( 'Links to other sites', 'compass-digital' ); ?></h2>
			<p><?php esc_html_e( 'The site may link to third-party websites (for example WhatsApp or social profiles). We are not responsible for the content or practices of those sites; their own terms and privacy policies apply.', 'compass-digital' ); ?></p>

			<h2><?php esc_html_e( 'No warranty and liability', 'compass-digital' ); ?></h2>
			<p><?php esc_html_e( 'We work to keep the site accurate and available, but we provide it “as is” without warranties, and we do not guarantee it will always be available or error-free. To the extent the law allows, we are not liable for loss arising from your use of the site. [Confirm the limitation of liability and governing-law wording with your lawyer.]', 'compass-digital' ); ?></p>

			<h2><?php esc_html_e( 'Governing law', 'compass-digital' ); ?></h2>
			<p><?php esc_html_e( 'These terms are governed by the laws of Sri Lanka, and the courts of Sri Lanka have jurisdiction. [Confirm or change this if you serve or contract in other jurisdictions.]', 'compass-digital' ); ?></p>

			<h2><?php esc_html_e( 'Changes', 'compass-digital' ); ?></h2>
			<p><?php esc_html_e( 'We may update these terms from time to time. The “last updated” date above shows when they last changed.', 'compass-digital' ); ?></p>

			<h2><?php esc_html_e( 'Contact', 'compass-digital' ); ?></h2>
			<p>
				<?php
				printf(
					/* translators: %s: contact email. */
					esc_html__( 'Questions about these terms? Email %s.', 'compass-digital' ),
					'<a href="mailto:' . esc_attr( $email ) . '">' . esc_html( $email ) . '</a>'
				);
				?>
			</p>

			<p class="legal-related">
				<a href="<?php echo esc_url( compass_nav_url( 'privacy/' ) ); ?>"><?php esc_html_e( 'Privacy Policy', 'compass-digital' ); ?></a>
				· <a href="<?php echo esc_url( compass_nav_url( 'cookie-policy/' ) ); ?>"><?php esc_html_e( 'Cookie Policy', 'compass-digital' ); ?></a>
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
		'name' => __( 'Terms of Use', 'compass-digital' ),
		'url'  => $page_url,
	),
);
compass_print_schema( compass_breadcrumb_schema( $breadcrumbs ) );

get_footer();
