<?php
/**
 * Site Settings — the client's business details in one place.
 *
 * Stored as a single option array (`compass_site_settings`) via the Settings
 * API. The theme reads values with compass_get_setting(); contact-shaped values
 * are also bridged onto the theme's existing `compass_contact_details` filter so
 * the header, footer, command menu and WhatsApp links pick them up with no theme
 * changes.
 *
 * @package CompassCore
 */

defined( 'ABSPATH' ) || exit;

const COMPASS_CORE_OPTION = 'compass_site_settings';

/**
 * Field definitions for the settings page.
 *
 * @return array key => array( label, type, help )
 */
function compass_core_setting_fields() {
	return array(
		'phone'            => array(
			'label' => __( 'Phone', 'compass-core' ),
			'type'  => 'tel',
		),
		'whatsapp'         => array(
			'label' => __( 'WhatsApp number', 'compass-core' ),
			'type'  => 'text',
			'help'  => __( 'Digits only, including country code, e.g. 94771234567.', 'compass-core' ),
		),
		'email'            => array(
			'label' => __( 'Email', 'compass-core' ),
			'type'  => 'email',
		),
		'address'          => array(
			'label' => __( 'Address', 'compass-core' ),
			'type'  => 'textarea',
		),
		'service_area'     => array(
			'label' => __( 'Service area', 'compass-core' ),
			'type'  => 'text',
			'help'  => __( 'e.g. Colombo & across Sri Lanka.', 'compass-core' ),
		),
		'reg_no'           => array(
			'label' => __( 'Registration number', 'compass-core' ),
			'type'  => 'text',
		),
		'hours'            => array(
			'label' => __( 'Opening hours', 'compass-core' ),
			'type'  => 'textarea',
			'help'  => __( 'One line per day, e.g. Mon–Fri 9:00–17:30.', 'compass-core' ),
		),
		'facebook'         => array(
			'label' => __( 'Facebook URL', 'compass-core' ),
			'type'  => 'url',
		),
		'instagram'        => array(
			'label' => __( 'Instagram URL', 'compass-core' ),
			'type'  => 'url',
		),
		'linkedin'         => array(
			'label' => __( 'LinkedIn URL', 'compass-core' ),
			'type'  => 'url',
		),
		'youtube'          => array(
			'label' => __( 'YouTube URL', 'compass-core' ),
			'type'  => 'url',
		),
		'tiktok'           => array(
			'label' => __( 'TikTok URL', 'compass-core' ),
			'type'  => 'url',
		),

		// --- Local & maps (third section). Drives the service-area block, the
		// embed-free map link, the reviews CTA and the LocalBusiness schema.
		'map_url'          => array(
			'label'   => __( 'Google Maps link', 'compass-core' ),
			'type'    => 'url',
			'section' => 'local',
			'help'    => __( 'The "share" link to your Google Business Profile on Maps. Shown as a lightweight link — no heavy embedded map.', 'compass-core' ),
		),
		'reviews_url'      => array(
			'label'   => __( 'Google reviews link', 'compass-core' ),
			'type'    => 'url',
			'section' => 'local',
			'help'    => __( 'The public "read / write a review" link for your Google Business Profile. Leave blank until the profile is live.', 'compass-core' ),
		),
		'geo_lat'          => array(
			'label'   => __( 'Latitude', 'compass-core' ),
			'type'    => 'text',
			'section' => 'local',
			'help'    => __( 'Optional. Decimal degrees, e.g. 6.9271. Added to the LocalBusiness schema.', 'compass-core' ),
		),
		'geo_lng'          => array(
			'label'   => __( 'Longitude', 'compass-core' ),
			'type'    => 'text',
			'section' => 'local',
			'help'    => __( 'Optional. Decimal degrees, e.g. 79.8612.', 'compass-core' ),
		),
		'rating_value'     => array(
			'label'   => __( 'Average review rating', 'compass-core' ),
			'type'    => 'text',
			'section' => 'local',
			'help'    => __( 'Optional, 1–5, e.g. 4.9. Only fill this in with real Google figures — it feeds star-rating schema.', 'compass-core' ),
		),
		'rating_count'     => array(
			'label'   => __( 'Number of reviews', 'compass-core' ),
			'type'    => 'text',
			'section' => 'local',
			'help'    => __( 'Optional. The real count behind the rating above. Leave both blank if you have no reviews yet.', 'compass-core' ),
		),

		// --- Analytics & verification (fourth section). IDs only — scripts load
		// after cookie consent and once the page is idle. Each can be set as a PHP
		// constant (COMPASS_GA4_ID, COMPASS_GSC_VERIFICATION) instead.
		'ga4_id'           => array(
			'label'   => __( 'GA4 Measurement ID', 'compass-core' ),
			'type'    => 'text',
			'section' => 'analytics',
			'help'    => __( 'From Google Analytics 4, looks like G-XXXXXXXXXX. Leave blank to load no analytics.', 'compass-core' ),
		),
		'gsc_verification' => array(
			'label'   => __( 'Search Console verification', 'compass-core' ),
			'type'    => 'text',
			'section' => 'analytics',
			'help'    => __( 'The content value from Google Search Console\'s "HTML tag" method — the part after content=. Not the whole tag.', 'compass-core' ),
		),

		// --- Quote & email (fifth section). Each value can be overridden by a
		// PHP constant (see compass_core_secret()) so secrets can live in the
		// environment / wp-config.php instead of the database.
		'notify_email'     => array(
			'label'   => __( 'Quote notification email', 'compass-core' ),
			'type'    => 'email',
			'section' => 'quote',
			'help'    => __( 'Where new quote requests are emailed. Defaults to the site admin email.', 'compass-core' ),
		),
		'turnstile_site'   => array(
			'label'   => __( 'Turnstile site key', 'compass-core' ),
			'type'    => 'text',
			'section' => 'quote',
			'help'    => __( 'Cloudflare Turnstile site key. Leave blank to disable the spam widget.', 'compass-core' ),
		),
		'turnstile_secret' => array(
			'label'   => __( 'Turnstile secret key', 'compass-core' ),
			'type'    => 'password',
			'section' => 'quote',
		),
		'smtp_host'        => array(
			'label'   => __( 'SMTP host', 'compass-core' ),
			'type'    => 'text',
			'section' => 'quote',
			'help'    => __( 'Leave SMTP fields blank to use the server default mailer.', 'compass-core' ),
		),
		'smtp_port'        => array(
			'label'   => __( 'SMTP port', 'compass-core' ),
			'type'    => 'text',
			'section' => 'quote',
			'help'    => __( 'e.g. 587 (TLS) or 465 (SSL).', 'compass-core' ),
		),
		'smtp_user'        => array(
			'label'   => __( 'SMTP username', 'compass-core' ),
			'type'    => 'text',
			'section' => 'quote',
		),
		'smtp_pass'        => array(
			'label'   => __( 'SMTP password', 'compass-core' ),
			'type'    => 'password',
			'section' => 'quote',
		),
		'smtp_encryption'  => array(
			'label'   => __( 'SMTP encryption', 'compass-core' ),
			'type'    => 'text',
			'section' => 'quote',
			'help'    => __( 'tls, ssl, or blank for none.', 'compass-core' ),
		),
		'smtp_from'        => array(
			'label'   => __( 'From email', 'compass-core' ),
			'type'    => 'email',
			'section' => 'quote',
			'help'    => __( 'The address auto-replies and notifications are sent from. Defaults to the contact email.', 'compass-core' ),
		),
	);
}

