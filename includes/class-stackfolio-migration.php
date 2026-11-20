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

	const DB_VERSION       = 2;
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
		add_action( 'admin_notices', array( __CLASS__, 'legacy_active_notice' ) );
		add_action( 'deactivated_plugin', array( __CLASS__, 'legacy_deactivated' ) );
	}

	/**
	 * Whether Album Station is loaded in this request.
	 *
	 * @return bool
	 */
	public static function legacy_plugin_active() {
		return function_exists( 'album_station_css' ) || class_exists( 'AS_Elementor_Widgets', false );
	}

	/**
	 * Move the albums as soon as Album Station is deactivated, so pages never
	 * show an empty grid in between. Its code is still loaded in this request,
	 * but it is no longer active.
	 *
	 * @param string $plugin Deactivated plugin basename.
	 */
	public static function legacy_deactivated( $plugin ) {
		if ( 'album-station.php' === basename( (string) $plugin ) ) {
			self::maybe_migrate( true );
		}
	}

	/**
	 * Activation hook.
	 */
	public static function activate() {
		self::maybe_migrate();
	}

	/**
	 * Run the pending upgrade steps once per site.
	 *
	 * Albums are not moved while Album Station is active: it keeps its widget
	 * and shortcode, which would then find no albums.
	 *
	 * @param bool $legacy_deactivated Album Station was just deactivated in this request.
	 */
	public static function maybe_migrate( $legacy_deactivated = false ) {
		$version = (int) get_option( self::VERSION_OPTION, 0 );
		if ( $version >= self::DB_VERSION ) {
			return;
		}

		if ( $version < 1 ) {
			if ( ! $legacy_deactivated && self::legacy_plugin_active() ) {
				return;
			}
			$moved = self::migrate();
			if ( $moved > 0 ) {
				update_option( self::NOTICE_OPTION, $moved, false );
			}
		}

		// Version 2: album categories are top-level only.
		self::flatten_categories();

		update_option( self::VERSION_OPTION, self::DB_VERSION );
	}

	/**
	 * Make every album category top-level. Albums keep their categories.
	 */
	public static function flatten_categories() {
		global $wpdb;

		// phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- One-time upgrade.
		$term_ids = $wpdb->get_col(
			$wpdb->prepare(
				"SELECT term_id FROM {$wpdb->term_taxonomy} WHERE taxonomy = %s AND parent <> 0",
				StackFolio_Post_Type::TAXONOMY
			)
		);
		if ( ! $term_ids ) {
			return;
		}
		$wpdb->query(
			$wpdb->prepare(
				"UPDATE {$wpdb->term_taxonomy} SET parent = 0 WHERE taxonomy = %s AND parent <> 0",
				StackFolio_Post_Type::TAXONOMY
			)
		);
		// phpcs:enable

		clean_term_cache( array_map( 'intval', $term_ids ), StackFolio_Post_Type::TAXONOMY );
		delete_option( StackFolio_Post_Type::TAXONOMY . '_children' );
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
			self::migrate_terms( $ids );
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
				$parsed = StackFolio_Videos::parse( $playlist );
				if ( $parsed ) {
					$links[] = $parsed['url'];
				} elseif ( preg_match( '#^https?://#i', $playlist ) ) {
					$links[] = $playlist; // Unrecognised link: keep it, the editor flags it.
				} else {
					$links[] = 'https://www.youtube.com/playlist?list=' . rawurlencode( $playlist );
				}
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
	 * Move the album_category terms of the migrated albums to stackfolio_category.
	 *
	 * A term used only by migrated albums is moved as it is. A term that other
	 * content still uses stays in album_category, and the migrated albums get a
	 * copy of it in stackfolio_category instead. Terms no migrated album uses are
	 * left alone. Moved terms become top-level. Plain SQL, because album_category is usually no longer
	 * registered once Album Station is deactivated.
	 *
	 * @param int[] $album_ids Migrated album IDs.
	 */
	private static function migrate_terms( array $album_ids ) {
		global $wpdb;

		$album_ids = array_values( array_filter( array_map( 'intval', $album_ids ) ) );
		if ( ! $album_ids ) {
			return;
		}
		$id_list = implode( ',', $album_ids ); // Integers only, safe to inline.

		// phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- One-time migration; no API moves terms between taxonomies, and $id_list holds only integers.
		$rows = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT DISTINCT tt.term_taxonomy_id, tt.term_id, tt.description FROM {$wpdb->term_taxonomy} tt INNER JOIN {$wpdb->term_relationships} tr ON tr.term_taxonomy_id = tt.term_taxonomy_id WHERE tt.taxonomy = %s AND tr.object_id IN ($id_list)",
				self::LEGACY_TAXONOMY
			)
		);
		if ( ! $rows ) {
			return;
		}

		$touched = array();

		foreach ( $rows as $row ) {
			$ttid    = (int) $row->term_taxonomy_id;
			$term_id = (int) $row->term_id;
			$shared  = (int) $wpdb->get_var(
				$wpdb->prepare(
					"SELECT COUNT(*) FROM {$wpdb->term_relationships} WHERE term_taxonomy_id = %d AND object_id NOT IN ($id_list)",
					$ttid
				)
			);

			if ( 0 === $shared ) {
				// Album categories are top-level only.
				$wpdb->update(
					$wpdb->term_taxonomy,
					array(
						'taxonomy' => StackFolio_Post_Type::TAXONOMY,
						'parent'   => 0,
					),
					array( 'term_taxonomy_id' => $ttid ),
					array( '%s', '%d' ),
					array( '%d' )
				);
				$touched[] = $term_id;
				continue;
			}

			// Shared with content that stays: copy the term for the migrated albums.
			$term = $wpdb->get_row( $wpdb->prepare( "SELECT name, slug FROM {$wpdb->terms} WHERE term_id = %d", $term_id ) );
			if ( ! $term ) {
				continue;
			}
			$wpdb->insert(
				$wpdb->terms,
				array(
					'name' => $term->name,
					'slug' => $term->slug,
				),
				array( '%s', '%s' )
			);
			$copy_id = (int) $wpdb->insert_id;
			$wpdb->insert(
				$wpdb->term_taxonomy,
				array(
					'term_id'     => $copy_id,
					'taxonomy'    => StackFolio_Post_Type::TAXONOMY,
					'description' => (string) $row->description,
					'parent'      => 0,
					'count'       => 0,
				),
				array( '%d', '%s', '%s', '%d', '%d' )
			);
			$copy_ttid = (int) $wpdb->insert_id;
			$wpdb->query(
				$wpdb->prepare(
					"UPDATE {$wpdb->term_relationships} SET term_taxonomy_id = %d WHERE term_taxonomy_id = %d AND object_id IN ($id_list)",
					$copy_ttid,
					$ttid
				)
			);
			$touched[] = $term_id;
			$touched[] = $copy_id;
		}

		// Recount every term involved, in both taxonomies.
		$wpdb->query(
			$wpdb->prepare(
				"UPDATE {$wpdb->term_taxonomy} SET count = ( SELECT COUNT(*) FROM {$wpdb->term_relationships} WHERE {$wpdb->term_relationships}.term_taxonomy_id = {$wpdb->term_taxonomy}.term_taxonomy_id ) WHERE taxonomy IN ( %s, %s )",
				self::LEGACY_TAXONOMY,
				StackFolio_Post_Type::TAXONOMY
			)
		);
		// phpcs:enable

		$touched = array_values( array_unique( $touched ) );
		clean_term_cache( $touched, self::LEGACY_TAXONOMY );
		clean_term_cache( $touched, StackFolio_Post_Type::TAXONOMY );
		clean_object_term_cache( $album_ids, StackFolio_Post_Type::POST_TYPE );
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
	 * While Album Station is active, ask to deactivate it.
	 */
	public static function legacy_active_notice() {
		if ( ! self::legacy_plugin_active() || ! current_user_can( 'activate_plugins' ) ) {
			return;
		}

		$count = (int) get_option( self::VERSION_OPTION, 0 ) < 1 ? count( self::legacy_album_ids() ) : 0;
		if ( $count > 0 ) {
			$message = sprintf(
				/* translators: %d: number of albums. */
				_n(
					'Album Station is still active. Deactivate it to finish switching to StackFolio: your %d album moves over automatically.',
					'Album Station is still active. Deactivate it to finish switching to StackFolio: your %d albums move over automatically.',
					$count,
					'stackfolio'
				),
				$count
			);
		} else {
			$message = __( 'Album Station is still active, but StackFolio now manages its albums. Deactivate Album Station so your album pages use StackFolio.', 'stackfolio' );
		}

		printf(
			'<div class="notice notice-warning"><p>%1$s <a href="%2$s">%3$s</a></p></div>',
			esc_html( $message ),
			esc_url( admin_url( 'plugins.php' ) ),
			esc_html__( 'Go to Plugins', 'stackfolio' )
		);
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
					'StackFolio moved %d album from Album Station. You can now delete Album Station.',
					'StackFolio moved %d albums from Album Station. You can now delete Album Station.',
					$moved,
					'stackfolio'
				),
				$moved
			)
		);
		echo '</p></div>';
	}
}
