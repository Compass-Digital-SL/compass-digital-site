<?php
/**
 * Studio Team — the owner manages staff accounts from inside the Studio.
 *
 * The owner (anyone with the `compass_manage_team` capability) can add a staff
 * member with a username + password, reset a staff password, and remove a staff
 * member. Staff accounts get the `studio_staff` role and can never reach this
 * page, change their own password, or use the lost-password reset (enforced in
 * studio.php).
 *
 * Every create / set-password / remove action only ever targets a `studio_staff`
 * user, so this page can never touch an owner or administrator account.
 *
 * @package CompassCore
 */

defined( 'ABSPATH' ) || exit;

const COMPASS_TEAM_MIN_PASS = 10;

/**
 * Register the Team menu (owner / admin only).
 */
function compass_team_menu() {
	add_menu_page(
		__( 'Team', 'compass-core' ),
		__( 'Team', 'compass-core' ),
		COMPASS_STUDIO_CAP,
		'compass-team',
		'compass_team_render',
		'dashicons-groups',
		21
	);
}
add_action( 'admin_menu', 'compass_team_menu' );

/**
 * Render the Team page.
 */
function compass_team_render() {
	if ( ! current_user_can( COMPASS_STUDIO_CAP ) ) {
		wp_die( esc_html__( 'You are not allowed to manage the team.', 'compass-core' ) );
	}

	$staff = get_users(
		array(
			'role'    => 'studio_staff',
			'orderby' => 'display_name',
			'order'   => 'ASC',
		)
	);
	?>
	<div class="wrap compass-projects compass-team">
		<h1 class="wp-heading-inline"><?php esc_html_e( 'Team', 'compass-core' ); ?></h1>
		<hr class="wp-header-end" />

		<?php compass_team_notices(); ?>

		<p class="description">
			<?php esc_html_e( 'Staff can manage projects, testimonials and quote requests. They cannot change their own password, reset it, or manage the team — only you can set their password here.', 'compass-core' ); ?>
		</p>

		<div class="compass-edit-grid">
			<div class="compass-edit-main">
				<table class="wp-list-table widefat fixed striped">
					<thead>
						<tr>
							<th scope="col"><?php esc_html_e( 'Name', 'compass-core' ); ?></th>
							<th scope="col"><?php esc_html_e( 'Username', 'compass-core' ); ?></th>
							<th scope="col"><?php esc_html_e( 'Email', 'compass-core' ); ?></th>
							<th scope="col"><?php esc_html_e( 'Actions', 'compass-core' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php if ( ! $staff ) : ?>
							<tr><td colspan="4"><?php esc_html_e( 'No staff accounts yet. Add one on the right.', 'compass-core' ); ?></td></tr>
						<?php endif; ?>
						<?php foreach ( $staff as $member ) : ?>
							<?php compass_team_row( $member ); ?>
						<?php endforeach; ?>
					</tbody>
				</table>
			</div>

			<div class="compass-edit-side">
				<div class="compass-box">
					<h2><?php esc_html_e( 'Add a team member', 'compass-core' ); ?></h2>
					<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
						<input type="hidden" name="action" value="compass_team_add" />
						<?php wp_nonce_field( 'compass_team_add', 'compass_team_nonce' ); ?>
						<p class="compass-field">
							<label for="ct-name"><strong><?php esc_html_e( 'Full name', 'compass-core' ); ?></strong></label>
							<input type="text" id="ct-name" name="display_name" class="regular-text" />
						</p>
						<p class="compass-field">
							<label for="ct-user"><strong><?php esc_html_e( 'Username', 'compass-core' ); ?></strong></label>
							<input type="text" id="ct-user" name="user_login" class="regular-text" autocomplete="off" required />
						</p>
						<p class="compass-field">
							<label for="ct-email"><strong><?php esc_html_e( 'Email (optional)', 'compass-core' ); ?></strong></label>
							<input type="email" id="ct-email" name="user_email" class="regular-text" autocomplete="off" />
						</p>
						<p class="compass-field">
							<label for="ct-pass"><strong><?php esc_html_e( 'Password', 'compass-core' ); ?></strong></label>
							<input type="text" id="ct-pass" name="user_pass" class="regular-text" autocomplete="off" required />
							<span class="description">
								<?php
								/* translators: %d: minimum password length. */
								printf( esc_html__( 'At least %d characters. They cannot change it — you set and reset it here.', 'compass-core' ), (int) COMPASS_TEAM_MIN_PASS );
								?>
							</span>
						</p>
						<?php submit_button( __( 'Add member', 'compass-core' ) ); ?>
					</form>
				</div>
			</div>
		</div>
	</div>
	<?php
}

/**
 * Render one staff row with inline set-password + remove forms.
 *
 * @param WP_User $member Staff user.
 */
function compass_team_row( $member ) {
	$id = (int) $member->ID;
	?>
	<tr>
		<td><strong><?php echo esc_html( $member->display_name ); ?></strong></td>
		<td><?php echo esc_html( $member->user_login ); ?></td>
		<td><?php echo esc_html( $member->user_email ); ?></td>
		<td>
			<details class="compass-team-setpass">
				<summary class="button-link"><?php esc_html_e( 'Set password', 'compass-core' ); ?></summary>
				<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="compass-team-inline">
					<input type="hidden" name="action" value="compass_team_setpass" />
					<input type="hidden" name="user_id" value="<?php echo esc_attr( $id ); ?>" />
					<?php wp_nonce_field( 'compass_team_setpass_' . $id, 'compass_team_nonce' ); ?>
					<input type="text" name="user_pass" class="regular-text" autocomplete="off" placeholder="<?php esc_attr_e( 'New password', 'compass-core' ); ?>" required />
					<button type="submit" class="button button-primary"><?php esc_html_e( 'Save', 'compass-core' ); ?></button>
				</form>
			</details>
			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="compass-team-remove" onsubmit="return confirm('<?php echo esc_js( __( 'Remove this team member? Their projects stay, reassigned to you.', 'compass-core' ) ); ?>');">
				<input type="hidden" name="action" value="compass_team_remove" />
				<input type="hidden" name="user_id" value="<?php echo esc_attr( $id ); ?>" />
				<?php wp_nonce_field( 'compass_team_remove_' . $id, 'compass_team_nonce' ); ?>
				<button type="submit" class="button-link delete"><?php esc_html_e( 'Remove', 'compass-core' ); ?></button>
			</form>
		</td>
	</tr>
	<?php
}

/**
 * Guard: the actor can manage the team and (for existing targets) the target is
 * a staff account. Dies on failure.
 *
 * @param string $nonce_action Nonce action to verify.
 * @param int    $target_id    Target user id, or 0 for create.
 */
function compass_team_guard( $nonce_action, $target_id = 0 ) {
	if ( ! current_user_can( COMPASS_STUDIO_CAP ) ) {
		wp_die( esc_html__( 'You are not allowed to manage the team.', 'compass-core' ) );
	}
	if ( ! isset( $_POST['compass_team_nonce'] ) ||
		! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['compass_team_nonce'] ) ), $nonce_action ) ) {
		wp_die( esc_html__( 'Security check failed.', 'compass-core' ) );
	}
	if ( $target_id ) {
		$target = get_userdata( $target_id );
		if ( ! $target || ! in_array( 'studio_staff', (array) $target->roles, true ) ) {
			wp_die( esc_html__( 'That account is not a staff member.', 'compass-core' ) );
		}
	}
}

