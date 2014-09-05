<?php
/**
 * This file contains class::ConfigurationCategory
 * @package Runalyze\System\Configuration
 */
/**
 * Configuration category
 * @author Hannes Christiansen
 * @package Runalyze\System\Configuration
 */
abstract class ConfigurationCategory {
	/**
	 * Values
	 * @var ConfigurationValue[]
	 */
	private $Values;

	/**
	 * User id
	 * @todo use instead a user object
	 * @var int
	 */
	private $UserID = null;

	/**
	 * Constructor
	 * 
	 * To load values from database, make sure to call
	 * <code>$Category->setUserID($id);</code>
	 * 
	 * Otherwise this object will only contain default values
	 * 
	 * @todo require database as parameter
	 * @param ConfigurationCategory $Category
	 */
	public function __construct() {
		$this->createValues();
	}

	/**
	 * Set user ID
	 * @param int $id
	 */
	final public function setUserID($id) {
		if ($id !== $this->UserID) {
			$this->UserID = $id;
			$this->loadValues();
		}
	}

	/**
	 * Has user ID?
	 * @return bool
	 */
	private function hasUserID() {
		return is_int($this->UserID);
	}

	/**
	 * User ID
	 * @return int
	 */
	private function userID() {
		return (int)$this->UserID;
	}

	/**
	 * Internal key
	 * @return string
	 */
	abstract protected function key();

	/**
	 * Create values
	 */
	abstract protected function createValues();

	/**
	 * Create value
	 * @param ConfigurationValue $Object
	 */
	final protected function createValue(ConfigurationValue $Object) {
		$this->Values[$Object->key()] = $Object;
	}

	/**
	 * Get value
	 * @param string $key
	 * @return mixed
	 * @throws InvalidArgumentException
	 */
	final protected function get($key) {
		if (isset($this->Values[$key])) {
			return $this->Values[$key]->value();
		} else {
			throw new InvalidArgumentException('Asked for unknown value key "'.$key.'" in configuration category.');
		}
	}

	/**
	 * Update from post
	 */
	final public function updateFromPost() {
		foreach ($this->Values as $Value) {
			$Value->setFromPost();

			if ($Value->hasChanged()) {
				$this->updateValue($Value);
				$Value->doOnchangeJobs();
			}
		}
	}

	/**
	 * Update value
	 * @param ConfigurationValue $Value
	 */
	final public function updateValue(ConfigurationValue $Value) {
		if ($this->hasUserID() && !SharedLinker::isOnSharedPage()) {
			$where = '`accountid`='.$this->userID();
			$where .= ' AND `key`='.DB::getInstance()->escape($Value->key());

			DB::getInstance()->updateWhere('conf', $where, 'value', $Value->valueAsString());
		}
	}

	/**
	 * Load values
	 */
	private function loadValues() {
		$KeysInDatabase = array();
		$Values = $this->fetchValues();

		foreach ($Values as $Value) {
			$KeysInDatabase[] = $Value['key'];

			if (isset($this->Values[$Value['key']])) {
				$this->Values[$Value['key']]->setFromString($Value['value']);
			}
		}

		if (!FrontendShared::$IS_SHOWN && $this->hasUserID()) {
			$this->correctDatabaseFor($KeysInDatabase);
		}
	}

	/**
	 * Fetch values
	 * @return array
	 */
	private function fetchValues() {
		$Data = DB::getInstance()->query('SELECT `key`,`value` FROM '.PREFIX.'conf WHERE `accountid`="'.$this->userID().'" AND `category`="'.$this->key().'"')->fetchAll();

		return $Data;
	}

	/**
	 * Correct database
	 * @param array $KeysInDatabase
	 */
	private function correctDatabaseFor(array $KeysInDatabase) {
		$WantedKeys = array_keys($this->Values);
		$UnusedKeys = array_diff($KeysInDatabase, $WantedKeys);
		$MissingKeys = array_diff($WantedKeys, $KeysInDatabase);

		foreach ($UnusedKeys as $Key) {
			$this->deleteKeyFromDatabase($Key);
		}

		foreach ($MissingKeys as $Key) {
			$this->insertKeyToDatabase($Key);
		}
	}

	/**
	 * Delete key from database
	 * @param string $Key
	 */
	private function deleteKeyFromDatabase($Key) {
		DB::getInstance()->exec('DELETE FROM '.PREFIX.'conf WHERE `accountid`="'.$this->userID().'" AND `category`="'.$this->key().'" AND `key`="'.$Key.'"');
	}

	/**
	 * Insert key to database
	 * @param string $Key
	 */
	private function insertKeyToDatabase($Key) {
		DB::getInstance()->insert('conf',
			array('key', 'value', 'category', 'accountid'),
			array(
				$this->Values[$Key]->key(),
				$this->Values[$Key]->valueAsString(),
				$this->key(),
				$this->userID()
			)
		);
	}
}