/**
 * A secret / configurable value, preferring a PHP constant over the stored
 * option so SMTP and Turnstile keys can be supplied via the environment.
 *
 * @param string $key Setting key.
 * @return string
 */
function compass_core_secret( $key ) {
	$const = 'COMPASS_' . strtoupper( $key );
	if ( defined( $const ) && '' !== (string) constant( $const ) ) {
		return (string) constant( $const );
	}
	return (string) compass_get_setting( $key );
}

/**
 * The email address quote notifications go to.
 *
 * @return string
 */
function compass_core_notify_email() {
	$email = compass_core_secret( 'notify_email' );
	return $email ? $email : get_option( 'admin_email' );
}

/**
 * Get one stored setting.
 *
 * @param string $key     Field key.
 * @param mixed  $default Fallback.
 * @return mixed
 */
function compass_get_setting( $key, $default = '' ) {
	$all = get_option( COMPASS_CORE_OPTION, array() );
	return ( is_array( $all ) && isset( $all[ $key ] ) && '' !== $all[ $key ] ) ? $all[ $key ] : $default;
}

/**
 * Register the option and its fields.
 */
function compass_core_register_settings() {
	register_setting(
		'compass_site_settings_group',
		COMPASS_CORE_OPTION,
		array(
			'type'              => 'array',
			'sanitize_callback' => 'compass_core_sanitize_settings',
			'default'           => array(),
		)
	);

	add_settings_section(
		'compass_core_contact',
		__( 'Business details', 'compass-core' ),
		function () {
			echo '<p>' . esc_html__( 'The theme reads these for the header, footer and contact links.', 'compass-core' ) . '</p>';
		},
		'compass-site-settings'
	);

	add_settings_section(
		'compass_core_local',
		__( 'Local &amp; maps', 'compass-core' ),
		function () {
			echo '<p>' . esc_html__( 'Map, reviews and coordinates for the service-area block and the LocalBusiness schema. Fill the review rating only with real Google figures.', 'compass-core' ) . '</p>';
		},
		'compass-site-settings'
	);

	add_settings_section(
		'compass_core_analytics',
		__( 'Analytics &amp; verification', 'compass-core' ),
		function () {
			echo '<p>' . esc_html__( 'Enter IDs only. Analytics loads after the visitor accepts cookies and once the page is idle; Search Console verification is a lightweight meta tag.', 'compass-core' ) . '</p>';
		},
		'compass-site-settings'
	);

	add_settings_section(
		'compass_core_quote',
		__( 'Quote &amp; email', 'compass-core' ),
		function () {
			echo '<p>' . esc_html__( 'Spam protection and email delivery for the quote form. SMTP and Turnstile keys can instead be set as PHP constants in wp-config.php (e.g. COMPASS_SMTP_PASS).', 'compass-core' ) . '</p>';
		},
		'compass-site-settings'
	);

	$section_map = array(
		'quote'     => 'compass_core_quote',
		'local'     => 'compass_core_local',
		'analytics' => 'compass_core_analytics',
	);

	foreach ( compass_core_setting_fields() as $key => $field ) {
		$section = ( isset( $field['section'] ) && isset( $section_map[ $field['section'] ] ) ) ? $section_map[ $field['section'] ] : 'compass_core_contact';
		add_settings_field(
			$key,
			$field['label'],
			'compass_core_render_setting_field',
			'compass-site-settings',
			$section,
			array(
				'key'       => $key,
				'field'     => $field,
				'label_for' => 'compass_set_' . $key,
			)
		);
	}
}
add_action( 'admin_init', 'compass_core_register_settings' );

