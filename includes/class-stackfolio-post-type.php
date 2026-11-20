<?php
/**
 * Album post type and category taxonomy.
 *
 * @package StackFolio
 */

defined( 'ABSPATH' ) || exit;

/**
 * Registers the album post type, its taxonomy, image size and admin columns.
 */
class StackFolio_Post_Type {

	const POST_TYPE  = 'stackfolio_album';
	const TAXONOMY   = 'stackfolio_category';
	const IMAGE_SIZE = 'stackfolio-cover';

	/**
	 * Hook everything up.
	 */
	public static function init() {
		add_action( 'init', array( __CLASS__, 'register' ) );
		add_action( 'after_setup_theme', array( __CLASS__, 'register_image_size' ) );
		add_filter( 'manage_' . self::POST_TYPE . '_posts_columns', array( __CLASS__, 'columns' ) );
		add_action( 'manage_' . self::POST_TYPE . '_posts_custom_column', array( __CLASS__, 'column_content' ), 10, 2 );
		add_filter( 'enter_title_here', array( __CLASS__, 'title_placeholder' ), 10, 2 );
		add_action( 'saved_' . self::TAXONOMY, array( __CLASS__, 'keep_category_top_level' ), 10, 2 );
		add_action( 'admin_head', array( __CLASS__, 'hide_parent_category_fields' ) );
	}

	/**
	 * Album categories are top-level only: the filter bar is one flat row.
	 * Undo a parent set through any route (admin, REST, import).
	 *
	 * @param int $term_id Term ID.
	 * @param int $tt_id   Term taxonomy ID.
	 */
	public static function keep_category_top_level( $term_id, $tt_id ) {
		global $wpdb;

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery -- wp_update_term() would fire this hook again.
		$changed = $wpdb->update(
			$wpdb->term_taxonomy,
			array( 'parent' => 0 ),
			array( 'term_taxonomy_id' => (int) $tt_id ),
			array( '%d' ),
			array( '%d' )
		);
		if ( $changed ) {
			clean_term_cache( (int) $term_id, self::TAXONOMY );
			delete_option( self::TAXONOMY . '_children' );
		}
	}

	/**
	 * Hide the Parent fields for album categories.
	 */
	public static function hide_parent_category_fields() {
		$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
		if ( ! $screen ) {
			return;
		}
		if ( self::TAXONOMY === $screen->taxonomy && in_array( $screen->base, array( 'edit-tags', 'term' ), true ) ) {
			echo '<style>.term-parent-wrap{display:none}</style>';
		} elseif ( 'post' === $screen->base && self::POST_TYPE === $screen->post_type ) {
			echo '<style>#new' . esc_attr( self::TAXONOMY ) . '_parent{display:none}</style>';
		}
	}

