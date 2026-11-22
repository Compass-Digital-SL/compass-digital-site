<?php
/**
 * Projects dashboard — a custom admin surface for managing Work (case studies).
 *
 * This sits alongside the native Work editor (which stays registered, so
 * revisions, autosave and the media library keep working). It gives the owner a
 * single place to list every project, toggle Featured / published inline, and
 * create or edit a project on one screen — with the Gemini "Draft with AI"
 * helpers (see ai-assist.php) wired into each copy field.
 *
 * Data is written through the same post + `_compass_*` meta + taxonomy terms the
 * native editor uses, so the front end is unchanged.
 *
 * @package CompassCore
 */

defined( 'ABSPATH' ) || exit;

/**
 * Register the top-level "Projects" menu.
 */
function compass_projects_menu() {
	$hook = add_menu_page(
		__( 'Projects', 'compass-core' ),
		__( 'Projects', 'compass-core' ),
		'edit_posts',
		'compass-projects',
		'compass_projects_render',
		'dashicons-portfolio',
		19
	);
	add_action( 'load-' . $hook, 'compass_projects_handle_actions' );
}
add_action( 'admin_menu', 'compass_projects_menu' );

/**
 * Register the SEO meta description used by the theme's meta tag.
 */
function compass_projects_register_meta() {
	register_post_meta(
		'work',
		'_compass_seo_desc',
		array(
			'type'          => 'string',
			'single'        => true,
			'show_in_rest'  => false,
			'auth_callback' => function () {
				return current_user_can( 'edit_posts' );
			},
		)
	);
}
add_action( 'init', 'compass_projects_register_meta' );

/**
 * Enqueue dashboard assets only on the Projects page.
 *
 * @param string $hook Current admin page hook suffix.
 */
