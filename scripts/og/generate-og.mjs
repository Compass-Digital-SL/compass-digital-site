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
 * Palette (docs/BRAND.md): Navy #0F2A43, Brass #C9A24B, Ivory #F6F2EA,
 * Teal #2F7F86, Ink #1B1F24.
 */

import { fileURLToPath } from 'node:url';
import { dirname, join } from 'node:path';
import { mkdir } from 'node:fs/promises';

const __dirname = dirname( fileURLToPath( import.meta.url ) );
const OUT_DIR = join( __dirname, '..', '..', 'theme', 'assets', 'og' );

const WIDTH = 1200;
const HEIGHT = 630;

const NAVY = '#0F2A43';
const NAVY_DEEP = '#0A1E30';
const BRASS = '#C9A24B';
const IVORY = '#F6F2EA';
const TEAL = '#2F7F86';

// One entry per share image. `name` is the file basename the theme references.
const IMAGES = [
	{ name: 'og-default', eyebrow: 'Compass Digital', title: 'Websites, SEO & POS\nin Sri Lanka' },
	{ name: 'og-home', eyebrow: 'Compass Digital', title: 'Find your way\nforward.' },
	{ name: 'og-services', eyebrow: 'Our services', title: 'Websites, SEO, POS\n& software' },
	{ name: 'og-industries', eyebrow: 'Industries we serve', title: 'Built for Sri Lankan\nbusinesses' },
	{ name: 'og-work', eyebrow: 'Our work', title: 'Real projects,\nreal results' },
	{ name: 'og-insights', eyebrow: 'Insights', title: 'Plain-language\ndigital advice' },
	{ name: 'og-about', eyebrow: 'About Compass Digital', title: 'Your digital\npartner in Sri Lanka' },
	{ name: 'og-contact', eyebrow: 'Contact', title: 'Let’s talk.\nRequest a quote.' },
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
 * @returns {string} SVG document.
 */
function svgFor( entry ) {
	const lines = entry.title.split( '\n' );
	const startY = HEIGHT / 2 - ( lines.length - 1 ) * 46 + 10;
	const titleTspans = lines
		.map( ( line, i ) => `<tspan x="90" y="${ startY + i * 92 }">${ escapeXml( line ) }</tspan>` )
		.join( '' );

	// A simple compass ring + needle motif on the right.
	const cx = 980;
	const cy = 315;
	const ticks = Array.from( { length: 24 }, ( _, i ) => {
		const major = i % 6 === 0;
		const r1 = major ? 118 : 128;
		const angle = ( i * 15 * Math.PI ) / 180;
		const x1 = cx + Math.sin( angle ) * r1;
		const y1 = cy - Math.cos( angle ) * r1;
		const x2 = cx + Math.sin( angle ) * 138;
		const y2 = cy - Math.cos( angle ) * 138;
		return `<line x1="${ x1.toFixed( 1 ) }" y1="${ y1.toFixed( 1 ) }" x2="${ x2.toFixed( 1 ) }" y2="${ y2.toFixed( 1 ) }" stroke="${ BRASS }" stroke-width="${ major ? 4 : 2 }" opacity="0.8"/>`;
	} ).join( '' );

	return `<svg xmlns="http://www.w3.org/2000/svg" width="${ WIDTH }" height="${ HEIGHT }" viewBox="0 0 ${ WIDTH } ${ HEIGHT }">
  <defs>
    <radialGradient id="bg" cx="30%" cy="30%" r="90%">
      <stop offset="0%" stop-color="${ NAVY }"/>
      <stop offset="100%" stop-color="${ NAVY_DEEP }"/>
    </radialGradient>
  </defs>
  <rect width="${ WIDTH }" height="${ HEIGHT }" fill="url(#bg)"/>
  <rect x="0" y="0" width="14" height="${ HEIGHT }" fill="${ BRASS }"/>
  <g font-family="Georgia, 'Times New Roman', serif">
    <text x="90" y="${ startY - 92 }" fill="${ BRASS }" font-size="30" letter-spacing="3" font-family="Arial, Helvetica, sans-serif" font-weight="700">${ escapeXml( entry.eyebrow.toUpperCase() ) }</text>
    <text fill="${ IVORY }" font-size="76" font-weight="700">${ titleTspans }</text>
  </g>
  <circle cx="${ cx }" cy="${ cy }" r="150" fill="none" stroke="${ TEAL }" stroke-width="2" opacity="0.5"/>
  <circle cx="${ cx }" cy="${ cy }" r="100" fill="none" stroke="${ BRASS }" stroke-width="2" opacity="0.6"/>
  ${ ticks }
  <polygon points="${ cx },${ cy - 90 } ${ cx + 16 },${ cy } ${ cx },${ cy + 90 } ${ cx - 16 },${ cy }" fill="${ BRASS }"/>
  <polygon points="${ cx },${ cy - 90 } ${ cx + 16 },${ cy } ${ cx - 16 },${ cy }" fill="${ IVORY }"/>
  <circle cx="${ cx }" cy="${ cy }" r="10" fill="${ NAVY }" stroke="${ IVORY }" stroke-width="3"/>
  <text x="90" y="${ HEIGHT - 60 }" fill="${ IVORY }" opacity="0.75" font-size="28" font-family="Arial, Helvetica, sans-serif">compassdigital.lk · Colombo, Sri Lanka</text>
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

	for ( const entry of IMAGES ) {
		const svg = svgFor( entry );
		const out = join( OUT_DIR, `${ entry.name }.png` );
		await sharp( Buffer.from( svg ) ).png( { compressionLevel: 9 } ).toFile( out );
		console.log( `  ✓ ${ entry.name }.png` );
	}

	console.log( `\nGenerated ${ IMAGES.length } share images in theme/assets/og/` );
}

main();
