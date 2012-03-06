<?php
class DatabaseSchemePool {
	/**
	 * Array with all objects
	 * @var array
	 */
	static protected $Objects = array();

	/**
	 * Constructor is private 
	 */
	private function __construct() {}

	/**
	 * Get instance of a DatabaseScheme
	 * @param string $schemeFile
	 * @return DatabaseScheme 
	 */
	static public function get($schemeFile) {
		if (!isset(self::$Objects[$schemeFile]))
			self::$Objects[$schemeFile] = new DatabaseScheme($schemeFile);

		return self::$Objects[$schemeFile];
	}
}
?>
