<?php
/**
 * Primary navigation (desktop header).
 *
 * Real anchors throughout, so the whole menu works without JavaScript. The
 * dropdowns open on hover and keyboard focus via CSS; a small script upgrades
 * them to click/aria-expanded toggles. The "Navigate" button opens the command
 * menu (progressive enhancement — it falls back to the footer sitemap link).
 *
 * @package CompassDigital
 */

defined( 'ABSPATH' ) || exit;

$compass_links = compass_primary_links();
?>
<nav class="primary-nav" aria-label="<?php esc_attr_e( 'Primary', 'compass-digital' ); ?>">
	<ul class="primary-nav__menu">
		<?php foreach ( $compass_links as $i => $compass_link ) : ?>
			<?php $has_items = ! empty( $compass_link['items'] ); ?>
			<li class="primary-nav__item<?php echo $has_items ? ' has-dropdown' : ''; ?>">
				<?php if ( $has_items ) : ?>
					<?php $panel_id = 'nav-panel-' . $i; ?>
					<a
						class="primary-nav__link"
						href="<?php echo esc_url( $compass_link['url'] ); ?>"
						aria-expanded="false"
						aria-controls="<?php echo esc_attr( $panel_id ); ?>"
					>
						<?php echo esc_html( $compass_link['label'] ); ?>
						<span class="primary-nav__chev" aria-hidden="true"></span>
					</a>
					<div class="primary-nav__dropdown" id="<?php echo esc_attr( $panel_id ); ?>">
						<ul class="primary-nav__sublist">
							<?php foreach ( $compass_link['items'] as $item ) : ?>
								<li>
									<a class="primary-nav__sublink" href="<?php echo esc_url( $item['url'] ); ?>">
										<?php echo esc_html( $item['label'] ); ?>
									</a>
								</li>
							<?php endforeach; ?>
						</ul>
					</div>
				<?php else : ?>
					<a class="primary-nav__link" href="<?php echo esc_url( $compass_link['url'] ); ?>">
						<?php echo esc_html( $compass_link['label'] ); ?>
					</a>
				<?php endif; ?>
			</li>
		<?php endforeach; ?>
	</ul>
</nav>

<div class="header-actions">
	<button
		type="button"
		class="header-actions__navigate"
		data-command-open
		aria-haspopup="dialog"
		aria-controls="command-menu"
		aria-keyshortcuts="Control+K Meta+K"
	>
		<svg class="header-actions__search-icon" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true" focusable="false"><circle cx="9" cy="9" r="6"/><path d="m13.5 13.5 4 4"/></svg>
		<span class="header-actions__navigate-label"><?php esc_html_e( 'Search', 'compass-digital' ); ?></span>
		<kbd class="header-actions__key" aria-hidden="true" data-shortcut-label>K</kbd>
	</button>

	<div class="tint" data-tint>
		<button type="button" class="icon-btn tint__btn" aria-expanded="false" aria-controls="tint-pop" data-tint-toggle>
			<span class="tint__dot" aria-hidden="true"></span>
			<span class="screen-reader-text"><?php esc_html_e( 'Choose accent colour', 'compass-digital' ); ?></span>
		</button>
		<div class="tint__pop" id="tint-pop" role="group" aria-label="<?php esc_attr_e( 'Accent colours', 'compass-digital' ); ?>" hidden data-tint-pop></div>
	</div>

	<button type="button" class="icon-btn theme-btn" data-theme-toggle aria-label="<?php esc_attr_e( 'Switch theme', 'compass-digital' ); ?>">
		<svg class="theme-btn__moon" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.4" aria-hidden="true" focusable="false"><path d="M16.5 12.2A7 7 0 0 1 7.8 3.5a7 7 0 1 0 8.7 8.7z"/></svg>
		<svg class="theme-btn__sun" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.4" aria-hidden="true" focusable="false"><circle cx="10" cy="10" r="3.6"/><path d="M10 1.5v2M10 16.5v2M1.5 10h2M16.5 10h2M4 4l1.4 1.4M14.6 14.6 16 16M4 16l1.4-1.4M14.6 5.4 16 4"/></svg>
	</button>

	<a class="btn btn--primary btn--sm header-actions__quote" href="<?php echo esc_url( compass_nav_url( 'get-a-quote/' ) ); ?>">
		<?php esc_html_e( 'Get a quote', 'compass-digital' ); ?> <span class="btn__arrow" aria-hidden="true">→</span>
	</a>
</div>
