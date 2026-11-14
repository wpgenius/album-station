<?php
/**
 * StackFolio Albums Elementor widget.
 *
 * @package StackFolio
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
class StackFolio_Elementor_Widget extends Widget_Base {

	/**
	 * Widget name.
	 *
	 * @return string
	 */
	public function get_name() {
		return 'stackfolio-album-grid';
	}

	/**
	 * Widget title.
	 *
	 * @return string
	 */
	public function get_title() {
		return esc_html__( 'StackFolio Albums', 'stackfolio' );
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
		return array( 'stackfolio', 'album', 'gallery', 'portfolio', 'lightbox', 'video', 'photo' );
	}

	/**
	 * Styles.
	 *
	 * @return string[]
	 */
	public function get_style_depends() {
		return array( 'stackfolio' );
	}

	/**
	 * Scripts.
	 *
	 * @return string[]
	 */
	public function get_script_depends() {
		return array( 'stackfolio-isotope', 'stackfolio' );
	}

	/**
	 * Album category options.
	 *
	 * @return array
	 */
	private function category_options() {
		$terms   = get_terms(
			array(
				'taxonomy'   => StackFolio_Post_Type::TAXONOMY,
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
			'label_on'     => esc_html__( 'Yes', 'stackfolio' ),
			'label_off'    => esc_html__( 'No', 'stackfolio' ),
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
				'title' => esc_html__( 'Left', 'stackfolio' ),
				'icon'  => 'eicon-text-align-left',
			),
			'center' => array(
				'title' => esc_html__( 'Center', 'stackfolio' ),
				'icon'  => 'eicon-text-align-center',
			),
			'right'  => array(
				'title' => esc_html__( 'Right', 'stackfolio' ),
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
		$this->start_controls_section( 'section_albums', array( 'label' => esc_html__( 'Albums', 'stackfolio' ) ) );

		$this->add_control(
			'categories',
			array(
				'label'       => esc_html__( 'Categories', 'stackfolio' ),
				'type'        => Controls_Manager::SELECT2,
				'multiple'    => true,
				'label_block' => true,
				'options'     => $this->category_options(),
				'description' => esc_html__( 'Leave empty to show all albums.', 'stackfolio' ),
			)
		);

		$this->add_control(
			'number',
			array(
				'label'       => esc_html__( 'Number of albums', 'stackfolio' ),
				'type'        => Controls_Manager::NUMBER,
				'min'         => 0,
				'default'     => 0,
				'description' => esc_html__( '0 shows all albums.', 'stackfolio' ),
			)
		);

		$this->add_control(
			'orderby',
			array(
				'label'   => esc_html__( 'Order by', 'stackfolio' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'date',
				'options' => array(
					'date'       => esc_html__( 'Date', 'stackfolio' ),
					'title'      => esc_html__( 'Title', 'stackfolio' ),
					'menu_order' => esc_html__( 'Custom order (Order field)', 'stackfolio' ),
					'modified'   => esc_html__( 'Last modified', 'stackfolio' ),
					'rand'       => esc_html__( 'Random', 'stackfolio' ),
				),
			)
		);

		$this->add_control(
			'order',
			array(
				'label'   => esc_html__( 'Order', 'stackfolio' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'DESC',
				'options' => array(
					'DESC' => esc_html__( 'Descending', 'stackfolio' ),
					'ASC'  => esc_html__( 'Ascending', 'stackfolio' ),
				),
			)
		);

		$this->end_controls_section();

		// Layout.
		$this->start_controls_section( 'section_layout', array( 'label' => esc_html__( 'Layout', 'stackfolio' ) ) );

		$columns = array_combine( array( '1', '2', '3', '4', '5', '6' ), array( '1', '2', '3', '4', '5', '6' ) );
		$this->add_responsive_control(
			'grid_layout',
			array(
				'label'          => esc_html__( 'Columns', 'stackfolio' ),
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
				'label'      => esc_html__( 'Spacing', 'stackfolio' ),
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
				'label'   => esc_html__( 'Hover effect', 'stackfolio' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'zoom',
				'options' => array(
					'zoom' => esc_html__( 'Zoom', 'stackfolio' ),
					'none' => esc_html__( 'None', 'stackfolio' ),
				),
			)
		);

		$this->switcher( 'show_title', esc_html__( 'Show album titles', 'stackfolio' ) );

		$this->add_control(
			'heading_type',
			array(
				'label'     => esc_html__( 'Title tag', 'stackfolio' ),
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
				'label'     => esc_html__( 'Title alignment', 'stackfolio' ),
				'type'      => Controls_Manager::CHOOSE,
				'options'   => $this->align_options(),
				'default'   => 'center',
				'toggle'    => false,
				'condition' => array( 'show_title' => 'yes' ),
			)
		);

		$this->switcher( 'show_count', esc_html__( 'Show photo and video count', 'stackfolio' ), '' );

		$this->end_controls_section();

		// Filters.
		$this->start_controls_section( 'section_filters', array( 'label' => esc_html__( 'Category filters', 'stackfolio' ) ) );

		$this->switcher( 'show_filters', esc_html__( 'Show filters', 'stackfolio' ) );
		$this->switcher( 'show_filter_all', esc_html__( 'Show an "All" button', 'stackfolio' ), 'yes', array( 'show_filters' => 'yes' ) );

		$this->add_control(
			'all_label',
			array(
				'label'       => esc_html__( '"All" button label', 'stackfolio' ),
				'type'        => Controls_Manager::TEXT,
				'placeholder' => esc_html__( 'All', 'stackfolio' ),
				'condition'   => array(
					'show_filters'    => 'yes',
					'show_filter_all' => 'yes',
				),
			)
		);

		$this->add_control(
			'isotop_alignment_text',
			array(
				'label'     => esc_html__( 'Filter alignment', 'stackfolio' ),
				'type'      => Controls_Manager::CHOOSE,
				'options'   => $this->align_options(),
				'default'   => 'center',
				'toggle'    => false,
				'condition' => array( 'show_filters' => 'yes' ),
			)
		);

		$this->end_controls_section();

		// Lightbox.
		$this->start_controls_section( 'section_lightbox', array( 'label' => esc_html__( 'Lightbox', 'stackfolio' ) ) );

		$this->switcher( 'is_video', esc_html__( 'Include videos', 'stackfolio' ) );
		$this->switcher( 'captions', esc_html__( 'Show captions', 'stackfolio' ) );
		$this->switcher( 'is_thumbnail', esc_html__( 'Thumbnail strip', 'stackfolio' ) );
		$this->switcher( 'hide_thumbnail_on_fullscreen', esc_html__( 'Hide thumbnails in fullscreen', 'stackfolio' ), 'yes', array( 'is_thumbnail' => 'yes' ) );
		$this->switcher( 'is_zoom', esc_html__( 'Zoom', 'stackfolio' ) );
		$this->switcher( 'is_full_screen', esc_html__( 'Fullscreen button', 'stackfolio' ) );
		$this->switcher( 'is_autoplay', esc_html__( 'Slideshow button', 'stackfolio' ) );
		$this->switcher( 'slideshow_autostart', esc_html__( 'Start slideshow automatically', 'stackfolio' ), '', array( 'is_autoplay' => 'yes' ) );
		$this->switcher( 'download', esc_html__( 'Download button', 'stackfolio' ), '' );

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
				'label' => esc_html__( 'Album cards', 'stackfolio' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_control(
			'ratio',
			array(
				'label'     => esc_html__( 'Cover shape', 'stackfolio' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => '3 / 2',
				'options'   => array(
					'1 / 1'  => esc_html__( 'Square', 'stackfolio' ),
					'4 / 3'  => '4:3',
					'3 / 2'  => '3:2',
					'16 / 9' => '16:9',
					'3 / 4'  => esc_html__( 'Portrait 3:4', 'stackfolio' ),
				),
				'selectors' => array( '{{WRAPPER}} .stackfolio' => '--stackfolio-ratio: {{VALUE}};' ),
			)
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'     => 'border',
				'selector' => '{{WRAPPER}} .stackfolio-card',
			)
		);

		$this->add_responsive_control(
			'card_radius',
			array(
				'label'      => esc_html__( 'Border radius', 'stackfolio' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array(
					'px' => array(
						'min' => 0,
						'max' => 50,
					),
				),
				'selectors'  => array( '{{WRAPPER}} .stackfolio' => '--stackfolio-radius: {{SIZE}}{{UNIT}};' ),
			)
		);

		$this->add_responsive_control(
			'title_padding',
			array(
				'label'      => esc_html__( 'Padding', 'stackfolio' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em', '%' ),
				'selectors'  => array( '{{WRAPPER}} .stackfolio-card' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ),
			)
		);

		$this->end_controls_section();

		// Titles.
		$this->start_controls_section(
			'section_style_title',
			array(
				'label'     => esc_html__( 'Album titles', 'stackfolio' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => array( 'show_title' => 'yes' ),
			)
		);

		$this->add_control(
			'text_color',
			array(
				'label'     => esc_html__( 'Color', 'stackfolio' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .stackfolio-title' => 'color: {{VALUE}};' ),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'content_typography',
				'selector' => '{{WRAPPER}} .stackfolio-title',
			)
		);

		$this->add_responsive_control(
			'title_spacing',
			array(
				'label'      => esc_html__( 'Space above title', 'stackfolio' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', 'em' ),
				'selectors'  => array( '{{WRAPPER}} .stackfolio-title' => 'margin-top: {{SIZE}}{{UNIT}};' ),
			)
		);

		$this->end_controls_section();

		// Filters.
		$this->start_controls_section(
			'section_style_filters',
			array(
				'label'     => esc_html__( 'Filters', 'stackfolio' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => array( 'show_filters' => 'yes' ),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'filter_typography',
				'selector' => '{{WRAPPER}} .stackfolio-filter',
			)
		);

		$this->add_responsive_control(
			'isotope_title_padding',
			array(
				'label'      => esc_html__( 'Padding', 'stackfolio' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em', '%' ),
				'selectors'  => array( '{{WRAPPER}} .stackfolio-filter' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ),
			)
		);

		$this->add_responsive_control(
			'isotope_title_margin',
			array(
				'label'      => esc_html__( 'Margin', 'stackfolio' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em', '%' ),
				'selectors'  => array( '{{WRAPPER}} .stackfolio-filter' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ),
			)
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'     => 'filter_border',
				'selector' => '{{WRAPPER}} .stackfolio-filter',
			)
		);

		$this->add_responsive_control(
			'isotope_filter_border_radius',
			array(
				'label'      => esc_html__( 'Border radius', 'stackfolio' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em', '%' ),
				'selectors'  => array( '{{WRAPPER}} .stackfolio-filter' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ),
			)
		);

		$this->add_responsive_control(
			'isotope_filter_spacing',
			array(
				'label'      => esc_html__( 'Space below filters', 'stackfolio' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array(
					'px' => array(
						'min' => 0,
						'max' => 100,
					),
				),
				'selectors'  => array( '{{WRAPPER}} .stackfolio-filters' => 'margin-bottom: {{SIZE}}{{UNIT}};' ),
			)
		);

		$this->start_controls_tabs( 'style_tabs' );

		$states = array(
			'normal' => array( esc_html__( 'Normal', 'stackfolio' ), '{{WRAPPER}} .stackfolio-filter', 'isotope_text_color', 'isotope_background_color' ),
			'hover'  => array( esc_html__( 'Hover', 'stackfolio' ), '{{WRAPPER}} .stackfolio-filter:hover', 'isotope_hover_text_color', 'isotope_hover_background_color' ),
			'active' => array( esc_html__( 'Active', 'stackfolio' ), '{{WRAPPER}} .stackfolio-filter.is-active', 'isotope_active_text_color', 'isotope_active_background_color' ),
		);

		foreach ( $states as $state => $config ) {
			list( $label, $selector, $color_id, $bg_id ) = $config;
			$this->start_controls_tab( 'style_' . $state . '_tab', array( 'label' => $label ) );

			$this->add_control(
				$color_id,
				array(
					'label'     => esc_html__( 'Text color', 'stackfolio' ),
					'type'      => Controls_Manager::COLOR,
					'selectors' => array( $selector => 'color: {{VALUE}};' ),
				)
			);

			$this->add_control(
				$bg_id,
				array(
					'label'     => esc_html__( 'Background color', 'stackfolio' ),
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

		echo StackFolio_Renderer::render( $args ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped in the renderer.
	}
}
