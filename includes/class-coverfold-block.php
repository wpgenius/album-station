<?php
/**
 * Coverfold Albums block.
 *
 * @package Coverfold
 */

defined( 'ABSPATH' ) || exit;

/**
 * Registers the coverfold/album-grid block (server-rendered).
 */
class Coverfold_Block {

	/**
	 * Block attribute name => renderer argument name.
	 *
	 * @var string[]
	 */
	const ATTRIBUTE_MAP = array(
		'categories'           => 'categories',
		'number'               => 'number',
		'orderby'              => 'orderby',
		'order'                => 'order',
		'columns'              => 'columns',
		'columnsTablet'        => 'columns_tablet',
		'columnsMobile'        => 'columns_mobile',
		'gap'                  => 'gap',
		'hover'                => 'hover',
		'showTitle'            => 'show_title',
		'titleTag'             => 'title_tag',
		'titleAlign'           => 'title_align',
		'showCount'            => 'show_count',
		'filters'              => 'filters',
		'filterAll'            => 'filter_all',
		'allLabel'             => 'all_label',
		'filterAlign'          => 'filter_align',
		'videos'               => 'videos',
		'thumbnails'           => 'thumbnails',
		'hideThumbsFullscreen' => 'hide_thumbs_fullscreen',
		'zoom'                 => 'zoom',
		'fullscreen'           => 'fullscreen',
		'slideshow'            => 'slideshow',
		'slideshowAutostart'   => 'slideshow_autostart',
		'captions'             => 'captions',
		'download'             => 'download',
	);

	/**
	 * Hook everything up.
	 */
	public static function init() {
		add_action( 'init', array( __CLASS__, 'register' ) );
	}

	/**
	 * Register the block from block.json.
	 */
	public static function register() {
		$block = register_block_type(
			COVERFOLD_PATH . 'blocks/album-grid',
			array( 'render_callback' => array( __CLASS__, 'render' ) )
		);

		if ( $block && ! empty( $block->editor_script_handles ) ) {
			foreach ( $block->editor_script_handles as $handle ) {
				wp_set_script_translations( $handle, 'coverfold' );
			}
		}
	}

	/**
	 * Render callback.
	 *
	 * @param array $attributes Block attributes.
	 * @return string
	 */
	public static function render( $attributes ) {
		$args = array();
		foreach ( self::ATTRIBUTE_MAP as $attribute => $arg ) {
			if ( isset( $attributes[ $attribute ] ) ) {
				$args[ $arg ] = $attributes[ $attribute ];
			}
		}

		$html = Coverfold_Renderer::render( $args );
		if ( '' === $html ) {
			return '';
		}

		return sprintf( '<div %1$s>%2$s</div>', get_block_wrapper_attributes(), $html );
	}
}
