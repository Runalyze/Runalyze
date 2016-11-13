<?php
/**
 * This file contains class::UpdaterWithAccountID
 * @package Runalyze\Model
 */

namespace Runalyze\Model;

/**
 * Update object in database
 * 
 * @author Hannes Christiansen
 * @package Runalyze\Model
 */
abstract class UpdaterWithAccountID extends Updater {
	/**
	 * Key: account id
	 * @var string
	 */
	const ACCOUNTID = 'accountid';

	/**
	 * Account id
	 * @var int
	 */
	protected $AccountID = null;

	/**
	 * Set account id
	 * @param int $id
	 */
	public function setAccountID($id) {
		$this->AccountID = $id;
	}

	/**
	 * Where clause
	 * @return string
	 */
	final protected function where() {
		return $this->whereSubclass().' AND `'.self::ACCOUNTID.'`='.$this->value(self::ACCOUNTID);
	}

	/**
	 * Where clause
	 * @return string
	 */
	abstract protected function whereSubclass();

	/**
	 * Has the value changed?
	 * @param string $key
	 * @return boolean
	 */
	protected function hasChanged($key) {
		if ($key == self::ACCOUNTID) {
			return false;
		}

		return parent::hasChanged($key);
	}

	/**
	 * Value for key
	 * @param string $key
	 * @return string
	 * @throws \RuntimeException
	 */
	protected function value($key) {
		if ($key == self::ACCOUNTID) {
			if (is_null($this->AccountID)) {
				throw new \RuntimeException('Account id must be set.');
			}

			return $this->AccountID;
		}

		return parent::value($key);
	}

	/**
	 * Tasks before insertion
	 */
	protected function before() {
		parent::before();

		if (!in_array(self::ACCOUNTID, $this->keys())) {
			throw new \RuntimeException('Account id must be part of the internal keys.');
		}
	}
}
