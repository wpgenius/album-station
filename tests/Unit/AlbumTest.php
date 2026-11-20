<?php
/**
 * Tests for helpers in includes/class-coverfold-album.php.
 *
 * @package Coverfold
 */

namespace Coverfold\Tests\Unit;

use Brain\Monkey\Functions;
use Coverfold\Tests\TestCase;
use Coverfold_Album;

/**
 * Album helper tests.
 */
class AlbumTest extends TestCase {

	protected function setUp(): void {
		parent::setUp();
		Functions\when( 'absint' )->alias(
			static function ( $value ) {
				return abs( (int) $value );
			}
		);
	}

	public function test_parse_ids_accepts_strings_and_arrays() {
		$this->assertSame( array( 3, 1, 7 ), Coverfold_Album::parse_ids( '3, 1,7' ) );
		$this->assertSame( array( 4, 2 ), Coverfold_Album::parse_ids( array( '4', 2, '4' ) ) );
	}

	public function test_parse_ids_drops_invalid_values() {
		$this->assertSame( array( 5 ), Coverfold_Album::parse_ids( '0,abc,5,' ) );
		$this->assertSame( array(), Coverfold_Album::parse_ids( null ) );
	}
}
