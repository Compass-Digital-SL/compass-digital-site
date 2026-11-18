<?php
/**
 * Desktop contact chip.
 *
 * One small, tasteful WhatsApp chip, shown only on desktop (the mobile bottom
 * bar already carries WhatsApp). A plain link — no JavaScript needed.
 *
 * @package CompassDigital
 */

defined( 'ABSPATH' ) || exit;
?>
<a
	class="contact-chip"
	href="<?php echo esc_url( compass_whatsapp_url() ); ?>"
	rel="noopener"
	target="_blank"
>
	<span class="contact-chip__icon" aria-hidden="true"></span>
	<span class="contact-chip__label"><?php esc_html_e( 'Chat on WhatsApp', 'compass-digital' ); ?></span>
</a>
