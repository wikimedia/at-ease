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
		$a = array();
		MediaWiki\suppressWarnings();
		$a['unsetkey'];
		MediaWiki\restoreWarnings();
		// No warnings generated
		$this->assertTrue( true );
	}

	/**
	 * Ensure that MediaWiki\quietCall calls the callback function with the
	 * correct parameters, that it returns the callback's return value, and
	 * that warnings (if any) are suppressed.
	 */
	public function testQuietCall() {
		$this->assertEquals(
			MediaWiki\quietCall( 'filemtime', __FILE__ ),
			filemtime( __FILE__ )
		);

		$this->assertFalse(
			MediaWiki\quietCall( 'filemtime', '/this/file/does/not/exist' )
		);
	}
}
