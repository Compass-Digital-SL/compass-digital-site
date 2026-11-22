<?php
/**
 * Studio — a branded, projects-first admin experience.
 *
 * Gives the owner (and, later, staff) a single URL (`/studio`) and a reskinned,
 * de-cluttered WordPress admin that shows only the things they manage: Projects,
 * Testimonials, Quote Requests and (owner only) Site Settings and the Team page.
 *
 * It is NOT a second login system — it uses WordPress's own secure auth. Two
 * custom roles scope what each person sees:
 *   - studio_owner : manages everything in the Studio, adds/edits staff, sets
 *                    their passwords, and can change their own password.
 *   - studio_staff : manages Projects / Testimonials / Quote Requests only, and
 *                    cannot change or reset any password or add users.
 *
 * Administrators are never affected by the lock-down, so developers keep the
 * full WordPress admin.
 *
 * @package CompassCore
 */

defined( 'ABSPATH' ) || exit;

const COMPASS_STUDIO_VERSION = 2;               // Bump to re-sync roles + rewrite.
const COMPASS_STUDIO_CAP     = 'compass_manage_team';
const COMPASS_STUDIO_PAGE    = 'compass-projects';

/**
 * Capabilities for each Studio role.
 *
 * @return array{studio_owner:array<string,bool>,studio_staff:array<string,bool>}
 */
function compass_studio_role_caps() {
	// Editor-level content caps, scoped to the post types the Studio manages.
	$content = array(
		'read'                   => true,
		'upload_files'           => true,
		'edit_posts'             => true,
		'edit_others_posts'      => true,
		'edit_published_posts'   => true,
		'publish_posts'          => true,
		'delete_posts'           => true,
		'delete_others_posts'    => true,
		'delete_published_posts' => true,
	);

	$staff = $content;

	$owner = array_merge(
		$content,
		array(
			'manage_options'   => true, // Site Settings.
			'list_users'       => true,
			'create_users'     => true,
			'edit_users'       => true,
			'delete_users'     => true,
			'promote_users'    => true,
			COMPASS_STUDIO_CAP => true, // Team management.
		)
	);

	return array(
		'studio_owner' => $owner,
		'studio_staff' => $staff,
	);
}

/**
 * Create / refresh the Studio roles and flush the `/studio` rewrite once per
 * version bump.
 */
function compass_studio_sync() {
	if ( (int) get_option( 'compass_studio_version' ) === COMPASS_STUDIO_VERSION ) {
		return;
	}

	$caps = compass_studio_role_caps();

	remove_role( 'studio_owner' );
	remove_role( 'studio_staff' );
	add_role( 'studio_owner', __( 'Studio Owner', 'compass-core' ), $caps['studio_owner'] );
	add_role( 'studio_staff', __( 'Studio Staff', 'compass-core' ), $caps['studio_staff'] );

	// Make sure real admins can also manage the Team page.
	$admin = get_role( 'administrator' );
	if ( $admin && ! $admin->has_cap( COMPASS_STUDIO_CAP ) ) {
		$admin->add_cap( COMPASS_STUDIO_CAP );
	}

	compass_studio_rewrite();
	flush_rewrite_rules();

	update_option( 'compass_studio_version', COMPASS_STUDIO_VERSION );
}
add_action( 'admin_init', 'compass_studio_sync' );

/**
 * The Studio role of a user, or '' for anyone who is not a Studio user
 * (administrators included — they keep the full admin).
 *
 * @param WP_User|null $user User, or null for the current user.
 * @return string 'owner' | 'staff' | ''.
 */
function compass_studio_role( $user = null ) {
	$user = $user ? $user : wp_get_current_user();
	if ( ! $user || ! $user->exists() ) {
		return '';
	}
	// A real administrator is never treated as a locked-down Studio user.
	if ( in_array( 'administrator', (array) $user->roles, true ) ) {
		return '';
	}
	if ( in_array( 'studio_owner', (array) $user->roles, true ) ) {
		return 'owner';
	}
	if ( in_array( 'studio_staff', (array) $user->roles, true ) ) {
		return 'staff';
	}
	return '';
}

/**
 * Is the given (or current) user a staff member?
 *
 * @param WP_User|null $user User.
 * @return bool
 */
function compass_studio_is_staff( $user = null ) {
	return 'staff' === compass_studio_role( $user );
}

/**
 * The Projects dashboard URL — where the Studio lands.
 *
 * @return string
 */
function compass_studio_home_url() {
	return admin_url( 'admin.php?page=' . COMPASS_STUDIO_PAGE );
}

// --- /studio pretty URL.

/**
 * Register the `/studio` rewrite.
 */
