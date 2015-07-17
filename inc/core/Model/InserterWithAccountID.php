<?php
/**
 * This file contains class::InserterWithAccountID
 * @package Runalyze\Model
 */

namespace Runalyze\Model;

/**
 * Insert object to database
 * 
 * It may be of need to set an object before using prepared statements.
 * 
 * @author Hannes Christiansen
 * @package Runalyze\Model
 */
abstract class InserterWithAccountID extends Inserter {
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