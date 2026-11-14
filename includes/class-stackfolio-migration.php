<?php
/**
 * One-time migration from Album Station.
 *
 * @package StackFolio
 */

defined( 'ABSPATH' ) || exit;

/**
 * Moves Album Station albums, categories and settings into StackFolio.
 *
 * Only posts of type `album` that carry Album Station meta are moved, so an
 * unrelated `album` post type from another plugin is left alone. Old meta is
 * kept, nothing is deleted.
 */
class StackFolio_Migration {

	const DB_VERSION       = 1;
	const VERSION_OPTION   = 'stackfolio_db_version';
	const NOTICE_OPTION    = 'stackfolio_migration_notice';
	const LEGACY_POST_TYPE = 'album';
	const LEGACY_TAXONOMY  = 'album_category';
	// Not `video_popup`: Album Station saved it on every post of every type.
	const LEGACY_META_KEYS  = array( 'image_post_meta_value', 'youtube_video_post_meta_value', 'vimeo_video_post_meta_value', 'playlist_id', 'video_choice' );
	const LEGACY_API_OPTION = 'youtube_api_key';

	/**
	 * Hook everything up.
	 */
	public static function init() {
		add_action( 'admin_init', array( __CLASS__, 'maybe_migrate' ) );
		add_action( 'admin_notices', array( __CLASS__, 'notice' ) );
	}

	/**
	 * Activation hook.
	 */
	public static function activate() {
		self::maybe_migrate();
	}

	/**
	 * Run the migration once per site.
	 */
	public static function maybe_migrate() {
		if ( (int) get_option( self::VERSION_OPTION, 0 ) >= self::DB_VERSION ) {
			return;
		}
		$moved = self::migrate();
		update_option( self::VERSION_OPTION, self::DB_VERSION );
		if ( $moved > 0 ) {
			update_option( self::NOTICE_OPTION, $moved, false );
		}
	}

	/**
	 * IDs of Album Station albums still to move.
	 *
	 * @return int[]
	 */
	public static function legacy_album_ids() {
		global $wpdb;
		$keys         = self::LEGACY_META_KEYS;
		$placeholders = implode( ',', array_fill( 0, count( $keys ), '%s' ) );

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQLPlaceholders.UnfinishedPrepare -- One-time migration; placeholders are built above.
		$ids = $wpdb->get_col(
			$wpdb->prepare(
				"SELECT DISTINCT p.ID FROM {$wpdb->posts} p INNER JOIN {$wpdb->postmeta} m ON m.post_id = p.ID WHERE p.post_type = %s AND m.meta_key IN ($placeholders)", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				array_merge( array( self::LEGACY_POST_TYPE ), $keys )
			)
		);

		return array_map( 'intval', $ids );
	}

	/**
	 * Do the migration.
	 *
	 * @return int Number of albums moved.
	 */
	public static function migrate() {
		global $wpdb;

		$ids = self::legacy_album_ids();

		foreach ( $ids as $id ) {
			self::migrate_meta( $id );

			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery -- Changing a post type has no API that skips hooks and revisions.
			$wpdb->update(
				$wpdb->posts,
				array( 'post_type' => StackFolio_Post_Type::POST_TYPE ),
				array(
					'ID'        => $id,
					'post_type' => self::LEGACY_POST_TYPE,
				),
				array( '%s' ),
				array( '%d', '%s' )
			);
			clean_post_cache( $id );
		}

		if ( $ids ) {
			self::migrate_terms();
		}

		self::migrate_settings();

		if ( $ids ) {
			self::clear_elementor_cache();
		}

		return count( $ids );
	}

