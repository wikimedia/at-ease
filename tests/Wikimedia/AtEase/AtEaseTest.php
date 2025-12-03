<?php
/**
 * @license GPL-2.0-or-later
 * @file
 */

namespace Wikimedia\AtEase;

use PHPUnit\Framework\TestCase;
use RuntimeException;

/**
 * @covers \Wikimedia\AtEase\AtEase
 */
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
