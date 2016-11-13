<?php
/**
 * This file contains class::AccountHandler
 * @package Runalyze\System
 */

/**
 * AccountHandler
 *
 * @author Hannes Christiansen
 * @package Runalyze\System
 *
 * @deprecated since v3.0
 */
class AccountHandler {
	/**
	 * Get account-data from database
	 * @param string $username
	 * @return array
	 */
	public static function getDataFor($username) {
		return DB::getInstance()->query('SELECT * FROM `'.PREFIX.'account` WHERE `username`='.DB::getInstance()->escape($username).' LIMIT 1')->fetch();
	}

	/**
	 * Get account-data from database
	 * @param int $id
	 * @return array
	 */
	public static function getDataForId($id) {
		return DB::getInstance()->query('SELECT * FROM `'.PREFIX.'account` WHERE `id`="'.(int)$id.'" LIMIT 1')->fetch();
	}
}
