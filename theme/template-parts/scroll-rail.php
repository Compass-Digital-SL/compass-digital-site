<?php
/**
 * Scroll waypoint rail (desktop only).
 *
 * An empty, decorative container that the script fills with one compass tick per
 * on-page section. Sections opt in by carrying `data-waypoint` and `data-label`
 * (and an id) on the page; if there are none, the script leaves the rail hidden.
 * Purely an enhancement — it is aria-hidden and never the only way to reach
 * content. Honours prefers-reduced-motion (no smooth scroll) via CSS/JS.
 *
 * @package CompassDigital
 */

defined( 'ABSPATH' ) || exit;
?>
<nav class="scroll-rail" aria-label="<?php esc_attr_e( 'On this page', 'compass-digital' ); ?>" data-scroll-rail hidden>
	<ol class="scroll-rail__list"></ol>
</nav>
