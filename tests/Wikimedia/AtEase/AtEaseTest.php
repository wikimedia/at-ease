<?php
/**
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.
 * http://www.gnu.org/copyleft/gpl.html
 *
 * @file
 */

namespace Wikimedia\AtEase;

use PHPUnit\Framework\TestCase;
use RuntimeException;

class AtEaseTest extends TestCase {

	/** @var int|null */
	private $originalPhpErrorFilter;

	/**
	 * @before
	 */
	protected function phpErrorFilterSetUp() {
		$this->originalPhpErrorFilter = intval( ini_get( 'error_reporting' ) );
	}

	/**
	 * @after
	 */
	protected function phpErrorFilterTearDown() {
		$phpErrorFilter = intval( ini_get( 'error_reporting' ) );

		if ( $phpErrorFilter !== $this->originalPhpErrorFilter ) {
			ini_set( 'error_reporting', $this->originalPhpErrorFilter );
			$this->fail( "PHP error_reporting setting found dirty."
				. " Did you forget AtEase::restoreWarnings?" );
		}
	}

	/**
	 * Ensure that operations that would normally trigger warnings are passed
	 * over in silence when enclosed in warning suppress / restore calls.
	 */
	public function testWarningSuppression() {
		$a = [];
		AtEase::suppressWarnings();
		$a['unsetkey'];
		AtEase::restoreWarnings();
		// No warnings generated
		$this->assertTrue( true );
	}

	public static function dummyStaticMethod( $x ) {
		return $x * 2;
	}

	public function dummyInstanceMethod( $x ) {
		return $x * 2;
	}

	/**
	 * Ensure that AtEase::quietCall calls the callback function with the
	 * correct parameters, that it returns the callback's return value, and
	 * that the warnings (if any) are suppressed.
	 */
	public function testQuietCall() {
		$double = static function ( $num ) {
			return $num * 2;
		};

		$this->assertEquals(
			AtEase::quietCall( 'filemtime', __FILE__ ),
			filemtime( __FILE__ ),
			'with built-in function'
		);

		$this->assertEquals(
			AtEase::quietCall( __CLASS__ . '::dummyStaticMethod', 24 ),
			self::dummyStaticMethod( 24 ),
			'with static method'
		);

		$this->assertEquals(
			AtEase::quietCall( [ $this, 'dummyInstanceMethod' ], 24 ),
			$this->dummyInstanceMethod( 24 ),
			'with instance method'
		);

		$this->assertEquals(
			AtEase::quietCall( $double, 24 ),
			$double( 24 ),
			'with closure'
		);

		$this->assertFalse(
			AtEase::quietCall( 'filemtime', '/this/file/does/not/exist' )
		);
	}

	public function testQuietCallException() {
		$exception = static function () {
			throw new RuntimeException();
		};
		$this->expectException( RuntimeException::class );
		AtEase::quietCall( $exception );
	}
}
