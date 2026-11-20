#!/usr/bin/env node
/**
 * Generate Open Graph / X share images for the Compass Digital theme.
 *
 * Renders branded 1200x630 PNGs from inline SVG with sharp, into
 * theme/assets/og/. Commit the PNGs so production needs no build step; re-run
 * this whenever the brand or copy changes.
 *
 *   node scripts/og/generate-og.mjs
 *
 * Palette (docs/DESIGN.md, premium v2): near-black #0A0C10, ivory #F6F2EA,
 * muted #8E96A3, teal accent #309393 (fill #1C8282). The white wordmark comes
 * from theme/assets/brand/logo-white.webp.
 */

import { fileURLToPath } from 'node:url';
import { dirname, join } from 'node:path';
import { mkdir } from 'node:fs/promises';

const __dirname = dirname( fileURLToPath( import.meta.url ) );
const THEME = join( __dirname, '..', '..', 'theme' );
const OUT_DIR = join( THEME, 'assets', 'og' );
const LOGO = join( THEME, 'assets', 'brand', 'logo-white.webp' );

const WIDTH = 1200;
const HEIGHT = 630;

const BG = '#0A0C10';
const IVORY = '#F6F2EA';
const MUTED = '#8E96A3';
const TEAL = '#309393';
const TEAL_FILL = '#1C8282';
const GLOW = '#1D4F7A';

// One entry per share image. `name` is the file basename the theme references.
const IMAGES = [
	{ name: 'og-default', eyebrow: 'Websites · SEO · POS systems', title: 'Find your way\nforward.' },
	{ name: 'og-home', eyebrow: 'Websites · SEO · POS systems', title: 'Find your way\nforward.' },
	{ name: 'og-services', eyebrow: 'Our services', title: 'Websites, SEO, POS\n& software.' },
	{ name: 'og-industries', eyebrow: 'Industries we serve', title: 'Built for the way\nyour trade works.' },
	{ name: 'og-work', eyebrow: 'Our work', title: 'Real businesses,\nreal results.' },
	{ name: 'og-insights', eyebrow: 'Insights', title: 'Plain-language\ndigital advice.' },
	{ name: 'og-about', eyebrow: 'About Compass Digital', title: 'A digital partner\nyou can reach.' },
	{ name: 'og-contact', eyebrow: 'Contact', title: 'Tell us where\nyou want to go.' },
];

/**
 * Escape text for inclusion in SVG.
 *
 * @param {string} value Raw string.
 * @returns {string} XML-safe string.
 */
function escapeXml( value ) {
	return value
		.replace( /&/g, '&amp;' )
		.replace( /</g, '&lt;' )
		.replace( />/g, '&gt;' );
}

/**
 * Build the SVG markup for one share image.
 *
 * @param {{eyebrow:string,title:string}} entry Image copy.
 * @param {string} logoUri PNG data URI of the white wordmark.
 * @returns {string} SVG document.
 */
