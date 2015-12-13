<?php
/**
 * This file contains class::Benchmark
 * @package Runalyze\System
 */
/**
 * Benchmark
 * 
 * Usage:
 * <code>Benchmark::start();
 * Benchmark::end();</code>
 * 
 * This will produce a debug message with execution time.
 * @author Hannes Christiansen
 * @package Runalyze\System
 */
use Runalyze\Error;

class Benchmark {
	/**
	 * Start time
	 * @var int
	 */
	private static $StartTime = null;

	/**
	 * Start benchmark
	 */
	public static function start() {
		self::$StartTime = microtime(true);
	}

	/**
	 * End benchmark
	 * 
	 * This will produce a debug message with execution time.
	 */
	public static function end() {
		if (!is_null(self::$StartTime)) {
			Error::getInstance()->addDebug('Benchmark time: '.(microtime(true) - self::$StartTime));
		}
	}
}