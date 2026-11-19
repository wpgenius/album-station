<?php
/**
 * Shared album grid renderer (block, Elementor widget and shortcode).
 *
 * @package Coverfold
 */

defined( 'ABSPATH' ) || exit;

/**
 * Renders the album grid markup.
 */
class Coverfold_Renderer {

	/**
	 * Counter for unique grid IDs.
	 *
	 * @var int
	 */
	private static $instance = 0;

	/**
	 * Default render arguments.
	 *
	 * @return array
	 */
	public static function defaults() {
		return array(
			'categories'             => array(),
			'include'                => array(),
			'number'                 => -1,
			'orderby'                => 'date',
			'order'                  => 'DESC',
			'columns'                => 3,
			'columns_tablet'         => 2,
			'columns_mobile'         => 1,
			'gap'                    => 20,
			'image_size'             => Coverfold_Post_Type::IMAGE_SIZE,
			'hover'                  => 'zoom',
			'show_title'             => true,
			'title_tag'              => 'h3',
			'title_align'            => 'center',
			'show_count'             => false,
			'filters'                => false,
			'filter_all'             => true,
			'all_label'              => '',
			'filter_align'           => 'center',
			'videos'                 => true,
			'thumbnails'             => true,
			'hide_thumbs_fullscreen' => false,
			'zoom'                   => true,
			'fullscreen'             => true,
			'slideshow'              => true,
			'slideshow_autostart'    => false,
			'captions'               => true,
			'download'               => false,
			'class'                  => '',
		);
	}

	/**
	 * Interpret yes/no style values.
	 *
	 * @param mixed $value Value.
	 * @return bool
	 */
	public static function to_bool( $value ) {
		if ( is_bool( $value ) ) {
			return $value;
		}
		return in_array( strtolower( trim( (string) $value ) ), array( '1', 'yes', 'true', 'on' ), true );
	}

	/**
	 * Normalize and validate arguments.
	 *
	 * @param array $args Raw arguments.
	 * @return array
	 */
	public static function normalize( array $args ) {
		$args = wp_parse_args( $args, self::defaults() );

		foreach ( array( 'show_title', 'show_count', 'filters', 'filter_all', 'videos', 'thumbnails', 'hide_thumbs_fullscreen', 'zoom', 'fullscreen', 'slideshow', 'slideshow_autostart', 'captions', 'download' ) as $key ) {
			$args[ $key ] = self::to_bool( $args[ $key ] );
		}

		foreach ( array( 'categories', 'include' ) as $key ) {
			if ( is_string( $args[ $key ] ) ) {
				$args[ $key ] = '' === trim( $args[ $key ] ) ? array() : array_map( 'trim', explode( ',', $args[ $key ] ) );
			}
			$args[ $key ] = array_values(
				array_filter(
					(array) $args[ $key ],
					static function ( $value ) {
						return '' !== (string) $value;
					}
				)
			);
		}
		$args['include'] = array_values( array_filter( array_map( 'absint', $args['include'] ) ) );

		$args['number']         = (int) $args['number'] > 0 ? (int) $args['number'] : -1;
		$args['columns']        = max( 1, min( 6, (int) $args['columns'] ) );
		$args['columns_tablet'] = max( 1, min( 6, (int) $args['columns_tablet'] ) );
		$args['columns_mobile'] = max( 1, min( 6, (int) $args['columns_mobile'] ) );
		$args['gap']            = max( 0, min( 100, (int) $args['gap'] ) );

		$args['orderby'] = in_array( $args['orderby'], array( 'date', 'title', 'menu_order', 'rand', 'modified' ), true ) ? $args['orderby'] : 'date';
		$args['order']   = 'ASC' === strtoupper( (string) $args['order'] ) ? 'ASC' : 'DESC';

		$args['title_tag']    = in_array( $args['title_tag'], array( 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'p', 'div', 'span' ), true ) ? $args['title_tag'] : 'h3';
		$args['title_align']  = in_array( $args['title_align'], array( 'left', 'center', 'right' ), true ) ? $args['title_align'] : 'center';
		$args['filter_align'] = in_array( $args['filter_align'], array( 'left', 'center', 'right' ), true ) ? $args['filter_align'] : 'center';
		$args['hover']        = in_array( $args['hover'], array( 'zoom', 'none' ), true ) ? $args['hover'] : 'zoom';
		$args['image_size']   = sanitize_key( $args['image_size'] ) ? (string) $args['image_size'] : Coverfold_Post_Type::IMAGE_SIZE;
		$args['all_label']    = '' !== trim( (string) $args['all_label'] ) ? (string) $args['all_label'] : __( 'All', 'coverfold' );

		return $args;
	}