function compass_projects_assets( $hook ) {
	if ( 'toplevel_page_compass-projects' !== $hook ) {
		return;
	}

	wp_enqueue_style(
		'compass-core-admin',
		plugins_url( 'assets/admin.css', COMPASS_CORE_FILE ),
		array(),
		COMPASS_CORE_VERSION
	);

	// The edit view uses the media frame (featured image + gallery) and the
	// classic editor for the body.
	$view = isset( $_GET['view'] ) ? sanitize_key( wp_unslash( $_GET['view'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only view switch.
	if ( 'edit' === $view ) {
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

	wp_enqueue_script(
		'compass-core-projects',
		plugins_url( 'assets/projects.js', COMPASS_CORE_FILE ),
		array(),
		COMPASS_CORE_VERSION,
		true
	);
	wp_localize_script(
		'compass-core-projects',
		'compassProjects',
		array(
			'ajaxUrl'     => admin_url( 'admin-ajax.php' ),
			'aiEndpoint'  => esc_url_raw( rest_url( 'compass/v1/ai/draft' ) ),
			'restNonce'   => wp_create_nonce( 'wp_rest' ),
			'toggleNonce' => wp_create_nonce( 'compass_projects_toggle' ),
			'mediaTitle'  => __( 'Choose featured image', 'compass-core' ),
			'mediaButton' => __( 'Use this image', 'compass-core' ),
			'i18n'        => array(
				'drafting' => __( 'Drafting…', 'compass-core' ),
				'draft'    => __( 'Draft with AI', 'compass-core' ),
				'insert'   => __( 'Insert', 'compass-core' ),
				'replace'  => __( 'Replace', 'compass-core' ),
				'error'    => __( 'Something went wrong.', 'compass-core' ),
			),
		)
	);
}
add_action( 'admin_enqueue_scripts', 'compass_projects_assets' );

/**
 * Handle trash / restore actions (nonce-checked) before the page renders.
 */
function compass_projects_handle_actions() {
	$action = isset( $_GET['action'] ) ? sanitize_key( wp_unslash( $_GET['action'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- nonce checked per-branch below.
	$id     = isset( $_GET['id'] ) ? absint( wp_unslash( $_GET['id'] ) ) : 0;

	if ( ! $action || ! $id ) {
		return;
	}

	if ( 'trash' === $action ) {
		check_admin_referer( 'compass_project_trash_' . $id );
		if ( current_user_can( 'delete_post', $id ) ) {
			wp_trash_post( $id );
		}
		wp_safe_redirect(
			add_query_arg(
				array(
					'page'    => 'compass-projects',
					'trashed' => 1,
				),
				admin_url( 'admin.php' )
			)
		);
		exit;
	}

	if ( 'restore' === $action ) {
		check_admin_referer( 'compass_project_restore_' . $id );
		if ( current_user_can( 'edit_post', $id ) ) {
			wp_untrash_post( $id );
		}
		wp_safe_redirect(
			add_query_arg(
				array(
					'page'     => 'compass-projects',
					'restored' => 1,
				),
				admin_url( 'admin.php' )
			)
		);
		exit;
	}
}

/**
 * Route the Projects page to the list or the editor.
 */
function compass_projects_render() {
	if ( ! current_user_can( 'edit_posts' ) ) {
		wp_die( esc_html__( 'You are not allowed to manage projects.', 'compass-core' ) );
	}

	$view = isset( $_GET['view'] ) ? sanitize_key( wp_unslash( $_GET['view'] ) ) : 'list'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only view switch.

	if ( 'edit' === $view ) {
		compass_projects_render_edit();
		return;
	}

	compass_projects_render_list();
}

/**
 * List view: every project, with filters and inline toggles.
 */
function compass_projects_render_list() {
	// phpcs:disable WordPress.Security.NonceVerification.Recommended -- read-only, shareable GET filters.
	$search   = isset( $_GET['s'] ) ? sanitize_text_field( wp_unslash( $_GET['s'] ) ) : '';
	$service  = isset( $_GET['service'] ) ? sanitize_title( wp_unslash( $_GET['service'] ) ) : '';
	$industry = isset( $_GET['industry'] ) ? sanitize_title( wp_unslash( $_GET['industry'] ) ) : '';
	$pstatus  = isset( $_GET['pstatus'] ) ? sanitize_key( wp_unslash( $_GET['pstatus'] ) ) : '';
	// phpcs:enable WordPress.Security.NonceVerification.Recommended

	$statuses = in_array( $pstatus, array( 'publish', 'draft', 'pending', 'future', 'trash' ), true )
		? array( $pstatus )
		: array( 'publish', 'draft', 'pending', 'future' );

	$args = array(
		'post_type'      => 'work',
		'post_status'    => $statuses,
		'posts_per_page' => 200, // phpcs:ignore WordPress.WP.PostsPerPage.posts_per_page_posts_per_page -- bounded admin list, no pagination.
		'orderby'        => 'modified',
		'order'          => 'DESC',
		's'              => $search,
	);

	$tax_query = array();
	if ( $service ) {
		$tax_query[] = array(
			'taxonomy' => 'service',
			'field'    => 'slug',
			'terms'    => $service,
		);
	}
	if ( $industry ) {
		$tax_query[] = array(
			'taxonomy' => 'industry',
			'field'    => 'slug',
			'terms'    => $industry,
		);
	}
	if ( $tax_query ) {
		$args['tax_query'] = $tax_query; // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query -- admin list, bounded.
	}

	$projects = get_posts( $args );
	$add_url  = add_query_arg(
		array(
			'page' => 'compass-projects',
			'view' => 'edit',
			'new'  => 1,
		),
		admin_url( 'admin.php' )
	);
	?>
	<div class="wrap compass-projects">
		<h1 class="wp-heading-inline"><?php esc_html_e( 'Projects', 'compass-core' ); ?></h1>
		<a href="<?php echo esc_url( $add_url ); ?>" class="page-title-action"><?php esc_html_e( 'Add project', 'compass-core' ); ?></a>
		<hr class="wp-header-end" />

		<?php compass_projects_admin_notices(); ?>

		<form method="get" class="compass-projects-filters">
			<input type="hidden" name="page" value="compass-projects" />
			<p class="search-box">
				<label class="screen-reader-text" for="compass-project-search"><?php esc_html_e( 'Search projects', 'compass-core' ); ?></label>
				<input type="search" id="compass-project-search" name="s" value="<?php echo esc_attr( $search ); ?>" placeholder="<?php esc_attr_e( 'Search projects', 'compass-core' ); ?>" />
				<?php compass_projects_tax_dropdown( 'service', $service ); ?>
				<?php compass_projects_tax_dropdown( 'industry', $industry ); ?>
				<?php compass_projects_status_dropdown( $pstatus ); ?>
				<button type="submit" class="button"><?php esc_html_e( 'Filter', 'compass-core' ); ?></button>
			</p>
		</form>

		<table class="wp-list-table widefat fixed striped compass-projects-table">
			<thead>
				<tr>
					<th scope="col" class="column-thumb"><?php esc_html_e( 'Image', 'compass-core' ); ?></th>
					<th scope="col"><?php esc_html_e( 'Title', 'compass-core' ); ?></th>
					<th scope="col"><?php esc_html_e( 'Client', 'compass-core' ); ?></th>
					<th scope="col"><?php esc_html_e( 'Year', 'compass-core' ); ?></th>
					<th scope="col"><?php esc_html_e( 'Featured', 'compass-core' ); ?></th>
					<th scope="col"><?php esc_html_e( 'Status', 'compass-core' ); ?></th>
					<th scope="col"><?php esc_html_e( 'Updated', 'compass-core' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php if ( ! $projects ) : ?>
					<tr>
						<td colspan="7"><?php esc_html_e( 'No projects yet. Add your first one.', 'compass-core' ); ?></td>
					</tr>
				<?php endif; ?>
				<?php foreach ( $projects as $project ) : ?>
					<?php compass_projects_row( $project ); ?>
				<?php endforeach; ?>
			</tbody>
		</table>
	</div>
	<?php
}

/**
 * Render one project row.
 *
 * @param WP_Post $project Work post.
 */
function compass_projects_row( $project ) {
	$id        = (int) $project->ID;
	$edit_url  = add_query_arg(
		array(
			'page' => 'compass-projects',
			'view' => 'edit',
			'id'   => $id,
		),
		admin_url( 'admin.php' )
	);
	$client    = (string) get_post_meta( $id, '_compass_client', true );
	$year      = (string) get_post_meta( $id, '_compass_year', true );
	$featured  = '1' === (string) get_post_meta( $id, '_compass_featured', true );
	$published = 'publish' === $project->post_status;
	$thumb     = get_the_post_thumbnail( $id, array( 60, 60 ) );

	$trash_url = wp_nonce_url(
		add_query_arg(
			array(
				'page'   => 'compass-projects',
				'action' => 'trash',
				'id'     => $id,
			),
			admin_url( 'admin.php' )
		),
		'compass_project_trash_' . $id
	);
	?>
	<tr>
		<td class="column-thumb">
			<?php echo $thumb ? wp_kses_post( $thumb ) : '<span class="compass-thumb-empty" aria-hidden="true"></span>'; ?>
		</td>
		<td>
			<strong><a href="<?php echo esc_url( $edit_url ); ?>"><?php echo esc_html( get_the_title( $id ) ? get_the_title( $id ) : __( '(no title)', 'compass-core' ) ); ?></a></strong>
			<div class="row-actions">
				<span class="edit"><a href="<?php echo esc_url( $edit_url ); ?>"><?php esc_html_e( 'Edit', 'compass-core' ); ?></a> | </span>
				<span class="view"><a href="<?php echo esc_url( get_permalink( $id ) ); ?>" target="_blank" rel="noopener"><?php esc_html_e( 'View', 'compass-core' ); ?></a> | </span>
				<span class="trash"><a href="<?php echo esc_url( $trash_url ); ?>" class="submitdelete"><?php esc_html_e( 'Trash', 'compass-core' ); ?></a></span>
			</div>
		</td>
		<td><?php echo esc_html( $client ); ?></td>
		<td><?php echo esc_html( $year ); ?></td>
		<td>
			<button type="button" class="button-link compass-toggle compass-toggle--featured <?php echo $featured ? 'is-on' : ''; ?>"
				data-id="<?php echo esc_attr( $id ); ?>" data-which="featured"
				aria-pressed="<?php echo $featured ? 'true' : 'false'; ?>">
				<span class="compass-toggle-yes"><?php esc_html_e( 'Featured', 'compass-core' ); ?></span>
				<span class="compass-toggle-no"><?php esc_html_e( 'Not featured', 'compass-core' ); ?></span>
			</button>
		</td>
		<td>
			<button type="button" class="button-link compass-toggle compass-toggle--status <?php echo $published ? 'is-on' : ''; ?>"
				data-id="<?php echo esc_attr( $id ); ?>" data-which="status"
				aria-pressed="<?php echo $published ? 'true' : 'false'; ?>">
				<span class="compass-toggle-yes"><?php esc_html_e( 'Published', 'compass-core' ); ?></span>
				<span class="compass-toggle-no"><?php esc_html_e( 'Draft', 'compass-core' ); ?></span>
			</button>
		</td>
		<td><?php echo esc_html( get_the_modified_date( 'Y-m-d', $id ) ); ?></td>
	</tr>
	<?php
}

/**
 * A taxonomy filter dropdown.
 *
 * @param string $taxonomy Taxonomy slug.
 * @param string $current  Selected term slug.
 */
function compass_projects_tax_dropdown( $taxonomy, $current ) {
	$terms = get_terms(
		array(
			'taxonomy'   => $taxonomy,
			'hide_empty' => false,
		)
	);
	if ( is_wp_error( $terms ) || ! $terms ) {
		return;
	}
	$tax_obj = get_taxonomy( $taxonomy );
	$label   = $tax_obj ? $tax_obj->labels->singular_name : $taxonomy;
	printf( '<label class="screen-reader-text" for="compass-filter-%1$s">%2$s</label>', esc_attr( $taxonomy ), esc_html( $label ) );
	printf( '<select id="compass-filter-%1$s" name="%1$s">', esc_attr( $taxonomy ) );
	/* translators: %s: taxonomy name (Service/Industry). */
	printf( '<option value="">%s</option>', esc_html( sprintf( __( 'All %s', 'compass-core' ), $label ) ) );
	foreach ( $terms as $term ) {
		printf(
			'<option value="%1$s" %2$s>%3$s</option>',
			esc_attr( $term->slug ),
			selected( $current, $term->slug, false ),
			esc_html( $term->name )
		);
	}
	echo '</select>';
}

/**
 * The status filter dropdown.
 *
 * @param string $current Selected status.
 */
function compass_projects_status_dropdown( $current ) {
	$options = array(
		''        => __( 'All statuses', 'compass-core' ),
		'publish' => __( 'Published', 'compass-core' ),
		'draft'   => __( 'Draft', 'compass-core' ),
		'pending' => __( 'Pending', 'compass-core' ),
		'trash'   => __( 'Trash', 'compass-core' ),
	);
	echo '<label class="screen-reader-text" for="compass-filter-status">' . esc_html__( 'Status', 'compass-core' ) . '</label>';
	echo '<select id="compass-filter-status" name="pstatus">';
	foreach ( $options as $value => $label ) {
		printf(
			'<option value="%1$s" %2$s>%3$s</option>',
			esc_attr( $value ),
			selected( $current, $value, false ),
			esc_html( $label )
		);
	}
	echo '</select>';
}

/**
 * Edit / create view.
 */
function compass_projects_render_edit() {
	$new = ! empty( $_GET['new'] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only view switch.
	$id  = isset( $_GET['id'] ) ? absint( wp_unslash( $_GET['id'] ) ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

	$post = null;
	if ( $id ) {
		$post = get_post( $id );
		if ( ! $post || 'work' !== $post->post_type ) {
			wp_die( esc_html__( 'Project not found.', 'compass-core' ) );
		}
		if ( ! current_user_can( 'edit_post', $id ) ) {
			wp_die( esc_html__( 'You are not allowed to edit this project.', 'compass-core' ) );
		}
	}

	$title    = $post ? $post->post_title : '';
	$slug     = $post ? $post->post_name : '';
	$excerpt  = $post ? $post->post_excerpt : '';
	$content  = $post ? $post->post_content : '';
	$status   = $post ? $post->post_status : 'draft';
	$thumb_id = $post ? (int) get_post_thumbnail_id( $id ) : 0;
	$seo_desc = $post ? (string) get_post_meta( $id, '_compass_seo_desc', true ) : '';

	$defs       = compass_core_field_defs();
	$fields     = isset( $defs['work'] ) ? $defs['work'] : array();
	$list_url   = add_query_arg( array( 'page' => 'compass-projects' ), admin_url( 'admin.php' ) );
	$page_title = $new ? __( 'Add project', 'compass-core' ) : __( 'Edit project', 'compass-core' );
	?>
	<div class="wrap compass-projects compass-project-edit">
		<h1 class="wp-heading-inline"><?php echo esc_html( $page_title ); ?></h1>
		<a href="<?php echo esc_url( $list_url ); ?>" class="page-title-action"><?php esc_html_e( 'Back to projects', 'compass-core' ); ?></a>
		<hr class="wp-header-end" />

		<?php compass_projects_admin_notices(); ?>

		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="compass-project-form">
			<input type="hidden" name="action" value="compass_project_save" />
			<input type="hidden" name="project_id" value="<?php echo esc_attr( $id ); ?>" />
			<?php wp_nonce_field( 'compass_project_save_' . $id, 'compass_project_nonce' ); ?>

			<div class="compass-edit-grid">
				<div class="compass-edit-main">
					<p class="compass-field">
						<label for="compass-title"><strong><?php esc_html_e( 'Title', 'compass-core' ); ?></strong></label>
						<input type="text" id="compass-title" name="post_title" class="large-text" value="<?php echo esc_attr( $title ); ?>" data-ai-context="title" />
					</p>

					<p class="compass-field">
						<label for="compass-slug"><strong><?php esc_html_e( 'Slug', 'compass-core' ); ?></strong></label>
						<input type="text" id="compass-slug" name="post_name" class="regular-text" value="<?php echo esc_attr( $slug ); ?>" />
						<span class="description"><?php esc_html_e( 'Leave blank to generate from the title.', 'compass-core' ); ?></span>
					</p>

					<?php
					// Structured case-study fields, with AI helpers on the copy ones.
					$ai_fields = array( 'problem', 'approach', 'solution', 'results' );
					foreach ( $fields as $field ) {
						compass_projects_render_field( $field, $post ? $id : 0, in_array( $field['key'], $ai_fields, true ) );
					}
					?>

					<p class="compass-field compass-field--ai">
						<label for="compass-excerpt"><strong><?php esc_html_e( 'Summary / excerpt', 'compass-core' ); ?></strong></label>
						<?php compass_projects_ai_button( 'summary', 'compass-excerpt' ); ?>
						<textarea id="compass-excerpt" name="post_excerpt" rows="3" class="large-text" data-ai-field="summary"><?php echo esc_textarea( $excerpt ); ?></textarea>
						<span class="description"><?php esc_html_e( 'Shown on project cards and used as the page meta description if no SEO description is set.', 'compass-core' ); ?></span>
					</p>

					<div class="compass-field compass-field--ai compass-field--body">
						<label for="compass-body"><strong><?php esc_html_e( 'Case study body', 'compass-core' ); ?></strong></label>
						<?php compass_projects_ai_button( 'body', 'compass-body' ); ?>
						<?php
						wp_editor(
							$content,
							'compass-body',
							array(
								'textarea_name' => 'post_content',
								'media_buttons' => true,
								'textarea_rows' => 12,
							)
						);
						?>
					</div>
				</div>

				<div class="compass-edit-side">
					<div class="compass-box">
						<h2><?php esc_html_e( 'Publish', 'compass-core' ); ?></h2>
						<p class="compass-field">
							<label for="compass-status"><strong><?php esc_html_e( 'Status', 'compass-core' ); ?></strong></label>
							<select id="compass-status" name="post_status">
								<option value="draft" <?php selected( $status, 'draft' ); ?>><?php esc_html_e( 'Draft', 'compass-core' ); ?></option>
								<option value="pending" <?php selected( $status, 'pending' ); ?>><?php esc_html_e( 'Pending review', 'compass-core' ); ?></option>
								<option value="publish" <?php selected( $status, 'publish' ); ?>><?php esc_html_e( 'Published', 'compass-core' ); ?></option>
							</select>
						</p>
						<?php submit_button( $new ? __( 'Create project', 'compass-core' ) : __( 'Save project', 'compass-core' ) ); ?>
					</div>

					<div class="compass-box">
						<h2><?php esc_html_e( 'Featured image', 'compass-core' ); ?></h2>
						<div class="compass-thumb-wrap">
							<input type="hidden" id="compass-thumb-id" name="_thumbnail_id" value="<?php echo esc_attr( $thumb_id ); ?>" />
							<div class="compass-thumb-preview">
								<?php echo $thumb_id ? wp_get_attachment_image( $thumb_id, 'medium' ) : ''; ?>
							</div>
							<button type="button" class="button compass-thumb-pick"><?php esc_html_e( 'Choose image', 'compass-core' ); ?></button>
							<button type="button" class="button-link compass-thumb-clear"><?php esc_html_e( 'Remove', 'compass-core' ); ?></button>
						</div>
					</div>

					<?php compass_projects_render_terms_box( 'service', $id ); ?>
					<?php compass_projects_render_terms_box( 'industry', $id ); ?>

					<div class="compass-box">
						<h2><?php esc_html_e( 'SEO', 'compass-core' ); ?></h2>
						<p class="compass-field compass-field--ai">
							<label for="compass-seo"><strong><?php esc_html_e( 'Meta description', 'compass-core' ); ?></strong></label>
							<?php compass_projects_ai_button( 'meta', 'compass-seo' ); ?>
							<textarea id="compass-seo" name="_compass_seo_desc" rows="3" class="large-text" maxlength="200" data-ai-field="meta"><?php echo esc_textarea( $seo_desc ); ?></textarea>
							<span class="description"><?php esc_html_e( 'About 150 characters. Overrides the excerpt for the search snippet.', 'compass-core' ); ?></span>
						</p>
					</div>
				</div>
			</div>
		</form>
	</div>
	<?php
}

/**
 * Render one structured case-study field in the editor.
 *
 * @param array $field    Field definition from compass_core_field_defs().
 * @param int   $post_id  Post id (0 for new).
 * @param bool  $ai       Whether to show an AI "Draft" button.
 */
function compass_projects_render_field( $field, $post_id, $ai ) {
	$key   = $field['key'];
	$name  = '_compass_' . $key;
	$id    = 'compass-' . $key;
	$value = $post_id ? get_post_meta( $post_id, $name, true ) : '';
	$type  = $field['type'];

	// Context fields (client, tech, year…) feed the AI prompt via data-ai-context.
	$context_keys = array( 'client', 'year', 'tech', 'problem', 'approach', 'solution', 'results' );
	$ctx_attr     = in_array( $key, $context_keys, true ) ? ' data-ai-context="' . esc_attr( $key ) . '"' : '';

	echo '<div class="compass-field compass-field--' . esc_attr( $type ) . ( $ai ? ' compass-field--ai' : '' ) . '">';
	printf( '<label for="%1$s"><strong>%2$s</strong></label>', esc_attr( $id ), esc_html( $field['label'] ) );

	if ( $ai ) {
		compass_projects_ai_button( $key, $id );
	}

	switch ( $type ) {
		case 'textarea':
			printf(
				'<textarea id="%1$s" name="%2$s" rows="4" class="large-text"%4$s data-ai-field="%5$s">%3$s</textarea>',
				esc_attr( $id ),
				esc_attr( $name ),
				esc_textarea( $value ),
				$ctx_attr, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- pre-escaped above.
				esc_attr( $key )
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
				$t = wp_get_attachment_image( $att_id, 'thumbnail' );
				if ( $t ) {
					echo '<span class="compass-gallery-thumb">' . wp_kses_post( $t ) . '</span>';
				}
			}
			echo '</span><br />';
			printf(
				'<button type="button" class="button compass-gallery-pick">%1$s</button> <button type="button" class="button-link compass-gallery-clear">%2$s</button>',
				esc_html__( 'Choose images', 'compass-core' ),
				esc_html__( 'Clear', 'compass-core' )
			);
			break;

		default: // text, url, number.
			$input_type = in_array( $type, array( 'url', 'number', 'email', 'tel' ), true ) ? $type : 'text';
			printf(
				'<input type="%1$s" id="%2$s" name="%3$s" value="%4$s" class="regular-text"%5$s />',
				esc_attr( $input_type ),
				esc_attr( $id ),
				esc_attr( $name ),
				esc_attr( $value ),
				$ctx_attr // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- pre-escaped above.
			);
			break;
	}

	if ( ! empty( $field['help'] ) ) {
		echo '<span class="description">' . esc_html( $field['help'] ) . '</span>';
	}

	echo '</div>';
}

/**
 * Print an AI "Draft with AI" button bound to a field.
 *
 * @param string $field    AI field key (problem|approach|solution|results|summary|body|meta).
 * @param string $target_id DOM id of the input/textarea/editor to fill.
 */
function compass_projects_ai_button( $field, $target_id ) {
	printf(
		'<button type="button" class="button compass-ai-btn" data-ai-draft="%1$s" data-ai-target="%2$s">%3$s</button>',
		esc_attr( $field ),
		esc_attr( $target_id ),
		esc_html__( 'Draft with AI', 'compass-core' )
	);
}

/**
 * Render a taxonomy checkbox box (service / industry).
 *
 * @param string $taxonomy Taxonomy slug.
 * @param int    $post_id  Post id (0 for new).
 */
function compass_projects_render_terms_box( $taxonomy, $post_id ) {
	$terms = get_terms(
		array(
			'taxonomy'   => $taxonomy,
			'hide_empty' => false,
		)
	);
	if ( is_wp_error( $terms ) || ! $terms ) {
		return;
	}
	$tax_obj  = get_taxonomy( $taxonomy );
	$label    = $tax_obj ? $tax_obj->labels->name : $taxonomy;
	$selected = $post_id ? wp_get_object_terms( $post_id, $taxonomy, array( 'fields' => 'ids' ) ) : array();
	$selected = is_wp_error( $selected ) ? array() : $selected;
	?>
	<div class="compass-box">
		<h2><?php echo esc_html( $label ); ?></h2>
		<ul class="compass-term-list">
			<?php foreach ( $terms as $term ) : ?>
				<li>
					<label>
						<input type="checkbox" name="compass_tax[<?php echo esc_attr( $taxonomy ); ?>][]" value="<?php echo esc_attr( $term->term_id ); ?>" <?php checked( in_array( (int) $term->term_id, array_map( 'intval', $selected ), true ) ); ?> />
						<?php echo esc_html( $term->name ); ?>
					</label>
				</li>
			<?php endforeach; ?>
		</ul>
	</div>
	<?php
}

/**
 * Save a project (create or update) from the dashboard form.
 */
function compass_projects_save() {
	$id = isset( $_POST['project_id'] ) ? absint( wp_unslash( $_POST['project_id'] ) ) : 0;

	if ( ! isset( $_POST['compass_project_nonce'] ) ||
		! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['compass_project_nonce'] ) ), 'compass_project_save_' . $id ) ) {
		wp_die( esc_html__( 'Security check failed.', 'compass-core' ) );
	}

	$cap = $id ? 'edit_post' : 'edit_posts';
	if ( ( $id && ! current_user_can( $cap, $id ) ) || ( ! $id && ! current_user_can( 'edit_posts' ) ) ) {
		wp_die( esc_html__( 'You are not allowed to save this project.', 'compass-core' ) );
	}

	$status  = isset( $_POST['post_status'] ) ? sanitize_key( wp_unslash( $_POST['post_status'] ) ) : 'draft';
	$status  = in_array( $status, array( 'draft', 'pending', 'publish' ), true ) ? $status : 'draft';
	$title   = isset( $_POST['post_title'] ) ? sanitize_text_field( wp_unslash( $_POST['post_title'] ) ) : '';
	$slug    = isset( $_POST['post_name'] ) ? sanitize_title( wp_unslash( $_POST['post_name'] ) ) : '';
	$excerpt = isset( $_POST['post_excerpt'] ) ? sanitize_textarea_field( wp_unslash( $_POST['post_excerpt'] ) ) : '';
	// Body may contain HTML; allow the standard post-content set.
	$content = isset( $_POST['post_content'] ) ? wp_kses_post( wp_unslash( $_POST['post_content'] ) ) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- wp_kses_post sanitises.

	$postarr = array(
		'post_type'    => 'work',
		'post_title'   => $title,
		'post_excerpt' => $excerpt,
		'post_content' => $content,
		'post_status'  => $status,
	);
	if ( '' !== $slug ) {
		$postarr['post_name'] = $slug;
	}

	if ( $id ) {
		$postarr['ID'] = $id;
		$result        = wp_update_post( $postarr, true );
	} else {
		$result = wp_insert_post( $postarr, true );
	}

	if ( is_wp_error( $result ) ) {
		wp_safe_redirect(
			add_query_arg(
				array(
					'page'  => 'compass-projects',
					'view'  => 'edit',
					'id'    => $id,
					'error' => 1,
				),
				admin_url( 'admin.php' )
			)
		);
		exit;
	}

	$id = (int) $result;

	// --- Featured image.
	$thumb_id = isset( $_POST['_thumbnail_id'] ) ? absint( wp_unslash( $_POST['_thumbnail_id'] ) ) : 0;
	if ( $thumb_id ) {
		set_post_thumbnail( $id, $thumb_id );
	} else {
		delete_post_thumbnail( $id );
	}

	// --- SEO meta description.
	$seo = isset( $_POST['_compass_seo_desc'] ) ? sanitize_textarea_field( wp_unslash( $_POST['_compass_seo_desc'] ) ) : '';
	update_post_meta( $id, '_compass_seo_desc', $seo );

	// --- Structured case-study meta (same sanitising rules as the native box).
	compass_projects_save_meta( $id );

	// --- Taxonomies.
	$tax_input = isset( $_POST['compass_tax'] ) && is_array( $_POST['compass_tax'] ) ? wp_unslash( $_POST['compass_tax'] ) : array(); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- ids sanitised below.
	foreach ( array( 'service', 'industry' ) as $taxonomy ) {
		$ids = isset( $tax_input[ $taxonomy ] ) && is_array( $tax_input[ $taxonomy ] )
			? array_map( 'absint', $tax_input[ $taxonomy ] )
			: array();
		wp_set_object_terms( $id, $ids, $taxonomy );
	}

	wp_safe_redirect(
		add_query_arg(
			array(
				'page'  => 'compass-projects',
				'view'  => 'edit',
				'id'    => $id,
				'saved' => 1,
			),
			admin_url( 'admin.php' )
		)
	);
	exit;
}
add_action( 'admin_post_compass_project_save', 'compass_projects_save' );

/**
 * Save the structured `_compass_*` meta for a project from the posted form.
 * Mirrors the per-type sanitising in compass_core_save_meta().
 *
 * @param int $id Post id.
 */
function compass_projects_save_meta( $id ) {
	$defs   = compass_core_field_defs();
	$fields = isset( $defs['work'] ) ? $defs['work'] : array();

	foreach ( $fields as $field ) {
		$name = '_compass_' . $field['key'];

		if ( 'checkbox' === $field['type'] ) {
			update_post_meta( $id, $name, isset( $_POST[ $name ] ) ? '1' : '' ); // phpcs:ignore WordPress.Security.NonceVerification.Missing -- nonce verified in compass_projects_save().
			continue;
		}

		$raw = isset( $_POST[ $name ] ) ? wp_unslash( $_POST[ $name ] ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- nonce verified in caller; sanitised per-type below.

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
			default:
				$clean = sanitize_text_field( $raw );
				break;
		}

		update_post_meta( $id, $name, $clean );
	}
}

/**
 * Inline admin notices for save / trash results.
 */
function compass_projects_admin_notices() {
	// phpcs:disable WordPress.Security.NonceVerification.Recommended -- read-only result flags on a redirect.
	if ( ! empty( $_GET['saved'] ) ) {
		echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( 'Project saved.', 'compass-core' ) . '</p></div>';
	}
	if ( ! empty( $_GET['error'] ) ) {
		echo '<div class="notice notice-error is-dismissible"><p>' . esc_html__( 'Could not save the project. Please try again.', 'compass-core' ) . '</p></div>';
	}
	if ( ! empty( $_GET['trashed'] ) ) {
		echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( 'Project moved to Trash.', 'compass-core' ) . '</p></div>';
	}
	if ( ! empty( $_GET['restored'] ) ) {
		echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( 'Project restored.', 'compass-core' ) . '</p></div>';
	}
	// phpcs:enable WordPress.Security.NonceVerification.Recommended
}

/**
 * AJAX: toggle a project's Featured flag or published status from the list.
 */
function compass_projects_ajax_toggle() {
	check_ajax_referer( 'compass_projects_toggle', 'nonce' );

	$id    = isset( $_POST['id'] ) ? absint( wp_unslash( $_POST['id'] ) ) : 0;
	$which = isset( $_POST['which'] ) ? sanitize_key( wp_unslash( $_POST['which'] ) ) : '';

	if ( ! $id || ! current_user_can( 'edit_post', $id ) ) {
		wp_send_json_error( array( 'message' => __( 'Not allowed.', 'compass-core' ) ), 403 );
	}
	if ( 'work' !== get_post_type( $id ) ) {
		wp_send_json_error( array( 'message' => __( 'Not a project.', 'compass-core' ) ), 400 );
	}

	if ( 'featured' === $which ) {
		$on = '1' !== (string) get_post_meta( $id, '_compass_featured', true );
		update_post_meta( $id, '_compass_featured', $on ? '1' : '' );
		wp_send_json_success( array( 'on' => $on ) );
	}

	if ( 'status' === $which ) {
		$publish = 'publish' !== get_post_status( $id );
		wp_update_post(
			array(
				'ID'          => $id,
				'post_status' => $publish ? 'publish' : 'draft',
			)
		);
		wp_send_json_success( array( 'on' => $publish ) );
	}

	wp_send_json_error( array( 'message' => __( 'Unknown toggle.', 'compass-core' ) ), 400 );
}
add_action( 'wp_ajax_compass_project_toggle', 'compass_projects_ajax_toggle' );
