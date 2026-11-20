<?php
/**
 * Unit test bootstrap: loads Coverfold's classes without WordPress.
 * WordPress functions are mocked per test with Brain Monkey.
 *
 * @package Coverfold
 */

require_once dirname( __DIR__ ) . '/vendor/autoload.php';

define( 'ABSPATH', __DIR__ . '/' );
define( 'HOUR_IN_SECONDS', 3600 );
define( 'DAY_IN_SECONDS', 86400 );
define( 'COVERFOLD_VERSION', '0.0.0-test' );
define( 'COVERFOLD_FILE', dirname( __DIR__ ) . '/coverfold.php' );
define( 'COVERFOLD_PATH', dirname( __DIR__ ) . '/' );
define( 'COVERFOLD_URL', 'https://example.test/wp-content/plugins/coverfold/' );

require_once COVERFOLD_PATH . 'includes/class-coverfold-videos.php';
require_once COVERFOLD_PATH . 'includes/class-coverfold-album.php';
