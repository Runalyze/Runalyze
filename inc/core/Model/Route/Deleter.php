<?php
/**
 * This file contains class::Deleter
 * @package Runalyze\Model\Route
 */

namespace Runalyze\Model\Route;

use Runalyze\Model\DeleterWithIDAndAccountID;

/**
 * Delete object in database
 * 
 * @author Hannes Christiansen
 * @package Runalyze\Model\Route
 */
class Deleter extends DeleterWithIDAndAccountID {
	/**
	 * Construct updater
	 * @param \PDO $connection
	 * @param \Runalyze\Model\Route\Entity $object [optional]
	 */
	public function __construct(\PDO $connection, Entity $object = null) {
		parent::__construct($connection, $object);
	}

	/**
	 * Tablename without prefix
	 * @return string
	 */
	protected function table() {
		return 'route';
	}
}