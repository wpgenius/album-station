<?php

function feature_album_shortcode( ){
    $mypost= get_posts( array(
		'post_type' => 'album',
		'numberposts'=> -1,
		 ));
        
        wp_enqueue_script( 'lightgallery-combined', WBC_DIR_URL . 'assets/js/lightgallery-combined.js', array( 'jquery' ) );

        wp_enqueue_style( 'lightgallery-medium-zoom', WBC_DIR_URL . 'assets/css/lightgallery.css' );
?>

<?php
    if($mypost){
        foreach( $mypost as $post ){
            $post_id = $post->ID;
            $thumbnail = get_the_post_thumbnail_url($post_id);
            $img_ids = get_post_meta($post_id, 'image_post_meta_value', true);

            $video_ids = explode( ',' , get_post_meta($post_id, 'youtube_video_post_meta_value', true));
            
            $img_arr = explode(',', $img_ids);
            $image_list = array();
            
            foreach( $img_arr as $img_id ){

                $src = wp_get_attachment_image_url($img_id);

                $img_post = get_post($img_id);

                $image_list[] = array(

                    'src'=> wp_get_attachment_image_url($img_id, 'full'),
                    //'responsive' => "https://images.unsplash.com/photo-1609342122563-a43ac8917a3a?ixlib=rb-1.2.1&ixid=MXwxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHw%3D&auto=format&fit=crop&w=480&q=80 480, https://images.unsplash.com/photo-1609342122563-a43ac8917a3a?ixlib=rb-1.2.1&ixid=MXwxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHw%3D&auto=format&fit=crop&w=800&q=80 800",
                    'thumb' =>  wp_get_attachment_image_url($img_id),
                    'subHtml'=> '<div class="lightGallery-captions">
                                    <h4>'.get_post_meta($img_id, '_wp_attachment_image_alt', true ).'</h4>
                                    <p></p>
                                </div>'

                );

            }

            $video_list = array();
            if( get_post_meta( $post_id, 'video_post_meta_value', true ) ){
                foreach( $video_ids as $video ){
                    $video_url = "//www.youtube.com/watch?v=".$video;

                    $video_list[] = array(
                        'src' => "//www.youtube.com/watch?v=".$video,
                        'poster' => "https://img.youtube.com/vi/".$video."/maxresdefault.jpg",
                        'thumb' => "https://img.youtube.com/vi/".$video."/maxresdefault.jpg"
                    );
                }
            }

            $merged_gallery = array_merge( $image_list, $video_list );

            echo '<div class="album-station col-xs-12 col-sm-6 col-md-4">';
                echo '<div>';
                    echo '<a id="album-'.$post_id.'"  class="gallery-item" data-src=\''.json_encode($merged_gallery).'\'>';
                        echo '<img src="'.$thumbnail.'" />';
                    echo '</a>';
                echo '</div>';
            echo '</div>';
        }
    }

    ?>
    <script type="text/javascript">
        jQuery('.gallery-item').click(function(){
            var myarr = jQuery(this).data('src');
                const $dynamicGallery = document.getElementById( jQuery(this).attr('id') );
                const dynamicGallery = window.lightGallery($dynamicGallery, {
                    dynamic: true,
                    slideDelay: 400,
                    autoPlay: true,
                    download: false,
                    share: false,
                    fullScreen :true,

                    plugins: [lgVideo, lgFullscreen, lgThumbnail, lgZoom, lgAutoplay],
                    dynamicEl: myarr
                    });
                    dynamicGallery.openGallery(0);
            });
            </script>
    <?php
}

add_shortcode( 'feature_album', 'feature_album_shortcode' );