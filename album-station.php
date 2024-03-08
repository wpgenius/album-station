<?php
/**
 * Plugin Name:     Album Station
 * Plugin URI:      https://wpgenius.in/
 * Description:     Manage portfolio based on album concept using LightGallery.js.
 * Author:          Team WPGenius
 * Author URI:      https://wpgenius.in/
 * Text Domain:     album-station
 * Domain Path:     /languages
 * Version:         0.1.0
 *
 * @package         Album_Station
 */

// Your code starts here.

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'WBC_DIR_URL', plugin_dir_url( __FILE__ ) );
define( 'WBC_DIR_PATH', plugin_dir_path( __FILE__ ) );

require 'post-types/album.php';
require 'includes/shortcode.php';
require 'includes/elementor-widgets.php';
require 'includes/admin-settings.php';


function album_station_css() {
	wp_enqueue_style( 'album-style', WBC_DIR_URL . '/assets/css/album-station.css' );

}
add_action( 'admin_menu', 'album_station_css' );

function album_station_js() {

	// I recommend to add additional conditions just to not to load the scipts on each page

	if ( ! did_action( 'wp_enqueue_media' ) ) {
		wp_enqueue_media();
	}

	wp_enqueue_script( 'admin-albumstation', WBC_DIR_URL . '/assets/js/admin-albumstation.js', array( 'jquery' ) );
}
add_action( 'admin_enqueue_scripts', 'album_station_js' );

add_action( 'after_setup_theme', 'wpdocs_theme_setup' );
function wpdocs_theme_setup() {
	add_image_size( 'album-size', 280, 185, true );
}
