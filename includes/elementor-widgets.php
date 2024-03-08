<?php

//https://develowp.com/build-a-custom-elementor-widget/

class AS_Elementor_Widgets {

	protected static $instance = null;

	public static function get_instance() {
		if ( ! isset( static::$instance ) ) {
			static::$instance = new static;
		}

		return static::$instance;
	}

	protected function __construct() {
		// Register widget Styles
		add_action( 'elementor/frontend/before_enqueue_styles', [ $this, 'widget_styles' ] );
		// Register widget scripts
		add_action( 'elementor/frontend/after_register_scripts', [ $this, 'widget_scripts' ] );
		// Register widgets
		add_action( 'elementor/widgets/widgets_registered', [ $this, 'register_widgets' ] );
	}
	public function widget_styles() {

		wp_enqueue_style( 'album-style', WBC_DIR_URL . '/assets/css/album-station.css' );
		wp_enqueue_style( 'lightgallery-medium-zoom', WBC_DIR_URL . 'assets/css/lightgallery.css' );

	}

	public function widget_scripts() {

		wp_enqueue_script( 'lightgallery-combined', WBC_DIR_URL . 'assets/js/lightgallery-combined.js', array( 'jquery' ) );
		wp_enqueue_script( 'albumstation', WBC_DIR_URL . 'assets/js/albumstation.js', array( 'jquery' ) );
	}

	public function register_widgets() {

		require_once( __DIR__.'/widgets/album-widget.php');
		\Elementor\Plugin::instance()->widgets_manager->register_widget_type( new \Elementor\Album_Station_widget() );
	}

}

add_action( 'init', 'as_elementor_widgets' );
function as_elementor_widgets() {
	AS_Elementor_Widgets::get_instance();
}
