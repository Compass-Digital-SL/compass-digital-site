/**
 * Work gallery picker — admin only, uses the core wp.media frame.
 * jQuery here is fine: this is the WordPress admin, not the front end.
 */
( function ( $ ) {
	'use strict';

	$( document ).on( 'click', '.compass-gallery-pick', function ( e ) {
		e.preventDefault();

		var $wrap = $( this ).closest( '.compass-field' );
		var $ids = $wrap.find( '.compass-gallery-ids' );
		var $preview = $wrap.find( '.compass-gallery-preview' );

		var frame = wp.media( {
			title: compassGallery.title,
			button: { text: compassGallery.button },
			library: { type: 'image' },
			multiple: true,
		} );

		frame.on( 'select', function () {
			var ids = [];
			$preview.empty();

			frame
				.state()
				.get( 'selection' )
				.each( function ( attachment ) {
					var data = attachment.toJSON();
					ids.push( data.id );
					var src =
						data.sizes && data.sizes.thumbnail
							? data.sizes.thumbnail.url
							: data.url;
					$preview.append(
						$( '<span class="compass-gallery-thumb" />' ).append(
							$( '<img />', { src: src, alt: '' } )
						)
					);
				} );

			$ids.val( ids.join( ',' ) );
		} );

		frame.open();
	} );

	$( document ).on( 'click', '.compass-gallery-clear', function ( e ) {
		e.preventDefault();
		var $wrap = $( this ).closest( '.compass-field' );
		$wrap.find( '.compass-gallery-ids' ).val( '' );
		$wrap.find( '.compass-gallery-preview' ).empty();
	} );
} )( jQuery );
