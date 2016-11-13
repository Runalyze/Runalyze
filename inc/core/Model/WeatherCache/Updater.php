<?php
/**
 * This file contains class::Updater
 * @package Runalyze\Model\WeatherCache
 */

namespace Runalyze\Model\WeatherCache;

use Runalyze\Model;

/**
 * Update weather in database
 * 
 * @author Hannes Christiansen 
 * @author Michael Pohl
 * @package Runalyze\Model\WeatherCache
 */
class Updater extends Model\Updater {
	/**
	 * Old object
	 * @var \Runalyze\Model\WeatherCache\Entity
	 */
	protected $OldObject;

	/**
	 * New object
	 * @var \Runalyze\Model\WeatherCache\Entity
	 */
	protected $NewObject;

	/**
	 * Construct updater
	 * @param \PDO $connection
	 * @param \Runalyze\Model\WeatherCache\Entity $newObject [optional]
	 * @param \Runalyze\Model\WeatherCache\Entity $oldObject [optional]
	 */
	public function __construct(\PDO $connection, Entity $newObject = null, Entity $oldObject = null) {
		parent::__construct($connection, $newObject, $oldObject);
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
	
	/**
	 * Where clause
	 * @return string
	 */
	 protected function where() {
	 	return "`time` = ".$this->OldObject->time()." AND `geohash` = '".$this->OldObject->geohash()."'";
	 }
	
	
}
