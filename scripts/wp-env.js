#!/usr/bin/env node
'use strict';

/**
 * wp-env connectivity-detection workaround.
 *
 * wp-env decides whether it is online by calling `dns.promises.resolve('WordPress.org')`
 * (see node_modules/@wordpress/env/lib/wordpress.js). `dns.resolve` sends a direct DNS
 * query via c-ares using the OS-configured DNS servers. On some Windows/VPN setups that
 * query fails even though the network is perfectly fine — browsers and `fetch` keep
 * working because they use `dns.lookup` (the OS resolver / hosts file), a different path.
 * When the probe fails, wp-env flags itself "offline", refuses to look up the WordPress
 * version, and skips every download — so `wp-env start` never works.
 *
 * Fix: make `dns.promises.resolve` fall back to `dns.promises.lookup` when the direct
 * query throws, then hand off to the real wp-env CLI. No system changes, no patched
 * node_modules. All npm wp:* scripts and the seed script run through this wrapper.
 */

const dns = require( 'dns' );

const realResolve = dns.promises.resolve.bind( dns.promises );
dns.promises.resolve = async function ( hostname, ...rest ) {
	try {
		return await realResolve( hostname, ...rest );
	} catch ( err ) {
		// Fall back to the OS resolver, which works when c-ares does not.
		const { address } = await dns.promises.lookup( hostname );
		return [ address ];
	}
};

// Mirror node_modules/@wordpress/env/bin/wp-env: run the local CLI with our args.
let command = process.argv.slice( 2 );
if ( ! command.length ) {
	command = [ '--help' ];
}

const cliPath = require.resolve( '@wordpress/env/lib/cli.js', {
	paths: [ process.cwd(), __dirname ],
} );

require( cliPath )().parse( command );
