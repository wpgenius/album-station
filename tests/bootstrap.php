<?php
/**
 * Unit test bootstrap: loads StackFolio's classes without WordPress.
 * WordPress functions are mocked per test with Brain Monkey.
 *
 * @package StackFolio
 */

require_once dirname( __DIR__ ) . '/vendor/autoload.php';

define( 'ABSPATH', __DIR__ . '/' );
define( 'HOUR_IN_SECONDS', 3600 );
define( 'STACKFOLIO_VERSION', '0.0.0-test' );
define( 'STACKFOLIO_FILE', dirname( __DIR__ ) . '/stackfolio.php' );
define( 'STACKFOLIO_PATH', dirname( __DIR__ ) . '/' );
define( 'STACKFOLIO_URL', 'https://example.test/wp-content/plugins/stackfolio/' );

require_once STACKFOLIO_PATH . 'includes/class-stackfolio-videos.php';
require_once STACKFOLIO_PATH . 'includes/class-stackfolio-album.php';
