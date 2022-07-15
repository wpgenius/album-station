<?php 
namespace Elementor;

class Album_Station_widget extends Widget_Base {
	
	public function get_name() {
		return 'as-gallery';
	}
	
	public function get_title() {
		return 'Album Gallery';
	}
	
	public function get_icon() {
		return 'eicon-photo-library';
	}
	
	public function get_categories() {
		return [ 'basic' ];
	}
	
	public function get_style_depends() {
	  $styles = [ 'lightgallery-combined' ];
	
	  return $styles;
	}
	
	public function get_script_depends() {
	  $scripts = [ 'lightgallery-medium-zoom' ];
	
	  return $scripts;
	}
	
	protected function register_controls() {

		$this->start_controls_section(
			'section_title',
			[
				'label' => __( 'Album Station', 'elementor' ),
			]
		);

		$this->add_control(
			'gallery_options',
			[
				'label' => esc_html__( 'Gallery Control Settings', 'elementor' ),
				'type' => \Elementor\Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_control(
			'is_video',
			[
				'label' => esc_html__( 'Show videos', 'plugin-name' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'label_on' => esc_html__( 'Yes', 'your-plugin' ),
				'label_off' => esc_html__( 'No', 'your-plugin' ),
				'return_value' => 'yes',
				'default' => 'yes',
			]
		);
		
		$this->add_control(
			'is_full_screen',
			[
				'label' => esc_html__( 'Full Screen', 'plugin-name' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'label_on' => esc_html__( 'Yes', 'your-plugin' ),
				'label_off' => esc_html__( 'No', 'your-plugin' ),
				'return_value' => 'yes',
				'default' => 'yes',
			]
		);

		$this->add_control(
			'is_thumbnail',
			[
				'label' => esc_html__( 'Show Thumbnails', 'plugin-name' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'label_on' => esc_html__( 'Yes', 'your-plugin' ),
				'label_off' => esc_html__( 'No', 'your-plugin' ),
				'return_value' => 'yes',
				'default' => 'yes',
			]
		);

		$this->add_control(
			'is_zoom',
			[
				'label' => esc_html__( 'Enable Zoom', 'plugin-name' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'label_on' => esc_html__( 'Yes', 'your-plugin' ),
				'label_off' => esc_html__( 'No', 'your-plugin' ),
				'return_value' => 'yes',
				'default' => 'yes',
			]
		);

		$this->add_control(
			'is_autoplay',
			[
				'label' => esc_html__( 'Enable Autoplay', 'plugin-name' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'label_on' => esc_html__( 'Yes', 'your-plugin' ),
				'label_off' => esc_html__( 'No', 'your-plugin' ),
				'return_value' => 'yes',
				'default' => 'yes',
			]
		);

		$this->add_control(
			'grid_layout',

		[
			'type' => \Elementor\Controls_Manager::SELECT,
			'label' => esc_html__( 'Grid Layout', 'elementor' ),
			'options' => [
				'3' => esc_html__( '3', 'elementor' ),
				'4' => esc_html__( '4', 'elementor' ),
			],
				'default' => '4',
			]
		);

		$this->add_control(
			'more_options',
			[
				'label' => esc_html__( 'Additional Options', 'plugin-name' ),
				'type' => \Elementor\Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_control(
			'heading_type',

			[
				'type' => \Elementor\Controls_Manager::SELECT,
				'label' => esc_html__( 'Heading', 'elementor' ),
				'options' => [
					'h1' => esc_html__( 'h1', 'elementor' ),
					'h2' => esc_html__( 'h2', 'elementor' ),
					'h3' => esc_html__( 'h3', 'elementor' ),
					'h4' => esc_html__( 'h4', 'elementor' ),
					'h5' => esc_html__( 'h5', 'elementor' ),
					'h6' => esc_html__( 'h6', 'elementor' ),
				],
				'default' => 'h3',
			]
		);


		$this->add_control(
			'text_color',
			[
				'label' => esc_html__( 'Title Color', 'plugin-name' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => array(
					'.album_title {{heading_type}}' => 'color: {{VALUE}};',
				),
			]
		);

		$this->add_control(
			'alignment_text',
			[
				'type' => \Elementor\Controls_Manager::CHOOSE,
				'label' => esc_html__( 'Alignment', 'elementor' ),
				'options' => [
					'left' => [
						'title' => esc_html__( 'Left', 'elementor' ),
						'icon' => 'eicon-text-align-left',
					],
					'center' => [
						'title' => esc_html__( 'Center', 'elementor' ),
						'icon' => 'eicon-text-align-center',
					],
					'right' => [
						'title' => esc_html__( 'Right', 'elementor' ),
						'icon' => 'eicon-text-align-right',
					],
				],
				'default' => 'center',
				'toggle' => false,
				'selectors' => array(
					'.album_title' => 'text-align: {{VALUE}};',
				),
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			[
				'name' => 'content_typography',
				'selector' => '.album_title',
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Border::get_type(),
			[
				'name' => 'border',
				'label' => esc_html__( 'Border', 'elementor' ),
				'selector' => '.image-wrapper',
		]
	);

		$this->add_responsive_control(
			'title_padding',
			[
				'type' => \Elementor\Controls_Manager::DIMENSIONS,
				'label' => esc_html__( 'Padding', 'plugin-name' ),
				'size_units' => [ 'px', 'em', '%' ],
				'selectors' => [
					'.image-wrapper' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();
	}
	
	protected function render() {
		$settings = $this->get_settings_for_display();

        $mypost= get_posts( array(
			'post_type' => 'album',
			'numberposts'=> 1000,
			 ));
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
				
				if($img_ids){

				foreach( $img_arr as $img_id ){
	
					$img_post = get_post($img_id);
					
					$img_title = $img_post->post_title;
					$img_dec = $img_post->post_content;

					$subhtml = '';
					ob_start(); ?>
						<div class="lightGallery-captions">
							<h4><?php if( $img_title ){echo $img_title; } ?></h4>
						</div>
	
					<?php $subhtml = ob_get_contents();
						ob_end_clean();
					$image_list[] = array(
	
						'src'=> wp_get_attachment_image_url($img_id, 'full'),
						'thumb' =>  wp_get_attachment_image_url($img_id),
							'subHtml'=> $subhtml
	
					);
	
				}
					$gallery = $image_list;
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
				
				$grid_layout = ( $settings['grid_layout'] =='3' ) ? 'col-md-4': 'col-md-3';

				echo '<div class="album-station col-xs-12 col-sm-6 '.$grid_layout.'">';
					echo '<div>';
						echo '<a id="album-'.$post_id.'"  class="gallery-item" data-src=\''.json_encode( $merged_gallery ).'\'>';
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
	
	protected function content_template() {
		$mypost= get_posts( array(
			'post_type' => 'album',
			'numberposts'=> 1000,
			 ));
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

				

				echo '<div class="col-xs-12 col-sm-6 ">';
					echo '<div>';
						echo '<a id="album-'.$post_id.'" ';
							echo '<img src="'.$thumbnail.'" />';
						echo '</a>';
					echo '</div>';
				echo '</div>';
			}
		}
    }
	
	
}