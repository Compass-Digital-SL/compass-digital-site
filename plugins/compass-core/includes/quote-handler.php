<?php
/**
 * Quote + Contact form handler.
 *
 * Receives the public form (both /get-a-quote/ and /contact/ post here),
 * validates on the server, defends against spam (honeypot, time trap, per-IP
 * rate limit, optional Cloudflare Turnstile), saves a private quote_request,
 * emails the owner a clean summary and the visitor an auto-reply, then redirects
 * to the thank-you page. On a validation error it flashes the messages + the
 * visitor's own input back to the form so nothing typed is lost.
 *
 * Lives in the plugin (not the theme) so enquiries survive a theme change.
 *
 * @package CompassCore
 */

defined( 'ABSPATH' ) || exit;

if ( ! defined( 'COMPASS_QUOTE_FLASH_PREFIX' ) ) {
	define( 'COMPASS_QUOTE_FLASH_PREFIX', 'compass_quote_flash_' );
}
const COMPASS_QUOTE_RL_PREFIX = 'compass_quote_rl_';

add_action( 'admin_post_nopriv_compass_quote', 'compass_core_handle_quote' );
add_action( 'admin_post_compass_quote', 'compass_core_handle_quote' );

/**
 * Handle a quote / contact submission.
 */
function compass_core_handle_quote() {
	$is_contact = isset( $_POST['compass_form'] ) && 'contact' === sanitize_key( wp_unslash( $_POST['compass_form'] ) );

	// Where to send the visitor back on error (same-site only).
	$source = isset( $_POST['source'] ) ? esc_url_raw( wp_unslash( $_POST['source'] ) ) : '';
	if ( ! $source || ! compass_core_is_local_url( $source ) ) {
		$source = home_url( $is_contact ? '/contact/' : '/get-a-quote/' );
	}

	// Nonce.
	$nonce = isset( $_POST['compass_nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['compass_nonce'] ) ) : '';
	if ( ! wp_verify_nonce( $nonce, 'compass_quote' ) ) {
		compass_core_quote_fail( $source, array( '_form' => __( 'Your session expired. Please check the form and send again.', 'compass-core' ) ), array() );
	}

	// --- Silent spam traps: a hit looks like success to the bot, saves nothing.
	$honeypot = isset( $_POST['compass_hp_url'] ) ? trim( sanitize_text_field( wp_unslash( $_POST['compass_hp_url'] ) ) ) : '';
	$started  = isset( $_POST['compass_ts'] ) ? absint( $_POST['compass_ts'] ) : 0;
	$elapsed  = $started ? ( time() - $started ) : 0;
	if ( '' !== $honeypot || ( $started && $elapsed < 2 ) ) {
		wp_safe_redirect( home_url( '/thank-you/' ) );
		exit;
	}

	// --- Per-IP rate limit: max 5 submissions / 15 min.
	$ip_key = COMPASS_QUOTE_RL_PREFIX . md5( compass_core_client_ip() );
	$hits   = (int) get_transient( $ip_key );
	if ( $hits >= 5 ) {
		compass_core_quote_fail( $source, array( '_form' => __( 'Too many requests from your connection. Please try again in a few minutes.', 'compass-core' ) ), compass_core_quote_raw_input() );
	}

	// --- Turnstile (only if a secret is configured).
	$secret = function_exists( 'compass_core_secret' ) ? compass_core_secret( 'turnstile_secret' ) : '';
	if ( $secret ) {
		$token = isset( $_POST['cf-turnstile-response'] ) ? sanitize_text_field( wp_unslash( $_POST['cf-turnstile-response'] ) ) : '';
		if ( ! compass_core_verify_turnstile( $secret, $token ) ) {
			compass_core_quote_fail( $source, array( '_form' => __( 'The spam check did not pass. Please tick the box and try again.', 'compass-core' ) ), compass_core_quote_raw_input() );
		}
	}

	// --- Collect + validate.
	$input  = compass_core_quote_raw_input();
	$errors = array();

	$services = isset( $_POST['services'] ) ? array_map( 'sanitize_text_field', (array) wp_unslash( $_POST['services'] ) ) : array();
	$services = array_values( array_filter( $services ) );
	if ( ! $services ) {
		$errors['services'] = __( 'Please pick at least one thing you need.', 'compass-core' );
	}

	$name = isset( $_POST['name'] ) ? sanitize_text_field( wp_unslash( $_POST['name'] ) ) : '';
	if ( '' === $name ) {
		$errors['name'] = __( 'Please tell us your name.', 'compass-core' );
	}

	$email = isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '';
	if ( '' === $email || ! is_email( $email ) ) {
		$errors['email'] = __( 'Please enter a valid email address.', 'compass-core' );
	}

	$phone = isset( $_POST['phone'] ) ? sanitize_text_field( wp_unslash( $_POST['phone'] ) ) : '';
	if ( '' === $phone ) {
		$errors['phone'] = __( 'Please add a phone or WhatsApp number.', 'compass-core' );
	}

	$message = isset( $_POST['message'] ) ? sanitize_textarea_field( wp_unslash( $_POST['message'] ) ) : '';
	if ( strlen( trim( $message ) ) < 10 ) {
		$errors['message'] = __( 'Please tell us a little about your project (at least a sentence).', 'compass-core' );
	}

	$methods = function_exists( 'compass_quote_contact_methods' ) ? array_keys( compass_quote_contact_methods() ) : array( 'email', 'phone', 'whatsapp' );
	$method  = isset( $_POST['contact_method'] ) ? sanitize_key( wp_unslash( $_POST['contact_method'] ) ) : 'email';
	if ( ! in_array( $method, $methods, true ) ) {
		$method = 'email';
	}

	$consent = ! empty( $_POST['consent'] );
	if ( ! $consent ) {
		$errors['consent'] = __( 'Please agree to us contacting you about your request.', 'compass-core' );
	}

	$industry = isset( $_POST['industry'] ) ? sanitize_text_field( wp_unslash( $_POST['industry'] ) ) : '';
	$timeline = isset( $_POST['timeline'] ) ? sanitize_text_field( wp_unslash( $_POST['timeline'] ) ) : '';
	$budget   = isset( $_POST['budget'] ) ? sanitize_text_field( wp_unslash( $_POST['budget'] ) ) : '';
	$company  = isset( $_POST['company'] ) ? sanitize_text_field( wp_unslash( $_POST['company'] ) ) : '';

	if ( $errors ) {
		compass_core_quote_fail( $source, $errors, $input );
	}

	// --- Passed: count this submission toward the rate limit.
	set_transient( $ip_key, $hits + 1, 15 * MINUTE_IN_SECONDS );

	// Map service slugs to readable labels for the saved record + emails.
	$service_labels = compass_core_quote_labels( $services, 'compass_quote_services' );
	$service_text   = implode( ', ', $service_labels );
	$industry_label = $industry ? compass_core_quote_label( $industry, 'compass_quote_industries' ) : '';
	$timeline_label = $timeline ? compass_core_quote_label( $timeline, 'compass_quote_timelines' ) : '';
	$budget_label   = $budget ? compass_core_quote_label( $budget, 'compass_quote_budgets' ) : '';
	$method_label   = compass_core_quote_label( $method, 'compass_quote_contact_methods' );

	// --- Save the private enquiry.
	$post_id = wp_insert_post(
		array(
			'post_type'   => 'quote_request',
			'post_status' => 'publish',
			'post_title'  => $name . ' — ' . ( $service_text ? $service_text : __( 'Enquiry', 'compass-core' ) ),
		),
		true
	);

	if ( is_wp_error( $post_id ) || ! $post_id ) {
		compass_core_quote_fail( $source, array( '_form' => __( 'Something went wrong saving your request. Please try again or WhatsApp us.', 'compass-core' ) ), $input );
	}

	$meta = array(
		'name'    => $name,
		'email'   => $email,
		'phone'   => $phone,
		'company' => $company,
		'service' => $service_text,
		'budget'  => $budget_label,
		'message' => $message,
		'source'  => $source,
		'status'  => 'new',
	);
	foreach ( $meta as $key => $value ) {
		update_post_meta( $post_id, '_compass_' . $key, $value );
	}
	// Extra context not in the shared field set.
	update_post_meta( $post_id, '_compass_industry', $industry_label );
	update_post_meta( $post_id, '_compass_timeline', $timeline_label );
	update_post_meta( $post_id, '_compass_contact_method', $method_label );
	update_post_meta( $post_id, '_compass_services_raw', implode( ',', $services ) );
	update_post_meta( $post_id, '_compass_form', $is_contact ? 'contact' : 'quote' );

	// --- Emails (failure to send never blocks the visitor).
	compass_core_quote_notify(
		array(
			'name'           => $name,
			'email'          => $email,
			'phone'          => $phone,
			'company'        => $company,
			'service_text'   => $service_text,
			'industry_label' => $industry_label,
			'timeline_label' => $timeline_label,
			'budget_label'   => $budget_label,
			'method_label'   => $method_label,
			'message'        => $message,
			'source'         => $source,
			'is_contact'     => $is_contact,
			'post_id'        => $post_id,
		)
	);

	wp_safe_redirect( add_query_arg( 'sent', '1', home_url( '/thank-you/' ) ) );
	exit;
}

/**
 * Flash errors + the visitor's input and redirect back to the form.
 *
 * @param string $source Form page URL.
 * @param array  $errors field => message.
 * @param array  $input  Raw submitted values to replay.
 */
function compass_core_quote_fail( $source, $errors, $input ) {
	$token = wp_generate_password( 20, false );
	set_transient(
		COMPASS_QUOTE_FLASH_PREFIX . $token,
		array(
			'errors' => $errors,
			'input'  => $input,
		),
		10 * MINUTE_IN_SECONDS
	);
	$url = add_query_arg( 'quote_error', $token, $source ) . '#quote-form';
	wp_safe_redirect( $url );
	exit;
}

/**
 * Gather the submitted values to replay on error (sanitised, no secrets).
 *
 * @return array
 */
function compass_core_quote_raw_input() {
	$services = isset( $_POST['services'] ) ? array_map( 'sanitize_text_field', (array) wp_unslash( $_POST['services'] ) ) : array(); // phpcs:ignore WordPress.Security.NonceVerification.Missing -- nonce checked by caller.
	return array(
		'services'       => $services,
		'industry'       => isset( $_POST['industry'] ) ? sanitize_text_field( wp_unslash( $_POST['industry'] ) ) : '', // phpcs:ignore WordPress.Security.NonceVerification.Missing
		'timeline'       => isset( $_POST['timeline'] ) ? sanitize_text_field( wp_unslash( $_POST['timeline'] ) ) : '', // phpcs:ignore WordPress.Security.NonceVerification.Missing
		'budget'         => isset( $_POST['budget'] ) ? sanitize_text_field( wp_unslash( $_POST['budget'] ) ) : '', // phpcs:ignore WordPress.Security.NonceVerification.Missing
		'message'        => isset( $_POST['message'] ) ? sanitize_textarea_field( wp_unslash( $_POST['message'] ) ) : '', // phpcs:ignore WordPress.Security.NonceVerification.Missing
		'name'           => isset( $_POST['name'] ) ? sanitize_text_field( wp_unslash( $_POST['name'] ) ) : '', // phpcs:ignore WordPress.Security.NonceVerification.Missing
		'email'          => isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '', // phpcs:ignore WordPress.Security.NonceVerification.Missing
		'phone'          => isset( $_POST['phone'] ) ? sanitize_text_field( wp_unslash( $_POST['phone'] ) ) : '', // phpcs:ignore WordPress.Security.NonceVerification.Missing
		'company'        => isset( $_POST['company'] ) ? sanitize_text_field( wp_unslash( $_POST['company'] ) ) : '', // phpcs:ignore WordPress.Security.NonceVerification.Missing
		'contact_method' => isset( $_POST['contact_method'] ) ? sanitize_key( wp_unslash( $_POST['contact_method'] ) ) : '', // phpcs:ignore WordPress.Security.NonceVerification.Missing
		'consent'        => ! empty( $_POST['consent'] ) ? '1' : '', // phpcs:ignore WordPress.Security.NonceVerification.Missing
	);
}

/**
 * Map one option slug to its readable label via a theme option function.
 *
 * @param string $slug    Option slug.
 * @param string $fn_name Theme function returning slug => label.
 * @return string
 */
function compass_core_quote_label( $slug, $fn_name ) {
	if ( function_exists( $fn_name ) ) {
		$options = call_user_func( $fn_name );
		if ( isset( $options[ $slug ] ) ) {
			return (string) $options[ $slug ];
		}
	}
	return $slug;
}

/**
 * Map a list of slugs to labels.
 *
 * @param array  $slugs   Slugs.
 * @param string $fn_name Theme function returning slug => label.
 * @return array
 */
function compass_core_quote_labels( $slugs, $fn_name ) {
	$out = array();
	foreach ( $slugs as $slug ) {
		$out[] = compass_core_quote_label( $slug, $fn_name );
	}
	return $out;
}

/**
 * Send the owner summary + the visitor auto-reply.
 *
 * @param array $d Collected, already-sanitised fields.
 */
function compass_core_quote_notify( $d ) {
	$site = wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES );

	// From address (settings → contact email → admin email).
	$from = function_exists( 'compass_core_secret' ) ? compass_core_secret( 'smtp_from' ) : '';
	if ( ! $from && function_exists( 'compass_get_setting' ) ) {
		$from = compass_get_setting( 'email' );
	}
	if ( ! $from || ! is_email( $from ) ) {
		$from = get_option( 'admin_email' );
	}

	$lines = array(
		sprintf( /* translators: %s: form name. */ __( 'New %s request', 'compass-core' ), $d['is_contact'] ? __( 'contact', 'compass-core' ) : __( 'quote', 'compass-core' ) ),
		'',
		__( 'Name: ', 'compass-core' ) . $d['name'],
		__( 'Email: ', 'compass-core' ) . $d['email'],
		__( 'Phone / WhatsApp: ', 'compass-core' ) . $d['phone'],
		__( 'Preferred contact: ', 'compass-core' ) . $d['method_label'],
	);
	if ( $d['company'] ) {
		$lines[] = __( 'Company: ', 'compass-core' ) . $d['company'];
	}
	$lines[] = __( 'Services: ', 'compass-core' ) . $d['service_text'];
	if ( $d['industry_label'] ) {
		$lines[] = __( 'Industry: ', 'compass-core' ) . $d['industry_label'];
	}
	if ( $d['timeline_label'] ) {
		$lines[] = __( 'Timeline: ', 'compass-core' ) . $d['timeline_label'];
	}
	if ( $d['budget_label'] ) {
		$lines[] = __( 'Budget: ', 'compass-core' ) . $d['budget_label'];
	}
	$lines[] = '';
	$lines[] = __( 'Message:', 'compass-core' );
	$lines[] = $d['message'];
	$lines[] = '';
	$lines[] = __( 'From page: ', 'compass-core' ) . $d['source'];
	$lines[] = __( 'Open in admin: ', 'compass-core' ) . admin_url( 'post.php?post=' . $d['post_id'] . '&action=edit' );

	$owner_subject = sprintf(
		/* translators: 1: site name, 2: visitor name. */
		__( '[%1$s] New quote request — %2$s', 'compass-core' ),
		$site,
		$d['name']
	);
	$headers = array(
		'From: ' . $site . ' <' . $from . '>',
		'Reply-To: ' . $d['name'] . ' <' . $d['email'] . '>',
	);
	wp_mail( compass_core_notify_email(), $owner_subject, implode( "\n", $lines ), $headers );

	// Visitor auto-reply.
	$reply_lines   = array(
		sprintf( /* translators: %s: visitor first name. */ __( 'Hi %s,', 'compass-core' ), compass_core_first_name( $d['name'] ) ),
		'',
		__( 'Thanks for reaching out to Compass Digital — we have your request and a real person will read it.', 'compass-core' ),
		'',
		__( 'What happens next: we will reply within one business day, usually sooner, with any quick questions and the next step toward a clear, honest quote.', 'compass-core' ),
		'',
		__( 'For reference, here is what you sent us:', 'compass-core' ),
		__( 'Services: ', 'compass-core' ) . $d['service_text'],
		__( 'Message: ', 'compass-core' ) . $d['message'],
		'',
		__( 'If it is urgent, you can also message us on WhatsApp.', 'compass-core' ),
		'',
		__( '— The Compass Digital team', 'compass-core' ),
	);
	$reply_subject = sprintf(
		/* translators: %s: site name. */
		__( 'We have your request — %s', 'compass-core' ),
		$site
	);
	wp_mail( $d['email'], $reply_subject, implode( "\n", $reply_lines ), array( 'From: ' . $site . ' <' . $from . '>' ) );
}

/**
 * First name from a full name, for a warmer auto-reply.
 *
 * @param string $name Full name.
 * @return string
 */
function compass_core_first_name( $name ) {
	$parts = preg_split( '/\s+/', trim( $name ) );
	return $parts ? $parts[0] : $name;
}

/**
 * Verify a Cloudflare Turnstile token.
 *
 * @param string $secret Secret key.
 * @param string $token  Response token.
 * @return bool
 */
function compass_core_verify_turnstile( $secret, $token ) {
	if ( '' === $token ) {
		return false;
	}
	$response = wp_remote_post(
		'https://challenges.cloudflare.com/turnstile/v0/siteverify',
		array(
			'timeout' => 5,
			'body'    => array(
				'secret'   => $secret,
				'response' => $token,
				'remoteip' => compass_core_client_ip(),
			),
		)
	);
	if ( is_wp_error( $response ) ) {
		return false;
	}
	$body = json_decode( wp_remote_retrieve_body( $response ), true );
	return is_array( $body ) && ! empty( $body['success'] );
}

/**
 * Best-effort client IP (used only for rate limiting + Turnstile).
 *
 * @return string
 */
function compass_core_client_ip() {
	$ip = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';
	return $ip ? $ip : '0.0.0.0';
}

/**
 * Is a URL on this site (used to stop open redirects)?
 *
 * @param string $url URL.
 * @return bool
 */
function compass_core_is_local_url( $url ) {
	$host = wp_parse_url( $url, PHP_URL_HOST );
	return ! $host || wp_parse_url( home_url(), PHP_URL_HOST ) === $host;
}

/**
 * Wire SMTP from settings / constants, if a host is configured.
 *
 * @param PHPMailer\PHPMailer\PHPMailer $phpmailer Mailer instance.
 */
function compass_core_configure_smtp( $phpmailer ) {
	$host = function_exists( 'compass_core_secret' ) ? compass_core_secret( 'smtp_host' ) : '';
	if ( ! $host ) {
		return;
	}
	$phpmailer->isSMTP();
	$phpmailer->Host     = $host; // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
	$phpmailer->SMTPAuth = true;   // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
	$port                = (int) compass_core_secret( 'smtp_port' );
	if ( $port ) {
		$phpmailer->Port = $port; // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
	}
	$user = compass_core_secret( 'smtp_user' );
	$pass = compass_core_secret( 'smtp_pass' );
	if ( $user ) {
		$phpmailer->Username = $user; // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
	}
	if ( $pass ) {
		$phpmailer->Password = $pass; // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
	}
	$enc = strtolower( compass_core_secret( 'smtp_encryption' ) );
	if ( 'tls' === $enc || 'ssl' === $enc ) {
		$phpmailer->SMTPSecure = $enc; // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
	} else {
		$phpmailer->SMTPAutoTLS = false; // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
	}
}
add_action( 'phpmailer_init', 'compass_core_configure_smtp' );
