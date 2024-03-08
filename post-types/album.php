<?php

/**
 * Registers the `album` post type.
 */
function album_init() {
	register_post_type(
		'album',
		array(
			'labels'                => array(
				'name'                  => __( 'Projects', 'album-station' ),
				'singular_name'         => __( 'Project', 'album-station' ),
				'all_items'             => __( 'All Projects', 'album-station' ),
				'archives'              => __( 'Project Archives', 'album-station' ),
				'attributes'            => __( 'Project Attributes', 'album-station' ),
				'insert_into_item'      => __( 'Insert into Project', 'album-station' ),
				'uploaded_to_this_item' => __( 'Uploaded to this Project', 'album-station' ),
				'featured_image'        => _x( 'Featured Image', 'album', 'album-station' ),
				'set_featured_image'    => _x( 'Set featured image', 'album', 'album-station' ),
				'remove_featured_image' => _x( 'Remove featured image', 'album', 'album-station' ),
				'use_featured_image'    => _x( 'Use as featured image', 'album', 'album-station' ),
				'filter_items_list'     => __( 'Filter Projects list', 'album-station' ),
				'items_list_navigation' => __( 'Projects list navigation', 'album-station' ),
				'items_list'            => __( 'Projects list', 'album-station' ),
				'new_item'              => __( 'New Project', 'album-station' ),
				'add_new'               => __( 'Add New', 'album-station' ),
				'add_new_item'          => __( 'Add New Project', 'album-station' ),
				'edit_item'             => __( 'Edit Project', 'album-station' ),
				'view_item'             => __( 'View Project', 'album-station' ),
				'view_items'            => __( 'View Projects', 'album-station' ),
				'search_items'          => __( 'Search Projects', 'album-station' ),
				'not_found'             => __( 'No Projects found', 'album-station' ),
				'not_found_in_trash'    => __( 'No Projects found in trash', 'album-station' ),
				'parent_item_colon'     => __( 'Parent Project:', 'album-station' ),
				'menu_name'             => __( 'Projects', 'album-station' ),
			),
			'public'                => true,
			'publicly_queryable'	=> true,
			'hierarchical'          => false,
			'show_ui'               => true,
			'show_in_nav_menus'     => true,
			'supports'              => array( 'title', 'editor', 'thumbnail' ),
			'has_archive'           => false,
			'rewrite'               => array(
				'with_front' => false,
				'slug'       => 'project'
			),
			'query_var'             => true,
			'menu_position'         => null,
			'menu_icon'             => 'dashicons-admin-post',
			'show_in_rest'          => true,
			'rest_base'             => 'album',
			'rest_controller_class' => 'WP_REST_Posts_Controller',
		)
	);

	$labels = array(
		'name'                       => _x( 'Product Categories', 'Product Categories', 'crezza' ),
		'singular_name'              => _x( 'Category', 'Project Category', 'album-station' ),
		'menu_name'                  => __( 'Categories', 'album-station' ),
		'all_items'                  => __( 'All Items', 'album-station' ),
		'parent_item'                => __( 'Parent Item', 'album-station' ),
		'parent_item_colon'          => __( 'Parent Item:', 'album-station' ),
		'new_item_name'              => __( 'New Project Category', 'album-station' ),
		'add_new_item'               => __( 'Add New Project Category', 'album-station' ),
		'edit_item'                  => __( 'Edit Category', 'album-station' ),
		'update_item'                => __( 'Update Project Category', 'album-station' ),
		'view_item'                  => __( 'View Category', 'album-station' ),
		'separate_items_with_commas' => __( 'Separate items with commas', 'album-station' ),
		'add_or_remove_items'        => __( 'Add or remove Project', 'album-station' ),
		'choose_from_most_used'      => __( 'Choose from the most used', 'album-station' ),
		'popular_items'              => __( 'Popular Project Categories', 'album-station' ),
		'search_items'               => __( 'Search Project', 'album-station' ),
		'not_found'                  => __( 'Not Found', 'album-station' ),
		'no_terms'                   => __( 'No items', 'album-station' ),
		'items_list'                 => __( 'Project list', 'album-station' ),
		'items_list_navigation'      => __( 'Project list navigation', 'album-station' ),
	);

	$args = array(
		'labels'            => $labels,
		'hierarchical'      => true,
		'public'            => false,
		'show_ui'           => true,
		'show_admin_column' => true,
		'show_in_nav_menus' => true,
		'rewrite'           => array(
			'slug'         => 'catalogue',
			'with_front'   => false,
			'hierarchical' => true,
		),
	);
	register_taxonomy( 'album_category', array( 'album' ), $args );

	$labels = array(
		'name'                       => _x( 'Construction types', 'Product Categories', 'crezza' ),
		'singular_name'              => _x( 'Type', 'Type', 'album-station' ),
		'menu_name'                  => __( 'Construction types', 'album-station' ),
		'all_items'                  => __( 'All Items', 'album-station' ),
		'parent_item'                => __( 'Parent Item', 'album-station' ),
		'parent_item_colon'          => __( 'Parent Item:', 'album-station' ),
		'new_item_name'              => __( 'New construction type', 'album-station' ),
		'add_new_item'               => __( 'Add New construction type', 'album-station' ),
		'edit_item'                  => __( 'Edit type', 'album-station' ),
		'update_item'                => __( 'Update construction type', 'album-station' ),
		'view_item'                  => __( 'View type', 'album-station' ),
		'separate_items_with_commas' => __( 'Separate items with commas', 'album-station' ),
		'add_or_remove_items'        => __( 'Add or remove construction type', 'album-station' ),
		'choose_from_most_used'      => __( 'Choose from the most used', 'album-station' ),
		'popular_items'              => __( 'Popular construction Categories', 'album-station' ),
		'search_items'               => __( 'Search construction', 'album-station' ),
		'not_found'                  => __( 'Not Found', 'album-station' ),
		'no_terms'                   => __( 'No items', 'album-station' ),
		'items_list'                 => __( 'construction list', 'album-station' ),
		'items_list_navigation'      => __( 'construction list navigation', 'album-station' ),
	);

	$args = array(
		'labels'            => $labels,
		'hierarchical'      => true,
		'public'            => false,
		'show_ui'           => true,
		'show_admin_column' => true,
		'show_in_nav_menus' => true,
		'rewrite'           => array(
			'slug'         => 'type',
			'with_front'   => false,
			'hierarchical' => true,
		),
	);
	register_taxonomy( 'type', array( 'album' ), $args );

}

