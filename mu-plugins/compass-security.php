<?php
/**
 * Compass Digital — security hardening (must-use).
 *
 * WordPress loads must-use plugins automatically and they cannot be
 * deactivated, so this hardening survives theme and plugin changes and runs
 * early. It covers:
 *
 *   - Security response headers (X-Frame-Options, X-Content-Type-Options,
 *     Referrer-Policy, Permissions-Policy, HSTS over TLS) and a
 *     Content-Security-Policy in **Report-Only** mode first, so it can be
 *     tuned against the real inline scripts (speculation rules, consent,
 *     JSON-LD) before it is enforced.
 *   - Disabling XML-RPC and its pingback surface.
 *   - Disabling the admin file editor (DISALLOW_FILE_EDIT).
 *   - Blocking user enumeration (?author=N and the REST users endpoint for
 *     unauthenticated requests).
 *   - A lightweight per-IP login-attempt throttle and generic login errors.
 *   - Serving /.well-known/security.txt (RFC 9116).
 *
 * Tunable with the `compass_csp_directives` and `compass_security_headers`
 * filters. See docs/SECURITY.md for the policy, roles and update cadence.
 *
 * @package CompassSecurity
 */

defined( 'ABSPATH' ) || exit;

/**
 * Forbid editing plugin/theme files from the WP admin.
 *
 * A code-managed site never edits files through wp-admin; turning the editor
 * off removes a direct code-execution path if an admin account is taken over.
 * Defined here (loads before admin) rather than relying on wp-config, so the
 * guarantee travels with the repo.
 */
if ( ! defined( 'DISALLOW_FILE_EDIT' ) ) {
	define( 'DISALLOW_FILE_EDIT', true );
}

// ==== XML-RPC ====

// Turn XML-RPC off: the site has no mobile-app or pingback use for it, and it
// is a common brute-force and amplification vector.
add_filter( 'xmlrpc_enabled', '__return_false' );

// Strip pingback methods and the pingback advertisement header outright.
add_filter(
	'xmlrpc_methods',
	function ( $methods ) {
		unset( $methods['pingback.ping'], $methods['pingback.extensions.getPingbacks'] );
		return $methods;
	}
);
add_filter(
	'wp_headers',
	function ( $headers ) {
		unset( $headers['X-Pingback'] );
		return $headers;
	}
);
// Drop the <head> pingback link.
add_filter(
	'bloginfo_url',
	function ( $output, $show ) {
		return ( 'pingback_url' === $show ) ? '' : $output;
	},
	10,
	2
);

// ==== User enumeration ====

/**
 * Block the ?author=N probe and author archives for guests.
 *
 * `?author=N` would otherwise be canonical-redirected to /author/<login>/,
 * leaking the login name, so this runs at priority 1 — before core's
 * redirect_canonical (priority 10). Author archives themselves also reveal the
 * login in the URL and have no content worth showing here (the blog is single
 * author), so guests are sent home from those too. Logged-in users are
 * unaffected.
 */
add_action(
	'template_redirect',
	function () {
		if ( is_admin() || is_user_logged_in() ) {
			return;
		}
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only probe check on a public query string.
		$author = isset( $_GET['author'] ) ? sanitize_text_field( wp_unslash( $_GET['author'] ) ) : '';
		if ( '' !== $author || is_author() ) {
			wp_safe_redirect( home_url( '/' ), 301 );
			exit;
		}
	},
	1
);

// Never print author archive links for guests (post bylines, feeds).
add_filter(
	'author_link',
	function ( $link ) {
		return is_user_logged_in() ? $link : home_url( '/' );
	}
);

/**
 * Remove the REST `users` endpoints for unauthenticated requests.
 *
 * The public /wp-json/wp/v2/users route lists author slugs and display names —
 * the same enumeration by another door. Authenticated, capable requests keep
 * full access.
 */
add_filter(
	'rest_endpoints',
	function ( $endpoints ) {
		if ( is_user_logged_in() ) {
			return $endpoints;
		}
		foreach ( array( '/wp/v2/users', '/wp/v2/users/(?P<id>[\d]+)' ) as $route ) {
			if ( isset( $endpoints[ $route ] ) ) {
				unset( $endpoints[ $route ] );
			}
		}
		return $endpoints;
	}
);

// Drop the oEmbed author field so it cannot leak the author slug either.
add_filter(
	'oembed_response_data',
	function ( $data ) {
		unset( $data['author_url'], $data['author_name'] );
		return $data;
	}
);