	/**
	 * Register the post type and taxonomy.
	 */
	public static function register() {
		$labels = array(
			'name'                  => _x( 'Albums', 'post type general name', 'stackfolio' ),
			'singular_name'         => _x( 'Album', 'post type singular name', 'stackfolio' ),
			'menu_name'             => _x( 'StackFolio', 'admin menu', 'stackfolio' ),
			'all_items'             => __( 'All Albums', 'stackfolio' ),
			'add_new'               => __( 'Add New Album', 'stackfolio' ),
			'add_new_item'          => __( 'Add New Album', 'stackfolio' ),
			'edit_item'             => __( 'Edit Album', 'stackfolio' ),
			'new_item'              => __( 'New Album', 'stackfolio' ),
			'view_item'             => __( 'View Album', 'stackfolio' ),
			'view_items'            => __( 'View Albums', 'stackfolio' ),
			'search_items'          => __( 'Search Albums', 'stackfolio' ),
			'not_found'             => __( 'No albums found.', 'stackfolio' ),
			'not_found_in_trash'    => __( 'No albums found in Trash.', 'stackfolio' ),
			'featured_image'        => __( 'Album cover', 'stackfolio' ),
			'set_featured_image'    => __( 'Set album cover', 'stackfolio' ),
			'remove_featured_image' => __( 'Remove album cover', 'stackfolio' ),
			'use_featured_image'    => __( 'Use as album cover', 'stackfolio' ),
			'insert_into_item'      => __( 'Insert into album', 'stackfolio' ),
			'uploaded_to_this_item' => __( 'Uploaded to this album', 'stackfolio' ),
			'filter_items_list'     => __( 'Filter albums list', 'stackfolio' ),
			'items_list_navigation' => __( 'Albums list navigation', 'stackfolio' ),
			'items_list'            => __( 'Albums list', 'stackfolio' ),
		);

		$args = array(
			'labels'              => $labels,
			'public'              => false,
			'publicly_queryable'  => false,
			'exclude_from_search' => true,
			'show_ui'             => true,
			'show_in_menu'        => true,
			'show_in_nav_menus'   => false,
			'show_in_rest'        => true,
			'menu_icon'           => 'dashicons-images-alt2',
			'menu_position'       => 25,
			'supports'            => array( 'title', 'thumbnail', 'page-attributes' ),
			'has_archive'         => false,
			'rewrite'             => false,
		);

		/**
		 * Filters the album post type arguments, e.g. to make albums public.
		 *
		 * @param array $args Post type arguments.
		 */
		register_post_type( self::POST_TYPE, apply_filters( 'stackfolio_post_type_args', $args ) );

		$tax_labels = array(
			'name'          => _x( 'Album Categories', 'taxonomy general name', 'stackfolio' ),
			'singular_name' => _x( 'Album Category', 'taxonomy singular name', 'stackfolio' ),
			'menu_name'     => __( 'Categories', 'stackfolio' ),
			'all_items'     => __( 'All Categories', 'stackfolio' ),
			'edit_item'     => __( 'Edit Category', 'stackfolio' ),
			'view_item'     => __( 'View Category', 'stackfolio' ),
			'update_item'   => __( 'Update Category', 'stackfolio' ),
			'add_new_item'  => __( 'Add New Category', 'stackfolio' ),
			'new_item_name' => __( 'New Category Name', 'stackfolio' ),
			'parent_item'   => __( 'Parent Category', 'stackfolio' ),
			'search_items'  => __( 'Search Categories', 'stackfolio' ),
			'not_found'     => __( 'No categories found.', 'stackfolio' ),
		);

		$tax_args = array(
			'labels'            => $tax_labels,
			'hierarchical'      => true,
			'public'            => false,
			'show_ui'           => true,
			'show_admin_column' => true,
			'show_in_nav_menus' => false,
			'show_in_rest'      => true,
			'rewrite'           => false,
		);

		/**
		 * Filters the album category taxonomy arguments.
		 *
		 * @param array $tax_args Taxonomy arguments.
		 */
		register_taxonomy( self::TAXONOMY, array( self::POST_TYPE ), apply_filters( 'stackfolio_taxonomy_args', $tax_args ) );
	}

	/**
	 * Cover image size used in the grid.
	 */
	public static function register_image_size() {
		add_image_size( self::IMAGE_SIZE, 600, 400, true );
	}

	/**
	 * Add cover and item-count columns to the albums list.
	 *
	 * @param array $columns Existing columns.
	 * @return array
	 */
	public static function columns( $columns ) {
		$new = array();
		foreach ( $columns as $key => $label ) {
			if ( 'title' === $key ) {
				$new['stackfolio_cover'] = __( 'Cover', 'stackfolio' );
			}
			$new[ $key ] = $label;
			if ( 'title' === $key ) {
				$new['stackfolio_items'] = __( 'Items', 'stackfolio' );
			}
		}
		return $new;
	}

	/**
	 * Output custom column content.
	 *
	 * @param string $column  Column key.
	 * @param int    $post_id Album ID.
	 */
	public static function column_content( $column, $post_id ) {
		if ( 'stackfolio_cover' === $column ) {
			echo get_the_post_thumbnail( $post_id, array( 60, 60 ) );
		} elseif ( 'stackfolio_items' === $column ) {
			$media  = StackFolio_Album::get_media_ids( $post_id );
			$videos = StackFolio_Album::get_video_links( $post_id );
			echo esc_html(
				sprintf(
					/* translators: 1: number of media files, 2: number of video links. */
					__( '%1$d media, %2$d video links', 'stackfolio' ),
					count( $media ),
					count( $videos )
				)
			);
		}
	}

	/**
	 * Title placeholder on the album edit screen.
	 *
	 * @param string  $text Placeholder.
	 * @param WP_Post $post Post.
	 * @return string
	 */
	public static function title_placeholder( $text, $post ) {
		return self::POST_TYPE === $post->post_type ? __( 'Album name', 'stackfolio' ) : $text;
	}
}
