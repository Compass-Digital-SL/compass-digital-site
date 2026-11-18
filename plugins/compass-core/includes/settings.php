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
		'phone'        => array(
			'label' => __( 'Phone', 'compass-core' ),
			'type'  => 'tel',
		),
		'whatsapp'     => array(
			'label' => __( 'WhatsApp number', 'compass-core' ),
			'type'  => 'text',
			'help'  => __( 'Digits only, including country code, e.g. 94771234567.', 'compass-core' ),
		),
		'email'        => array(
			'label' => __( 'Email', 'compass-core' ),
			'type'  => 'email',
		),
		'address'      => array(
			'label' => __( 'Address', 'compass-core' ),
			'type'  => 'textarea',
		),
		'service_area' => array(
			'label' => __( 'Service area', 'compass-core' ),
			'type'  => 'text',
			'help'  => __( 'e.g. Colombo & across Sri Lanka.', 'compass-core' ),
		),
		'reg_no'       => array(
			'label' => __( 'Registration number', 'compass-core' ),
			'type'  => 'text',
		),
		'hours'        => array(
			'label' => __( 'Opening hours', 'compass-core' ),
			'type'  => 'textarea',
			'help'  => __( 'One line per day, e.g. Mon–Fri 9:00–17:30.', 'compass-core' ),
		),
		'facebook'     => array(
			'label' => __( 'Facebook URL', 'compass-core' ),
			'type'  => 'url',
		),
		'instagram'    => array(
			'label' => __( 'Instagram URL', 'compass-core' ),
			'type'  => 'url',
		),
		'linkedin'     => array(
			'label' => __( 'LinkedIn URL', 'compass-core' ),
			'type'  => 'url',
		),
		'youtube'      => array(
			'label' => __( 'YouTube URL', 'compass-core' ),
			'type'  => 'url',
		),
		'tiktok'       => array(
			'label' => __( 'TikTok URL', 'compass-core' ),
			'type'  => 'url',
		),
	);
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

	foreach ( compass_core_setting_fields() as $key => $field ) {
		add_settings_field(
			$key,
			$field['label'],
			'compass_core_render_setting_field',
			'compass-site-settings',
			'compass_core_contact',
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
		$input_type = in_array( $field['type'], array( 'url', 'email', 'tel' ), true ) ? $field['type'] : 'text';
		printf(
			'<input type="%1$s" id="%2$s" name="%3$s" value="%4$s" class="regular-text" />',
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
