/**
 * StackFolio front end: category filters (Isotope) and album lightbox (lightGallery).
 */
( function () {
	'use strict';

	var PLUGINS = {
		video: 'lgVideo',
		thumbnail: 'lgThumbnail',
		zoom: 'lgZoom',
		fullscreen: 'lgFullscreen',
		autoplay: 'lgAutoplay',
	};

	function getConfig( root ) {
		try {
			return JSON.parse( root.getAttribute( 'data-stackfolio' ) ) || {};
		} catch ( e ) {
			return {};
		}
	}

	function openAlbum( card ) {
		var root = card.closest( '.stackfolio' );
		var config = root ? getConfig( root ) : {};
		var items;

		if ( typeof window.lightGallery !== 'function' ) {
			return;
		}

		try {
			items = JSON.parse( card.getAttribute( 'data-items' ) );
		} catch ( e ) {
			return;
		}
		if ( ! items || ! items.length ) {
			return;
		}

		var plugins = ( config.plugins || [] )
			.map( function ( name ) {
				return window[ PLUGINS[ name ] ];
			} )
			.filter( Boolean );

		var classes = [ 'stackfolio-lightbox' ];
		if ( config.hideThumbsFullscreen ) {
			classes.push( 'stackfolio-lightbox--hide-thumbs-fs' );
		}

		var gallery = window.lightGallery( card, {
			dynamic: true,
			dynamicEl: items,
			plugins: plugins,
			// lightGallery is used under its GPLv3 licence; StackFolio is GPLv3 too.
			licenseKey: 'GPLv3',
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

		card.addEventListener( 'lgAfterClose', function onClose() {
			card.removeEventListener( 'lgAfterClose', onClose );
			gallery.destroy();
			card.focus();
		} );

		gallery.openGallery( 0 );
	}

	function setActive( buttons, active ) {
		buttons.forEach( function ( button ) {
			var isActive = button === active;
			button.classList.toggle( 'is-active', isActive );
			button.setAttribute( 'aria-pressed', isActive ? 'true' : 'false' );
		} );
	}

	function initFilters( root ) {
		var grid = root.querySelector( '.stackfolio-grid' );
		var buttons = Array.prototype.slice.call( root.querySelectorAll( '.stackfolio-filter' ) );
		if ( ! grid || ! buttons.length ) {
			return;
		}

		var active = root.querySelector( '.stackfolio-filter.is-active' ) || buttons[ 0 ];
		var iso = null;

		function applyFallback( filter ) {
			Array.prototype.forEach.call( grid.querySelectorAll( '.stackfolio-item' ), function ( item ) {
				item.hidden = filter !== '*' && ! item.matches( filter );
			} );
		}

		if ( typeof window.Isotope === 'function' ) {
			iso = new window.Isotope( grid, {
				itemSelector: '.stackfolio-item',
				layoutMode: 'fitRows',
				percentPosition: true,
				filter: active.getAttribute( 'data-filter' ),
			} );
			Array.prototype.forEach.call( grid.querySelectorAll( 'img' ), function ( img ) {
				if ( ! img.complete ) {
					img.addEventListener( 'load', function () {
						iso.layout();
					}, { once: true } );
				}
			} );
		} else {
			applyFallback( active.getAttribute( 'data-filter' ) );
		}

		buttons.forEach( function ( button ) {
			button.addEventListener( 'click', function () {
				var filter = button.getAttribute( 'data-filter' );
				setActive( buttons, button );
				if ( iso ) {
					iso.arrange( { filter: filter } );
				} else {
					applyFallback( filter );
				}
			} );
		} );
	}

	function init( context ) {
		var scope = context || document;
		var roots = scope.matches && scope.matches( '.stackfolio' ) ? [ scope ] : scope.querySelectorAll( '.stackfolio' );

		Array.prototype.forEach.call( roots, function ( root ) {
			if ( root.getAttribute( 'data-stackfolio-ready' ) ) {
				return;
			}
			root.setAttribute( 'data-stackfolio-ready', '1' );
			if ( root.classList.contains( 'stackfolio--filterable' ) ) {
				initFilters( root );
			}
		} );
	}

	// One delegated listener opens any album, including grids added later.
	document.addEventListener( 'click', function ( event ) {
		var card = event.target.closest && event.target.closest( '.stackfolio .stackfolio-card[data-items]' );
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
	var elementorHooked = false;
	function hookElementor() {
		if ( elementorHooked || ! window.elementorFrontend || ! window.elementorFrontend.hooks ) {
			return;
		}
		elementorHooked = true;
		[ 'stackfolio-album-grid.default', 'as-gallery.default' ].forEach( function ( name ) {
			window.elementorFrontend.hooks.addAction( 'frontend/element_ready/' + name, function ( $scope ) {
				init( $scope[ 0 ] || $scope );
			} );
		} );
	}
	hookElementor();
	if ( window.jQuery ) {
		window.jQuery( window ).on( 'elementor/frontend/init', hookElementor );
	}

	window.StackFolio = { init: init, open: openAlbum };
}() );