// ==== Login hardening ====

/**
 * Transient key for the per-IP login-failure counter.
 *
 * @return string
 */
function compass_security_login_key() {
	$ip = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : 'unknown';
	return 'compass_login_fail_' . md5( $ip );
}

/**
 * Throttle repeated failed logins per IP.
 *
 * After COMPASS_LOGIN_MAX failures inside the window, further attempts from the
 * same IP are refused for the lockout period. Lightweight (one transient), no
 * plugin, no table — enough to blunt credential-stuffing without locking out a
 * forgetful owner for long.
 */
const COMPASS_LOGIN_MAX    = 5;
const COMPASS_LOGIN_WINDOW = 15 * MINUTE_IN_SECONDS;

add_filter(
	'authenticate',
	function ( $user ) {
		$fails = (int) get_transient( compass_security_login_key() );
		if ( $fails >= COMPASS_LOGIN_MAX ) {
			return new WP_Error(
				'compass_locked',
				__( 'Too many failed attempts. Please wait a few minutes and try again.', 'compass-digital' )
			);
		}
		return $user;
	},
	30
);

add_action(
	'wp_login_failed',
	function () {
		$key   = compass_security_login_key();
		$fails = (int) get_transient( $key );
		set_transient( $key, $fails + 1, COMPASS_LOGIN_WINDOW );
	}
);

add_action(
	'wp_login',
	function () {
		delete_transient( compass_security_login_key() );
	}
);

// Generic login error — never reveal whether the username or the password was
// the wrong half.
add_filter(
	'login_errors',
	function () {
		return __( 'Those details did not match. Please try again.', 'compass-digital' );
	}
);

// ==== Security headers ====

/**
 * The Content-Security-Policy directives, as an ordered map.
 *
 * Shipped **Report-Only** (see below) so violations are reported, not blocked,
 * while the policy is tuned. `'unsafe-inline'` is a deliberate, temporary
 * allowance for the inline blocks the site still emits (critical CSS, consent
 * config, JSON-LD, speculation rules); the next step is to nonce those and drop
 * it. Filterable via `compass_csp_directives`.
 *
 * @return array<string,string>
 */
function compass_security_csp_directives() {
	// Google's documented GA4 hosts, including the regional collection endpoints.
	$ga        = 'https://*.googletagmanager.com https://*.google-analytics.com';
	$ga_conn   = 'https://*.google-analytics.com https://*.analytics.google.com https://*.googletagmanager.com';
	$turnstile = 'https://challenges.cloudflare.com';

	$directives = array(
		'default-src'     => "'self'",
		'base-uri'        => "'self'",
		'object-src'      => "'none'",
		'frame-ancestors' => "'self'",
		'form-action'     => "'self'",
		'script-src'      => "'self' 'unsafe-inline' {$ga} {$turnstile}",
		'style-src'       => "'self' 'unsafe-inline'",
		'img-src'         => "'self' data: {$ga}",
		'font-src'        => "'self'",
		'connect-src'     => "'self' {$ga_conn}",
		'frame-src'       => $turnstile,
		'manifest-src'    => "'self'",
		'report-uri'      => esc_url_raw( rest_url( 'compass/v1/csp-report' ) ),
		'report-to'       => 'csp',
	);

	// upgrade-insecure-requests is ignored (with a console error) in a
	// Report-Only policy, so it is only sent once the policy is enforced.
	if ( compass_security_csp_enforced() ) {
		$directives['upgrade-insecure-requests'] = '';
	}

	/**
	 * Filter the CSP directives before they are assembled.
	 *
	 * @param array<string,string> $directives Directive => value (value '' for valueless directives).
	 */
	return apply_filters( 'compass_csp_directives', $directives );
}

/**
 * Whether the CSP is enforced (COMPASS_CSP_ENFORCE) or Report-Only.
 *
 * @return bool
 */
function compass_security_csp_enforced() {
	return defined( 'COMPASS_CSP_ENFORCE' ) && COMPASS_CSP_ENFORCE;
}

/**
 * Receive CSP violation reports (legacy report-uri and Reporting API report-to).
 *
 * Each report goes to the PHP error log as one capped line, so the Report-Only
 * run can be reviewed before enforcing. Nothing is stored in the database.
 */
