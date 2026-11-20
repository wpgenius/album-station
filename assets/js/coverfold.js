/**
 * Coverfold front end: animated category filters and the album lightbox.
 */
( function () {
	'use strict';

	const PLUGINS = {
		video: 'lgVideo',
		thumbnail: 'lgThumbnail',
		zoom: 'lgZoom',
		fullscreen: 'lgFullscreen',
		autoplay: 'lgAutoplay',
	};

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
		if ( isOpen ) {
			return;
		}
		const root = card.closest( '.coverfold' );
		const config = root ? getConfig( root ) : {};
		let items;

		if ( typeof window.lightGallery !== 'function' ) {
			return;
		}

		try {
			items = JSON.parse( card.getAttribute( 'data-items' ) );
		} catch {
			return;
		}
		if ( ! items || ! items.length ) {
			return;
		}

		const plugins = ( config.plugins || [] )
			.map( function ( name ) {
				return window[ PLUGINS[ name ] ];
			} )
			.filter( Boolean );

		const classes = [ 'coverfold-lightbox' ];
		if ( config.hideThumbsFullscreen ) {
			classes.push( 'coverfold-lightbox--hide-thumbs-fs' );
		}

		const gallery = window.lightGallery( card, {
			dynamic: true,
			dynamicEl: items,
			plugins,
			addClass: classes.join( ' ' ),
			download: !! config.download,
			slideShowAutoplay: !! config.slideshowAutostart,
			autoplayFirstVideo: false,
			loadYouTubeThumbnail: false,
			loadVimeoThumbnail: false,
			youTubePlayerParams: { rel: 0, modestbranding: 1 },
			mobileSettings: {
				controls: false,
				showCloseIcon: true,
				download: !! config.download,
			},
		} );

		isOpen = true;
		card.addEventListener( 'lgAfterClose', function onClose() {
			card.removeEventListener( 'lgAfterClose', onClose );
			gallery.destroy();
			isOpen = false;
			card.focus();
		} );

		try {
			gallery.openGallery( 0 );
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

	const DURATION = 400;

	// Show the matching items. Cards that stay glide to their new place, new
	// cards scale in and removed cards scale out (FLIP, no library needed).
	function applyFilter( grid, filter ) {
		const items = Array.prototype.slice.call(
			grid.querySelectorAll( ':scope > .coverfold-item' )
		);
		const matches = function ( item ) {
			return filter === '*' || item.matches( filter );
		};
		const animate =
			typeof window.Element.prototype.animate === 'function' &&
			! (
				window.matchMedia &&
				window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches
			);

		if ( ! animate ) {
			items.forEach( function ( item ) {
				item.hidden = ! matches( item );
			} );
			return;
		}

		const gridRect = grid.getBoundingClientRect();
		const before = new Map();
		items.forEach( function ( item ) {
			if ( ! item.hidden ) {
				before.set( item, item.getBoundingClientRect() );
			}
		} );

		// Ghosts of the leaving cards, so they can fade out where they were.
		const ghosts = [];
		items.forEach( function ( item ) {
			if ( item.hidden || matches( item ) ) {
				return;
			}
			const rect = before.get( item );
			const ghost = item.cloneNode( true );
			ghost.setAttribute( 'aria-hidden', 'true' );
			ghost.classList.add( 'coverfold-item--ghost' );
			ghost.style.left = rect.left - gridRect.left + 'px';
			ghost.style.top = rect.top - gridRect.top + 'px';
			ghost.style.width = rect.width + 'px';
			ghosts.push( ghost );
		} );

		items.forEach( function ( item ) {
			item.hidden = ! matches( item );
		} );

		ghosts.forEach( function ( ghost ) {
			grid.appendChild( ghost );
			ghost
				.animate(
					[
						{ opacity: 1, transform: 'scale(1)' },
						{ opacity: 0, transform: 'scale(0.6)' },
					],
					{ duration: DURATION, easing: 'ease-in' }
				)
				.finished.then( function () {
					ghost.remove();
				} );
		} );

		items.forEach( function ( item ) {
			if ( item.hidden ) {
				return;
			}
			const old = before.get( item );
			if ( ! old ) {
				item.animate(
					[
						{ opacity: 0, transform: 'scale(0.6)' },
						{ opacity: 1, transform: 'scale(1)' },
					],
					{ duration: DURATION, easing: 'ease-out' }
				);
				return;
			}
			const now = item.getBoundingClientRect();
			const dx = old.left - now.left;
			const dy = old.top - now.top;
			if ( dx || dy ) {
				item.animate(
					[
						{ transform: 'translate(' + dx + 'px, ' + dy + 'px)' },
						{ transform: 'none' },
					],
					{ duration: DURATION, easing: 'ease' }
				);
			}
		} );
	}

	function initFilters( root ) {
		const grid = root.querySelector( '.coverfold-grid' );
		const buttons = Array.prototype.slice.call(
			root.querySelectorAll( '.coverfold-filter' )
		);
		if ( ! grid || ! buttons.length ) {
			return;
		}

		const active =
			root.querySelector( '.coverfold-filter.is-active' ) || buttons[ 0 ];
		const initial = active.getAttribute( 'data-filter' ) || '*';
		Array.prototype.forEach.call(
			grid.querySelectorAll( ':scope > .coverfold-item' ),
			function ( item ) {
				item.hidden = initial !== '*' && ! item.matches( initial );
			}
		);

		buttons.forEach( function ( button ) {
			button.addEventListener( 'click', function () {
				if ( button.classList.contains( 'is-active' ) ) {
					return;
				}
				setActive( buttons, button );
				applyFilter(
					grid,
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
