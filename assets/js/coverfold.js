/**
 * Coverfold front end: category filters (Isotope) and album lightbox (lightGallery).
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

	function initFilters( root ) {
		const grid = root.querySelector( '.coverfold-grid' );
		const buttons = Array.prototype.slice.call(
			root.querySelectorAll( '.coverfold-filter' )
		);
		if ( ! grid || ! buttons.length ) {
			return;
		}

		const active =
			root.querySelector( '.coverfold-filter.is-active' ) ||
			buttons[ 0 ];
		let iso = null;

		function applyFallback( filter ) {
			Array.prototype.forEach.call(
				grid.querySelectorAll( '.coverfold-item' ),
				function ( item ) {
					item.hidden = filter !== '*' && ! item.matches( filter );
				}
			);
		}

		if ( typeof window.Isotope === 'function' ) {
			iso = new window.Isotope( grid, {
				itemSelector: '.coverfold-item',
				layoutMode: 'fitRows',
				percentPosition: true,
				filter: active.getAttribute( 'data-filter' ),
			} );
			Array.prototype.forEach.call(
				grid.querySelectorAll( 'img' ),
				function ( img ) {
					if ( ! img.complete ) {
						img.addEventListener(
							'load',
							function () {
								iso.layout();
							},
							{ once: true }
						);
					}
				}
			);
		} else {
			applyFallback( active.getAttribute( 'data-filter' ) );
		}

		buttons.forEach( function ( button ) {
			button.addEventListener( 'click', function () {
				const filter = button.getAttribute( 'data-filter' );
				setActive( buttons, button );
				if ( iso ) {
					iso.arrange( { filter } );
				} else {
					applyFallback( filter );
				}
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