add_action( 'init', 'album_init' );

/**
 * Add a album photo column to album post type.
 *
 * @param [type] $columns
 * @return array
 */
function manage_album_cols( $columns ) {

	$inserted = array(
		'thumbnail' => 'album Photo',
	);

	return array_merge(
		array_slice( $columns, 0, 2 ),
		$inserted,
		array_slice( $columns, 2 )
	);
}
add_filter( 'manage_album_posts_columns', 'manage_album_cols' );

/**
 * show feature image of album post type in album photo.
 *
 * @param [type] $column_name
 * @param [type] $post_id
 * @return void
 */
function album_field_col( $column_name, $post_id ) {

	if ( $column_name == 'thumbnail' ) {
		echo get_the_post_thumbnail( $post_id, 'thumbnail' );
	}

}
add_action( 'manage_album_posts_custom_column', 'album_field_col', 10, 2 );

/**
 * Add metabox for image gallery.
 *
 * @return array
 */
function image_meta_box() {

	add_meta_box( 'image-gallery', __( 'Image Gallery' ), 'image_post_meta_callback', 'album', 'advanced', 'high' );
}
add_action( 'add_meta_boxes', 'image_meta_box' );

/**
 * Add post meta boxes to album post type
 *
 * @param [type] $post
 * @return void
 */
function image_post_meta_callback( $post ) {

	$value = get_post_meta( $post->ID, 'image_post_meta_value', true );
	$has_single_page = get_post_meta( $post->ID, 'project_has_single_page', true ) ?  : 'no' ;

	?>

	<table class="form-table as_metabox">
		<div style="margin-bottom: 20px;">
			<div class="as_title">
				<label for="single_page">Project type setting (Single page / Popup)</label>
			</div>
			<input type="radio" name="single_page" id="single_page_yes" value="yes" <?php checked( $has_single_page,'yes', true ) ?> >
			<label for="single_page_yes">Single page</label>
			<input type="radio" name="single_page" id="single_page_no" value="no" <?php checked(  $has_single_page,'no', true ) ?> style="margin-right: 10px;">
			<label for="single_page_no">Popup</label>
		</div>
		<div class="myplugin-image-preview" id="disp_image_gallery">
			<div class="as_title">
				<label for="immage_id">Selected Images</label>
			</div>
				<input style="width:60%; padding:10px !important;" type="hidden" id="immage_id" name="immage_id" value="<?php echo $value; ?>" />
			</div>
			<div class="selected_images">
				<?php
						$images_id = explode( ',', $value );

				foreach ( $images_id as $id ) {
					echo '<img src="' . wp_get_attachment_image_url( $id ) . '" height="100" >';
				}
				?>
			</div>
		</div>
		<div class="btn_upload">
			<input type="submit" class="button" value="Upload Image" id="upload_img" name="update_gallery"/>
		</div>
	</table>
	<?php
}

/**
 * Save post data of image gallery
 *
 * @param [type] $post_id
 * @return void
 */
