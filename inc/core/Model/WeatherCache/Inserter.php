<?php
/**
 * This file contains class::Inserter
 * @package Runalyze\Model\WeatherCache
 */

namespace Runalyze\Model\WeatherCache;

use Runalyze\Model;

/**
 * Insert tag to database
 * 
 * @author Hannes Christiansen 
 * @author Michael Pohl
 * @package Runalyze\Model\WeatherCache
 */
class Inserter extends Model\Inserter {
	/**
	 * Object
	 * @var \Runalyze\Model\WeatherCache\Entity
	 */
	protected $Object;

	/**
	 * Construct inserter
	 * @param \PDO $connection
	 * @param \Runalyze\Model\WeatherCache\Entity $object [optional]
	 */
	public function __construct(\PDO $connection, Entity $object = null) {
		parent::__construct($connection, $object);
	}

	/**
	 * Tablename without prefix
	 * @return string
	 */
	protected function table() {
		return 'weathercache';
	}

	/**
	 * Keys to insert
	 * @return array
	 */
	protected function keys() {
		return Entity::allDatabaseProperties();
	}
}
