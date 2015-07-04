<?php
/**
 * This file contains class::DeleterWithAccountID
 * @package Runalyze\Model
 */

namespace Runalyze\Model;

/**
 * Delete object in database
 * 
 * @author Hannes Christiansen
 * @package Runalyze\Model
 */
abstract class DeleterWithAccountID extends Deleter {
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
	protected function where() {
		return '`accountid`='.$this->AccountID;
	}

	/**
	 * Tasks before delete
	 */
	protected function before() {
		if (is_null($this->AccountID)) {
			throw new \RuntimeException('Account id must be set.');
		}
	}
}