/**
 * Render one settings field.
 *
 * @param array $args Field args.
 */
function compass_core_render_setting_field( $args ) {
	$key   = $args['key'];
	$field = $args['field'];
	$id    = 'compass_set_' . $key;
	$name  = COMPASS_CORE_OPTION . '[' . $key . ']';
	$value = compass_get_setting( $key );

	if ( 'textarea' === $field['type'] ) {
		printf(
			'<textarea id="%1$s" name="%2$s" rows="3" class="large-text">%3$s</textarea>',
			esc_attr( $id ),
			esc_attr( $name ),
			esc_textarea( $value )
		);
	} else {
		$input_type = in_array( $field['type'], array( 'url', 'email', 'tel', 'password' ), true ) ? $field['type'] : 'text';
		printf(
			'<input type="%1$s" id="%2$s" name="%3$s" value="%4$s" class="regular-text" autocomplete="off" />',
			esc_attr( $input_type ),
			esc_attr( $id ),
			esc_attr( $name ),
			esc_attr( $value )
		);
	}

	if ( ! empty( $field['help'] ) ) {
		echo '<p class="description">' . esc_html( $field['help'] ) . '</p>';
	}
}

/**
 * Sanitise the whole settings array on save.
 *
 * @param mixed $input Submitted values.
 * @return array
 */
