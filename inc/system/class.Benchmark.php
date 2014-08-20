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
class Benchmark {
	/**
	 * Start time
	 * @var int
	 */
	static private $StartTime = null;

	/**
	 * Start benchmark
	 */
	static public function start() {
		self::$StartTime = microtime(true);
	}

	/**
	 * End benchmark
	 * 
	 * This will produce a debug message with execution time.
	 */
	static public function end() {
		if (!is_null(self::$StartTime)) {
			Error::getInstance()->addDebug('Benchmark time: '.(microtime(true) - self::$StartTime));
		}
	}
}