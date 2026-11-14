<?php
/**
 * Uninstall: remove plugin settings. Albums are your content and are kept.
 *
 * @package StackFolio
 */

defined( 'WP_UNINSTALL_PLUGIN' ) || exit;

delete_option( 'stackfolio_settings' );
delete_option( 'stackfolio_db_version' );
delete_option( 'stackfolio_migration_notice' );
delete_option( 'stackfolio_cache_version' );
