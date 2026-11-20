<?php
/**
 * Album post type and category taxonomy.
 *
 * @package Coverfold
 */

defined( 'ABSPATH' ) || exit;

/**
 * Registers the album post type, its taxonomy, image size and admin columns.
 */
class Coverfold_Post_Type {

	const POST_TYPE  = 'coverfold_album';
	const TAXONOMY   = 'coverfold_category';
	const IMAGE_SIZE = 'coverfold-cover';

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
			'name'                  => _x( 'Albums', 'post type general name', 'coverfold' ),
			'singular_name'         => _x( 'Album', 'post type singular name', 'coverfold' ),
			'menu_name'             => _x( 'Coverfold', 'admin menu', 'coverfold' ),
			'all_items'             => __( 'All Albums', 'coverfold' ),
			'add_new'               => __( 'Add New Album', 'coverfold' ),
			'add_new_item'          => __( 'Add New Album', 'coverfold' ),
			'edit_item'             => __( 'Edit Album', 'coverfold' ),
			'new_item'              => __( 'New Album', 'coverfold' ),
			'view_item'             => __( 'View Album', 'coverfold' ),
			'view_items'            => __( 'View Albums', 'coverfold' ),
			'search_items'          => __( 'Search Albums', 'coverfold' ),
			'not_found'             => __( 'No albums found.', 'coverfold' ),
			'not_found_in_trash'    => __( 'No albums found in Trash.', 'coverfold' ),
			'featured_image'        => __( 'Album cover', 'coverfold' ),
			'set_featured_image'    => __( 'Set album cover', 'coverfold' ),
			'remove_featured_image' => __( 'Remove album cover', 'coverfold' ),
			'use_featured_image'    => __( 'Use as album cover', 'coverfold' ),
			'insert_into_item'      => __( 'Insert into album', 'coverfold' ),
			'uploaded_to_this_item' => __( 'Uploaded to this album', 'coverfold' ),
			'filter_items_list'     => __( 'Filter albums list', 'coverfold' ),
			'items_list_navigation' => __( 'Albums list navigation', 'coverfold' ),
			'items_list'            => __( 'Albums list', 'coverfold' ),
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
		register_post_type( self::POST_TYPE, apply_filters( 'coverfold_post_type_args', $args ) );

		$tax_labels = array(
			'name'          => _x( 'Album Categories', 'taxonomy general name', 'coverfold' ),
			'singular_name' => _x( 'Album Category', 'taxonomy singular name', 'coverfold' ),
			'menu_name'     => __( 'Categories', 'coverfold' ),
			'all_items'     => __( 'All Categories', 'coverfold' ),
			'edit_item'     => __( 'Edit Category', 'coverfold' ),
			'view_item'     => __( 'View Category', 'coverfold' ),
			'update_item'   => __( 'Update Category', 'coverfold' ),
			'add_new_item'  => __( 'Add New Category', 'coverfold' ),
			'new_item_name' => __( 'New Category Name', 'coverfold' ),
			'parent_item'   => __( 'Parent Category', 'coverfold' ),
			'search_items'  => __( 'Search Categories', 'coverfold' ),
			'not_found'     => __( 'No categories found.', 'coverfold' ),
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
		register_taxonomy( self::TAXONOMY, array( self::POST_TYPE ), apply_filters( 'coverfold_taxonomy_args', $tax_args ) );
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
				$new['coverfold_cover'] = __( 'Cover', 'coverfold' );
			}
			$new[ $key ] = $label;
			if ( 'title' === $key ) {
				$new['coverfold_items'] = __( 'Items', 'coverfold' );
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
		if ( 'coverfold_cover' === $column ) {
			echo get_the_post_thumbnail( $post_id, array( 60, 60 ) );
		} elseif ( 'coverfold_items' === $column ) {
			$media  = Coverfold_Album::get_media_ids( $post_id );
			$videos = Coverfold_Album::get_video_links( $post_id );
			echo esc_html(
				sprintf(
					/* translators: 1: number of media files, 2: number of video links. */
					__( '%1$d media, %2$d video links', 'coverfold' ),
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
		return self::POST_TYPE === $post->post_type ? __( 'Album name', 'coverfold' ) : $text;
	}
}
