#!/usr/bin/env node
/**
 * Compass Digital — local SEO crawl + audit.
 *
 * Crawls the running local site (same origin, breadth-first) and reports:
 *   - Broken links (internal links returning 4xx/5xx, or failing to fetch).
 *   - Missing or empty <title>.
 *   - Missing or duplicate <h1> (exactly one is expected).
 *   - Heading-hierarchy skips (e.g. an <h2> followed by an <h4>).
 *   - Images with no alt attribute (alt="" is allowed for decorative images).
 *   - Missing canonical or meta description.
 *   - Orphan pages (crawled pages with no inbound internal link).
 *
 * Usage:
 *   node scripts/seo/audit.mjs [baseUrl]
 *   SITE_URL=http://localhost:8888 node scripts/seo/audit.mjs
 *
 * Exits non-zero when any error-level issue is found, so CI can gate on it.
 * Warnings (heading skips, orphans, missing meta) do not fail the build unless
 * --strict is passed.
 */

const BASE = ( process.argv[ 2 ] || process.env.SITE_URL || 'http://localhost:8888' ).replace( /\/$/, '' );
const STRICT = process.argv.includes( '--strict' );
const MAX_PAGES = Number( process.env.MAX_PAGES || 200 );

const origin = new URL( BASE ).origin;

/** @type {Map<string,{status:number,issues:string[],warnings:string[]}>} */
const results = new Map();
/** @type {Map<string,number>} inbound internal link counts. */
const inbound = new Map();
/** @type {Map<string,number>} link status cache. */
const linkStatus = new Map();

/**
 * Normalise a URL to an absolute, fragment-free string, or null if off-site /
 * non-http / an asset we should not crawl.
 *
 * @param {string} href Raw href.
 * @param {string} from  Page the link was found on.
 * @returns {string|null}
 */
function normalise( href, from ) {
	if ( ! href ) {
		return null;
	}
	let url;
	try {
		url = new URL( href, from );
	} catch {
		return null;
	}
	if ( url.origin !== origin ) {
		return null;
	}
	if ( ! /^https?:$/.test( url.protocol ) ) {
		return null;
	}
	url.hash = '';
	// Skip wp-admin, feeds, and obvious asset/file requests.
	if ( /\/wp-admin\//.test( url.pathname ) || /\/wp-json\//.test( url.pathname ) ) {
		return null;
	}
	if ( /\.(png|jpe?g|gif|svg|webp|ico|css|js|mjs|json|xml|pdf|zip|woff2?|ttf)$/i.test( url.pathname ) ) {
		return null;
	}
	return url.toString();
}

/**
 * Extract the <body> or whole doc text once; helpers below operate on it.
 *
 * @param {string} html Raw HTML.
 * @returns {{title:string|null,canonical:boolean,description:boolean,h1:number,headings:number[],imgsMissingAlt:number,links:string[]}}
 */