add_action(
	'rest_api_init',
	function () {
		register_rest_route(
			'compass/v1',
			'/csp-report',
			array(
				'methods'             => 'POST',
				'permission_callback' => '__return_true',
				'callback'            => function ( WP_REST_Request $request ) {
					$body = substr( (string) $request->get_body(), 0, 2000 );
					if ( '' !== $body ) {
						error_log( 'CSP report: ' . preg_replace( '/\s+/', ' ', $body ) ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- intentional violation log.
					}
					return new WP_REST_Response( null, 204 );
				},
			)
		);
	}
);

/**
 * Assemble the directive map into a CSP header string.
 *
 * @param array<string,string> $directives Directive map.
 * @return string
 */
function compass_security_csp_string( array $directives ) {
	$parts = array();
	foreach ( $directives as $name => $value ) {
		$parts[] = '' === $value ? $name : $name . ' ' . $value;
	}
	return implode( '; ', $parts );
}

/**
 * Send the security response headers.
 *
 * Applied on every front-end response. CSP is Report-Only for now; flip to
 * enforcing by defining COMPASS_CSP_ENFORCE (true) once the Report-Only run is
 * clean. HSTS is sent only over TLS.
 */
add_action(
	'send_headers',
	function () {
		if ( is_admin() || ( defined( 'WP_CLI' ) && WP_CLI ) || ( defined( 'REST_REQUEST' ) && REST_REQUEST ) ) {
			return;
		}

		$headers = array(
			'X-Content-Type-Options'     => 'nosniff',
			'X-Frame-Options'            => 'SAMEORIGIN',
			'Referrer-Policy'            => 'strict-origin-when-cross-origin',
			'Permissions-Policy'         => 'geolocation=(), microphone=(), camera=(), payment=(), usb=()',
			'Cross-Origin-Opener-Policy' => 'same-origin',
		);

		if ( is_ssl() ) {
			$headers['Strict-Transport-Security'] = 'max-age=31536000; includeSubDomains';
		}

		$csp         = compass_security_csp_string( compass_security_csp_directives() );
		$csp_enforce = compass_security_csp_enforced();
		$headers[ $csp_enforce ? 'Content-Security-Policy' : 'Content-Security-Policy-Report-Only' ] = $csp;
		$headers['Reporting-Endpoints'] = 'csp="' . esc_url_raw( rest_url( 'compass/v1/csp-report' ) ) . '"';

		/**
		 * Filter the full set of security headers before they are sent.
		 *
		 * @param array<string,string> $headers Header name => value.
		 */
		$headers = apply_filters( 'compass_security_headers', $headers );

		// Do not advertise the PHP version.
		header_remove( 'X-Powered-By' );

		foreach ( $headers as $name => $value ) {
			if ( '' !== $value ) {
				header( $name . ': ' . $value );
			}
		}
	}
);

// ==== security.txt (RFC 9116) ====

/**
 * Serve /.well-known/security.txt (and the legacy /security.txt).
 *
 * Output on `init` by matching the request path, which avoids a rewrite-rule
 * flush. The contact and the signed-fresh Expires date are generated so the
 * file never goes stale. Update the contact/policy details in docs/SECURITY.md.
 */
add_action(
	'init',
	function () {
		$path = isset( $_SERVER['REQUEST_URI'] )
			? (string) wp_parse_url( esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ) ), PHP_URL_PATH )
			: '';
		$path = untrailingslashit( $path );

		if ( '/.well-known/security.txt' !== $path && '/security.txt' !== $path ) {
			return;
		}

		$contact = 'info@compassdigital.com';
		if ( function_exists( 'compass_contact_details' ) ) {
			$details = compass_contact_details();
			if ( ! empty( $details['email'] ) ) {
				$contact = $details['email'];
			}
		}

		$expires   = gmdate( 'Y-m-d\TH:i:s\Z', strtotime( '+1 year' ) );
		$canonical = home_url( '/.well-known/security.txt' );
		$policy    = home_url( '/privacy/' );

		$lines = array(
			'# Security contact for Compass Digital — responsible disclosure welcome.',
			'Contact: mailto:' . $contact,
			'Expires: ' . $expires,
			'Preferred-Languages: en',
			'Canonical: ' . $canonical,
			'Policy: ' . $policy,
		);

		if ( ! headers_sent() ) {
			header( 'Content-Type: text/plain; charset=utf-8' );
			header( 'X-Content-Type-Options: nosniff' );
			header( 'Cache-Control: max-age=86400' );
		}
		echo implode( "\n", $lines ) . "\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static plain-text security.txt, no user input.
		exit;
	},
	1
);