function svgFor( entry, logoUri ) {
	const lines = entry.title.split( '\n' );
	const lineH = 96;
	const startY = 330 - ( ( lines.length - 1 ) * lineH ) / 2 + 30;
	const titleTspans = lines
		.map( ( line, i ) => `<tspan x="80" y="${ startY + i * lineH }">${ escapeXml( line ) }</tspan>` )
		.join( '' );

	// Fine-line compass rose on the right, needle pointing north-east.
	const cx = 1010;
	const cy = 330;
	const ticks = Array.from( { length: 72 }, ( _, i ) => {
		const major = i % 6 === 0;
		const angle = ( i * 5 * Math.PI ) / 180;
		const r1 = 300;
		const r2 = major ? 278 : 290;
		const x1 = cx + Math.sin( angle ) * r1;
		const y1 = cy - Math.cos( angle ) * r1;
		const x2 = cx + Math.sin( angle ) * r2;
		const y2 = cy - Math.cos( angle ) * r2;
		return `<line x1="${ x1.toFixed( 1 ) }" y1="${ y1.toFixed( 1 ) }" x2="${ x2.toFixed( 1 ) }" y2="${ y2.toFixed( 1 ) }" stroke="${ IVORY }" stroke-opacity="${ major ? 0.45 : 0.2 }" stroke-width="1.5"/>`;
	} ).join( '' );

	return `<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="${ WIDTH }" height="${ HEIGHT }" viewBox="0 0 ${ WIDTH } ${ HEIGHT }">
  <defs>
    <radialGradient id="g1" cx="85%" cy="15%" r="60%">
      <stop offset="0%" stop-color="${ GLOW }" stop-opacity="0.55"/>
      <stop offset="100%" stop-color="${ GLOW }" stop-opacity="0"/>
    </radialGradient>
    <radialGradient id="g2" cx="80%" cy="55%" r="45%">
      <stop offset="0%" stop-color="${ TEAL_FILL }" stop-opacity="0.35"/>
      <stop offset="100%" stop-color="${ TEAL_FILL }" stop-opacity="0"/>
    </radialGradient>
  </defs>
  <rect width="${ WIDTH }" height="${ HEIGHT }" fill="${ BG }"/>
  <rect width="${ WIDTH }" height="${ HEIGHT }" fill="url(#g1)"/>
  <rect width="${ WIDTH }" height="${ HEIGHT }" fill="url(#g2)"/>
  <g transform="rotate(32 ${ cx } ${ cy })" opacity="0.9">
    <circle cx="${ cx }" cy="${ cy }" r="300" fill="none" stroke="${ IVORY }" stroke-opacity="0.1" stroke-width="1.5"/>
    <circle cx="${ cx }" cy="${ cy }" r="200" fill="none" stroke="${ IVORY }" stroke-opacity="0.08" stroke-width="1.5" stroke-dasharray="3 12"/>
    ${ ticks }
    <polygon points="${ cx },${ cy - 220 } ${ cx + 16 },${ cy } ${ cx - 16 },${ cy }" fill="${ TEAL_FILL }"/>
    <polygon points="${ cx },${ cy + 220 } ${ cx + 16 },${ cy } ${ cx - 16 },${ cy }" fill="${ IVORY }" fill-opacity="0.85"/>
    <circle cx="${ cx }" cy="${ cy }" r="6" fill="${ BG }" stroke="${ IVORY }" stroke-opacity="0.7" stroke-width="2"/>
  </g>
  <image x="80" y="64" width="233" height="36" xlink:href="${ logoUri }" href="${ logoUri }"/>
  <circle cx="86" cy="${ startY - lineH + 6 }" r="5" fill="${ TEAL }"/>
  <text x="104" y="${ startY - lineH + 14 }" fill="${ MUTED }" font-size="22" letter-spacing="4" font-family="Arial, Helvetica, sans-serif">${ escapeXml( entry.eyebrow.toUpperCase() ) }</text>
  <text fill="${ IVORY }" font-size="84" font-family="Georgia, 'Times New Roman', serif" letter-spacing="-2">${ titleTspans }</text>
  <text x="80" y="${ HEIGHT - 60 }" fill="${ MUTED }" font-size="24" font-family="Arial, Helvetica, sans-serif">Websites, SEO and POS systems for businesses worldwide</text>
</svg>`;
}

async function main() {
	let sharp;
	try {
		sharp = ( await import( 'sharp' ) ).default;
	} catch ( err ) {
		console.error( 'sharp is required. Install it with:  npm i -D sharp' );
		process.exitCode = 1;
		return;
	}

	await mkdir( OUT_DIR, { recursive: true } );
	const logoPng = await sharp( LOGO ).png().toBuffer();
	const logoUri = `data:image/png;base64,${ logoPng.toString( 'base64' ) }`;

	for ( const entry of IMAGES ) {
		const svg = svgFor( entry, logoUri );
		const out = join( OUT_DIR, `${ entry.name }.png` );
		await sharp( Buffer.from( svg ) ).png( { compressionLevel: 9 } ).toFile( out );
		console.log( `  ✓ ${ entry.name }.png` );
	}

	console.log( `\nGenerated ${ IMAGES.length } share images in theme/assets/og/` );
}

main();
