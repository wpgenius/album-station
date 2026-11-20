<?php
/**
 * Plugin Name:       Coverfold Album Gallery
 * Plugin URI:        https://github.com/wpgenius/coverfold
 * Description:       Stunning photo and video albums. One cover image opens the full album in a popup, with a filterable grid for Elementor and Gutenberg.
 * Version:           2.1.0
 * Requires at least: 6.3
 * Requires PHP:      7.4
 * Author:            Team WPGenius
 * Author URI:        https://profiles.wordpress.org/wpgenius/
 * License:           GPL-3.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain:       coverfold
 *
 * @package Coverfold
 */

defined( 'ABSPATH' ) || exit;

define( 'COVERFOLD_VERSION', '2.1.0' );
define( 'COVERFOLD_FILE', __FILE__ );
define( 'COVERFOLD_PATH', plugin_dir_path( __FILE__ ) );
define( 'COVERFOLD_URL', plugin_dir_url( __FILE__ ) );

require_once COVERFOLD_PATH . 'includes/class-coverfold-post-type.php';
require_once COVERFOLD_PATH . 'includes/class-coverfold-settings.php';
require_once COVERFOLD_PATH . 'includes/class-coverfold-videos.php';
require_once COVERFOLD_PATH . 'includes/class-coverfold-album.php';
require_once COVERFOLD_PATH . 'includes/class-coverfold-meta-box.php';
require_once COVERFOLD_PATH . 'includes/class-coverfold-assets.php';
require_once COVERFOLD_PATH . 'includes/class-coverfold-renderer.php';
require_once COVERFOLD_PATH . 'includes/class-coverfold-shortcode.php';
require_once COVERFOLD_PATH . 'includes/class-coverfold-block.php';
require_once COVERFOLD_PATH . 'includes/class-coverfold-migration.php';
require_once COVERFOLD_PATH . 'includes/elementor/class-coverfold-elementor.php';

Coverfold_Post_Type::init();
Coverfold_Settings::init();
Coverfold_Videos::init();
Coverfold_Meta_Box::init();
Coverfold_Assets::init();
Coverfold_Shortcode::init();
Coverfold_Block::init();
Coverfold_Migration::init();
Coverfold_Elementor::init();

register_activation_hook( __FILE__, array( 'Coverfold_Migration', 'activate' ) );
register_deactivation_hook( __FILE__, 'flush_rewrite_rules' );
