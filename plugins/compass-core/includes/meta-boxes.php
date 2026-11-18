<?php
/**
 * Native meta boxes for Work, Testimonial and Quote Request.
 *
 * No ACF: every field is a hand-rolled meta box with a nonce and a sanitised
 * save handler. Meta keys are prefixed `_compass_` and registered with
 * register_post_meta() so they are typed, hidden from the custom-fields UI and
 * (where safe) exposed to REST for the theme.
 *
 * @package CompassCore
 */

defined( 'ABSPATH' ) || exit;

/**
 * Field definitions per post type.
 *
 * Each field: key (without the `_compass_` prefix), label, type.
 * Types: text, url, number, textarea, checkbox, email, tel, gallery, select.
 *
 * @return array
 */
function compass_core_field_defs() {
	return array(
		'work'          => array(
			array(
				'key'   => 'client',
				'label' => __( 'Client', 'compass-core' ),
				'type'  => 'text',
			),
			array(
				'key'   => 'year',
				'label' => __( 'Year', 'compass-core' ),
				'type'  => 'number',
			),
			array(
				'key'   => 'live_url',
				'label' => __( 'Live URL', 'compass-core' ),
				'type'  => 'url',
			),
			array(
				'key'   => 'tech',
				'label' => __( 'Tech used', 'compass-core' ),
				'type'  => 'text',
				'help'  => __( 'Comma-separated, e.g. WordPress, Vite, WooCommerce.', 'compass-core' ),
			),
			array(
				'key'   => 'problem',
				'label' => __( 'Problem', 'compass-core' ),
				'type'  => 'textarea',
			),
			array(
				'key'   => 'solution',
				'label' => __( 'Solution', 'compass-core' ),
				'type'  => 'textarea',
			),
			array(
				'key'   => 'results',
				'label' => __( 'Results', 'compass-core' ),
				'type'  => 'textarea',
				'help'  => __( 'One result per line.', 'compass-core' ),
			),
			array(
				'key'   => 'gallery',
				'label' => __( 'Gallery', 'compass-core' ),
				'type'  => 'gallery',
			),
			array(
				'key'   => 'featured',
				'label' => __( 'Featured', 'compass-core' ),
				'type'  => 'checkbox',
				'help'  => __( 'Show this case study in featured spots.', 'compass-core' ),
			),
		),
		'testimonial'   => array(
			array(
				'key'   => 'author_name',
				'label' => __( 'Author name', 'compass-core' ),
				'type'  => 'text',
			),
			array(
				'key'   => 'author_role',
				'label' => __( 'Role & company', 'compass-core' ),
				'type'  => 'text',
				'help'  => __( 'e.g. Owner, Ella Spice Café.', 'compass-core' ),
			),
			array(
				'key'   => 'rating',
				'label' => __( 'Rating (1–5)', 'compass-core' ),
				'type'  => 'number',
			),
			array(
				'key'   => 'work_url',
				'label' => __( 'Related URL', 'compass-core' ),
				'type'  => 'url',
			),
			array(
				'key'   => 'featured',
				'label' => __( 'Featured', 'compass-core' ),
				'type'  => 'checkbox',
			),
		),
		'quote_request' => array(
			array(
				'key'   => 'name',
				'label' => __( 'Name', 'compass-core' ),
				'type'  => 'text',
			),
			array(
				'key'   => 'email',
				'label' => __( 'Email', 'compass-core' ),
				'type'  => 'email',
			),
			array(
				'key'   => 'phone',
				'label' => __( 'Phone', 'compass-core' ),
				'type'  => 'tel',
			),
			array(
				'key'   => 'company',
				'label' => __( 'Company', 'compass-core' ),
				'type'  => 'text',
			),
			array(
				'key'   => 'service',
				'label' => __( 'Service wanted', 'compass-core' ),
				'type'  => 'text',
			),
			array(
				'key'   => 'budget',
				'label' => __( 'Budget', 'compass-core' ),
				'type'  => 'text',
			),
			array(
				'key'   => 'message',
				'label' => __( 'Message', 'compass-core' ),
				'type'  => 'textarea',
			),
			array(
				'key'   => 'source',
				'label' => __( 'Source page', 'compass-core' ),
				'type'  => 'url',
			),
			array(
				'key'     => 'status',
				'label'   => __( 'Status', 'compass-core' ),
				'type'    => 'select',
				'options' => compass_core_quote_statuses(),
			),
		),
	);
}

/**
 * Allowed statuses for a quote request.
 *
 * @return array key => label.
 */
