<?php
/**
 * Template Name: Cookie Policy
 *
 * Applies to the page with slug `cookie-policy` (linked from the footer and the
 * Privacy Policy). Drafted to match the cookies the site actually sets — the
 * consent choice itself and, only after consent, Google Analytics 4. Marked
 * DRAFT for the owner's legal review.
 *
 * @package CompassDigital
 */

defined( 'ABSPATH' ) || exit;

get_header();

$page_url = get_permalink();
$contact  = function_exists( 'compass_contact_details' ) ? compass_contact_details() : array();
$email    = ! empty( $contact['email'] ) ? $contact['email'] : 'info@compassdigital.com';
$updated  = gmdate( 'j F Y' );
?>
<article class="legal-page">

	<header class="service-hero">
		<div class="container">
			<div class="service-hero__inner">
				<p class="service-hero__eyebrow"><?php esc_html_e( 'Legal', 'compass-digital' ); ?></p>
				<h1 class="service-hero__title"><?php esc_html_e( 'Cookie Policy', 'compass-digital' ); ?></h1>
				<p class="service-hero__lede"><?php esc_html_e( 'What cookies this site uses, why, and how to control them.', 'compass-digital' ); ?></p>
			</div>
		</div>
	</header>

	<div class="service-body container">
		<div class="service-prose legal-prose">

			<p class="legal-draft" role="note">
				<strong><?php esc_html_e( 'DRAFT — for legal review.', 'compass-digital' ); ?></strong>
				<?php esc_html_e( 'This policy lists the cookies the site technically sets. It is a starting draft, not legal advice. Have it reviewed and confirm the cookie durations before launch.', 'compass-digital' ); ?>
			</p>
			<?php /* translators: %s: date the policy was last updated. */ ?>
			<p class="legal-meta"><?php printf( esc_html__( 'Last updated: %s', 'compass-digital' ), esc_html( $updated ) ); ?></p>

			<h2><?php esc_html_e( 'What cookies are', 'compass-digital' ); ?></h2>
			<p><?php esc_html_e( 'Cookies are small files a website stores in your browser. Some are needed for the site to remember your choices; others, which are optional, help us understand how the site is used. We only set optional cookies after you accept them.', 'compass-digital' ); ?></p>

			<h2><?php esc_html_e( 'Your choice', 'compass-digital' ); ?></h2>
			<p><?php esc_html_e( 'When you first visit, a banner asks whether you accept optional analytics cookies. Nothing optional is set until you choose “Accept”. You can change your mind at any time using “Cookie preferences” in the footer, or by clearing cookies in your browser. If you decline, only the essential cookie that remembers your choice is used.', 'compass-digital' ); ?></p>

			<h2><?php esc_html_e( 'Cookies we use', 'compass-digital' ); ?></h2>

			<h3><?php esc_html_e( 'Essential', 'compass-digital' ); ?></h3>
			<table class="legal-table">
				<thead>
					<tr>
						<th scope="col"><?php esc_html_e( 'Cookie', 'compass-digital' ); ?></th>
						<th scope="col"><?php esc_html_e( 'Purpose', 'compass-digital' ); ?></th>
						<th scope="col"><?php esc_html_e( 'Duration', 'compass-digital' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td><code>compass_consent</code></td>
						<td><?php esc_html_e( 'Remembers whether you accepted or declined analytics cookies, so we do not ask every visit.', 'compass-digital' ); ?></td>
						<td><?php esc_html_e( '6 months', 'compass-digital' ); ?></td>
					</tr>
				</tbody>
			</table>

			<h3><?php esc_html_e( 'Analytics (only after you accept)', 'compass-digital' ); ?></h3>
			<table class="legal-table">
				<thead>
					<tr>
						<th scope="col"><?php esc_html_e( 'Cookie', 'compass-digital' ); ?></th>
						<th scope="col"><?php esc_html_e( 'Purpose', 'compass-digital' ); ?></th>
						<th scope="col"><?php esc_html_e( 'Duration', 'compass-digital' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td><code>_ga</code></td>
						<td><?php esc_html_e( 'Google Analytics — distinguishes visitors to measure site usage.', 'compass-digital' ); ?></td>
						<td><?php esc_html_e( '2 years', 'compass-digital' ); ?></td>
					</tr>
					<tr>
						<td><code>_ga_&lt;id&gt;</code></td>
						<td><?php esc_html_e( 'Google Analytics 4 — keeps session state for the measurement property.', 'compass-digital' ); ?></td>
						<td><?php esc_html_e( '2 years', 'compass-digital' ); ?></td>
					</tr>
				</tbody>
			</table>
			<p><?php esc_html_e( 'These analytics cookies are set by Google only if you accept. See our Privacy Policy for how analytics data is handled, and Google’s own policies for more detail.', 'compass-digital' ); ?></p>

			<h2><?php esc_html_e( 'Spam protection', 'compass-digital' ); ?></h2>
			<p><?php esc_html_e( 'Our forms block spam with a hidden honeypot field, a timing check and a rate limit. None of these set cookies.', 'compass-digital' ); ?></p>

			<h2><?php esc_html_e( 'Contact', 'compass-digital' ); ?></h2>
			<p>
				<?php
				printf(
					/* translators: %s: contact email. */
					esc_html__( 'Questions about cookies? Email %s.', 'compass-digital' ),
					'<a href="mailto:' . esc_attr( $email ) . '">' . esc_html( $email ) . '</a>'
				);
				?>
			</p>

			<p class="legal-related">
				<a href="<?php echo esc_url( compass_nav_url( 'privacy/' ) ); ?>"><?php esc_html_e( 'Privacy Policy', 'compass-digital' ); ?></a>
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
		'name' => __( 'Cookie Policy', 'compass-digital' ),
		'url'  => $page_url,
	),
);
compass_print_schema( compass_breadcrumb_schema( $breadcrumbs ) );

get_footer();