/**
 * Redirect back to the Team page with a result code.
 *
 * @param string $code Result code.
 */
function compass_team_redirect( $code ) {
	wp_safe_redirect(
		add_query_arg(
			array(
				'page'           => 'compass-team',
				'compass_result' => $code,
			),
			admin_url( 'admin.php' )
		)
	);
	exit;
}

/**
 * Handle adding a staff member.
 */
function compass_team_add() {
	compass_team_guard( 'compass_team_add' );

	// phpcs:disable WordPress.Security.NonceVerification.Missing -- nonce verified in compass_team_guard() above.
	$login = isset( $_POST['user_login'] ) ? sanitize_user( wp_unslash( $_POST['user_login'] ), true ) : '';
	$name  = isset( $_POST['display_name'] ) ? sanitize_text_field( wp_unslash( $_POST['display_name'] ) ) : '';
	$email = isset( $_POST['user_email'] ) ? sanitize_email( wp_unslash( $_POST['user_email'] ) ) : '';
	// Raw password (never altered); validated for length only.
	$pass = isset( $_POST['user_pass'] ) ? (string) wp_unslash( $_POST['user_pass'] ) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- a password must be stored verbatim.
	// phpcs:enable WordPress.Security.NonceVerification.Missing

	if ( '' === $login || ! validate_username( $login ) || username_exists( $login ) ) {
		compass_team_redirect( 'bad_user' );
	}
	if ( '' !== $email && ( ! is_email( $email ) || email_exists( $email ) ) ) {
		compass_team_redirect( 'bad_email' );
	}
	if ( strlen( $pass ) < COMPASS_TEAM_MIN_PASS ) {
		compass_team_redirect( 'bad_pass' );
	}

	$user_id = wp_insert_user(
		array(
			'user_login'   => $login,
			'user_pass'    => $pass,
			'user_email'   => $email,
			'display_name' => '' !== $name ? $name : $login,
			'nickname'     => '' !== $name ? $name : $login,
			'role'         => 'studio_staff',
		)
	);

	if ( is_wp_error( $user_id ) ) {
		compass_team_redirect( 'error' );
	}

	compass_team_redirect( 'added' );
}
add_action( 'admin_post_compass_team_add', 'compass_team_add' );