function parse( html ) {
	const titleMatch = html.match( /<title[^>]*>([\s\S]*?)<\/title>/i );
	const title = titleMatch ? titleMatch[ 1 ].trim() : null;

	const canonical = /<link[^>]+rel=["']canonical["'][^>]*>/i.test( html );
	const description = /<meta[^>]+name=["']description["'][^>]*>/i.test( html );

	const h1 = ( html.match( /<h1[\s>]/gi ) || [] ).length;

	const headings = [];
	const headingRe = /<h([1-6])[\s>]/gi;
	let m;
	while ( ( m = headingRe.exec( html ) ) !== null ) {
		headings.push( Number( m[ 1 ] ) );
	}

	// Images without an alt attribute at all (alt="" is fine — decorative).
	const imgs = html.match( /<img\b[^>]*>/gi ) || [];
	let imgsMissingAlt = 0;
	for ( const img of imgs ) {
		if ( ! /\balt\s*=/i.test( img ) ) {
			imgsMissingAlt++;
		}
	}

	const links = [];
	const linkRe = /<a\b[^>]*\bhref\s*=\s*["']([^"']+)["']/gi;
	while ( ( m = linkRe.exec( html ) ) !== null ) {
		links.push( m[ 1 ] );
	}

	return { title, canonical, description, h1, headings, imgsMissingAlt, links };
}

/**
 * Check a non-page link's status (cached). Returns HTTP status or 0 on failure.
 *
 * @param {string} url Absolute URL.
 * @returns {Promise<number>}
 */
async function checkStatus( url ) {
	if ( linkStatus.has( url ) ) {
		return linkStatus.get( url );
	}
	let status = 0;
	try {
		let res = await fetch( url, { method: 'HEAD', redirect: 'follow' } );
		if ( res.status === 405 || res.status === 501 ) {
			res = await fetch( url, { method: 'GET', redirect: 'follow' } );
		}
		status = res.status;
	} catch {
		status = 0;
	}
	linkStatus.set( url, status );
	return status;
}

async function crawl() {
	const queue = [ BASE + '/' ];
	const seen = new Set( queue );

	while ( queue.length && results.size < MAX_PAGES ) {
		const url = queue.shift();
		let res;
		try {
			res = await fetch( url, { redirect: 'manual' } );
		} catch ( err ) {
			results.set( url, { status: 0, issues: [ `Failed to fetch: ${ err.message }` ], warnings: [] } );
			continue;
		}

		// Redirects are fine for the crawl; follow once to the target.
		if ( res.status >= 300 && res.status < 400 ) {
			const loc = res.headers.get( 'location' );
			const target = loc ? normalise( loc, url ) : null;
			results.set( url, { status: res.status, issues: [], warnings: [ `Redirects (${ res.status }) → ${ loc }` ] } );
			if ( target && ! seen.has( target ) ) {
				seen.add( target );
				queue.push( target );
			}
			continue;
		}

		const ct = res.headers.get( 'content-type' ) || '';
		if ( ! ct.includes( 'text/html' ) ) {
			continue;
		}

		const html = await res.text();
		const data = parse( html );
		const issues = [];
		const warnings = [];

		if ( res.status >= 400 ) {
			issues.push( `HTTP ${ res.status }` );
		}
		if ( ! data.title ) {
			issues.push( 'Missing or empty <title>' );
		}
		if ( data.h1 === 0 ) {
			issues.push( 'No <h1>' );
		} else if ( data.h1 > 1 ) {
			issues.push( `Duplicate <h1> (${ data.h1 })` );
		}
		if ( data.imgsMissingAlt > 0 ) {
			issues.push( `${ data.imgsMissingAlt } image(s) with no alt attribute` );
		}
		if ( ! data.canonical ) {
			warnings.push( 'No canonical link' );
		}
		if ( ! data.description ) {
			warnings.push( 'No meta description' );
		}

		// Heading-hierarchy skips.
		for ( let i = 1; i < data.headings.length; i++ ) {
			if ( data.headings[ i ] - data.headings[ i - 1 ] > 1 ) {
				warnings.push( `Heading skip: h${ data.headings[ i - 1 ] } → h${ data.headings[ i ] }` );
				break;
			}
		}

		results.set( url, { status: res.status, issues, warnings } );

		// Queue internal links; check off-page / asset-ish links for 404s.
		for ( const raw of data.links ) {
			const abs = normalise( raw, url );
			if ( ! abs ) {
				continue;
			}
			inbound.set( abs, ( inbound.get( abs ) || 0 ) + 1 );
			if ( ! seen.has( abs ) ) {
				seen.add( abs );
				queue.push( abs );
			}
		}
	}
}

/**
 * Second pass: verify every discovered internal link resolves (broken links).
 */
async function checkLinks() {
	const broken = [];
	for ( const url of inbound.keys() ) {
		const status = results.has( url ) ? results.get( url ).status : await checkStatus( url );
		if ( status === 0 || status >= 400 ) {
			broken.push( { url, status } );
		}
	}
	return broken;
}

function report( broken ) {
	let errorCount = 0;
	let warnCount = 0;

	console.log( `\nSEO audit — ${ BASE }\nCrawled ${ results.size } page(s)\n` );

	for ( const [ url, r ] of results ) {
		if ( ! r.issues.length && ! r.warnings.length ) {
			continue;
		}
		console.log( url );
		for ( const i of r.issues ) {
			console.log( `  ✗ ${ i }` );
			errorCount++;
		}
		for ( const w of r.warnings ) {
			console.log( `  ! ${ w }` );
			warnCount++;
		}
	}

	if ( broken.length ) {
		console.log( '\nBroken internal links:' );
		for ( const b of broken ) {
			console.log( `  ✗ ${ b.status || 'ERR' }  ${ b.url }` );
			errorCount++;
		}
	}

	// Orphans: crawled pages (excluding the home page) with no inbound link.
	const orphans = [];
	for ( const url of results.keys() ) {
		if ( url === BASE + '/' ) {
			continue;
		}
		if ( ! inbound.get( url ) ) {
			orphans.push( url );
		}
	}
	if ( orphans.length ) {
		console.log( '\nOrphan pages (no inbound internal link):' );
		for ( const o of orphans ) {
			console.log( `  ! ${ o }` );
			warnCount++;
		}
	}

	console.log( `\n${ errorCount } error(s), ${ warnCount } warning(s).` );

	if ( errorCount > 0 || ( STRICT && warnCount > 0 ) ) {
		process.exitCode = 1;
	}
}

async function main() {
	try {
		await fetch( BASE + '/', { method: 'HEAD' } );
	} catch {
		console.error( `Cannot reach ${ BASE }. Start the site (npm run wp:start) or pass a base URL.` );
		process.exitCode = 1;
		return;
	}
	await crawl();
	const broken = await checkLinks();
	report( broken );
}

main();
