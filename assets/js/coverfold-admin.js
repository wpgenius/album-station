/* global jQuery, coverfoldAdmin */
( function ( $ ) {
	'use strict';

	$( function () {
		const $list = $( '#coverfold-media-list' );

		if ( ! $list.length ) {
			return;
		}

		const $input = $( '#coverfold-media' );
		const $clear = $( '#coverfold-clear-media' );
		let frame;

		function sync() {
			const ids = $list
				.children( '.coverfold-media-item' )
				.map( function () {
					return $( this ).data( 'id' );
				} )
				.get();
			$input.val( ids.join( ',' ) );
			$clear.prop( 'hidden', ids.length === 0 );
		}

		function itemHtml( attachment ) {
			const $li = $( '<li class="coverfold-media-item"></li>' ).attr(
				'data-id',
				attachment.id
			);
			if ( attachment.type === 'video' ) {
				$li.addClass( 'is-video' )
					.append(
						'<span class="dashicons dashicons-format-video" aria-hidden="true"></span>'
					)
					.append(
						$(
							'<span class="coverfold-media-item__name"></span>'
						).text( attachment.title || coverfoldAdmin.video )
					);
			} else {
				const size =
					attachment.sizes && attachment.sizes.thumbnail
						? attachment.sizes.thumbnail
						: attachment;
				$li.append( $( '<img alt="">' ).attr( 'src', size.url ) );
			}
			$li.append(
				$(
					'<button type="button" class="coverfold-media-item__remove">&times;</button>'
				).attr( 'aria-label', coverfoldAdmin.remove )
			);
			return $li;
		}

		$list.sortable( {
			items: '.coverfold-media-item',
			placeholder:
				'coverfold-media-item coverfold-media-item--placeholder',
			tolerance: 'pointer',
			update: sync,
		} );

		$list.on( 'click', '.coverfold-media-item__remove', function () {
			$( this ).closest( '.coverfold-media-item' ).remove();
			sync();
		} );

		$clear.on( 'click', function () {
			$list.empty();
			sync();
		} );

		$( '#coverfold-add-media' ).on( 'click', function ( e ) {
			e.preventDefault();

			if ( frame ) {
				frame.open();
				return;
			}

			frame = wp.media( {
				title: coverfoldAdmin.frameTitle,
				button: { text: coverfoldAdmin.frameButton },
				library: { type: [ 'image', 'video' ] },
				multiple: 'add',
			} );

			frame.on( 'select', function () {
				const existing = $input.val() ? $input.val().split( ',' ) : [];
				frame
					.state()
					.get( 'selection' )
					.each( function ( model ) {
						const attachment = model.toJSON();
						if (
							existing.indexOf( String( attachment.id ) ) === -1
						) {
							$list.append( itemHtml( attachment ) );
							existing.push( String( attachment.id ) );
						}
					} );
				sync();
			} );

			frame.open();
		} );
	} );
} )( jQuery );