function compass_core_quote_statuses() {
	return array(
		'new'       => __( 'New', 'compass-core' ),
		'contacted' => __( 'Contacted', 'compass-core' ),
		'quoted'    => __( 'Quoted', 'compass-core' ),
		'won'       => __( 'Won', 'compass-core' ),
		'lost'      => __( 'Lost', 'compass-core' ),
	);
}

/**
 * Register typed post meta for every field.
 */
function compass_core_register_meta() {
	foreach ( compass_core_field_defs() as $post_type => $fields ) {
		$rest = ( 'quote_request' !== $post_type ); // Never expose enquiries to REST.
		foreach ( $fields as $field ) {
			$type   = 'number' === $field['type'] ? 'integer' : 'string';
			$single = 'gallery' !== $field['key'];
			register_post_meta(
				$post_type,
				'_compass_' . $field['key'],
				array(
					'type'          => 'gallery' === $field['type'] ? 'string' : $type,
					'single'        => $single,
					'show_in_rest'  => $rest && 'gallery' !== $field['type'],
					'auth_callback' => function () use ( $post_type ) {
						return current_user_can( 'edit_posts' );
					},
				)
			);
		}
	}
}
add_action( 'init', 'compass_core_register_meta' );

/**
 * Add the meta boxes.
 */
function compass_core_add_meta_boxes() {
	add_meta_box(
		'compass_work_details',
		__( 'Case study details', 'compass-core' ),
		'compass_core_render_meta_box',
		'work',
		'normal',
		'high'
	);
	add_meta_box(
		'compass_testimonial_details',
		__( 'Testimonial details', 'compass-core' ),
		'compass_core_render_meta_box',
		'testimonial',
		'normal',
		'high'
	);
	add_meta_box(
		'compass_quote_details',
		__( 'Enquiry details', 'compass-core' ),
		'compass_core_render_meta_box',
		'quote_request',
		'normal',
		'high'
	);
}
add_action( 'add_meta_boxes', 'compass_core_add_meta_boxes' );

/**
 * Render a meta box for the given post.
 *
 * @param WP_Post $post Current post.
 */
function compass_core_render_meta_box( $post ) {
	$defs = compass_core_field_defs();
	if ( empty( $defs[ $post->post_type ] ) ) {
		return;
	}

	wp_nonce_field( 'compass_core_save_' . $post->post_type, 'compass_core_nonce' );

	echo '<div class="compass-meta">';
	foreach ( $defs[ $post->post_type ] as $field ) {
		$name  = '_compass_' . $field['key'];
		$id    = 'compass_' . $field['key'];
		$value = get_post_meta( $post->ID, $name, true );

		echo '<p class="compass-field compass-field--' . esc_attr( $field['type'] ) . '">';
		printf(
			'<label for="%1$s"><strong>%2$s</strong></label><br />',
			esc_attr( $id ),
			esc_html( $field['label'] )
		);

		switch ( $field['type'] ) {
			case 'textarea':
				printf(
					'<textarea id="%1$s" name="%2$s" rows="4" class="large-text">%3$s</textarea>',
					esc_attr( $id ),
					esc_attr( $name ),
					esc_textarea( $value )
				);
				break;

			case 'checkbox':
				printf(
					'<label><input type="checkbox" id="%1$s" name="%2$s" value="1" %3$s /> %4$s</label>',
					esc_attr( $id ),
					esc_attr( $name ),
					checked( $value, '1', false ),
					esc_html( $field['label'] )
				);
				break;

			case 'select':
				printf( '<select id="%1$s" name="%2$s">', esc_attr( $id ), esc_attr( $name ) );
				$selected = $value ? $value : 'new';
				foreach ( $field['options'] as $opt_key => $opt_label ) {
					printf(
						'<option value="%1$s" %2$s>%3$s</option>',
						esc_attr( $opt_key ),
						selected( $selected, $opt_key, false ),
						esc_html( $opt_label )
					);
				}
				echo '</select>';
				break;

			case 'gallery':
				$ids = array_filter( array_map( 'absint', explode( ',', (string) $value ) ) );
				printf(
					'<input type="hidden" id="%1$s" name="%2$s" value="%3$s" class="compass-gallery-ids" />',
					esc_attr( $id ),
					esc_attr( $name ),
					esc_attr( implode( ',', $ids ) )
				);
				echo '<span class="compass-gallery-preview">';
				foreach ( $ids as $att_id ) {
					$thumb = wp_get_attachment_image( $att_id, 'thumbnail' );
					if ( $thumb ) {
						echo '<span class="compass-gallery-thumb">' . wp_kses_post( $thumb ) . '</span>';
					}
				}
				echo '</span><br />';
				printf(
					'<button type="button" class="button compass-gallery-pick">%1$s</button> <button type="button" class="button-link compass-gallery-clear">%2$s</button>',
					esc_html__( 'Choose images', 'compass-core' ),
					esc_html__( 'Clear', 'compass-core' )
				);
				break;

			default: // text, url, number, email, tel.
				$input_type = in_array( $field['type'], array( 'url', 'number', 'email', 'tel' ), true ) ? $field['type'] : 'text';
				printf(
					'<input type="%1$s" id="%2$s" name="%3$s" value="%4$s" class="regular-text" />',
					esc_attr( $input_type ),
					esc_attr( $id ),
					esc_attr( $name ),
					esc_attr( $value )
				);
				break;
		}

		if ( ! empty( $field['help'] ) ) {
			echo '<br /><span class="description">' . esc_html( $field['help'] ) . '</span>';
		}
		echo '</p>';
	}
	echo '</div>';
}

