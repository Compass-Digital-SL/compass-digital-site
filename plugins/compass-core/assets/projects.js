/**
 * Projects dashboard — admin only.
 *
 * - "Draft with AI" buttons call the compass/v1/ai/draft REST route with the
 *   values typed so far and fill the target field.
 * - Featured-image picker (wp.media) and inline Featured / status toggles.
 *
 * Vanilla JS: no jQuery dependency (the gallery picker loads its own).
 */
( function () {
	'use strict';

	var cfg = window.compassProjects || {};
	var i18n = cfg.i18n || {};

	/* ---- Helpers ------------------------------------------------------- */

	function gatherContext() {
		var ctx = {};
		var nodes = document.querySelectorAll( '[data-ai-context]' );
		for ( var i = 0; i < nodes.length; i++ ) {
			var key = nodes[ i ].getAttribute( 'data-ai-context' );
			ctx[ key ] = nodes[ i ].value || '';
		}
		var title = document.getElementById( 'compass-title' );
		if ( title ) {
			ctx.title = title.value || '';
		}
		return ctx;
	}

	function setFieldValue( targetId, text, mode ) {
		// Rich editor body?
		if ( window.tinymce && window.tinymce.get && window.tinymce.get( targetId ) && ! window.tinymce.get( targetId ).isHidden() ) {
			var ed = window.tinymce.get( targetId );
			if ( 'append' === mode ) {
				ed.setContent( ed.getContent() + text );
			} else {
				ed.setContent( text );
			}
			return;
		}
		var el = document.getElementById( targetId );
		if ( ! el ) {
			return;
		}
		if ( 'append' === mode && el.value ) {
			el.value = el.value.replace( /\s*$/, '' ) + '\n\n' + text;
		} else {
			el.value = text;
		}
	}

	function clearResult( btn ) {
		var existing = btn.parentNode.querySelector( '.compass-ai-result' );
		if ( existing ) {
			existing.parentNode.removeChild( existing );
		}
	}

	function showResult( btn, targetId, text ) {
		clearResult( btn );

		var el = document.getElementById( targetId );
		var hasContent = false;
		if ( window.tinymce && window.tinymce.get && window.tinymce.get( targetId ) && ! window.tinymce.get( targetId ).isHidden() ) {
			hasContent = '' !== window.tinymce.get( targetId ).getContent().trim();
		} else if ( el ) {
			hasContent = '' !== ( el.value || '' ).trim();
		}

		// Empty field: just fill it, no prompt.
		if ( ! hasContent ) {
			setFieldValue( targetId, text, 'replace' );
			return;
		}

		var box = document.createElement( 'div' );
		box.className = 'compass-ai-result';

		var preview = document.createElement( 'div' );
		preview.className = 'compass-ai-preview';
		preview.textContent = text;
		box.appendChild( preview );

		var actions = document.createElement( 'div' );
		actions.className = 'compass-ai-actions';

		var replace = document.createElement( 'button' );
		replace.type = 'button';
		replace.className = 'button button-primary';
		replace.textContent = i18n.replace || 'Replace';
		replace.addEventListener( 'click', function () {
			setFieldValue( targetId, text, 'replace' );
			clearResult( btn );
		} );

		var insert = document.createElement( 'button' );
		insert.type = 'button';
		insert.className = 'button';
		insert.textContent = i18n.insert || 'Insert';
		insert.addEventListener( 'click', function () {
			setFieldValue( targetId, text, 'append' );
			clearResult( btn );
		} );

		actions.appendChild( replace );
		actions.appendChild( insert );
		box.appendChild( actions );
		btn.parentNode.appendChild( box );
	}

	/* ---- AI draft buttons --------------------------------------------- */

	function onDraftClick( e ) {
		var btn = e.currentTarget;
		var field = btn.getAttribute( 'data-ai-draft' );
		var targetId = btn.getAttribute( 'data-ai-target' );
		if ( ! field || ! targetId || ! cfg.aiEndpoint ) {
			return;
		}

		var original = btn.textContent;
		btn.disabled = true;
		btn.textContent = i18n.drafting || 'Drafting…';
		clearResult( btn );

		fetch( cfg.aiEndpoint, {
			method: 'POST',
			credentials: 'same-origin',
			headers: {
				'Content-Type': 'application/json',
				'X-WP-Nonce': cfg.restNonce || '',
			},
			body: JSON.stringify( { field: field, context: gatherContext() } ),
		} )
			.then( function ( res ) {
				return res.json().then( function ( data ) {
					return { ok: res.ok, data: data };
				} );
			} )
			.then( function ( out ) {
				if ( out.ok && out.data && out.data.ok && out.data.text ) {
					showResult( btn, targetId, out.data.text );
				} else {
					var msg = ( out.data && out.data.message ) || i18n.error || 'Something went wrong.';
					window.alert( msg );
				}
			} )
			.catch( function () {
				window.alert( i18n.error || 'Something went wrong.' );
			} )
			.then( function () {
				btn.disabled = false;
				btn.textContent = original;
			} );
	}

	/* ---- Featured image picker ---------------------------------------- */

	function initThumb() {
		var pick = document.querySelector( '.compass-thumb-pick' );
		var clear = document.querySelector( '.compass-thumb-clear' );
		var idField = document.getElementById( 'compass-thumb-id' );
		var preview = document.querySelector( '.compass-thumb-preview' );
		if ( ! pick || ! idField || ! window.wp || ! window.wp.media ) {
			return;
		}

		var frame;
		pick.addEventListener( 'click', function ( e ) {
			e.preventDefault();
			if ( frame ) {
				frame.open();
				return;
			}
			frame = window.wp.media( {
				title: cfg.mediaTitle || 'Choose image',
				button: { text: cfg.mediaButton || 'Use this image' },
				library: { type: 'image' },
				multiple: false,
			} );
			frame.on( 'select', function () {
				var att = frame.state().get( 'selection' ).first().toJSON();
				idField.value = att.id;
				if ( preview ) {
					var src = att.sizes && att.sizes.medium ? att.sizes.medium.url : att.url;
					preview.innerHTML = '';
					var img = document.createElement( 'img' );
					img.src = src;
					img.alt = '';
					preview.appendChild( img );
				}
			} );
			frame.open();
		} );

		if ( clear ) {
			clear.addEventListener( 'click', function ( e ) {
				e.preventDefault();
				idField.value = '';
				if ( preview ) {
					preview.innerHTML = '';
				}
			} );
		}
	}

	/* ---- Inline list toggles ------------------------------------------ */

	function initToggles() {
		var toggles = document.querySelectorAll( '.compass-toggle' );
		for ( var i = 0; i < toggles.length; i++ ) {
			toggles[ i ].addEventListener( 'click', onToggleClick );
		}
	}

	function onToggleClick( e ) {
		var btn = e.currentTarget;
		var id = btn.getAttribute( 'data-id' );
		var which = btn.getAttribute( 'data-which' );
		if ( ! id || ! which || ! cfg.ajaxUrl ) {
			return;
		}
		btn.disabled = true;

		var body = new window.FormData();
		body.append( 'action', 'compass_project_toggle' );
		body.append( 'nonce', cfg.toggleNonce || '' );
		body.append( 'id', id );
		body.append( 'which', which );

		fetch( cfg.ajaxUrl, {
			method: 'POST',
			credentials: 'same-origin',
			body: body,
		} )
			.then( function ( res ) {
				return res.json();
			} )
			.then( function ( data ) {
				if ( data && data.success ) {
					var on = !! data.data.on;
					btn.classList.toggle( 'is-on', on );
					btn.setAttribute( 'aria-pressed', on ? 'true' : 'false' );
				} else {
					window.alert( ( data && data.data && data.data.message ) || i18n.error );
				}
			} )
			.catch( function () {
				window.alert( i18n.error || 'Something went wrong.' );
			} )
			.then( function () {
				btn.disabled = false;
			} );
	}

	/* ---- Boot ---------------------------------------------------------- */

	document.addEventListener( 'DOMContentLoaded', function () {
		var drafts = document.querySelectorAll( '.compass-ai-btn' );
		for ( var i = 0; i < drafts.length; i++ ) {
			drafts[ i ].addEventListener( 'click', onDraftClick );
		}
		initThumb();
		initToggles();
	} );
} )();
