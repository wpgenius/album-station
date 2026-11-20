<?php
/**
 * Coverfold Albums Elementor widget.
 *
 * @package Coverfold
 */

defined( 'ABSPATH' ) || exit;

use Elementor\Controls_Manager;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Typography;
use Elementor\Widget_Base;

/**
 * Album grid widget. Control IDs that existed in Album Station keep their
 * names so pages built with the old widget keep their settings.
 */
class Coverfold_Elementor_Widget extends Widget_Base {

	/**
	 * Widget name.
	 *
	 * @return string
	 */
	public function get_name() {
		return 'coverfold-album-grid';
	}

	/**
	 * Widget title.
	 *
	 * @return string
	 */
	public function get_title() {
		return esc_html__( 'Coverfold Albums', 'coverfold' );
	}

	/**
	 * Widget icon.
	 *
	 * @return string
	 */
	public function get_icon() {
		return 'eicon-photo-library';
	}

	/**
	 * Widget categories.
	 *
	 * @return string[]
	 */
	public function get_categories() {
		return array( 'general' );
	}

	/**
	 * Search keywords.
	 *
	 * @return string[]
	 */
	public function get_keywords() {
		return array( 'coverfold', 'album', 'gallery', 'portfolio', 'lightbox', 'video', 'photo' );
	}

	/**
	 * Styles.
	 *
	 * @return string[]
	 */
	public function get_style_depends() {
		return array( 'coverfold' );
	}

	/**
	 * Scripts.
	 *
	 * @return string[]
	 */
	public function get_script_depends() {
		return array( 'coverfold' );
	}

	/**
	 * Album category options.
	 *
	 * @return array
	 */
	private function category_options() {
		$terms   = get_terms(
			array(
				'taxonomy'   => Coverfold_Post_Type::TAXONOMY,
				'hide_empty' => false,
			)
		);
		$options = array();
		if ( ! is_wp_error( $terms ) ) {
			foreach ( $terms as $term ) {
				$options[ $term->term_id ] = $term->name;
			}
		}
		return $options;
	}

	/**
	 * Switcher control helper.
	 *
	 * @param string $id        Control ID.
	 * @param string $label     Label.
	 * @param string $std       Default: yes or ''.
	 * @param array  $condition Condition.
	 */
	private function switcher( $id, $label, $std = 'yes', $condition = array() ) {
		$args = array(
			'label'        => $label,
			'type'         => Controls_Manager::SWITCHER,
			'label_on'     => esc_html__( 'Yes', 'coverfold' ),
			'label_off'    => esc_html__( 'No', 'coverfold' ),
			'return_value' => 'yes',
			'default'      => $std,
		);
		if ( $condition ) {
			$args['condition'] = $condition;
		}
		$this->add_control( $id, $args );
	}

	/**
	 * Left / center / right choice options.
	 *
	 * @return array
	 */
	private function align_options() {
		return array(
			'left'   => array(
				'title' => esc_html__( 'Left', 'coverfold' ),
				'icon'  => 'eicon-text-align-left',
			),
			'center' => array(
				'title' => esc_html__( 'Center', 'coverfold' ),
				'icon'  => 'eicon-text-align-center',
			),
			'right'  => array(
				'title' => esc_html__( 'Right', 'coverfold' ),
				'icon'  => 'eicon-text-align-right',
			),
		);
	}

	/**
	 * Register controls.
	 */
	protected function register_controls() {
		$this->register_content_controls();
		$this->register_style_controls();
	}

