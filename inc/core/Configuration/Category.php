<?php
/**
 * This file contains class::Category
 * @package Runalyze\Configuration
 */

namespace Runalyze\Configuration;

use Runalyze\Parameter;
use DB;
use SharedLinker;
use FrontendShared;

/**
 * Configuration category
 * @author Hannes Christiansen
 * @package Runalyze\Configuration
 */
abstract class Category {
	/**
	 * Handles
	 * @var \Runalyze\Configuration\Handle[]
	 */
	private $Handles;

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
	 */
	public function __construct() {
		$this->createHandles();
		$this->registerOnchangeEvents();
	}

	/**
	 * Set user ID
	 * @param int $id
	 * @param array $data values from database
	 */
	final public function setUserID($id, $data = null) {
		if ($id !== $this->UserID) {
			$this->UserID = (int)$id;
			$this->loadValues($data);
		}
	}

	/**
	 * Has user ID?
	 * @return bool
	 */
	private function hasUserID() {
		return is_numeric($this->UserID);
	}

	/**
	 * User ID
	 * @return int
	 */
	private function userID() {
		return (int)$this->UserID;
	}

	/**
	 * Keys
	 * @return array
	 */
	final public function keys() {
		return array_keys($this->Handles);
	}

	/**
	 * Internal key
	 * @return string
	 */
	abstract protected function key();

	/**
	 * Create values
	 */
	abstract protected function createHandles();

	/**
	 * Register onchange events
	 */
	protected function registerOnchangeEvents() {}

	/**
	 * Fieldset
	 * @return \Runalyze\Configuration\Fieldset
	 */
	public function Fieldset() {
		return null;
	}

	/**
	 * Add handle
	 * @param \Runalyze\Configuration\Handle $Handle
	 */
	final protected function addHandle(Handle $Handle) {
		$this->Handles[$Handle->key()] = $Handle;
	}

	/**
	 * Add handle
	 * @param string $key
	 * @param Parameter $Parameter
	 */
	final protected function createHandle($key, Parameter $Parameter) {
		$this->Handles[$key] = new Handle($key, $Parameter);
	}

	/**
	 * Get value
	 * @param string $key
	 * @return mixed
	 * @throws \InvalidArgumentException
	 */
	final protected function get($key) {
		return $this->object($key)->value();
	}

	/**
	 * Get value object
	 * @param string $key
	 * @return \Runalyze\Parameter
	 * @throws \InvalidArgumentException
	 */
	final protected function object($key) {
		if (isset($this->Handles[$key])) {
			return $this->Handles[$key]->object();
		} else {
			throw new \InvalidArgumentException('Asked for unknown value key "'.$key.'" in configuration category.');
		}
	}

	/**
	 * Get handle object
	 * @param string $key
	 * @return \Runalyze\Configuration\Handle
	 * @throws \InvalidArgumentException
	 */
	final protected function handle($key) {
		if (isset($this->Handles[$key])) {
			return $this->Handles[$key];
		} else {
			throw new \InvalidArgumentException('Asked for unknown value key "'.$key.'" in configuration category.');
		}
	}

	/**
	 * Update all values from post
	 */
	final public function updateFromPost() {
		foreach ($this->Handles as $Handle) {
			$this->updateValueFromPost($Handle);
		}
	}

	/**
	 * Update value
	 * @param \Runalyze\Configuration\Handle $Handle
	 */
	final protected function updateValue(Handle $Handle) {
		if ($this->hasUserID() && !SharedLinker::isOnSharedPage()) {
			$where = '`accountid`='.$this->userID();
			$where .= ' AND `key`='.DB::getInstance()->escape($Handle->key());

			DB::getInstance()->updateWhere('conf', $where, 'value', $Handle->object()->valueAsString());
		}
	}

	/**
	 * Update value from post
	 * @param \Runalyze\Configuration\Handle $Handle
	 */
	private function updateValueFromPost(Handle $Handle) {
		$key = $Handle->key();

		if (isset($_POST[$key]) || isset($_POST[$key.'_sent'])) {
			$value = $Handle->value();

			if ($Handle->object() instanceof Parameter\Boolean) {
				$Handle->object()->set( isset($_POST[$key]) );
			} else {
				$Handle->object()->setFromString($_POST[$key]);
			}

			if ($value != $Handle->value()) {
				$this->updateValue($Handle);
				$Handle->processOnchangeEvents();
			}
		}
	}

	/**
	 * Load values
	 * @param array $data values from database
	 */
	private function loadValues($data = null) {
		$KeysInDatabase = array();
		$Values = is_null($data) ? $this->fetchValues() : $data;

		foreach ($Values as $Value) {
			if (!isset($Value['category']) || $Value['category'] == $this->key()) {
				$KeysInDatabase[] = $Value['key'];

				if (isset($this->Handles[$Value['key']])) {
					$this->Handles[$Value['key']]->object()->setFromString($Value['value']);
				}
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
		$WantedKeys = array_keys($this->Handles);
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
				$this->Handles[$Key]->key(),
				$this->Handles[$Key]->object()->valueAsString(),
				$this->key(),
				$this->userID()
			)
		);
	}
}