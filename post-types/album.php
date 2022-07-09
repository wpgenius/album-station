<?php

/**
 * Registers the `album` post type.
 */
function album_init() {
	register_post_type(
		'album',
		[
			'labels'                => [
				'name'                  => __( 'Albums', 'album-station' ),
				'singular_name'         => __( 'Album', 'album-station' ),
				'all_items'             => __( 'All Albums', 'album-station' ),
				'archives'              => __( 'Album Archives', 'album-station' ),
				'attributes'            => __( 'Album Attributes', 'album-station' ),
				'insert_into_item'      => __( 'Insert into Album', 'album-station' ),
				'uploaded_to_this_item' => __( 'Uploaded to this Album', 'album-station' ),
				'featured_image'        => _x( 'Featured Image', 'album', 'album-station' ),
				'set_featured_image'    => _x( 'Set featured image', 'album', 'album-station' ),
				'remove_featured_image' => _x( 'Remove featured image', 'album', 'album-station' ),
				'use_featured_image'    => _x( 'Use as featured image', 'album', 'album-station' ),
				'filter_items_list'     => __( 'Filter Albums list', 'album-station' ),
				'items_list_navigation' => __( 'Albums list navigation', 'album-station' ),
				'items_list'            => __( 'Albums list', 'album-station' ),
				'new_item'              => __( 'New Album', 'album-station' ),
				'add_new'               => __( 'Add New', 'album-station' ),
				'add_new_item'          => __( 'Add New Album', 'album-station' ),
				'edit_item'             => __( 'Edit Album', 'album-station' ),
				'view_item'             => __( 'View Album', 'album-station' ),
				'view_items'            => __( 'View Albums', 'album-station' ),
				'search_items'          => __( 'Search Albums', 'album-station' ),
				'not_found'             => __( 'No Albums found', 'album-station' ),
				'not_found_in_trash'    => __( 'No Albums found in trash', 'album-station' ),
				'parent_item_colon'     => __( 'Parent Album:', 'album-station' ),
				'menu_name'             => __( 'Albums', 'album-station' ),
			],
			'public'                => true,
			'hierarchical'          => false,
			'show_ui'               => true,
			'show_in_nav_menus'     => true,
			'supports'              => [ 'title', 'editor', 'thumbnail' ],
			'has_archive'           => true,
			'rewrite'               => true,
			'query_var'             => true,
			'menu_position'         => null,
			'menu_icon'             => 'dashicons-admin-post',
			'show_in_rest'          => true,
			'rest_base'             => 'album',
			'rest_controller_class' => 'WP_REST_Posts_Controller',
		]
	);

}

add_action( 'init', 'album_init' );

function image_meta_box(){
    
	add_meta_box( 'image-gallery', __(  'Image Gallery'), 'image_post_meta_callback', 'album', 'advanced', 'high');
}   
add_action( 'add_meta_boxes', 'image_meta_box' );

function image_post_meta_callback($post){

	$value = get_post_meta($post->ID, 'image_post_meta_value', true); ?>

	<table class="form-table as_metabox">
		
		<div class="myplugin-image-preview" id="disp_image_gallery">
			<div class="as_title">
				<label for="immage_id">Selected Images</label>
			</div>
				<input style="width:60%; padding:10px !important;" type="hidden" id="immage_id" name="immage_id" value="<?php echo $value; ?>" />
			</div>
			<div class="selected_images">
				<?php 
						$images_id = explode("," ,$value);
						
						foreach($images_id as $id){
							echo '<img src="'.wp_get_attachment_image_url($id).'" height="100" >';
						}
				?>
			</div>
		</div>
		<div class="btn_upload">
			<input type="submit" class="button" value="Upload Image" id="upload_img" name="update_gallery"/>
		</div>
	</table><?php
}

add_action( 'save_post', 'image_save_post_meta' );

function image_save_post_meta( $post_id ){

	if(isset($_POST['immage_id']) && $_POST['immage_id'] != ''){
		$mydata =  $_POST['immage_id'];
		update_post_meta($post_id, 'image_post_meta_value', $mydata);
	
	}
}