/**
 * Save meta box values.
 *
 * @param int $post_id Post being saved.
 */
function compass_core_save_meta( $post_id ) {
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}

	$post_type = get_post_type( $post_id );
	$defs      = compass_core_field_defs();
	if ( empty( $defs[ $post_type ] ) ) {
		return;
	}

	if ( ! isset( $_POST['compass_core_nonce'] ) ||
		! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['compass_core_nonce'] ) ), 'compass_core_save_' . $post_type ) ) {
		return;
	}

	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	foreach ( $defs[ $post_type ] as $field ) {
		$name = '_compass_' . $field['key'];

		if ( 'checkbox' === $field['type'] ) {
			update_post_meta( $post_id, $name, isset( $_POST[ $name ] ) ? '1' : '' );
			continue;
		}

		// Raw value is sanitised per-field in the switch below.
		$raw = isset( $_POST[ $name ] ) ? wp_unslash( $_POST[ $name ] ) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- sanitised per-type immediately below.

		switch ( $field['type'] ) {
			case 'textarea':
				$clean = sanitize_textarea_field( $raw );
				break;
			case 'url':
				$clean = esc_url_raw( $raw );
				break;
			case 'email':
				$clean = sanitize_email( $raw );
				break;
			case 'number':
				$clean = '' === $raw ? '' : (string) absint( $raw );
				break;
			case 'gallery':
				$ids   = array_filter( array_map( 'absint', explode( ',', (string) $raw ) ) );
				$clean = implode( ',', $ids );
				break;
			case 'select':
				$allowed = array_keys( $field['options'] );
				$clean   = in_array( $raw, $allowed, true ) ? $raw : $allowed[0];
				break;
			default:
				$clean = sanitize_text_field( $raw );
				break;
		}

		update_post_meta( $post_id, $name, $clean );
	}

	// A brand-new quote request with no status gets "new".
	if ( 'quote_request' === $post_type && ! get_post_meta( $post_id, '_compass_status', true ) ) {
		update_post_meta( $post_id, '_compass_status', 'new' );
	}
}
add_action( 'save_post', 'compass_core_save_meta' );

/**
 * Enqueue the media picker + a little styling on the Work editor only.
 *
 * @param string $hook Current admin page.
 */
function compass_core_admin_assets( $hook ) {
	if ( ! in_array( $hook, array( 'post.php', 'post-new.php', 'edit.php' ), true ) ) {
		return;
	}
	$screen = get_current_screen();
	if ( ! $screen ) {
		return;
	}

	wp_enqueue_style(
		'compass-core-admin',
		plugins_url( 'assets/admin.css', COMPASS_CORE_FILE ),
		array(),
		COMPASS_CORE_VERSION
	);

	if ( 'edit.php' === $hook ) {
		return; // List screens only need the stylesheet (status pills).
	}

	if ( 'work' === $screen->post_type ) {
		wp_enqueue_media();
		wp_enqueue_script(
			'compass-core-gallery',
			plugins_url( 'assets/gallery.js', COMPASS_CORE_FILE ),
			array( 'jquery' ),
			COMPASS_CORE_VERSION,
			true
		);
		wp_localize_script(
			'compass-core-gallery',
			'compassGallery',
			array(
				'title'  => __( 'Case study gallery', 'compass-core' ),
				'button' => __( 'Use these images', 'compass-core' ),
			)
		);
	}
}
add_action( 'admin_enqueue_scripts', 'compass_core_admin_assets' );