	/**
	 * Copy Album Station meta to StackFolio meta (only where not set yet).
	 *
	 * @param int $id Album ID.
	 */
	private static function migrate_meta( $id ) {
		if ( ! metadata_exists( 'post', $id, StackFolio_Album::META_MEDIA ) ) {
			$images = StackFolio_Album::parse_ids( (string) get_post_meta( $id, 'image_post_meta_value', true ) );
			update_post_meta( $id, StackFolio_Album::META_MEDIA, implode( ',', $images ) );
		}

		if ( metadata_exists( 'post', $id, StackFolio_Album::META_VIDEOS ) ) {
			return;
		}

		$choice = get_post_meta( $id, 'video_choice', true );
		$links  = array();

		if ( 'playlist' === $choice ) {
			foreach ( self::split( get_post_meta( $id, 'playlist_id', true ) ) as $playlist ) {
				$parsed  = StackFolio_Videos::parse( $playlist );
				$links[] = $parsed ? $parsed['url'] : 'https://www.youtube.com/playlist?list=' . rawurlencode( $playlist );
			}
		} else {
			$values = array_merge(
				self::split( get_post_meta( $id, 'youtube_video_post_meta_value', true ) ),
				self::split( get_post_meta( $id, 'vimeo_video_post_meta_value', true ) )
			);
			foreach ( $values as $value ) {
				$parsed  = StackFolio_Videos::parse( $value );
				$links[] = $parsed ? $parsed['url'] : $value;
			}
		}

		update_post_meta( $id, StackFolio_Album::META_VIDEOS, implode( "\n", array_unique( $links ) ) );
		update_post_meta( $id, StackFolio_Album::META_VIDEOS_POSITION, 'playlist' === $choice ? 'before' : 'after' );
	}

	/**
	 * Split a comma or newline separated meta value.
	 *
	 * @param mixed $value Raw value.
	 * @return string[]
	 */
	private static function split( $value ) {
		return array_values( array_filter( array_map( 'trim', preg_split( '/[,\r\n]+/', (string) $value ) ) ) );
	}

	/**
	 * Move album_category terms to stackfolio_category.
	 */
	private static function migrate_terms() {
		global $wpdb;

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- One-time migration.
		$term_ids = $wpdb->get_col( $wpdb->prepare( "SELECT term_id FROM {$wpdb->term_taxonomy} WHERE taxonomy = %s", self::LEGACY_TAXONOMY ) );
		if ( ! $term_ids ) {
			return;
		}

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery -- No API renames a taxonomy.
		$wpdb->update(
			$wpdb->term_taxonomy,
			array( 'taxonomy' => StackFolio_Post_Type::TAXONOMY ),
			array( 'taxonomy' => self::LEGACY_TAXONOMY ),
			array( '%s' ),
			array( '%s' )
		);

		$term_ids = array_map( 'intval', $term_ids );
		clean_term_cache( $term_ids, self::LEGACY_TAXONOMY );
		clean_term_cache( $term_ids, StackFolio_Post_Type::TAXONOMY );
		delete_option( self::LEGACY_TAXONOMY . '_children' );
		delete_option( StackFolio_Post_Type::TAXONOMY . '_children' );
	}

	/**
	 * Copy the YouTube API key.
	 */
	private static function migrate_settings() {
		$legacy_key = get_option( self::LEGACY_API_OPTION, '' );
		if ( '' === $legacy_key || '' !== StackFolio_Settings::get( 'youtube_api_key' ) ) {
			return;
		}
		$options                    = get_option( StackFolio_Settings::OPTION, array() );
		$options                    = is_array( $options ) ? $options : array();
		$options['youtube_api_key'] = sanitize_text_field( $legacy_key );
		update_option( StackFolio_Settings::OPTION, $options );
	}

	/**
	 * Old widget CSS used Album Station class names, so let Elementor rebuild it.
	 */
	private static function clear_elementor_cache() {
		if ( class_exists( '\Elementor\Plugin' ) && isset( \Elementor\Plugin::$instance->files_manager ) ) {
			\Elementor\Plugin::$instance->files_manager->clear_cache();
		}
	}

	/**
	 * One-time notice after albums were moved.
	 */
	public static function notice() {
		$moved = (int) get_option( self::NOTICE_OPTION, 0 );
		if ( ! $moved || ! current_user_can( 'manage_options' ) ) {
			return;
		}
		delete_option( self::NOTICE_OPTION );

		echo '<div class="notice notice-success is-dismissible"><p>';
		echo esc_html(
			sprintf(
				/* translators: %d: number of albums. */
				_n(
					'StackFolio moved %d album from Album Station. You can now deactivate and delete Album Station.',
					'StackFolio moved %d albums from Album Station. You can now deactivate and delete Album Station.',
					$moved,
					'stackfolio'
				),
				$moved
			)
		);
		echo '</p></div>';
	}
}
