<?php
/**
 * Plugin Name:       StackFolio
 * Plugin URI:        https://wpgenius.in/
 * Description:       Stunning photo and video albums. One cover image opens the full album in a popup, with a filterable grid for Elementor and Gutenberg.
 * Version:           1.0.1
 * Requires at least: 6.3
 * Requires PHP:      7.4
 * Author:            Team WPGenius
 * Author URI:        https://wpgenius.in/
 * License:           GPL-3.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain:       stackfolio
 *
 * @package StackFolio
 */

defined( 'ABSPATH' ) || exit;

define( 'STACKFOLIO_VERSION', '1.0.1' );
define( 'STACKFOLIO_FILE', __FILE__ );
define( 'STACKFOLIO_PATH', plugin_dir_path( __FILE__ ) );
define( 'STACKFOLIO_URL', plugin_dir_url( __FILE__ ) );

require_once STACKFOLIO_PATH . 'includes/class-stackfolio-post-type.php';
require_once STACKFOLIO_PATH . 'includes/class-stackfolio-settings.php';
require_once STACKFOLIO_PATH . 'includes/class-stackfolio-videos.php';
require_once STACKFOLIO_PATH . 'includes/class-stackfolio-album.php';
require_once STACKFOLIO_PATH . 'includes/class-stackfolio-meta-box.php';
require_once STACKFOLIO_PATH . 'includes/class-stackfolio-assets.php';
require_once STACKFOLIO_PATH . 'includes/class-stackfolio-renderer.php';
require_once STACKFOLIO_PATH . 'includes/class-stackfolio-shortcode.php';
require_once STACKFOLIO_PATH . 'includes/class-stackfolio-block.php';
require_once STACKFOLIO_PATH . 'includes/class-stackfolio-migration.php';
require_once STACKFOLIO_PATH . 'includes/elementor/class-stackfolio-elementor.php';

StackFolio_Post_Type::init();
StackFolio_Settings::init();
StackFolio_Meta_Box::init();
StackFolio_Assets::init();
StackFolio_Shortcode::init();
StackFolio_Block::init();
StackFolio_Migration::init();
StackFolio_Elementor::init();

register_activation_hook( __FILE__, array( 'StackFolio_Migration', 'activate' ) );
register_deactivation_hook( __FILE__, 'flush_rewrite_rules' );
