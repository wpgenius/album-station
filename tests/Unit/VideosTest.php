<?php
/**
 * Tests for video link parsing in includes/class-coverfold-videos.php.
 *
 * @package Coverfold
 */

namespace Coverfold\Tests\Unit;

use Brain\Monkey\Functions;
use Coverfold\Tests\TestCase;
use Coverfold_Videos;

/**
 * Video link parsing tests.
 */
class VideosTest extends TestCase {

	protected function setUp(): void {
		parent::setUp();
		Functions\when( 'wp_parse_url' )->alias( 'parse_url' );
		Functions\when( 'sanitize_key' )->alias(
			static function ( $key ) {
				return preg_replace( '/[^a-z0-9_\-]/', '', strtolower( $key ) );
			}
		);
	}

	/**
	 * Links with the type and ID they should parse to.
	 *
	 * @return array<string, array{0: string, 1: string|null, 2: string|null}>
	 */
	public function links() {
		return array(
			'bare YouTube ID'       => array( 'dQw4w9WgXcQ', 'youtube', 'dQw4w9WgXcQ' ),
			'watch URL with time'   => array( 'https://www.youtube.com/watch?v=dQw4w9WgXcQ&t=10', 'youtube', 'dQw4w9WgXcQ' ),
			'youtu.be'              => array( 'https://youtu.be/dQw4w9WgXcQ', 'youtube', 'dQw4w9WgXcQ' ),
			'Shorts without scheme' => array( 'youtube.com/shorts/dQw4w9WgXcQ', 'youtube', 'dQw4w9WgXcQ' ),
			'video inside playlist' => array( 'https://m.youtube.com/watch?v=dQw4w9WgXcQ&list=PLx', 'youtube', 'dQw4w9WgXcQ' ),
			'embed URL'             => array( 'https://www.youtube.com/embed/dQw4w9WgXcQ', 'youtube', 'dQw4w9WgXcQ' ),
			'playlist URL'          => array( 'https://www.youtube.com/playlist?list=PLFgquLnL59alCl_2TQvOiD5Vgm1hCaGSI', 'youtube_playlist', 'PLFgquLnL59alCl_2TQvOiD5Vgm1hCaGSI' ),
			'bare playlist ID'      => array( 'PLFgquLnL59alCl_2TQvOiD5Vgm1hCaGSI', 'youtube_playlist', 'PLFgquLnL59alCl_2TQvOiD5Vgm1hCaGSI' ),
			'Vimeo'                 => array( 'https://vimeo.com/76979871', 'vimeo', '76979871' ),
			'private Vimeo'         => array( 'https://vimeo.com/123456789/abcdef1234', 'vimeo', '123456789' ),
			'Vimeo player URL'      => array( 'https://player.vimeo.com/video/76979871?h=abc123', 'vimeo', '76979871' ),
			'Vimeo channel'         => array( 'https://vimeo.com/channels/staffpicks/76979871', 'vimeo', '76979871' ),
			'other site'            => array( 'https://example.com/video.mp4', null, null ),
			'empty'                 => array( '', null, null ),
			'not a link'            => array( 'not a link', null, null ),
			'array v parameter'     => array( 'https://www.youtube.com/watch?v[]=dQw4w9WgXcQ', null, null ),
			'array list parameter'  => array( 'https://www.youtube.com/playlist?list[]=PLx', null, null ),
			'array Vimeo hash'      => array( 'https://player.vimeo.com/video/76979871?h[]=abc', 'vimeo', '76979871' ),
		);
	}

	/**
	 * Each link parses to the expected type and ID, or to null.
	 *
	 * @dataProvider links
	 *
	 * @param string      $link Input.
	 * @param string|null $type Expected type.
	 * @param string|null $id   Expected ID.
	 */
	public function test_parse( $link, $type, $id ) {
		$video = Coverfold_Videos::parse( $link );

		if ( null === $type ) {
			$this->assertNull( $video );
			return;
		}

		$this->assertSame( $type, $video['type'] );
		$this->assertSame( $id, $video['id'] );
	}

	public function test_private_vimeo_hash_is_kept_in_url() {
		$video = Coverfold_Videos::parse( 'https://player.vimeo.com/video/76979871?h=abc123' );

		$this->assertSame( 'https://vimeo.com/76979871/abc123', $video['url'] );
	}
}
