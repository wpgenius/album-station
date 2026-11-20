<?php
/**
 * [coverfold] shortcode (and the legacy [feature_album]).
 *
 * @package Coverfold
 */

defined( 'ABSPATH' ) || exit;

/**
 * Shortcode wrapper around the renderer.
 */
class Coverfold_Shortcode {

	/**
	 * Hook everything up.
	 */
	public static function init() {
		add_shortcode( 'coverfold', array( __CLASS__, 'render' ) );
		add_action( 'init', array( __CLASS__, 'register_legacy' ), 20 );
	}

	/**
	 * Keep [feature_album] from Album Station working, unless another plugin owns it.
	 */
	public static function register_legacy() {
		if ( ! shortcode_exists( 'feature_album' ) ) {
			add_shortcode( 'feature_album', array( __CLASS__, 'render_legacy' ) );
		}
	}

	/**
	 * Render [coverfold].
	 *
	 * Attributes: category, ids, number, orderby, order, columns, columns_tablet,
	 * columns_mobile, gap, title, title_tag, count, filters, all, all_label,
	 * videos, thumbnails, zoom, fullscreen, slideshow, autoplay, captions,
	 * download, hover, class.
	 *
	 * @param array|string $atts Shortcode attributes.
	 * @return string
	 */
	public static function render( $atts ) {
		$defaults = Coverfold_Renderer::defaults();
		$atts     = shortcode_atts(
			array(
				'category'       => '',
				'ids'            => '',
				'number'         => $defaults['number'],
				'orderby'        => $defaults['orderby'],
				'order'          => $defaults['order'],
				'columns'        => $defaults['columns'],
				'columns_tablet' => $defaults['columns_tablet'],
				'columns_mobile' => $defaults['columns_mobile'],
				'gap'            => $defaults['gap'],
				'title'          => 'yes',
				'title_tag'      => $defaults['title_tag'],
				'title_align'    => $defaults['title_align'],
				'count'          => 'no',
				'filters'        => 'no',
				'all'            => 'yes',
				'all_label'      => '',
				'filter_align'   => $defaults['filter_align'],
				'videos'         => 'yes',
				'thumbnails'     => 'yes',
				'zoom'           => 'yes',
				'fullscreen'     => 'yes',
				'slideshow'      => 'yes',
				'autoplay'       => 'no',
				'captions'       => 'yes',
				'download'       => 'no',
				'hover'          => $defaults['hover'],
				'class'          => '',
			),
			$atts,
			'coverfold'
		);

		return Coverfold_Renderer::render(
			array(
				'categories'          => $atts['category'],
				'include'             => $atts['ids'],
				'number'              => $atts['number'],
				'orderby'             => $atts['orderby'],
				'order'               => $atts['order'],
				'columns'             => $atts['columns'],
				'columns_tablet'      => $atts['columns_tablet'],
				'columns_mobile'      => $atts['columns_mobile'],
				'gap'                 => $atts['gap'],
				'show_title'          => $atts['title'],
				'title_tag'           => $atts['title_tag'],
				'title_align'         => $atts['title_align'],
				'show_count'          => $atts['count'],
				'filters'             => $atts['filters'],
				'filter_all'          => $atts['all'],
				'all_label'           => $atts['all_label'],
				'filter_align'        => $atts['filter_align'],
				'videos'              => $atts['videos'],
				'thumbnails'          => $atts['thumbnails'],
				'zoom'                => $atts['zoom'],
				'fullscreen'          => $atts['fullscreen'],
				'slideshow'           => $atts['slideshow'],
				'slideshow_autostart' => $atts['autoplay'],
				'captions'            => $atts['captions'],
				'download'            => $atts['download'],
				'hover'               => $atts['hover'],
				'class'               => $atts['class'],
			)
		);
	}

	/**
	 * Legacy [feature_album]: three columns, no titles or filters, like Album Station.
	 *
	 * @return string
	 */
	public static function render_legacy() {
		return Coverfold_Renderer::render(
			array(
				'columns'    => 3,
				'show_title' => false,
				'filters'    => false,
			)
		);
	}
}