	/**
	 * Query the albums to show.
	 *
	 * @param array $args Normalized arguments.
	 * @return WP_Post[]
	 */
	public static function query( array $args ) {
		$query_args = array(
			'post_type'        => Coverfold_Post_Type::POST_TYPE,
			'post_status'      => 'publish',
			'posts_per_page'   => $args['number'],
			'orderby'          => $args['orderby'],
			'order'            => $args['order'],
			'no_found_rows'    => true,
			'suppress_filters' => false,
		);

		if ( $args['include'] ) {
			$query_args['post__in'] = $args['include'];
			if ( 'date' === $args['orderby'] && empty( $args['categories'] ) ) {
				$query_args['orderby'] = 'post__in';
			}
		}

		if ( $args['categories'] ) {
			$numeric                 = array_filter( $args['categories'], 'is_numeric' );
			$query_args['tax_query'] = array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
				array(
					'taxonomy' => Coverfold_Post_Type::TAXONOMY,
					'field'    => count( $numeric ) === count( $args['categories'] ) ? 'term_id' : 'slug',
					'terms'    => count( $numeric ) === count( $args['categories'] ) ? array_map( 'absint', $args['categories'] ) : array_map( 'sanitize_title', $args['categories'] ),
				),
			);
		}

		/**
		 * Filters the album query arguments.
		 *
		 * @param array $query_args WP_Query arguments.
		 * @param array $args       Render arguments.
		 */
		$query = new WP_Query( apply_filters( 'coverfold_query_args', $query_args, $args ) );
		return $query->posts;
	}

	/**
	 * Render the grid.
	 *
	 * @param array $args Render arguments (see defaults()).
	 * @return string HTML.
	 */
	public static function render( array $args = array() ) {
		$args   = self::normalize( $args );
		$albums = self::query( $args );

		if ( ! $albums ) {
			if ( current_user_can( 'edit_posts' ) ) {
				return '<p class="coverfold-empty">' . esc_html__( 'No albums found. Add albums under Coverfold in the dashboard.', 'coverfold' ) . '</p>';
			}
			return '';
		}

		Coverfold_Assets::enqueue();
		++self::$instance;

		$cards     = array();
		$terms_map = array();
		foreach ( $albums as $album ) {
			$terms = get_the_terms( $album, Coverfold_Post_Type::TAXONOMY );
			$terms = ( $terms && ! is_wp_error( $terms ) ) ? $terms : array();
			foreach ( $terms as $term ) {
				$terms_map[ $term->term_id ] = $term;
			}
			$cards[] = self::render_card( $album, $terms, $args );
		}

		$config = array(
			'plugins'              => self::lightbox_plugins( $args ),
			'slideshowAutostart'   => $args['slideshow'] && $args['slideshow_autostart'],
			'download'             => $args['download'],
			'hideThumbsFullscreen' => $args['thumbnails'] && $args['hide_thumbs_fullscreen'],
		);

		$classes = array(
			'coverfold',
			'coverfold--hover-' . $args['hover'],
			$args['filters'] ? 'coverfold--filterable' : '',
			implode( ' ', array_map( 'sanitize_html_class', preg_split( '/\s+/', (string) $args['class'] ) ) ),
		);

		$style = sprintf(
			'--coverfold-columns:%d;--coverfold-columns-tablet:%d;--coverfold-columns-mobile:%d;--coverfold-gap:%dpx;',
			$args['columns'],
			$args['columns_tablet'],
			$args['columns_mobile'],
			$args['gap']
		);

		$html  = sprintf(
			'<div id="coverfold-%1$d" class="%2$s" style="%3$s" data-coverfold="%4$s">',
			self::$instance,
			esc_attr( implode( ' ', array_filter( $classes ) ) ),
			esc_attr( $style ),
			esc_attr( wp_json_encode( $config ) )
		);
		$html .= $args['filters'] ? self::render_filters( $terms_map, $args ) : '';
		$html .= '<div class="coverfold-grid">' . implode( '', $cards ) . '</div>';
		$html .= '</div>';

		/**
		 * Filters the grid HTML.
		 *
		 * @param string    $html   Markup.
		 * @param array     $args   Render arguments.
		 * @param WP_Post[] $albums Albums shown.
		 */
		return apply_filters( 'coverfold_grid_html', $html, $args, $albums );
	}

	/**
	 * Lightbox plugin names for the front-end script.
	 *
	 * @param array $args Normalized arguments.
	 * @return string[]
	 */
	private static function lightbox_plugins( array $args ) {
		$plugins = array();
		foreach ( array(
			'videos'     => 'video',
			'thumbnails' => 'thumbnail',
			'zoom'       => 'zoom',
			'fullscreen' => 'fullscreen',
			'slideshow'  => 'autoplay',
		) as $arg => $plugin ) {
			if ( $args[ $arg ] ) {
				$plugins[] = $plugin;
			}
		}
		return $plugins;
	}

	/**
	 * Category filter buttons, limited to categories that have albums in this grid.
	 *
	 * @param WP_Term[] $terms Terms keyed by ID.
	 * @param array     $args  Normalized arguments.
	 * @return string
	 */
	private static function render_filters( array $terms, array $args ) {
		if ( ! $terms ) {
			return '';
		}
		uasort(
			$terms,
			static function ( $a, $b ) {
				return strcasecmp( $a->name, $b->name );
			}
		);

		$buttons = array();
		if ( $args['filter_all'] ) {
			$buttons[] = sprintf(
				'<button type="button" class="coverfold-filter is-active" data-filter="*" aria-pressed="true">%s</button>',
				esc_html( $args['all_label'] )
			);
		}
		$first = ! $args['filter_all'];
		foreach ( $terms as $term ) {
			$buttons[] = sprintf(
				'<button type="button" class="coverfold-filter%1$s" data-filter=".coverfold-cat-%2$s" aria-pressed="%3$s">%4$s</button>',
				$first ? ' is-active' : '',
				esc_attr( (string) $term->term_id ),
				$first ? 'true' : 'false',
				esc_html( $term->name )
			);
			$first     = false;
		}

		return sprintf(
			'<div class="coverfold-filters coverfold-filters--%1$s" role="group" aria-label="%2$s">%3$s</div>',
			esc_attr( $args['filter_align'] ),
			esc_attr__( 'Filter albums', 'coverfold' ),
			implode( '', $buttons )
		);
	}

	/**
	 * One album card.
	 *
	 * @param WP_Post   $album Album.
	 * @param WP_Term[] $terms Album categories.
	 * @param array     $args  Normalized arguments.
	 * @return string
	 */
	private static function render_card( WP_Post $album, array $terms, array $args ) {
		$items = Coverfold_Album::get_items(
			$album->ID,
			array(
				'videos'   => $args['videos'],
				'captions' => $args['captions'],
			)
		);
		$title = get_the_title( $album );

		$classes = array( 'coverfold-item' );
		foreach ( $terms as $term ) {
			$classes[] = 'coverfold-cat-' . $term->term_id;
		}

		$cover = self::cover_html( $album, $args['image_size'] );

		if ( $args['show_count'] && $items ) {
			$cover .= '<span class="coverfold-count">' . esc_html( self::count_label( $items ) ) . '</span>';
		}

		if ( $items ) {
			$card = sprintf(
				'<button type="button" class="coverfold-card" data-items="%1$s" aria-label="%2$s">%3$s</button>',
				esc_attr( wp_json_encode( $items ) ),
				/* translators: %s: album title. */
				esc_attr( sprintf( __( 'Open album: %s', 'coverfold' ), wp_strip_all_tags( $title ) ) ),
				$cover
			);
		} else {
			$card = '<div class="coverfold-card is-empty">' . $cover . '</div>';
		}

		$heading = '';
		if ( $args['show_title'] && '' !== $title ) {
			$heading = sprintf(
				'<%1$s class="coverfold-title coverfold-title--%2$s">%3$s</%1$s>',
				tag_escape( $args['title_tag'] ),
				esc_attr( $args['title_align'] ),
				esc_html( wp_strip_all_tags( $title ) )
			);
		}

		return sprintf(
			'<div class="%1$s"><div class="coverfold-item__inner">%2$s%3$s</div></div>',
			esc_attr( implode( ' ', $classes ) ),
			$card,
			$heading
		);
	}

	/**
	 * Cover image: featured image, else the first image in the album.
	 *
	 * @param WP_Post $album Album.
	 * @param string  $size  Image size.
	 * @return string
	 */
	private static function cover_html( WP_Post $album, $size ) {
		$cover_id = (int) get_post_thumbnail_id( $album );
		if ( ! $cover_id ) {
			foreach ( Coverfold_Album::get_media_ids( $album->ID ) as $id ) {
				if ( wp_attachment_is_image( $id ) ) {
					$cover_id = $id;
					break;
				}
			}
		}

		if ( $cover_id ) {
			$img = wp_get_attachment_image(
				$cover_id,
				$size,
				false,
				array(
					'class'   => 'coverfold-cover__img',
					'loading' => 'lazy',
					'alt'     => wp_strip_all_tags( get_the_title( $album ) ),
				)
			);
			return '<span class="coverfold-cover">' . $img . '</span>';
		}

		return '<span class="coverfold-cover coverfold-cover--placeholder" aria-hidden="true"></span>';
	}

	/**
	 * "12 photos · 2 videos" label.
	 *
	 * @param array[] $items Lightbox items.
	 * @return string
	 */
	private static function count_label( array $items ) {
		$photos = 0;
		$videos = 0;
		foreach ( $items as $item ) {
			if ( isset( $item['alt'] ) ) {
				++$photos;
			} else {
				++$videos;
			}
		}
		$parts = array();
		if ( $photos ) {
			/* translators: %d: number of photos. */
			$parts[] = sprintf( _n( '%d photo', '%d photos', $photos, 'coverfold' ), $photos );
		}
		if ( $videos ) {
			/* translators: %d: number of videos. */
			$parts[] = sprintf( _n( '%d video', '%d videos', $videos, 'coverfold' ), $videos );
		}
		return implode( ' · ', $parts );
	}
}
