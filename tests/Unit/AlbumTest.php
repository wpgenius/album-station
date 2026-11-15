<?php
/**
 * Tests for helpers in includes/class-stackfolio-album.php.
 *
 * @package StackFolio
 */

namespace StackFolio\Tests\Unit;

use Brain\Monkey\Functions;
use StackFolio\Tests\TestCase;
use StackFolio_Album;

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
		$this->assertSame( array( 3, 1, 7 ), StackFolio_Album::parse_ids( '3, 1,7' ) );
		$this->assertSame( array( 4, 2 ), StackFolio_Album::parse_ids( array( '4', 2, '4' ) ) );
	}

	public function test_parse_ids_drops_invalid_values() {
		$this->assertSame( array( 5 ), StackFolio_Album::parse_ids( '0,abc,5,' ) );
		$this->assertSame( array(), StackFolio_Album::parse_ids( null ) );
	}
}