/**
 * Handle setting a staff member's password.
 */
function compass_team_setpass() {
	$id = isset( $_POST['user_id'] ) ? absint( wp_unslash( $_POST['user_id'] ) ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Missing -- nonce verified in compass_team_guard() below.
	compass_team_guard( 'compass_team_setpass_' . $id, $id );

	$pass = isset( $_POST['user_pass'] ) ? (string) wp_unslash( $_POST['user_pass'] ) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized,WordPress.Security.NonceVerification.Missing -- password stored verbatim; nonce verified in compass_team_guard().
	if ( strlen( $pass ) < COMPASS_TEAM_MIN_PASS ) {
		compass_team_redirect( 'bad_pass' );
	}

	wp_set_password( $pass, $id );
	compass_team_redirect( 'passset' );
}
add_action( 'admin_post_compass_team_setpass', 'compass_team_setpass' );

/**
 * Handle removing a staff member (their content is reassigned to the actor).
 */
function compass_team_remove() {
	$id = isset( $_POST['user_id'] ) ? absint( wp_unslash( $_POST['user_id'] ) ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Missing -- nonce verified in compass_team_guard() below.
	compass_team_guard( 'compass_team_remove_' . $id, $id );

	require_once ABSPATH . 'wp-admin/includes/user.php';
	wp_delete_user( $id, get_current_user_id() );
	compass_team_redirect( 'removed' );
}
add_action( 'admin_post_compass_team_remove', 'compass_team_remove' );

/**
 * Result notices on the Team page.
 */
function compass_team_notices() {
	// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only result flag on a redirect.
	$code = isset( $_GET['compass_result'] ) ? sanitize_key( wp_unslash( $_GET['compass_result'] ) ) : '';
	if ( '' === $code ) {
		return;
	}

	$map = array(
		'added'     => array( 'success', __( 'Team member added.', 'compass-core' ) ),
		'passset'   => array( 'success', __( 'Password updated.', 'compass-core' ) ),
		'removed'   => array( 'success', __( 'Team member removed.', 'compass-core' ) ),
		'bad_user'  => array( 'error', __( 'That username is invalid or already taken.', 'compass-core' ) ),
		'bad_email' => array( 'error', __( 'That email is invalid or already in use.', 'compass-core' ) ),
		'bad_pass'  => array( 'error', __( 'The password is too short.', 'compass-core' ) ),
		'error'     => array( 'error', __( 'Something went wrong. Please try again.', 'compass-core' ) ),
	);

	if ( ! isset( $map[ $code ] ) ) {
		return;
	}

	printf(
		'<div class="notice notice-%1$s is-dismissible"><p>%2$s</p></div>',
		esc_attr( $map[ $code ][0] ),
		esc_html( $map[ $code ][1] )
	);
}
