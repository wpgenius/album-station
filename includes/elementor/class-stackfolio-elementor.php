<?php
/**
 * Elementor integration.
 *
 * @package StackFolio
 */

defined( 'ABSPATH' ) || exit;

/**
 * Registers the StackFolio Elementor widget when Elementor is active.
 */
class StackFolio_Elementor {

	/**
	 * Hook everything up.
	 */
	public static function init() {
		add_action( 'elementor/widgets/register', array( __CLASS__, 'register_widgets' ) );
	}

	/**
	 * Register widgets.
	 *
	 * @param \Elementor\Widgets_Manager $widgets_manager Widgets manager.
	 */
	public static function register_widgets( $widgets_manager ) {
		require_once __DIR__ . '/class-stackfolio-elementor-widget.php';
		$widgets_manager->register( new StackFolio_Elementor_Widget() );

		// Keep pages built with Album Station's "Album Gallery" widget working,
		// unless Album Station itself is still active and registers it.
		if ( ! class_exists( 'AS_Elementor_Widgets' ) ) {
			require_once __DIR__ . '/class-stackfolio-elementor-legacy-widget.php';
			$widgets_manager->register( new StackFolio_Elementor_Legacy_Widget() );
		}
	}
}
