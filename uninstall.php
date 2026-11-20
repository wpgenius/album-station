<?php
/**
 * Uninstall: remove plugin settings. Albums are your content and are kept.
 *
 * @package Coverfold
 */

defined( 'WP_UNINSTALL_PLUGIN' ) || exit;

delete_option( 'coverfold_settings' );
delete_option( 'coverfold_db_version' );
delete_option( 'coverfold_migration_notice' );
delete_option( 'coverfold_cache_version' );
