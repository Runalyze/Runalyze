<?php
/**
 * This file contains class::DataObject
 * @package Runalyze\Model\Activity
 */

namespace Runalyze\Model\Activity;

use DataObject as OldDataObject;

/**
 * Mapper between new objects and old DataObject
 * 
 * @author Hannes Christiansen
 * @package Runalyze\Model\Activity
 */
class DataObject extends OldDataObject {
	/**
	 * @var \Runalyze\Model\Activity\Entity
	 */
	protected $Object;

	/**
	 * Constructor
	 * @param \Runalyze\Model\Activity\Entity
	 */
	final public function __construct(\Runalyze\Model\Activity\Entity $object) {
		$this->Object = $object;

		$this->setOldData();
		$this->initDatabaseScheme();
	}

	/**
	 * Init DatabaseScheme 
	 */
	protected function initDatabaseScheme() {
		$this->DatabaseScheme = \DatabaseSchemePool::get('training/schemes/scheme.Training.new.php');
	}

	/**
	 * Set old data array
	 */
	protected function setOldData() {
		$this->data = $this->Object->completeData();
	}

	/**
	 * Get id
	 * @return int 
	 */
	public function id() {
		return $this->Object->id();
	}

	/**
	 * Is default id?
	 * @return bool
	 */
	public function isDefaultId() {
		return ($this->id() == 0);
	}

	/**
	 * Insert object to database
	 */
	public function insert() {
		throw new \RuntimeException('Insert method has to me implemented in the child class.');
	}

	/**
	 * Update object in database
	 */
	public function update() {
		throw new \RuntimeException('Update method has to me implemented in the child class.');
	}
}