<?php
/**
 * Theme mode (dark / light) and the visitor's accent colour.
 *
 * Both choices are made in the header (see assets/src/js/theme-mode.js) and kept
 * in the visitor's own browser (localStorage). This tiny inline script re-applies
 * them before first paint so a returning visitor never sees a flash of the
 * default theme. With no saved choice, CSS follows the system setting.
 *
 * @package CompassDigital
 */

defined( 'ABSPATH' ) || exit;

add_action(
	'wp_head',
	function () {
		// The accent override is CSS this site generated itself (custom properties
		// and color-mix only). It is still validated before use: a strict character
		// whitelist plus a ban on url()/@import, so nothing else can be injected.
		$js = <<<'JS'
(function(){var d=document.documentElement;try{var t=localStorage.getItem('cd-theme');if(t==='light'||t==='dark')d.setAttribute('data-theme',t);var a=localStorage.getItem('cd-accent-css');if(a&&a.length<2000&&/^[\w\s:;{}()\[\]="#%.,@>*-]+$/.test(a)&&!/url|import|expression/i.test(a)){var s=document.createElement('style');s.id='compass-accent';s.textContent=a;document.head.appendChild(s);}}catch(e){}})();
JS;
		printf( "<script id=\"compass-theme-mode\">%s</script>\n", $js ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Static first-party script, no user input.
	},
	0
);
