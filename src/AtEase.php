<?php
/**
 * @license GPL-2.0-or-later
 * @file
 */

namespace Wikimedia\AtEase;

class AtEase {
	private static int $suppressCount = 0;

	/** @var false|int */
	private static $originalLevel = false;

	/**
	 * Reference-counted warning suppression
	 *
	 * @param bool $end Whether to restore warnings
	 */
	public static function suppressWarnings( $end = false ) {
		if ( $end ) {
			if ( self::$suppressCount ) {
				--self::$suppressCount;
				if ( !self::$suppressCount ) {
					error_reporting( self::$originalLevel );
				}
			}
		} else {
			if ( !self::$suppressCount ) {
				// T375707 - E_STRICT is deprecated on PHP >= 8.4
				if ( PHP_VERSION_ID < 80400 ) {
					self::$originalLevel = error_reporting( E_ALL & ~(
						E_WARNING |
						E_NOTICE |
						E_USER_WARNING |
						E_USER_NOTICE |
						E_DEPRECATED |
						E_USER_DEPRECATED |
						E_STRICT
					) );
				} else {
					self::$originalLevel = error_reporting( E_ALL & ~(
						E_WARNING |
						E_NOTICE |
						E_USER_WARNING |
						E_USER_NOTICE |
						E_DEPRECATED |
						E_USER_DEPRECATED
					) );
				}
			}
			++self::$suppressCount;
		}
	}

	/**
	 * Restore error level to previous value
	 */
	public static function restoreWarnings() {
		self::suppressWarnings( true );
	}

	/**
	 * Call the callback given by the first parameter, suppressing any warnings.
	 *
	 * @param callable $callback Function to call
	 * @param mixed ...$args Optional arguments for the function call
	 * @return mixed
	 */
	public static function quietCall( callable $callback, ...$args ) {
		self::suppressWarnings();
		$rv = null;
		try {
			$rv = $callback( ...$args );
		} finally {
			self::restoreWarnings();
		}
		return $rv;
	}

}
