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
			'hide_thumbnail_on_fullscreen',
			[
				'label' => esc_html__( 'Hide Thumbnails on Fullscreen mode', 'plugin-name' ),
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

		$this->add_control(
			'filter_settings',
			[
				'label' => esc_html__( 'Filter Settings', 'plugin-name' ),
				'type' => \Elementor\Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_control(
			'show_filters',
			[
				'label' => esc_html__( 'Show Filters', 'plugin-name' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'label_on' => esc_html__( 'Yes', 'your-plugin' ),
				'label_off' => esc_html__( 'No', 'your-plugin' ),
				'return_value' => 'yes',
				'default' => 'yes',
			]
		);

		$this->add_responsive_control(
			'isotope_title_padding',
			[
				'type' => \Elementor\Controls_Manager::DIMENSIONS,
				'label' => esc_html__( 'Padding', 'plugin-name' ),
				'size_units' => [ 'px', 'em', '%' ],
				'selectors' => [
					'.filter_buttons' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'isotope_title_margin',
			[
				'type' => \Elementor\Controls_Manager::DIMENSIONS,
				'label' => esc_html__( 'Margin', 'plugin-name' ),
				'size_units' => [ 'px', 'em', '%' ],
				'selectors' => [
					'.filter_buttons' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Border::get_type(),
			[
				'name' => 'filter_border',
				'label' => esc_html__( 'Filter Border', 'elementor' ),
				'selector' => '.filter_buttons',
			]
		);

		$this->add_responsive_control(
			'isotope_filter_border_radius',
			[
				'type' => \Elementor\Controls_Manager::DIMENSIONS,
				'label' => esc_html__( 'Filter border radius', 'plugin-name' ),
				'size_units' => [ 'px', 'em', '%' ],
				'selectors' => [
					'.filter_buttons' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'isotop_alignment_text',
			[
				'type' => \Elementor\Controls_Manager::CHOOSE,
				'label' => esc_html__( 'Filter Alignment', 'elementor' ),
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
					'.button-group' => 'text-align: {{VALUE}};',
				),
			]
		);

		$this->add_responsive_control(
			'isotope_filter_spacing',
			[
				'label' => esc_html__( 'Spacing', 'plugin-name' ),
				'type' => \Elementor\Controls_Manager::SLIDER,
				'size_units' => [ 'px', '%' ],
				'range' => [
					'px' => [
						'min' => 0,
						'max' => 100,
						'step' => 5,
					],
					'%' => [
						'min' => 0,
						'max' => 10,
					],
				],
				'default' => [
					'unit' => 'px',
					'size' => 30,
				],
				'selectors' => [
					'.button-group' => 'margin-bottom: {{SIZE}}{{UNIT}};',
				],
			]
		);
		
		$this->start_controls_tabs(
			'style_tabs'
		);

		$this->start_controls_tab(
			'style_normal_tab',
			[
				'label' => esc_html__( 'Normal', 'plugin-name' ),
			]
		);

		$this->add_control(
			'isotope_text_color',
			[
				'label' => esc_html__( 'Text Color', 'plugin-name' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => array(
					'.filter_buttons' => 'color: {{VALUE}};',
				),
			]
		);

		$this->add_control(
			'isotope_background_color',
			[
				'label' => esc_html__( 'Background Color', 'plugin-name' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => array(
					'.filter_buttons' => 'background-color: {{VALUE}};',
				),
			]
		);
		
		$this->end_controls_tab();

		$this->start_controls_tab(
			'style_hover_tab',
			[
				'label' => esc_html__( 'Hover', 'plugin-name' ),
			]
		);

		$this->add_control(
			'isotope_hover_text_color',
			[
				'label' => esc_html__( 'Text Color', 'plugin-name' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => array(
					'.filter_buttons:hover' => 'color: {{VALUE}};',
				),
			]
		);

		$this->add_control(
			'isotope_hover_background_color',
			[
				'label' => esc_html__( 'Background Color', 'plugin-name' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => array(
					'.filter_buttons:hover' => 'background-color: {{VALUE}};',
				),
			]
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'style_active_tab',
			[
				'label' => esc_html__( 'Active', 'plugin-name' ),
			]
		);

		$this->add_control(
			'isotope_active_text_color',
			[
				'label' => esc_html__( 'Text Color', 'plugin-name' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => array(
					'.is-checked' => 'color: {{VALUE}};',
				),
			]
		);

		$this->add_control(
			'isotope_active_background_color',
			[
				'label' => esc_html__( 'Background Color', 'plugin-name' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => array(
					'.is-checked' => 'background-color: {{VALUE}};',
				),
			]
		);

		$this->end_controls_tab();

		$this->end_controls_section();
	}
	
	protected function render() {
		$settings = $this->get_settings_for_display();
		global $post;
        $mypost= get_posts( array(
			'post_type' => 'album',
			'numberposts'=> 1000,
		));
		$terms_name=  get_terms( array(
			'taxonomy' => 'album_category',
			'hide_empty' => false
		));		
		
		if(( $settings["show_filters"] ==='yes' )){
			echo '<div class="button-group filter-button-group">';
					foreach( $terms_name as $my_term ){ 
						$slug = $my_term->slug; 
						echo '<span data-filter=".'.$slug.'" class="filter_buttons">'.$my_term->name.'</span>';
					} 
			echo '</div>';
		}
	?>
	<div class="album-grid">
	<?php
		if($mypost){
			foreach( $mypost as $post ){
				setup_postdata( $post );
				$post_id = $post->ID;
				$thumbnail = get_the_post_thumbnail_url($post_id);
				$img_ids = get_post_meta($post_id, 'image_post_meta_value', true);
					
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

				if(( $settings["is_video"] ==='yes' )){

					if( get_post_meta( $post->ID,'video_choice', true )== 'single'){
						
						$youtube_video_list = array();

						if( get_post_meta( $post_id, 'youtube_video_post_meta_value', true ) ){

							parse_str( parse_url( get_post_meta( $post_id, 'youtube_video_post_meta_value', true ), PHP_URL_QUERY), $id ) ;
							$youtube_video_id = $id['v'];

							$youtube_video_list[] = array(
								'src' => "//www.youtube.com/watch?v=".$youtube_video_id,
								'poster' => "https://img.youtube.com/vi/".$youtube_video_id."/maxresdefault.jpg",
								'thumb' => "https://img.youtube.com/vi/".$youtube_video_id."/maxresdefault.jpg"
							);
						}

						$vimeo_video_list = array();

						if( get_post_meta( $post_id, 'vimeo_video_post_meta_value', true ) ){
							
							$url = explode("/", parse_url( get_post_meta( $post_id, 'vimeo_video_post_meta_value', true ), PHP_URL_PATH));
							$vimeo_video_id = (int)$url[count($url)-1];

							$hash = unserialize(file_get_contents("http://vimeo.com/api/v2/video/$vimeo_video_id.php"));
							$img = $hash[0]['thumbnail_medium']; 

							$vimeo_video_list[] = array(
								'src' => "//vimeo.com/".$vimeo_video_id,
								'poster' => $img,
								'thumb' => $img
							);
						}

						$merged_videos = array_merge( $youtube_video_list, $vimeo_video_list );

						$gallery = (count($image_list) > 0) ? array_merge( $image_list, $merged_videos ) : $merged_videos ;
					} else {
						$gallery = $image_list;
						$api_key = 'AIzaSyApK9-NJu45Ai2DTlsd81LFRL-5z_N06hc';
						$playlist_id = get_post_meta( $post->ID,'playlist_id', true );

						$api_url = 'https://www.googleapis.com/youtube/v3/playlistItems?part=snippet&maxResults=25&playlistId='. $playlist_id . '&key=' . $api_key;
						$playlist_data = json_decode(file_get_contents($api_url),true);
						if($playlist_data) { 
							$youtube_playlist = array();
							foreach ($playlist_data['items'] as $item) { 
								$id = $item['snippet']['resourceId']['videoId'] ;
								$youtube_playlist[] = array(
									'src' => "//www.youtube.com/watch?v=".$id,
									'poster' => "https://img.youtube.com/vi/".$id."/maxresdefault.jpg",
									'thumb' => "https://img.youtube.com/vi/".$id."/maxresdefault.jpg"
								);
							}
							$gallery = (count($image_list) > 0) ? array_merge( $image_list, $youtube_playlist ) : $youtube_playlist;
						}

					}
				}
				
				$grid_layout = ( $settings['grid_layout'] =='3' ) ? 'col-md-4': 'col-md-3';
				$term_list = wp_get_post_terms( $post->ID, 'album_category', array( 'fields' => 'slugs' ) );
					echo '<div class="album-station col-xs-12 col-sm-6 '.$grid_layout.' '.implode(' ', $term_list).'">';
						echo '<div class="image-wrapper">';
							echo '<div class="gallery">';
							echo '<a id="album-'.$post_id.'"  class="gallery-item" data-src=\''.json_encode( $gallery ).'\'>';
								echo '<img src="'.$thumbnail.'" />';
							echo '</a>';
							echo '</div>';
						echo '</div>';
						echo "<div class='album_title' style='text-align:".esc_attr($settings['alignment_text'])."'>";
							echo '<'.$settings["heading_type"].'>'.get_the_title().'</'.$settings["heading_type"].'>';
						echo '</div>';
					echo '</div>';

			}
			wp_reset_postdata();
			
		} ?>
		</div>
		
	<?php
		$plugins = array();
		( $settings["is_video"] ==='yes' ) ? $plugins[]='lgVideo' :'';
		( $settings["is_full_screen"] ==='yes' ) ? $plugins[]='lgFullscreen' :'';
		( $settings["is_thumbnail"] ==='yes' ) ? $plugins[]='lgThumbnail' :'';
		( $settings["is_zoom"] ==='yes' ) ? $plugins[]='lgZoom' :'';
		( $settings["is_autoplay"] ==='yes' ) ? $plugins[]='lgAutoplay' :'';
		
		$plugin_str = implode(',', $plugins);

		?>
		<script type="text/javascript">
			jQuery('.gallery-item').click(function(){
				var myarr = jQuery(this).data('src');
				
				const $dynamicGallery = document.getElementById( jQuery(this).attr('id') );
				const dynamicGallery = window.lightGallery($dynamicGallery, {
					addClass: <?php echo ( $settings["hide_thumbnail_on_fullscreen"] ==='yes' ) ? "'hide_thumbnail_on_fullscreen'" : "'display_thumbnail_on_fullscreen'" ?>,
					dynamic: true,
					slideDelay: 400,
					autoPlay: true,
					download: false,
					share: false,
					fullScreen :true,

					plugins: [<?php echo $plugin_str; ?>],
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

		$terms_name=  get_terms( array(
			'taxonomy' => 'album_category',
			'hide_empty' => false
		));	
		?>
			<#
			 	var grid = '';
				if ( settings.grid_layout == 3 ){
					grid = 'col-md-4';
				} else{
					grid = 'col-md-3';
				}

			if(settings.show_filters=='yes'){
			
			#>
		<?php
		
		echo '<div class="button-group filter-button-group">';
				foreach( $terms_name as $my_term ){ 
					$slug = $my_term->slug; 
					echo '<a data-filter=".'.$slug.'" class="filter_buttons">'.$my_term->name.'</a>';
				} 
		echo '</div>';
		?>
			<# } #>
		<?php
		if($mypost){
			foreach( $mypost as $post ){
				setup_postdata( $post );
				$post_id = $post->ID;
				$thumbnail = get_the_post_thumbnail_url($post_id); ?>

				<div class="album-station col-xs-12 col-sm-6 {{grid}}">
					<div class="image-wrapper">
						<a id="album-<?php echo $post_id; ?>"  class="gallery-item" >
							<img src="<?php echo $thumbnail; ?>" />
						</a>
					</div>
					<div class='album_title'>
						<{{settings.heading_type}}><?php the_title(); ?> </{{settings.heading_type}}>
					</div>
				</div>
			<?php	
			}
		}		
		wp_reset_postdata();
    }
	
	
}