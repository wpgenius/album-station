<?php
/**
 * Front-end asset registration.
 *
 * @package Coverfold
 */

defined( 'ABSPATH' ) || exit;

/**
 * Registers Foldbox and the Coverfold front-end files.
 * They are enqueued only where a grid is rendered.
 */
class Coverfold_Assets {

	const FOLDBOX_VERSION = '1.1.1';

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
		$vendor = COVERFOLD_URL . 'assets/vendor/foldbox/';

		wp_register_style( 'coverfold-foldbox', $vendor . 'foldbox.min.css', array(), self::FOLDBOX_VERSION );
		wp_register_style( 'coverfold', COVERFOLD_URL . 'assets/css/coverfold.css', array( 'coverfold-foldbox' ), COVERFOLD_VERSION );

		$strategy = array(
			'in_footer' => true,
			'strategy'  => 'defer',
		);

		wp_register_script( 'coverfold-foldbox', $vendor . 'foldbox.min.js', array(), self::FOLDBOX_VERSION, $strategy );
		wp_register_script( 'coverfold', COVERFOLD_URL . 'assets/js/coverfold.js', array( 'coverfold-foldbox' ), COVERFOLD_VERSION, $strategy );
		wp_localize_script(
			'coverfold',
			'coverfoldL10n',
			array(
				'labels' => array(
					'dialog'         => __( 'Album', 'coverfold' ),
					'close'          => __( 'Close', 'coverfold' ),
					'prev'           => __( 'Previous', 'coverfold' ),
					'next'           => __( 'Next', 'coverfold' ),
					'zoomIn'         => __( 'Zoom in', 'coverfold' ),
					'zoomOut'        => __( 'Zoom out', 'coverfold' ),
					'fullscreen'     => __( 'Fullscreen', 'coverfold' ),
					'exitFullscreen' => __( 'Exit fullscreen', 'coverfold' ),
					'play'           => __( 'Start slideshow', 'coverfold' ),
					'pause'          => __( 'Pause slideshow', 'coverfold' ),
					'download'       => __( 'Download', 'coverfold' ),
					'playVideo'      => __( 'Play video', 'coverfold' ),
					/* translators: %1: current item number, %2: number of items. Keep %1 and %2 as they are. */
					'counter'        => __( '%1 / %2', 'coverfold' ),
					'error'          => __( 'This item could not be loaded.', 'coverfold' ),
				),
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
