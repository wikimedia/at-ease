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

class FunctionsTest extends PHPUnit_Framework_TestCase {

	/**
	 * Ensure that operations that would normally trigger warnings are passed
	 * over in silence when enclosed in warning suppress / restore calls.
	 */
	public function testWarningSuppression() {
		$a = [];
		MediaWiki\suppressWarnings();
		$a['unsetkey'];
		MediaWiki\restoreWarnings();
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
	 * Ensure that MediaWiki\quietCall calls the callback function with the
	 * correct parameters, that it returns the callback's return value, and
	 * that warnings (if any) are suppressed.
	 */
	public function testQuietCall() {
		$double = function ( $num ) {
			return $num * 2;
		};

		$this->assertEquals(
			MediaWiki\quietCall( 'filemtime', __FILE__ ),
			filemtime( __FILE__ ),
			'MediaWiki\quietCall() with built-in function'
		);

		$this->assertEquals(
			MediaWiki\quietCall( 'FunctionsTest::dummyStaticMethod', 24 ),
			self::dummyStaticMethod( 24 ),
			'MediaWiki\quietCall() with static method'
		);

		$this->assertEquals(
			MediaWiki\quietCall( [ $this, 'dummyInstanceMethod' ], 24 ),
			$this->dummyInstanceMethod( 24 ),
			'MediaWiki\quietCall() with instance method'
		);

		$this->assertEquals(
			MediaWiki\quietCall( $double, 24 ),
			$double( 24 ),
			'MediaWiki\quietCall() with closure'
		);

		$this->assertFalse(
			MediaWiki\quietCall( 'filemtime', '/this/file/does/not/exist' )
		);
	}
}
