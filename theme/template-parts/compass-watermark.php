<?php
/**
 * Decorative compass watermark.
 *
 * A faint, fixed compass that sits behind the page content. Its needle rotates
 * anti-clockwise as the reader scrolls down (progressive enhancement in
 * motion.js — static with JS off or under prefers-reduced-motion). Purely
 * decorative, so it is hidden from assistive tech. Rendered on every template
 * except the home page (see footer.php).
 *
 * @package Compass_Digital
 */

?>
<div class="compass-watermark" data-compass aria-hidden="true">
	<svg class="compass-watermark__svg" viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg" focusable="false">
		<circle cx="50" cy="50" r="46" stroke="currentColor" stroke-width="1.25" />
		<circle cx="50" cy="50" r="37" stroke="currentColor" stroke-width="0.6" />
		<g stroke="currentColor" stroke-width="1.25">
			<line x1="50" y1="3" x2="50" y2="12" />
			<line x1="50" y1="88" x2="50" y2="97" />
			<line x1="3" y1="50" x2="12" y2="50" />
			<line x1="88" y1="50" x2="97" y2="50" />
		</g>
		<g class="compass-watermark__needle" data-compass-needle>
			<path d="M50 15 L57 50 L50 50 Z" fill="currentColor" />
			<path d="M50 15 L43 50 L50 50 Z" fill="currentColor" opacity="0.72" />
			<path d="M50 85 L57 50 L50 50 Z" fill="currentColor" opacity="0.34" />
			<path d="M50 85 L43 50 L50 50 Z" fill="currentColor" opacity="0.24" />
			<circle cx="50" cy="50" r="2.6" fill="currentColor" />
		</g>
	</svg>
</div>