function compass_core_sanitize_settings( $input ) {
	$clean  = array();
	$fields = compass_core_setting_fields();
	$input  = is_array( $input ) ? $input : array();

	foreach ( $fields as $key => $field ) {
		$raw = isset( $input[ $key ] ) ? $input[ $key ] : '';
		switch ( $field['type'] ) {
			case 'textarea':
				$clean[ $key ] = sanitize_textarea_field( $raw );
				break;
			case 'url':
				$clean[ $key ] = esc_url_raw( $raw );
				break;
			case 'email':
				$clean[ $key ] = sanitize_email( $raw );
				break;
			default:
				$clean[ $key ] = sanitize_text_field( $raw );
				break;
		}
	}

	// WhatsApp: keep digits only.
	if ( ! empty( $clean['whatsapp'] ) ) {
		$clean['whatsapp'] = preg_replace( '/\D/', '', $clean['whatsapp'] );
	}

	// GA4 Measurement ID: uppercase, only the allowed characters.
	if ( ! empty( $clean['ga4_id'] ) ) {
		$clean['ga4_id'] = strtoupper( preg_replace( '/[^A-Za-z0-9\-]/', '', $clean['ga4_id'] ) );
	}

	// Search Console: a token only — strip any pasted <meta> wrapper.
	if ( ! empty( $clean['gsc_verification'] ) ) {
		if ( preg_match( '/content=["\']([^"\']+)["\']/', $clean['gsc_verification'], $m ) ) {
			$clean['gsc_verification'] = $m[1];
		}
		$clean['gsc_verification'] = preg_replace( '/[^A-Za-z0-9_\-]/', '', $clean['gsc_verification'] );
	}

	// Geo + rating: keep a sane numeric shape.
	foreach ( array( 'geo_lat', 'geo_lng', 'rating_value', 'rating_count' ) as $num_key ) {
		if ( ! empty( $clean[ $num_key ] ) ) {
			$clean[ $num_key ] = preg_replace( '/[^0-9.\-]/', '', $clean[ $num_key ] );
		}
	}

	return $clean;
}

/**
 * Add the Site Settings admin page under Settings.
 */
function compass_core_settings_page() {
	add_options_page(
		__( 'Site Settings', 'compass-core' ),
		__( 'Site Settings', 'compass-core' ),
		'manage_options',
		'compass-site-settings',
		'compass_core_render_settings_page'
	);
}
add_action( 'admin_menu', 'compass_core_settings_page' );

/**
 * Render the settings page wrapper.
 */
function compass_core_render_settings_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	?>
	<div class="wrap">
		<h1><?php echo esc_html__( 'Site Settings', 'compass-core' ); ?></h1>
		<form action="options.php" method="post">
			<?php
			settings_fields( 'compass_site_settings_group' );
			do_settings_sections( 'compass-site-settings' );
			submit_button();
			?>
		</form>
	</div>
	<?php
}

/**
 * Bridge stored settings onto the theme's contact-details filter.
 *
 * The theme (inc/nav.php) defines `compass_contact_details()` with placeholder
 * values and a filter. We overlay any values the client has saved so the whole
 * front end updates from the Site Settings page.
 *
 * @param array $details Theme defaults.
 * @return array
 */
function compass_core_filter_contact_details( $details ) {
	$map = array(
		'phone'    => 'phone',
		'whatsapp' => 'whatsapp',
		'email'    => 'email',
		'address'  => 'address',
		'reg_no'   => 'reg_no',
	);
	foreach ( $map as $setting => $detail ) {
		$value = compass_get_setting( $setting );
		if ( '' !== $value ) {
			$details[ $detail ] = $value;
		}
	}

	$social = array(
		'Facebook'  => compass_get_setting( 'facebook' ),
		'Instagram' => compass_get_setting( 'instagram' ),
		'LinkedIn'  => compass_get_setting( 'linkedin' ),
		'YouTube'   => compass_get_setting( 'youtube' ),
		'TikTok'    => compass_get_setting( 'tiktok' ),
	);
	$social = array_filter( $social );
	if ( ! empty( $social ) ) {
		$details['social'] = $social;
	}

	// Extra details the theme can read directly.
	$details['service_area'] = compass_get_setting( 'service_area', isset( $details['service_area'] ) ? $details['service_area'] : '' );
	$details['hours']        = compass_get_setting( 'hours', isset( $details['hours'] ) ? $details['hours'] : '' );

	return $details;
}
add_filter( 'compass_contact_details', 'compass_core_filter_contact_details' );
