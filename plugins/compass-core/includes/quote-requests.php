<?php
/**
 * Quote Requests admin experience.
 *
 * Custom list columns, a status filter, and a CSV export. Quote requests are a
 * private post type (see post-types.php) so none of this is ever front-end
 * visible.
 *
 * @package CompassCore
 */

defined( 'ABSPATH' ) || exit;

/**
 * List-table columns for quote requests.
 *
 * @param array $columns Default columns.
 * @return array
 */
function compass_core_quote_columns( $columns ) {
	$new = array(
		'cb'              => isset( $columns['cb'] ) ? $columns['cb'] : '',
		'title'           => __( 'Name', 'compass-core' ),
		'compass_email'   => __( 'Email', 'compass-core' ),
		'compass_phone'   => __( 'Phone', 'compass-core' ),
		'compass_service' => __( 'Service', 'compass-core' ),
		'compass_status'  => __( 'Status', 'compass-core' ),
		'date'            => __( 'Received', 'compass-core' ),
	);
	return $new;
}
add_filter( 'manage_quote_request_posts_columns', 'compass_core_quote_columns' );

/**
 * Render custom columns.
 *
 * @param string $column  Column key.
 * @param int    $post_id Row post ID.
 */
function compass_core_quote_column_content( $column, $post_id ) {
	switch ( $column ) {
		case 'compass_email':
			$email = get_post_meta( $post_id, '_compass_email', true );
			if ( $email ) {
				printf( '<a href="%1$s">%2$s</a>', esc_url( 'mailto:' . $email ), esc_html( $email ) );
			} else {
				echo '&mdash;';
			}
			break;

		case 'compass_phone':
			$phone = get_post_meta( $post_id, '_compass_phone', true );
			echo esc_html( '' !== $phone ? $phone : '—' );
			break;

		case 'compass_service':
			$service = get_post_meta( $post_id, '_compass_service', true );
			echo esc_html( '' !== $service ? $service : '—' );
			break;

		case 'compass_status':
			$status   = get_post_meta( $post_id, '_compass_status', true );
			$status   = $status ? $status : 'new';
			$statuses = compass_core_quote_statuses();
			$label    = isset( $statuses[ $status ] ) ? $statuses[ $status ] : $status;
			printf(
				'<span class="compass-status compass-status--%1$s">%2$s</span>',
				esc_attr( $status ),
				esc_html( $label )
			);
			break;
	}
}
add_action( 'manage_quote_request_posts_custom_column', 'compass_core_quote_column_content', 10, 2 );

/**
 * Add a status filter dropdown and an export button above the list.
 *
 * @param string $post_type Current post type.
 */
function compass_core_quote_filters( $post_type ) {
	if ( 'quote_request' !== $post_type ) {
		return;
	}

	$current = isset( $_GET['compass_status'] ) ? sanitize_key( wp_unslash( $_GET['compass_status'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only filter.

	echo '<select name="compass_status">';
	printf( '<option value="">%s</option>', esc_html__( 'All statuses', 'compass-core' ) );
	foreach ( compass_core_quote_statuses() as $key => $label ) {
		printf(
			'<option value="%1$s" %2$s>%3$s</option>',
			esc_attr( $key ),
			selected( $current, $key, false ),
			esc_html( $label )
		);
	}
	echo '</select>';

	$export_url = wp_nonce_url(
		add_query_arg(
			array(
				'action'         => 'compass_export_quotes',
				'compass_status' => $current,
			),
			admin_url( 'admin-post.php' )
		),
		'compass_export_quotes'
	);
	printf(
		' <a href="%1$s" class="button">%2$s</a>',
		esc_url( $export_url ),
		esc_html__( 'Export CSV', 'compass-core' )
	);
}
add_action( 'restrict_manage_posts', 'compass_core_quote_filters' );

/**
 * Apply the status filter to the admin query.
 *
 * @param WP_Query $query Current query.
 */
function compass_core_quote_filter_query( $query ) {
	if ( ! is_admin() || ! $query->is_main_query() ) {
		return;
	}
	if ( 'quote_request' !== $query->get( 'post_type' ) ) {
		return;
	}
	$status = isset( $_GET['compass_status'] ) ? sanitize_key( wp_unslash( $_GET['compass_status'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only filter.
	if ( $status && array_key_exists( $status, compass_core_quote_statuses() ) ) {
		$meta   = (array) $query->get( 'meta_query' );
		$meta[] = array(
			'key'   => '_compass_status',
			'value' => $status,
		);
		$query->set( 'meta_query', $meta );
	}
}
add_action( 'pre_get_posts', 'compass_core_quote_filter_query' );

/**
 * Stream a CSV of quote requests (optionally filtered by status).
 */
function compass_core_export_quotes() {
	if ( ! current_user_can( 'edit_others_posts' ) ) {
		wp_die( esc_html__( 'You are not allowed to export quote requests.', 'compass-core' ) );
	}
	check_admin_referer( 'compass_export_quotes' );

	$status = isset( $_GET['compass_status'] ) ? sanitize_key( wp_unslash( $_GET['compass_status'] ) ) : '';

	$args = array(
		'post_type'      => 'quote_request',
		'post_status'    => 'any',
		'posts_per_page' => -1,
		'orderby'        => 'date',
		'order'          => 'DESC',
	);
	if ( $status && array_key_exists( $status, compass_core_quote_statuses() ) ) {
		$args['meta_query'] = array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query -- admin export, runs on demand.
			array(
				'key'   => '_compass_status',
				'value' => $status,
			),
		);
	}

	$posts    = get_posts( $args );
	$filename = 'quote-requests-' . gmdate( 'Y-m-d' ) . '.csv';

	nocache_headers();
	header( 'Content-Type: text/csv; charset=utf-8' );
	header( 'Content-Disposition: attachment; filename=' . $filename );

	$out    = fopen( 'php://output', 'w' );
	$fields = array( 'name', 'email', 'phone', 'company', 'service', 'budget', 'status', 'message', 'source' );

	$header = array_merge( array( __( 'Received', 'compass-core' ) ), $fields );
	fputcsv( $out, $header );

	foreach ( $posts as $post ) {
		$row = array( get_the_date( 'Y-m-d H:i', $post ) );
		foreach ( $fields as $field ) {
			$row[] = (string) get_post_meta( $post->ID, '_compass_' . $field, true );
		}
		fputcsv( $out, $row );
	}

	fclose( $out ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fclose -- php://output stream.
	exit;
}
add_action( 'admin_post_compass_export_quotes', 'compass_core_export_quotes' );

/**
 * Make the Received column sortable by date (default) and keep newest first.
 *
 * @param WP_Query $query Current query.
 */
function compass_core_quote_default_order( $query ) {
	if ( ! is_admin() || ! $query->is_main_query() ) {
		return;
	}
	if ( 'quote_request' === $query->get( 'post_type' ) && ! $query->get( 'orderby' ) ) {
		$query->set( 'orderby', 'date' );
		$query->set( 'order', 'DESC' );
	}
}
add_action( 'pre_get_posts', 'compass_core_quote_default_order' );
