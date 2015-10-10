<?php
/**
 * This file contains class::DatabaseSchemePool
 * @package Runalyze\DataObjects
 */
/**
 * DatabaseSchemePool holds all needed DatabaseSchemes
 * 
 * This class is a container for all DatabaseSchemes.
 * Instead of creating a new DatabaseScheme each time one is needed,
 * this class holds all already created instances and creates a new one only
 * if there was no instance previously.
 * 
 * <code>$DatabaseScheme = DatabaseSchemePool::get($schemeFile);</code>
 * 
 * @author Hannes Christiansen
 * @package Runalyze\DataObjects
 */
class DatabaseSchemePool {
	/**
	 * Array with all objects
	 * @var array
	 */
	private static $Objects = array();

	/**
	 * Get instance of a DatabaseScheme
	 * @param string $schemeFile
	 * @return DatabaseScheme 
	 */
	public static function get($schemeFile) {
		if (!isset(self::$Objects[$schemeFile]))
			self::$Objects[$schemeFile] = new DatabaseScheme($schemeFile);

		return self::$Objects[$schemeFile];
	}
}