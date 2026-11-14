/* global jQuery, wp, stackfolioAdmin */
( function ( $ ) {
	'use strict';

	$( function () {
		var $list = $( '#stackfolio-media-list' );
		var $input = $( '#stackfolio-media' );
		var $clear = $( '#stackfolio-clear-media' );
		var frame;

		if ( ! $list.length ) {
			return;
		}

		function sync() {
			var ids = $list.children( '.stackfolio-media-item' ).map( function () {
				return $( this ).data( 'id' );
			} ).get();
			$input.val( ids.join( ',' ) );
			$clear.prop( 'hidden', ids.length === 0 );
		}

		function itemHtml( attachment ) {
			var $li = $( '<li class="stackfolio-media-item"></li>' ).attr( 'data-id', attachment.id );
			if ( attachment.type === 'video' ) {
				$li.addClass( 'is-video' )
					.append( '<span class="dashicons dashicons-format-video" aria-hidden="true"></span>' )
					.append( $( '<span class="stackfolio-media-item__name"></span>' ).text( attachment.title || stackfolioAdmin.video ) );
			} else {
				var size = attachment.sizes && attachment.sizes.thumbnail ? attachment.sizes.thumbnail : attachment;
				$li.append( $( '<img alt="">' ).attr( 'src', size.url ) );
			}
			$li.append(
				$( '<button type="button" class="stackfolio-media-item__remove">&times;</button>' ).attr( 'aria-label', stackfolioAdmin.remove )
			);
			return $li;
		}

		$list.sortable( {
			items: '.stackfolio-media-item',
			placeholder: 'stackfolio-media-item stackfolio-media-item--placeholder',
			tolerance: 'pointer',
			update: sync,
		} );

		$list.on( 'click', '.stackfolio-media-item__remove', function () {
			$( this ).closest( '.stackfolio-media-item' ).remove();
			sync();
		} );

		$clear.on( 'click', function () {
			$list.empty();
			sync();
		} );

		$( '#stackfolio-add-media' ).on( 'click', function ( e ) {
			e.preventDefault();

			if ( frame ) {
				frame.open();
				return;
			}

			frame = wp.media( {
				title: stackfolioAdmin.frameTitle,
				button: { text: stackfolioAdmin.frameButton },
				library: { type: [ 'image', 'video' ] },
				multiple: 'add',
			} );

			frame.on( 'select', function () {
				var existing = $input.val() ? $input.val().split( ',' ) : [];
				frame.state().get( 'selection' ).each( function ( model ) {
					var attachment = model.toJSON();
					if ( existing.indexOf( String( attachment.id ) ) === -1 ) {
						$list.append( itemHtml( attachment ) );
						existing.push( String( attachment.id ) );
					}
				} );
				sync();
			} );

			frame.open();
		} );
	} );
}( jQuery ) );