	/**
	 * Content tab.
	 */
	private function register_content_controls() {
		// Albums.
		$this->start_controls_section( 'section_albums', array( 'label' => esc_html__( 'Albums', 'coverfold' ) ) );

		$this->add_control(
			'categories',
			array(
				'label'       => esc_html__( 'Categories', 'coverfold' ),
				'type'        => Controls_Manager::SELECT2,
				'multiple'    => true,
				'label_block' => true,
				'options'     => $this->category_options(),
				'description' => esc_html__( 'Leave empty to show all albums.', 'coverfold' ),
			)
		);

		$this->add_control(
			'number',
			array(
				'label'       => esc_html__( 'Number of albums', 'coverfold' ),
				'type'        => Controls_Manager::NUMBER,
				'min'         => 0,
				'default'     => 0,
				'description' => esc_html__( '0 shows all albums.', 'coverfold' ),
			)
		);

		$this->add_control(
			'orderby',
			array(
				'label'   => esc_html__( 'Order by', 'coverfold' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'date',
				'options' => array(
					'date'       => esc_html__( 'Date', 'coverfold' ),
					'title'      => esc_html__( 'Title', 'coverfold' ),
					'menu_order' => esc_html__( 'Custom order (Order field)', 'coverfold' ),
					'modified'   => esc_html__( 'Last modified', 'coverfold' ),
					'rand'       => esc_html__( 'Random', 'coverfold' ),
				),
			)
		);

		$this->add_control(
			'order',
			array(
				'label'   => esc_html__( 'Order', 'coverfold' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'DESC',
				'options' => array(
					'DESC' => esc_html__( 'Descending', 'coverfold' ),
					'ASC'  => esc_html__( 'Ascending', 'coverfold' ),
				),
			)
		);

		$this->end_controls_section();

		// Layout.
		$this->start_controls_section( 'section_layout', array( 'label' => esc_html__( 'Layout', 'coverfold' ) ) );

		$columns = array_combine( array( '1', '2', '3', '4', '5', '6' ), array( '1', '2', '3', '4', '5', '6' ) );
		$this->add_responsive_control(
			'grid_layout',
			array(
				'label'          => esc_html__( 'Columns', 'coverfold' ),
				'type'           => Controls_Manager::SELECT,
				'options'        => $columns,
				'default'        => '4',
				'tablet_default' => '2',
				'mobile_default' => '1',
			)
		);

		$this->add_control(
			'gap',
			array(
				'label'      => esc_html__( 'Spacing', 'coverfold' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array(
					'px' => array(
						'min' => 0,
						'max' => 100,
					),
				),
				'default'    => array(
					'unit' => 'px',
					'size' => 20,
				),
			)
		);

		$this->add_control(
			'hover',
			array(
				'label'   => esc_html__( 'Hover effect', 'coverfold' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'zoom',
				'options' => array(
					'zoom' => esc_html__( 'Zoom', 'coverfold' ),
					'none' => esc_html__( 'None', 'coverfold' ),
				),
			)
		);

		$this->switcher( 'show_title', esc_html__( 'Show album titles', 'coverfold' ) );

		$this->add_control(
			'heading_type',
			array(
				'label'     => esc_html__( 'Title tag', 'coverfold' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'h3',
				'options'   => array(
					'h1' => 'H1',
					'h2' => 'H2',
					'h3' => 'H3',
					'h4' => 'H4',
					'h5' => 'H5',
					'h6' => 'H6',
					'p'  => 'p',
				),
				'condition' => array( 'show_title' => 'yes' ),
			)
		);

		$this->add_control(
			'alignment_text',
			array(
				'label'     => esc_html__( 'Title alignment', 'coverfold' ),
				'type'      => Controls_Manager::CHOOSE,
				'options'   => $this->align_options(),
				'default'   => 'center',
				'toggle'    => false,
				'condition' => array( 'show_title' => 'yes' ),
			)
		);

		$this->switcher( 'show_count', esc_html__( 'Show photo and video count', 'coverfold' ), '' );

		$this->end_controls_section();

		// Filters.
		$this->start_controls_section( 'section_filters', array( 'label' => esc_html__( 'Category filters', 'coverfold' ) ) );

		$this->switcher( 'show_filters', esc_html__( 'Show filters', 'coverfold' ) );
		$this->switcher( 'show_filter_all', esc_html__( 'Show an "All" button', 'coverfold' ), 'yes', array( 'show_filters' => 'yes' ) );

		$this->add_control(
			'all_label',
			array(
				'label'       => esc_html__( '"All" button label', 'coverfold' ),
				'type'        => Controls_Manager::TEXT,
				'placeholder' => esc_html__( 'All', 'coverfold' ),
				'condition'   => array(
					'show_filters'    => 'yes',
					'show_filter_all' => 'yes',
				),
			)
		);

		$this->add_control(
			'isotop_alignment_text',
			array(
				'label'     => esc_html__( 'Filter alignment', 'coverfold' ),
				'type'      => Controls_Manager::CHOOSE,
				'options'   => $this->align_options(),
				'default'   => 'center',
				'toggle'    => false,
				'condition' => array( 'show_filters' => 'yes' ),
			)
		);

		$this->end_controls_section();

		// Lightbox.
		$this->start_controls_section( 'section_lightbox', array( 'label' => esc_html__( 'Lightbox', 'coverfold' ) ) );

		$this->switcher( 'is_video', esc_html__( 'Include videos', 'coverfold' ) );
		$this->switcher( 'captions', esc_html__( 'Show captions', 'coverfold' ) );
		$this->switcher( 'is_thumbnail', esc_html__( 'Thumbnail strip', 'coverfold' ) );
		$this->switcher( 'hide_thumbnail_on_fullscreen', esc_html__( 'Hide thumbnails in fullscreen', 'coverfold' ), 'yes', array( 'is_thumbnail' => 'yes' ) );
		$this->switcher( 'is_zoom', esc_html__( 'Zoom', 'coverfold' ) );
		$this->switcher( 'is_full_screen', esc_html__( 'Fullscreen button', 'coverfold' ) );
		$this->switcher( 'is_autoplay', esc_html__( 'Slideshow button', 'coverfold' ) );
		$this->switcher( 'slideshow_autostart', esc_html__( 'Start slideshow automatically', 'coverfold' ), '', array( 'is_autoplay' => 'yes' ) );
		$this->switcher( 'download', esc_html__( 'Download button', 'coverfold' ), '' );

		$this->end_controls_section();
	}

	/**
	 * Style tab.
	 */
	private function register_style_controls() {
		// Cards.
		$this->start_controls_section(
			'section_style_card',
			array(
				'label' => esc_html__( 'Album cards', 'coverfold' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_control(
			'ratio',
			array(
				'label'     => esc_html__( 'Cover shape', 'coverfold' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => '3 / 2',
				'options'   => array(
					'1 / 1'  => esc_html__( 'Square', 'coverfold' ),
					'4 / 3'  => '4:3',
					'3 / 2'  => '3:2',
					'16 / 9' => '16:9',
					'3 / 4'  => esc_html__( 'Portrait 3:4', 'coverfold' ),
				),
				'selectors' => array( '{{WRAPPER}} .coverfold' => '--coverfold-ratio: {{VALUE}};' ),
			)
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'     => 'border',
				'selector' => '{{WRAPPER}} .coverfold-card',
			)
		);

		$this->add_responsive_control(
			'card_radius',
			array(
				'label'      => esc_html__( 'Border radius', 'coverfold' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array(
					'px' => array(
						'min' => 0,
						'max' => 50,
					),
				),
				'selectors'  => array( '{{WRAPPER}} .coverfold' => '--coverfold-radius: {{SIZE}}{{UNIT}};' ),
			)
		);

		$this->add_responsive_control(
			'title_padding',
			array(
				'label'      => esc_html__( 'Padding', 'coverfold' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em', '%' ),
				'selectors'  => array( '{{WRAPPER}} .coverfold-card' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ),
			)
		);

		$this->end_controls_section();

		// Titles.
		$this->start_controls_section(
			'section_style_title',
			array(
				'label'     => esc_html__( 'Album titles', 'coverfold' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => array( 'show_title' => 'yes' ),
			)
		);

		$this->add_control(
			'text_color',
			array(
				'label'     => esc_html__( 'Color', 'coverfold' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .coverfold-title' => 'color: {{VALUE}};' ),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'content_typography',
				'selector' => '{{WRAPPER}} .coverfold-title',
			)
		);

		$this->add_responsive_control(
			'title_spacing',
			array(
				'label'      => esc_html__( 'Space above title', 'coverfold' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', 'em' ),
				'selectors'  => array( '{{WRAPPER}} .coverfold-title' => 'margin-top: {{SIZE}}{{UNIT}};' ),
			)
		);

		$this->end_controls_section();

		// Filters.
		$this->start_controls_section(
			'section_style_filters',
			array(
				'label'     => esc_html__( 'Filters', 'coverfold' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => array( 'show_filters' => 'yes' ),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'filter_typography',
				'selector' => '{{WRAPPER}} .coverfold-filter',
			)
		);

		$this->add_responsive_control(
			'isotope_title_padding',
			array(
				'label'      => esc_html__( 'Padding', 'coverfold' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em', '%' ),
				'selectors'  => array( '{{WRAPPER}} .coverfold-filter' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ),
			)
		);

		$this->add_responsive_control(
			'isotope_title_margin',
			array(
				'label'      => esc_html__( 'Margin', 'coverfold' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em', '%' ),
				'selectors'  => array( '{{WRAPPER}} .coverfold-filter' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ),
			)
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'     => 'filter_border',
				'selector' => '{{WRAPPER}} .coverfold-filter',
			)
		);

		$this->add_responsive_control(
			'isotope_filter_border_radius',
			array(
				'label'      => esc_html__( 'Border radius', 'coverfold' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em', '%' ),
				'selectors'  => array( '{{WRAPPER}} .coverfold-filter' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ),
			)
		);

		$this->add_responsive_control(
			'isotope_filter_spacing',
			array(
				'label'      => esc_html__( 'Space below filters', 'coverfold' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array(
					'px' => array(
						'min' => 0,
						'max' => 100,
					),
				),
				'selectors'  => array( '{{WRAPPER}} .coverfold-filters' => 'margin-bottom: {{SIZE}}{{UNIT}};' ),
			)
		);

		$this->start_controls_tabs( 'style_tabs' );

		$states = array(
			'normal' => array( esc_html__( 'Normal', 'coverfold' ), '{{WRAPPER}} .coverfold-filter', 'isotope_text_color', 'isotope_background_color' ),
			'hover'  => array( esc_html__( 'Hover', 'coverfold' ), '{{WRAPPER}} .coverfold-filter:hover', 'isotope_hover_text_color', 'isotope_hover_background_color' ),
			'active' => array( esc_html__( 'Active', 'coverfold' ), '{{WRAPPER}} .coverfold-filter.is-active', 'isotope_active_text_color', 'isotope_active_background_color' ),
		);

		foreach ( $states as $state => $config ) {
			list( $label, $selector, $color_id, $bg_id ) = $config;
			$this->start_controls_tab( 'style_' . $state . '_tab', array( 'label' => $label ) );

			$this->add_control(
				$color_id,
				array(
					'label'     => esc_html__( 'Text color', 'coverfold' ),
					'type'      => Controls_Manager::COLOR,
					'selectors' => array( $selector => 'color: {{VALUE}};' ),
				)
			);

			$this->add_control(
				$bg_id,
				array(
					'label'     => esc_html__( 'Background color', 'coverfold' ),
					'type'      => Controls_Manager::COLOR,
					'selectors' => array( $selector => 'background-color: {{VALUE}};' ),
				)
			);

			$this->end_controls_tab();
		}

		$this->end_controls_tabs();
		$this->end_controls_section();
	}

	/**
	 * Read a yes/no switcher value.
	 *
	 * @param array  $settings Settings.
	 * @param string $key      Control ID.
	 * @return bool
	 */
	private function on( array $settings, $key ) {
		return isset( $settings[ $key ] ) && 'yes' === $settings[ $key ];
	}

	/**
	 * Render the widget.
	 */
	protected function render() {
		$s = $this->get_settings_for_display();

		$args = array(
			'categories'             => ! empty( $s['categories'] ) ? array_map( 'absint', (array) $s['categories'] ) : array(),
			'number'                 => isset( $s['number'] ) ? (int) $s['number'] : 0,
			'orderby'                => isset( $s['orderby'] ) ? $s['orderby'] : 'date',
			'order'                  => isset( $s['order'] ) ? $s['order'] : 'DESC',
			'columns'                => ! empty( $s['grid_layout'] ) ? $s['grid_layout'] : 4,
			'columns_tablet'         => ! empty( $s['grid_layout_tablet'] ) ? $s['grid_layout_tablet'] : 2,
			'columns_mobile'         => ! empty( $s['grid_layout_mobile'] ) ? $s['grid_layout_mobile'] : 1,
			'gap'                    => isset( $s['gap']['size'] ) && '' !== $s['gap']['size'] ? $s['gap']['size'] : 20,
			'hover'                  => isset( $s['hover'] ) ? $s['hover'] : 'zoom',
			'show_title'             => ! isset( $s['show_title'] ) || $this->on( $s, 'show_title' ),
			'title_tag'              => isset( $s['heading_type'] ) ? $s['heading_type'] : 'h3',
			'title_align'            => isset( $s['alignment_text'] ) ? $s['alignment_text'] : 'center',
			'show_count'             => $this->on( $s, 'show_count' ),
			'filters'                => $this->on( $s, 'show_filters' ),
			'filter_all'             => ! isset( $s['show_filter_all'] ) || $this->on( $s, 'show_filter_all' ),
			'all_label'              => isset( $s['all_label'] ) ? $s['all_label'] : '',
			'filter_align'           => isset( $s['isotop_alignment_text'] ) ? $s['isotop_alignment_text'] : 'center',
			'videos'                 => $this->on( $s, 'is_video' ),
			'captions'               => ! isset( $s['captions'] ) || $this->on( $s, 'captions' ),
			'thumbnails'             => $this->on( $s, 'is_thumbnail' ),
			'hide_thumbs_fullscreen' => $this->on( $s, 'hide_thumbnail_on_fullscreen' ),
			'zoom'                   => $this->on( $s, 'is_zoom' ),
			'fullscreen'             => $this->on( $s, 'is_full_screen' ),
			'slideshow'              => $this->on( $s, 'is_autoplay' ),
			'slideshow_autostart'    => $this->on( $s, 'slideshow_autostart' ),
			'download'               => $this->on( $s, 'download' ),
		);

		echo Coverfold_Renderer::render( $args ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped in the renderer.
	}
}