function video_meta_box(){
    
	add_meta_box( 'video-gallery', __(  'Video Gallery'), 'video_post_meta_callback', 'album', 'advanced', 'high');
}   
add_action( 'add_meta_boxes', 'video_meta_box' );

function video_post_meta_callback($post){
	 ?>
	<div class="video-choice as_container">
		<div class="as_title">
			<label>Select Video Choice</label>
		</div>
		<div>
			<input type="radio" id="single" name="video_choice" class="video_choice" value="single" <?php checked( 'single',get_post_meta( $post->ID,'video_choice', true ) ? get_post_meta( $post->ID,'video_choice', true ) : 'single',true ) ?>>
			<label for="single">Single</label>

			<input type="radio" id="playlist" name="video_choice" class="video_choice" value="playlist" <?php checked( 'playlist',get_post_meta( $post->ID,'video_choice', true ),true ) ?>>
			<label for="playlist">Playlist</label>
		</div>
	</div>

	<div class="video_settings">
		<div class="as_container video_ids">
			<div class="as_title youtube_video_id">
				<label for="youtube_video_id">Enter Youtube Video ID's</label>
			</div>
			<div>
				<input style="width:60%; padding:10px !important;" type="text" id="youtube_video_id" name="youtube_video_id" value="<?php echo get_post_meta($post->ID, 'youtube_video_post_meta_value', true); ?>" />
			</div>

			<div class="as_title vimeo_video_id">
				<label for="vimeo_video_id">Enter Vimeo Video ID's</label>
			</div>
			<div>
				<input style="width:60%; padding:10px !important;" type="text" id="vimeo_video_id" name="vimeo_video_id" value="<?php echo get_post_meta($post->ID, 'vimeo_video_post_meta_value', true); ?>" />
			</div>
			
		</div>
		<div class="as_container video_playlist">
			<div class="as_title">
				<label for="youtube_playlist">Enter Playlist ID's</label>
			</div>
			<div>
				<input style="width:60%; padding:10px !important;" type="text" id="playlist_id" name="playlist_id" value="<?php echo (get_post_meta( $post->ID,'playlist_id', true ))? get_post_meta( $post->ID,'playlist_id', true ):''; ?>" />
			</div>
		</div>
		<div class="as_container">
			<label for="video_popup">Check for video popup:</label> 
			<?php $checkboxMeta = get_post_meta( $post->ID );?>
			<input type="checkbox" name="video_popup" id="video_popup" value="yes" <?php if ( isset ( $checkboxMeta['video_popup'] ) ) checked( $checkboxMeta['video_popup'][0], 'yes' ); ?> />
		</div>
	</div>
<?php		
}

add_action( 'save_post', 'video_save_post_meta' );

function video_save_post_meta( $post_id ){

	if(isset($_POST['youtube_video_id']) && $_POST['youtube_video_id'] != ''){
		$mydata =  $_POST['youtube_video_id'];
		update_post_meta($post_id, 'youtube_video_post_meta_value', $mydata);
	}

	if(isset($_POST['vimeo_video_id']) && $_POST['vimeo_video_id'] != ''){
		$mydata =  $_POST['vimeo_video_id'];
		update_post_meta($post_id, 'vimeo_video_post_meta_value', $mydata);
	}

	if(isset($_POST['playlist_id']) && $_POST['playlist_id'] != ''){
		$mydata =  $_POST['playlist_id'];
		update_post_meta($post_id, 'playlist_id', $mydata);
	}

	if( isset( $_POST[ 'video_popup' ] ) ) {
		update_post_meta( $post_id, 'video_popup', 'yes' );
		} else {
		update_post_meta( $post_id, 'video_popup', 'no' );
	}  
	if( isset( $_POST[ 'video_choice' ] ) ) {
		$mydata =  $_POST['video_choice'];
		update_post_meta( $post_id, 'video_choice', $mydata );
	}

}