<?php
/**
 * This file contains class::Updater
 * @package Runalyze\Model\Route
 */

namespace Runalyze\Model\Route;

use Runalyze\Model;
use Runalyze\Calculation\Route\Calculator;

/**
 * Update route in database
 * 
 * @author Hannes Christiansen
 * @package Runalyze\Model\Route
 */
class Updater extends Model\UpdaterWithIDAndAccountID {
	/**
	 * Old object
	 * @var \Runalyze\Model\Route\Object
	 */
	protected $OldObject;

	/**
	 * New object
	 * @var \Runalyze\Model\Route\Object
	 */
	protected $NewObject;

	/**
	 * Construct updater
	 * @param \PDO $connection
	 * @param \Runalyze\Model\Route\Object $newObject [optional]
	 * @param \Runalyze\Model\Route\Object $oldObject [optional]
	 */
	public function __construct(\PDO $connection, Object $newObject = null, Object $oldObject = null) {
		parent::__construct($connection, $newObject, $oldObject);
	}

	/**
	 * Tablename without prefix
	 * @return string
	 */
	protected function table() {
		return 'route';
	}

	/**
	 * Keys to insert
	 * @return array
	 */
	protected function keys() {
		return array_merge(array(
				self::ACCOUNTID
			),
			Object::allDatabaseProperties()
		);
	}

	/**
	 * Tasks before insertion
	 */
	protected function before() {
		parent::before();

		if ($this->hasChanged(Object::ELEVATIONS_ORIGINAL) || $this->hasChanged(Object::ELEVATIONS_CORRECTED)) {
			$Calculator = new Calculator($this->NewObject);
			$Calculator->calculateElevation();
		}
	}
}