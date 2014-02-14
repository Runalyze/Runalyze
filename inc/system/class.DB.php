<?php
/**
 * This file contains class::DB
 * @package Runalyze\System
 */
/**
 * Database
 * 
 * Database class using PDO
 * 
 * @author Hannes Christiansen
 * @package Runalyze\System
 */
class DB {
	/**
	 * Private PDO instance
	 * @var PDO
	 */
	private static $PDO; 

	/**
	 * Private constructor
	 */
	private function __construct() {}

	/**
	 * Private clone
	 */
	private function __clone() {}

	/**
	 * Create connection
	 * @param $host string
	 * @param $user string
	 * @param $password string
	 * @param $database string
	 */
	public static function connect($host, $user, $password, $database) {
		self::$PDO = new PDOforRunalyze('mysql:dbname='.$database.';host='.$host, $user, $password);
		self::$PDO->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		self::$PDO->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
		self::$PDO->query("SET NAMES 'utf8'");
	}

	/**
	 * Returns DB instance or create initial connection
	 * @return PDOforRunalyze
	 */ 
	public static function getInstance() {
		if (!self::$PDO)
			throw new RuntimeException('No active database connection.');

		return self::$PDO;
	}
}