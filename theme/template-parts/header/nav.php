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
		class="btn btn--ghost header-actions__navigate"
		data-command-open
		aria-haspopup="dialog"
		aria-controls="command-menu"
	>
		<span class="header-actions__navigate-label"><?php esc_html_e( 'Navigate', 'compass-digital' ); ?></span>
		<kbd class="header-actions__key" aria-hidden="true">K</kbd>
	</button>
	<a class="btn btn--primary header-actions__quote" href="<?php echo esc_url( compass_nav_url( 'get-a-quote/' ) ); ?>">
		<?php esc_html_e( 'Get a quote', 'compass-digital' ); ?>
	</a>
</div>
