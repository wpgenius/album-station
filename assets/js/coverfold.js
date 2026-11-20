/**
 * Coverfold front end: animated category filters and the album lightbox (Foldbox).
 */
( function () {
	'use strict';

	function getConfig( root ) {
		try {
			return JSON.parse( root.getAttribute( 'data-coverfold' ) ) || {};
		} catch {
			return {};
		}
	}

	// Only one lightbox at a time, so a double-click doesn't open two.
	let isOpen = false;

	function openAlbum( card ) {
		if ( isOpen || ! window.Foldbox ) {
			return;
		}
		const root = card.closest( '.coverfold' );
		const config = root ? getConfig( root ) : {};
		let items;

		try {
			items = JSON.parse( card.getAttribute( 'data-items' ) );
		} catch {
			return;
		}
		if ( ! items || ! items.length ) {
			return;
		}

		const l10n = window.coverfoldL10n || {};
		isOpen = true;
		try {
			window.Foldbox.open(
				items,
				Object.assign( {}, config, {
					origin: card.querySelector( 'img' ) || card,
					className: 'coverfold-lightbox',
					labels: l10n.labels || {},
					onClose() {
						isOpen = false;
						card.focus();
					},
				} )
			);
		} catch ( error ) {
			isOpen = false;
			throw error;
		}
	}

	function setActive( buttons, active ) {
		buttons.forEach( function ( button ) {
			const isActive = button === active;
			button.classList.toggle( 'is-active', isActive );
			button.setAttribute( 'aria-pressed', isActive ? 'true' : 'false' );
		} );
	}

	// Cards that stay glide to their new place, new cards scale in and
	// removed cards scale out (Foldbox grid).
	function initFilters( root ) {
		const grid = root.querySelector( '.coverfold-grid' );
		const buttons = Array.prototype.slice.call(
			root.querySelectorAll( '.coverfold-filter' )
		);
		if ( ! grid || ! buttons.length || ! window.Foldbox ) {
			return;
		}

		const filterable = window.Foldbox.grid( grid, {
			itemSelector: ':scope > .coverfold-item',
		} );
		const active =
			root.querySelector( '.coverfold-filter.is-active' ) || buttons[ 0 ];
		filterable.filter( active.getAttribute( 'data-filter' ) || '*', true );

		buttons.forEach( function ( button ) {
			button.addEventListener( 'click', function () {
				if ( button.classList.contains( 'is-active' ) ) {
					return;
				}
				setActive( buttons, button );
				filterable.filter(
					button.getAttribute( 'data-filter' ) || '*'
				);
			} );
		} );
	}

	function init( context ) {
		const scope = context || document;
		const roots =
			scope.matches && scope.matches( '.coverfold' )
				? [ scope ]
				: scope.querySelectorAll( '.coverfold' );

		Array.prototype.forEach.call( roots, function ( root ) {
			if ( root.getAttribute( 'data-coverfold-ready' ) ) {
				return;
			}
			root.setAttribute( 'data-coverfold-ready', '1' );
			if ( root.classList.contains( 'coverfold--filterable' ) ) {
				initFilters( root );
			}
		} );
	}

	// One delegated listener opens any album, including grids added later.
	document.addEventListener( 'click', function ( event ) {
		const card =
			event.target.closest &&
			event.target.closest( '.coverfold .coverfold-card[data-items]' );
		if ( card ) {
			event.preventDefault();
			openAlbum( card );
		}
	} );

	if ( document.readyState === 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', function () {
			init();
		} );
	} else {
		init();
	}

	// Elementor: re-initialise widgets rendered in the editor preview.
	let elementorHooked = false;
	function hookElementor() {
		if (
			elementorHooked ||
			! window.elementorFrontend ||
			! window.elementorFrontend.hooks
		) {
			return;
		}
		elementorHooked = true;
		[ 'coverfold-album-grid.default', 'as-gallery.default' ].forEach(
			function ( name ) {
				window.elementorFrontend.hooks.addAction(
					'frontend/element_ready/' + name,
					function ( $scope ) {
						init( $scope[ 0 ] || $scope );
					}
				);
			}
		);
	}
	hookElementor();
	if ( window.jQuery ) {
		window.jQuery( window ).on( 'elementor/frontend/init', hookElementor );
	}

	window.Coverfold = { init, open: openAlbum };
} )();
