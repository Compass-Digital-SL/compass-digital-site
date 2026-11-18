<?php
/**
 * Mobile bottom bar + bottom sheet (shown under 768px via CSS).
 *
 * Five big thumb-friendly destinations: Home, Services, Work, WhatsApp, Quote.
 * "Services" and "More" open a bottom sheet rather than navigating; the sheet
 * is a real list of links, so with JavaScript off the buttons fall back to the
 * section landing pages (Services -> /services/, More -> command menu link).
 *
 * @package CompassDigital
 */

defined( 'ABSPATH' ) || exit;

$compass_map = compass_nav_map();
?>
<nav class="mobile-bar" aria-label="<?php esc_attr_e( 'Mobile', 'compass-digital' ); ?>">
	<a class="mobile-bar__btn" href="<?php echo esc_url( compass_nav_url( '' ) ); ?>">
		<span class="mobile-bar__icon mobile-bar__icon--home" aria-hidden="true"></span>
		<span class="mobile-bar__label"><?php esc_html_e( 'Home', 'compass-digital' ); ?></span>
	</a>
	<a
		class="mobile-bar__btn"
		href="<?php echo esc_url( compass_nav_url( 'services/' ) ); ?>"
		data-sheet-open="sheet-services"
		aria-haspopup="dialog"
	>
		<span class="mobile-bar__icon mobile-bar__icon--services" aria-hidden="true"></span>
		<span class="mobile-bar__label"><?php esc_html_e( 'Services', 'compass-digital' ); ?></span>
	</a>
	<a class="mobile-bar__btn" href="<?php echo esc_url( compass_nav_url( 'work/' ) ); ?>">
		<span class="mobile-bar__icon mobile-bar__icon--work" aria-hidden="true"></span>
		<span class="mobile-bar__label"><?php esc_html_e( 'Work', 'compass-digital' ); ?></span>
	</a>
	<a class="mobile-bar__btn" href="<?php echo esc_url( compass_whatsapp_url() ); ?>" rel="noopener" target="_blank">
		<span class="mobile-bar__icon mobile-bar__icon--whatsapp" aria-hidden="true"></span>
		<span class="mobile-bar__label"><?php esc_html_e( 'WhatsApp', 'compass-digital' ); ?></span>
	</a>
	<a class="mobile-bar__btn mobile-bar__btn--accent" href="<?php echo esc_url( compass_nav_url( 'get-a-quote/' ) ); ?>">
		<span class="mobile-bar__icon mobile-bar__icon--quote" aria-hidden="true"></span>
		<span class="mobile-bar__label"><?php esc_html_e( 'Quote', 'compass-digital' ); ?></span>
	</a>
</nav>

<?php
// Bottom sheet, one per group with items. Services opens by default; the "More"
// affordance inside each sheet links across to the other sections.
$compass_sheets = array(
	'services'   => $compass_map['services'],
	'industries' => $compass_map['industries'],
	'company'    => $compass_map['company'],
);
?>
<div class="sheet" id="sheet-services" role="dialog" aria-modal="true" aria-label="<?php esc_attr_e( 'Browse the site', 'compass-digital' ); ?>" hidden>
	<div class="sheet__backdrop" data-sheet-close></div>
	<div class="sheet__panel" role="document">
		<div class="sheet__handle" aria-hidden="true"></div>
		<button type="button" class="sheet__close" data-sheet-close>
			<span class="screen-reader-text"><?php esc_html_e( 'Close', 'compass-digital' ); ?></span>
		</button>
		<?php foreach ( $compass_sheets as $group ) : ?>
			<section class="sheet__group">
				<h2 class="sheet__group-title">
					<?php if ( ! empty( $group['url'] ) ) : ?>
						<a href="<?php echo esc_url( $group['url'] ); ?>"><?php echo esc_html( $group['label'] ); ?></a>
					<?php else : ?>
						<?php echo esc_html( $group['label'] ); ?>
					<?php endif; ?>
				</h2>
				<ul class="sheet__list">
					<?php foreach ( $group['items'] as $item ) : ?>
						<li><a class="sheet__link" href="<?php echo esc_url( $item['url'] ); ?>"><?php echo esc_html( $item['label'] ); ?></a></li>
					<?php endforeach; ?>
				</ul>
			</section>
		<?php endforeach; ?>
	</div>
</div>
