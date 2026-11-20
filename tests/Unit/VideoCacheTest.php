<?php
/**
 * Tests for the remote video data cache in includes/class-coverfold-videos.php.
 *
 * @package Coverfold
 */

namespace Coverfold\Tests\Unit;

use Brain\Monkey\Functions;
use Coverfold\Tests\TestCase;
use Coverfold_Videos;

/**
 * Vimeo data cache tests.
 */
class VideoCacheTest extends TestCase {

	/**
	 * Transients set during a test.
	 *
	 * @var array
	 */
	private $saved = array();

	protected function setUp(): void {
		parent::setUp();
		$this->saved = array();
		Functions\when( 'get_option' )->justReturn( 1 );
		Functions\when( 'apply_filters' )->returnArg( 2 );
		Functions\when( 'add_query_arg' )->justReturn( 'https://vimeo.com/api/oembed.json' );
		Functions\when( 'sanitize_text_field' )->returnArg();
		Functions\when( 'esc_url_raw' )->returnArg();
		Functions\when( 'set_transient' )->alias(
			function ( $key, $value ) {
				$this->saved[ $key ] = $value;
				return true;
			}
		);
	}

	private function vimeo_ok() {
		Functions\when( 'is_wp_error' )->justReturn( false );
		Functions\when( 'wp_remote_retrieve_response_code' )->justReturn( 200 );
		Functions\when( 'wp_remote_retrieve_body' )->justReturn( '{"title":"New","thumbnail_url":"https://i.vimeocdn.com/new.jpg"}' );
	}

	private function vimeo_down() {
		Functions\when( 'is_wp_error' )->justReturn( true );
	}

	public function test_fresh_cache_is_used_without_a_request() {
		Functions\when( 'get_transient' )->justReturn(
			array(
				'data'        => array(
					'title' => 'Cached',
					'thumb' => 'c.jpg',
				),
				'fresh_until' => time() + 100,
			)
		);
		Functions\expect( 'wp_remote_get' )->never();
		Functions\expect( 'wp_schedule_single_event' )->never();

		$this->assertSame( 'Cached', Coverfold_Videos::vimeo_data( 'https://vimeo.com/1' )['title'] );
	}

	public function test_expired_cache_is_served_and_refreshed_in_background() {
		Functions\when( 'get_transient' )->justReturn(
			array(
				'data'        => array(
					'title' => 'Old',
					'thumb' => 'o.jpg',
				),
				'fresh_until' => time() - 1,
			)
		);
		Functions\when( 'wp_next_scheduled' )->justReturn( false );
		Functions\expect( 'wp_remote_get' )->never();
		Functions\expect( 'wp_schedule_single_event' )->once();

		$this->assertSame( 'Old', Coverfold_Videos::vimeo_data( 'https://vimeo.com/1' )['title'] );
	}

	public function test_missing_cache_is_fetched_with_a_short_timeout() {
		Functions\when( 'get_transient' )->justReturn( false );
		Functions\expect( 'wp_remote_get' )->once()->andReturnUsing(
			function ( $url, $args ) {
				$this->assertSame( 3, $args['timeout'] );
				return array();
			}
		);
		$this->vimeo_ok();

		$this->assertSame( 'New', Coverfold_Videos::vimeo_data( 'https://vimeo.com/1' )['title'] );
		$entry = reset( $this->saved );
		$this->assertSame( 'New', $entry['data']['title'] );
		$this->assertGreaterThan( time() + 11 * 3600, $entry['fresh_until'] );
	}

	public function test_failed_refresh_keeps_old_data() {
		Functions\when( 'get_transient' )->justReturn(
			array(
				'data'        => array(
					'title' => 'Old',
					'thumb' => 'o.jpg',
				),
				'fresh_until' => time() - 1,
			)
		);
		Functions\when( 'wp_remote_get' )->justReturn( array() );
		$this->vimeo_down();

		$this->assertSame( 'Old', Coverfold_Videos::vimeo_data( 'https://vimeo.com/1', true )['title'] );
		$entry = reset( $this->saved );
		$this->assertSame( 'Old', $entry['data']['title'] );
		$this->assertLessThanOrEqual( time() + 3600, $entry['fresh_until'] );
	}

	public function test_vimeo_thumbnail_falls_back_to_video_icon() {
		Functions\when( 'wp_parse_url' )->alias( 'parse_url' );
		Functions\when( 'get_transient' )->justReturn( false );
		Functions\when( 'wp_remote_get' )->justReturn( array() );
		$this->vimeo_down();

		$items = Coverfold_Videos::to_items( array( 'https://vimeo.com/76979871' ) );

		$this->assertSame( COVERFOLD_URL . 'assets/images/video-thumb.svg', $items[0]['thumb'] );
	}
}
