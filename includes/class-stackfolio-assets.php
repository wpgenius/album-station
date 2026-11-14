<?php
/**
 * Front-end asset registration.
 *
 * @package StackFolio
 */

defined( 'ABSPATH' ) || exit;

/**
 * Registers lightGallery, Isotope and the StackFolio front-end files.
 * They are enqueued only where a grid is rendered.
 */
class StackFolio_Assets {

	const LIGHTGALLERY_VERSION = '2.9.0';
	const ISOTOPE_VERSION      = '3.0.6';
	const VIMEO_PLAYER_VERSION = '2.30.4';

	/**
	 * Lightbox plugin handles keyed by StackFolio feature name.
	 *
	 * @var string[]
	 */
	const LG_PLUGINS = array(
		'video'      => 'lg-video',
		'thumbnail'  => 'lg-thumbnail',
		'zoom'       => 'lg-zoom',
		'fullscreen' => 'lg-fullscreen',
		'autoplay'   => 'lg-autoplay',
	);

	/**
	 * Hook everything up.
	 */
	public static function init() {
		add_action( 'init', array( __CLASS__, 'register' ), 5 );
	}

	/**
	 * Register all handles.
	 */
	public static function register() {
		$vendor = STACKFOLIO_URL . 'assets/vendor/';

		wp_register_style( 'stackfolio-lightgallery', $vendor . 'lightgallery/css/lightgallery-bundle.min.css', array(), self::LIGHTGALLERY_VERSION );
		wp_register_style( 'stackfolio', STACKFOLIO_URL . 'assets/css/stackfolio.css', array( 'stackfolio-lightgallery' ), STACKFOLIO_VERSION );

		$strategy = array(
			'in_footer' => true,
			'strategy'  => 'defer',
		);

		wp_register_script( 'stackfolio-lightgallery', $vendor . 'lightgallery/lightgallery.min.js', array(), self::LIGHTGALLERY_VERSION, $strategy );

		// lightGallery's video plugin uses the Vimeo Player API to pause and play Vimeo videos.
		wp_register_script( 'stackfolio-vimeo-player', $vendor . 'vimeo-player/player.min.js', array(), self::VIMEO_PLAYER_VERSION, $strategy );

		$deps = array( 'stackfolio-lightgallery' );
		foreach ( self::LG_PLUGINS as $file ) {
			$handle      = 'stackfolio-' . $file;
			$plugin_deps = 'lg-video' === $file ? array( 'stackfolio-lightgallery', 'stackfolio-vimeo-player' ) : array( 'stackfolio-lightgallery' );
			wp_register_script( $handle, $vendor . 'lightgallery/plugins/' . $file . '.min.js', $plugin_deps, self::LIGHTGALLERY_VERSION, $strategy );
			$deps[] = $handle;
		}

		wp_register_script( 'stackfolio-isotope', $vendor . 'isotope/isotope.pkgd.min.js', array(), self::ISOTOPE_VERSION, $strategy );
		wp_register_script( 'stackfolio', STACKFOLIO_URL . 'assets/js/stackfolio.js', $deps, STACKFOLIO_VERSION, $strategy );
		wp_localize_script(
			'stackfolio',
			'stackfolioL10n',
			array(
				'openAlbum' => __( 'Open album', 'stackfolio' ),
			)
		);
	}

	/**
	 * Enqueue what a rendered grid needs.
	 *
	 * @param bool $filters Whether the grid has category filters (needs Isotope).
	 */
	public static function enqueue( $filters = false ) {
		wp_enqueue_style( 'stackfolio' );
		if ( $filters ) {
			wp_enqueue_script( 'stackfolio-isotope' );
		}
		wp_enqueue_script( 'stackfolio' );
	}
}
