<?php
/**
 * Front-end asset registration.
 *
 * @package Coverfold
 */

defined( 'ABSPATH' ) || exit;

/**
 * Registers the lightbox and the Coverfold front-end files.
 * They are enqueued only where a grid is rendered.
 */
class Coverfold_Assets {

	const LIGHTGALLERY_VERSION = '2.9.0';
	const VIMEO_PLAYER_VERSION = '2.30.4';

	/**
	 * Lightbox plugin handles keyed by Coverfold feature name.
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
		$vendor = COVERFOLD_URL . 'assets/vendor/';

		wp_register_style( 'coverfold-lightgallery', $vendor . 'lightgallery/css/lightgallery-bundle.min.css', array(), self::LIGHTGALLERY_VERSION );
		wp_register_style( 'coverfold', COVERFOLD_URL . 'assets/css/coverfold.css', array( 'coverfold-lightgallery' ), COVERFOLD_VERSION );

		$strategy = array(
			'in_footer' => true,
			'strategy'  => 'defer',
		);

		wp_register_script( 'coverfold-lightgallery', $vendor . 'lightgallery/lightgallery.min.js', array(), self::LIGHTGALLERY_VERSION, $strategy );

		// lightGallery's video plugin uses the Vimeo Player API to pause and play Vimeo videos.
		wp_register_script( 'coverfold-vimeo-player', $vendor . 'vimeo-player/player.min.js', array(), self::VIMEO_PLAYER_VERSION, $strategy );

		$deps = array( 'coverfold-lightgallery' );
		foreach ( self::LG_PLUGINS as $file ) {
			$handle      = 'coverfold-' . $file;
			$plugin_deps = 'lg-video' === $file ? array( 'coverfold-lightgallery', 'coverfold-vimeo-player' ) : array( 'coverfold-lightgallery' );
			wp_register_script( $handle, $vendor . 'lightgallery/plugins/' . $file . '.min.js', $plugin_deps, self::LIGHTGALLERY_VERSION, $strategy );
			$deps[] = $handle;
		}

		wp_register_script( 'coverfold', COVERFOLD_URL . 'assets/js/coverfold.js', $deps, COVERFOLD_VERSION, $strategy );
		wp_localize_script(
			'coverfold',
			'coverfoldL10n',
			array(
				'openAlbum' => __( 'Open album', 'coverfold' ),
			)
		);
	}

	/**
	 * Enqueue what a rendered grid needs.
	 */
	public static function enqueue() {
		wp_enqueue_style( 'coverfold' );
		wp_enqueue_script( 'coverfold' );
	}
}