function compass_studio_rewrite() {
	add_rewrite_rule( '^studio/?$', 'index.php?compass_studio=1', 'top' );
}
add_action( 'init', 'compass_studio_rewrite' );

/**
 * Register the query var the rewrite sets.
 *
 * @param array $vars Query vars.
 * @return array
 */
function compass_studio_query_var( $vars ) {
	$vars[] = 'compass_studio';
	return $vars;
}
add_filter( 'query_vars', 'compass_studio_query_var' );

/**
 * Send `/studio` to the dashboard (logged in) or the login (logged out).
 */
function compass_studio_route() {
	if ( ! get_query_var( 'compass_studio' ) ) {
		return;
	}

	if ( is_user_logged_in() && current_user_can( 'edit_posts' ) ) {
		wp_safe_redirect( compass_studio_home_url() );
		exit;
	}

	wp_safe_redirect( wp_login_url( compass_studio_home_url() ) );
	exit;
}
add_action( 'template_redirect', 'compass_studio_route' );

// --- Branded login.

/**
 * Point the login logo at the site home, not wordpress.org.
 *
 * @return string
 */
function compass_studio_login_url() {
	return home_url( '/' );
}
add_filter( 'login_headerurl', 'compass_studio_login_url' );

/**
 * Login logo title text.
 *
 * @return string
 */
function compass_studio_login_text() {
	return get_bloginfo( 'name' );
}
add_filter( 'login_headertext', 'compass_studio_login_text' );

/**
 * Brand the login screen with the site colours and custom logo (if set).
 */
function compass_studio_login_styles() {
	$logo    = '';
	$logo_id = (int) get_theme_mod( 'custom_logo' );
	if ( $logo_id ) {
		$src = wp_get_attachment_image_url( $logo_id, 'full' );
		if ( $src ) {
			$logo = $src;
		}
	}
	?>
	<style>
		body.login {
			background: #0a0c10;
		}
		.login h1 a {
			<?php if ( $logo ) : ?>
			background-image: url( "<?php echo esc_url( $logo ); ?>" );
			background-size: contain;
			width: 220px;
			height: 80px;
			<?php endif; ?>
		}
		.login h1 a,
		.login #backtoblog a,
		.login #nav a {
			color: #f6f2ea;
		}
		.login #nav a:hover,
		.login #backtoblog a:hover {
			color: #1e8a8a;
		}
		.login form {
			background: #12151b;
			border: 1px solid rgba( 246, 242, 234, 0.08 );
			border-radius: 10px;
			box-shadow: 0 10px 40px rgba( 0, 0, 0, 0.5 );
		}
		.login form label,
		.login .forgetmenot label {
			color: #cfd3da;
		}
		.login input[type="text"],
		.login input[type="password"] {
			background: #0a0c10;
			color: #f6f2ea;
			border-color: rgba( 246, 242, 234, 0.15 );
		}
		.wp-core-ui .button-primary {
			background: #1e8a8a;
			border-color: #1e8a8a;
			color: #fff;
		}
		.wp-core-ui .button-primary:hover {
			background: #176d6d;
			border-color: #176d6d;
		}
	</style>
	<?php
}
add_action( 'login_enqueue_scripts', 'compass_studio_login_styles' );

/**
 * After login, send Studio users (and anyone who can edit) to the dashboard.
 *
 * @param string           $redirect Default redirect URL.
 * @param string           $request  Requested redirect.
 * @param WP_User|WP_Error $user     Authenticated user.
 * @return string
 */
function compass_studio_login_redirect( $redirect, $request, $user ) {
	if ( $user instanceof WP_User && compass_studio_role( $user ) ) {
		return compass_studio_home_url();
	}
	return $redirect;
}
add_filter( 'login_redirect', 'compass_studio_login_redirect', 10, 3 );

// --- Staff may not change or reset passwords.

/**
 * Block the "lost password" flow for staff accounts.
 *
 * @param bool $allow   Whether to allow.
 * @param int  $user_id User id.
 * @return bool
 */
function compass_studio_block_reset( $allow, $user_id ) {
	$user = get_userdata( $user_id );
	if ( $user && compass_studio_is_staff( $user ) ) {
		return false;
	}
	return $allow;
}
add_filter( 'allow_password_reset', 'compass_studio_block_reset', 10, 2 );

/**
 * Hide the password-change fields on a staff member's own profile.
 *
 * @param bool    $show Whether to show.
 * @param WP_User $user Profile user.
 * @return bool
 */
function compass_studio_hide_password_fields( $show, $user ) {
	if ( $user && compass_studio_is_staff( $user ) ) {
		return false;
	}
	return $show;
}
add_filter( 'show_password_fields', 'compass_studio_hide_password_fields', 10, 2 );

/**
 * Stop a staff member from reaching the profile / user-edit screens at all
 * (belt-and-braces with the hidden fields above).
 */