function image_save_post_meta( $post_id ) {

	if ( isset( $_POST['immage_id'] ) && $_POST['immage_id'] != '' ) {

		$images_id = explode( ',', $_POST['immage_id'] );

		$mydata = array_filter( $images_id, fn($value) => !is_null($value) && $value !== '' );

		$mydata = implode( ',', $mydata );

		update_post_meta( $post_id, 'image_post_meta_value', $mydata );

	}

	if ( isset( $_POST['single_page'] ) && $_POST['single_page'] != '' ) {
		$mydata = $_POST['single_page'];
		update_post_meta( $post_id, 'project_has_single_page', $mydata );
	}
}
add_action( 'save_post', 'image_save_post_meta' );

/**
 * Add meta box for Video gallery
 *
 * @return void
 */
function video_meta_box() {
	add_meta_box( 'video-gallery', __( 'Video Gallery' ), 'video_post_meta_callback', 'album', 'advanced', 'high' );
}
// add_action( 'add_meta_boxes', 'video_meta_box' );

function video_post_meta_callback( $post ) {
	?>
	<div class="video-choice as_container">
		<div class="as_title">
			<label>Select Video Choice</label>
		</div>
		<div>
			<input type="radio" id="single" name="video_choice" class="video_choice" value="single" <?php checked( 'single', get_post_meta( $post->ID, 'video_choice', true ) ? get_post_meta( $post->ID, 'video_choice', true ) : 'single', true ); ?>>
			<label for="single">Single</label>

			<input type="radio" id="playlist" name="video_choice" class="video_choice" value="playlist" <?php checked( 'playlist', get_post_meta( $post->ID, 'video_choice', true ), true ); ?>>
			<label for="playlist">Playlist</label>
		</div>
	</div>

	<div class="video_settings">
		<div class="as_container video_ids">
			<div class="as_title youtube_video_id">
				<label for="youtube_video_id">Enter Youtube Video ID's</label>
			</div>
			<div>
				<input style="width:60%; padding:10px !important;" type="text" id="youtube_video_id" name="youtube_video_id" value="<?php echo get_post_meta( $post->ID, 'youtube_video_post_meta_value', true ); ?>" />
			</div>

			<div class="as_title vimeo_video_id">
				<label for="vimeo_video_id">Enter Vimeo Video ID's</label>
			</div>
			<div>
				<input style="width:60%; padding:10px !important;" type="text" id="vimeo_video_id" name="vimeo_video_id" value="<?php echo get_post_meta( $post->ID, 'vimeo_video_post_meta_value', true ); ?>" />
			</div>

		</div>
		<div class="as_container video_playlist">
			<div class="as_title">
				<label for="youtube_playlist">Enter Playlist ID's</label>
			</div>
			<div>
				<input style="width:60%; padding:10px !important;" type="text" id="playlist_id" name="playlist_id" value="<?php echo ( get_post_meta( $post->ID, 'playlist_id', true ) ) ? get_post_meta( $post->ID, 'playlist_id', true ) : ''; ?>" />
			</div>
		</div>
		<div class="as_container">
			<label for="video_popup">Check for video popup:</label>
			<?php $checkboxMeta = get_post_meta( $post->ID ); ?>
			<input type="checkbox" name="video_popup" id="video_popup" value="yes"
			<?php
			if ( isset( $checkboxMeta['video_popup'] ) ) {
				checked( $checkboxMeta['video_popup'][0], 'yes' );}
			?>
			 />
		</div>
	</div>
	<?php
}

/**
 * Save video post meta
 *
 * @param [type] $post_id
 * @return string
 */
function video_save_post_meta( $post_id ) {

	if ( isset( $_POST['youtube_video_id'] ) && $_POST['youtube_video_id'] != '' ) {
		$mydata = $_POST['youtube_video_id'];
		update_post_meta( $post_id, 'youtube_video_post_meta_value', $mydata );
	}

	if ( isset( $_POST['vimeo_video_id'] ) && $_POST['vimeo_video_id'] != '' ) {
		$mydata = $_POST['vimeo_video_id'];
		update_post_meta( $post_id, 'vimeo_video_post_meta_value', $mydata );
	}

	if ( isset( $_POST['playlist_id'] ) && $_POST['playlist_id'] != '' ) {
		$mydata = $_POST['playlist_id'];
		update_post_meta( $post_id, 'playlist_id', $mydata );
	}

	if ( isset( $_POST['video_popup'] ) ) {
		update_post_meta( $post_id, 'video_popup', 'yes' );
	} else {
		update_post_meta( $post_id, 'video_popup', 'no' );
	}
	if ( isset( $_POST['video_choice'] ) ) {
		$mydata = $_POST['video_choice'];
		update_post_meta( $post_id, 'video_choice', $mydata );
	}

}
add_action( 'save_post', 'video_save_post_meta' );
