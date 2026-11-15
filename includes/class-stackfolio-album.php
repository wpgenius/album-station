<?php
/**
 * Album data access.
 *
 * @package StackFolio
 */

defined( 'ABSPATH' ) || exit;

/**
 * Reads album meta and builds the lightGallery item list for an album.
 */
class StackFolio_Album {

	const META_MEDIA           = '_stackfolio_media';
	const META_VIDEOS          = '_stackfolio_video_links';
	const META_VIDEOS_POSITION = '_stackfolio_videos_position';

	/**
	 * Attachment IDs (images and videos) in album order.
	 *
	 * @param int $post_id Album ID.
	 * @return int[]
	 */
	public static function get_media_ids( $post_id ) {
		return self::parse_ids( get_post_meta( $post_id, self::META_MEDIA, true ) );
	}

	/**
	 * Video links, one per line.
	 *
	 * @param int $post_id Album ID.
	 * @return string[]
	 */
	public static function get_video_links( $post_id ) {
		$raw = (string) get_post_meta( $post_id, self::META_VIDEOS, true );
		return array_values( array_filter( array_map( 'trim', preg_split( '/[\r\n]+/', $raw ) ) ) );
	}

	/**
	 * Whether linked videos are shown before or after the media files.
	 *
	 * @param int $post_id Album ID.
	 * @return string before|after
	 */
	public static function get_videos_position( $post_id ) {
		return 'before' === get_post_meta( $post_id, self::META_VIDEOS_POSITION, true ) ? 'before' : 'after';
	}

	/**
	 * Turn "1,2,3" or an array into unique positive ints.
	 *
	 * @param mixed $value Raw value.
	 * @return int[]
	 */
	public static function parse_ids( $value ) {
		if ( is_string( $value ) ) {
			$value = explode( ',', $value );
		}
		if ( ! is_array( $value ) ) {
			return array();
		}
		return array_values( array_unique( array_filter( array_map( 'absint', $value ) ) ) );
	}

	/**
	 * Lightbox items for an album.
	 *
	 * @param int   $post_id Album ID.
	 * @param array $args    { videos: bool, captions: bool }.
	 * @return array[]
	 */
	public static function get_items( $post_id, array $args = array() ) {
		$args = wp_parse_args(
			$args,
			array(
				'videos'   => true,
				'captions' => true,
			)
		);

		$media = array();
		foreach ( self::get_media_ids( $post_id ) as $attachment_id ) {
			$item = self::attachment_item( $attachment_id, $args );
			if ( $item ) {
				$media[] = $item;
			}
		}

		$linked = $args['videos'] ? StackFolio_Videos::to_items( self::get_video_links( $post_id ) ) : array();

		$items = 'before' === self::get_videos_position( $post_id )
			? array_merge( $linked, $media )
			: array_merge( $media, $linked );

		/**
		 * Filters the lightbox items of an album.
		 *
		 * @param array[] $items   lightGallery dynamic items.
		 * @param int     $post_id Album ID.
		 * @param array   $args    Render arguments.
		 */
		return apply_filters( 'stackfolio_album_items', $items, $post_id, $args );
	}

	/**
	 * Lightbox item for one Media Library attachment.
	 *
	 * @param int   $attachment_id Attachment ID.
	 * @param array $args          Render arguments.
	 * @return array|null
	 */
	private static function attachment_item( $attachment_id, array $args ) {
		$attachment = get_post( $attachment_id );
		if ( ! $attachment || 'attachment' !== $attachment->post_type ) {
			return null;
		}

		$mime    = (string) get_post_mime_type( $attachment );
		$caption = $args['captions'] ? self::caption_html( $attachment ) : '';

		if ( 0 === strpos( $mime, 'image/' ) ) {
			$full = wp_get_attachment_image_src( $attachment_id, 'full' );
			if ( ! $full ) {
				return null;
			}
			$item = array(
				'src'   => $full[0],
				'thumb' => (string) wp_get_attachment_image_url( $attachment_id, 'thumbnail' ),
				'alt'   => (string) get_post_meta( $attachment_id, '_wp_attachment_image_alt', true ),
			);
			if ( $caption ) {
				$item['subHtml'] = $caption;
			}
			return $item;
		}

		if ( 0 === strpos( $mime, 'video/' ) && $args['videos'] ) {
			// Only the video's own image is a poster. Without one the player shows the
			// video's first frame, and the thumbnail strip a neutral video icon, so the
			// video never looks like a copy of the album cover.
			$poster = get_the_post_thumbnail_url( $attachment_id, 'large' );
			$item   = array(
				'video' => array(
					'source'     => array(
						array(
							'src'  => wp_get_attachment_url( $attachment_id ),
							'type' => $mime,
						),
					),
					'attributes' => array(
						'preload'     => 'metadata',
						'controls'    => true,
						'playsinline' => true,
					),
				),
				'thumb' => $poster ? $poster : STACKFOLIO_URL . 'assets/images/video-thumb.svg',
			);
			if ( $poster ) {
				$item['poster'] = $poster;
			}
			if ( $caption ) {
				$item['subHtml'] = $caption;
			}
			return $item;
		}

		return null;
	}

	/**
	 * Caption markup from the attachment title and caption.
	 *
	 * @param WP_Post $attachment Attachment.
	 * @return string
	 */
	private static function caption_html( $attachment ) {
		$title   = trim( get_the_title( $attachment ) );
		$caption = trim( (string) wp_get_attachment_caption( $attachment->ID ) );
		$html    = '';
		if ( '' !== $title ) {
			$html .= '<h4>' . esc_html( $title ) . '</h4>';
		}
		if ( '' !== $caption ) {
			$html .= '<p>' . esc_html( $caption ) . '</p>';
		}
		return $html;
	}
}