function compass_studio_guard_profile() {
	if ( ! compass_studio_is_staff() ) {
		return;
	}
	global $pagenow;
	if ( in_array( $pagenow, array( 'profile.php', 'user-edit.php', 'users.php', 'user-new.php' ), true ) ) {
		wp_safe_redirect( compass_studio_home_url() );
		exit;
	}
}
add_action( 'admin_init', 'compass_studio_guard_profile' );

/**
 * Never let a staff member update their own password through the profile
 * handler, even if the request is forged past the hidden fields.
 *
 * @param array  $errors Errors (by reference via WP).
 * @param bool   $update Whether updating.
 * @param object $user   User object being saved.
 */
function compass_studio_block_staff_pass_update( $errors, $update, $user ) {
	if ( ! $update || empty( $user->ID ) ) {
		return;
	}
	$target = get_userdata( $user->ID );
	if ( $target && compass_studio_is_staff( $target ) && ! current_user_can( COMPASS_STUDIO_CAP ) ) {
		// Strip any password change coming from the user themselves.
		unset( $user->user_pass );
	}
}
add_action( 'user_profile_update_errors', 'compass_studio_block_staff_pass_update', 10, 3 );

// --- De-clutter the admin for Studio users.

/**
 * Hide the admin bar on the front end for Studio users.
 *
 * @param bool $show Whether to show.
 * @return bool
 */
function compass_studio_admin_bar( $show ) {
	if ( compass_studio_role() ) {
		return false;
	}
	return $show;
}
add_filter( 'show_admin_bar', 'compass_studio_admin_bar' );

/**
 * Remove every admin menu except the Studio essentials.
 */
function compass_studio_lockdown_menu() {
	$role = compass_studio_role();
	if ( ! $role ) {
		return;
	}

	$allowed = array(
		'admin.php?page=' . COMPASS_STUDIO_PAGE,
		'edit.php?post_type=testimonial',
		'edit.php?post_type=quote_request',
	);

	if ( 'owner' === $role ) {
		$allowed[] = 'admin.php?page=compass-team';
		$allowed[] = 'options-general.php';
		$allowed[] = 'profile.php';
	}

	global $menu;
	if ( ! is_array( $menu ) ) {
		return;
	}

	$slugs = array();
	foreach ( $menu as $item ) {
		if ( isset( $item[2] ) && '' !== (string) $item[2] && 0 !== strpos( (string) $item[2], 'separator' ) ) {
			$slugs[] = (string) $item[2];
		}
	}

	foreach ( $slugs as $slug ) {
		if ( ! in_array( $slug, $allowed, true ) ) {
			remove_menu_page( $slug );
		}
	}

	// Trim the Settings submenu to just Site Settings (owner only).
	if ( 'owner' === $role ) {
		foreach ( array( 'options-general.php', 'options-writing.php', 'options-reading.php', 'options-discussion.php', 'options-media.php', 'options-permalink.php', 'options-privacy.php' ) as $sub ) {
			remove_submenu_page( 'options-general.php', $sub );
		}
	}
}
add_action( 'admin_menu', 'compass_studio_lockdown_menu', 9999 );

/**
 * An "Account" menu for the owner to change their own password.
 */
function compass_studio_account_menu() {
	if ( 'owner' !== compass_studio_role() ) {
		return;
	}
	add_menu_page(
		__( 'Account', 'compass-core' ),
		__( 'Account', 'compass-core' ),
		'read',
		'profile.php',
		'',
		'dashicons-admin-users',
		70
	);
}
add_action( 'admin_menu', 'compass_studio_account_menu', 9998 );

/**
 * Bounce Studio users off the WordPress dashboard onto Projects, and hide the
 * admin bar inside wp-admin.
 */
function compass_studio_admin_init() {
	if ( ! compass_studio_role() ) {
		return;
	}
	global $pagenow;
	if ( 'index.php' === $pagenow ) {
		wp_safe_redirect( compass_studio_home_url() );
		exit;
	}
}
add_action( 'admin_init', 'compass_studio_admin_init' );

/**
 * Hide the in-admin admin bar + the "Howdy"/WP chrome for Studio users.
 */
function compass_studio_admin_styles() {
	if ( ! compass_studio_role() ) {
		return;
	}
	wp_register_style( 'compass-studio', false, array(), COMPASS_CORE_VERSION );
	wp_enqueue_style( 'compass-studio' );
	$css = '#wpadminbar{display:none!important;}html.wp-toolbar{padding-top:0!important;}#wpfooter{display:none;}';
	wp_add_inline_style( 'compass-studio', $css );
}
add_action( 'admin_enqueue_scripts', 'compass_studio_admin_styles' );
