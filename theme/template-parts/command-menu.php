<?php
/**
 * Command menu overlay.
 *
 * A full-screen dialog listing every page grouped by section, with type-to-filter
 * search. Rendered server-side from the nav map, so the links exist in the DOM
 * regardless of JavaScript; the script (command-menu.js) adds open/close, the
 * filter, arrow-key navigation and the focus trap. Hidden by default via the
 * [hidden] attribute, which the script manages.
 *
 * @package CompassDigital
 */

defined( 'ABSPATH' ) || exit;

$compass_map = compass_nav_map();
?>
<div
	id="command-menu"
	class="command-menu"
	role="dialog"
	aria-modal="true"
	aria-label="<?php esc_attr_e( 'Navigate the site', 'compass-digital' ); ?>"
	hidden
>
	<div class="command-menu__backdrop" data-command-close></div>
	<div class="command-menu__panel" role="document">
		<div class="command-menu__search">
			<span class="command-menu__search-icon" aria-hidden="true"></span>
			<input
				type="search"
				class="command-menu__input"
				id="command-menu-input"
				placeholder="<?php esc_attr_e( 'Search pages…', 'compass-digital' ); ?>"
				aria-label="<?php esc_attr_e( 'Search pages', 'compass-digital' ); ?>"
				aria-controls="command-menu-results"
				autocomplete="off"
				spellcheck="false"
			>
			<button type="button" class="command-menu__close" data-command-close>
				<span class="screen-reader-text"><?php esc_html_e( 'Close', 'compass-digital' ); ?></span>
				<kbd aria-hidden="true">Esc</kbd>
			</button>
		</div>

		<div class="command-menu__results" id="command-menu-results">
			<?php foreach ( $compass_map as $group ) : ?>
				<?php
				// Flatten the group: landing page (if any) plus its items.
				$entries = array();
				if ( ! empty( $group['url'] ) ) {
					$entries[] = array(
						'label' => $group['label'],
						'url'   => $group['url'],
					);
				}
				foreach ( $group['items'] as $item ) {
					$entries[] = $item;
				}
				if ( empty( $entries ) ) {
					continue;
				}
				?>
				<section class="command-menu__group" data-command-group>
					<h2 class="command-menu__group-title"><?php echo esc_html( $group['label'] ); ?></h2>
					<ul class="command-menu__list">
						<?php foreach ( $entries as $entry ) : ?>
							<li>
								<a
									class="command-menu__option"
									href="<?php echo esc_url( $entry['url'] ); ?>"
									data-command-option
									data-label="<?php echo esc_attr( $entry['label'] ); ?>"
								>
									<span class="command-menu__option-label"><?php echo esc_html( $entry['label'] ); ?></span>
									<span class="command-menu__option-group"><?php echo esc_html( $group['label'] ); ?></span>
								</a>
							</li>
						<?php endforeach; ?>
					</ul>
				</section>
			<?php endforeach; ?>
			<p class="command-menu__empty" hidden><?php esc_html_e( 'No pages match your search.', 'compass-digital' ); ?></p>
		</div>
	</div>
</div>
