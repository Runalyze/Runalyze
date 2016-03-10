<?php
/**
 * This file contains class::Configuration
 * @package Runalyze
 */

namespace Runalyze\Dataset;

use Cache;

/**
 * Dataset configuration from database
 * 
 * @author Hannes Christiansen
 * @package Runalyze\Dataset
 */
class Configuration
{
	/** @var string */
	const CACHE_KEY = 'dataset';

	/**
	 * Complete data from database, sorted by position
	 * @var array array('keyid' => array('active' => 0/1, 'style' => '...'), ...)
	 */
	protected $Data;

	/** @var bool */
	protected $ShowAllKeys = false;

	/**
	 * Load dataset configuration from database
	 * @param \PDO $pdo database connection
	 * @param int $accountID accountid
	 * @param bool $fallbackToDefault
	 * @param bool $useCache
	 */
	public function __construct(\PDO $pdo, $accountID, $fallbackToDefault = true, $useCache = true)
	{
		$this->Data = $useCache ? Cache::get(self::CACHE_KEY) : null;

		if (is_null($this->Data)) {
			$completeData = $pdo->query('SELECT `keyid`, `active`, `style` FROM `'.PREFIX.'dataset` WHERE `accountid`="'.$accountID.'" ORDER BY `position` ASC')->fetchAll();

			if (empty($completeData) && $fallbackToDefault) {
				$this->Data = (new DefaultConfiguration)->data();
			} else {
				foreach ($completeData as $data) {
					$this->Data[$data['keyid']] = $data;
				}
			}

			if ($useCache) {
				Cache::set(self::CACHE_KEY, $this->Data, '600');
			}
		}
	}

	/**
	 * @return bool
	 */
	public function isDefault()
	{
		return false;
	}

	/**
	 * @return array
	 */
	public function data()
	{
		return $this->Data;
	}

	/**
	 * Set internal flag to activate all keys
	 */
	public function activateAllKeys()
	{
		$this->ShowAllKeys = true;
	}

	/**
	 * Remove internal flag to activate all keys
	 */
	public function deactivateAllKeys()
	{
		$this->ShowAllKeys = false;
	}

	/**
	 * @return bool
	 */
	public function isEmpty()
	{
		return empty($this->Data);
	}

	/**
	 * Get all active keys
	 * @return array active keys in dataset, sorted by position
	 */
	public function activeKeys()
	{
		if ($this->ShowAllKeys) {
			return $this->allKeys();
		}

		$activeKeys = array();

		foreach ($this->Data as $keyid => $keyData) {
			if ($keyData['active'] == 1) {
				$activeKeys[] = $keyid;
			}
		}

		return $activeKeys;
	}

	/**
	 * Get all keys
	 * @return array all keys in dataset, sorted by position
	 */
	public function allKeys()
	{
		return array_keys($this->Data);
	}

	/**
	 * Does this key exist in configuration?
	 * @param int $keyid enum, see \Runalyze\Dataset\Keys
	 * @return bool
	 */
	public function exists($keyid)
	{
		return isset($this->Data[$keyid]);
	}

	/**
	 * Is this dataset active?
	 * @param int $keyid enum, see \Runalyze\Dataset\Keys or $this->allKeys()
	 * @return bool
	 * @throws \InvalidArgumentException
	 */
	public function isActive($keyid)
	{
		if (!isset($this->Data[$keyid])) {
			throw new \InvalidArgumentException('Unknown dataset key "'.$keyid.'".');
		}

		return ($this->Data[$keyid]['active'] == 1) || $this->ShowAllKeys;
	}

	/**
	 * Get CSS inline style for dataset
	 * @param int $keyid enum, see \Runalyze\Dataset\Keys or $this->allKeys()
	 * @return string
	 * @throws \InvalidArgumentException
	 */
	public function getStyle($keyid)
	{
		if (!isset($this->Data[$keyid])) {
			throw new \InvalidArgumentException('Unknown dataset key "'.$keyid.'".');
		}

		return $this->Data[$keyid]['style'];
	}
}