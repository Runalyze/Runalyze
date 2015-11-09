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

	/**
	 * Load dataset configuration from database
	 * @param \PDO $pdo database connection
	 * @param int $accountID accountid
	 */
	public function __construct(\PDO $pdo, $accountID)
	{
		$this->Data = Cache::get(self::CACHE_KEY);

		if (is_null($this->Data)) {
			$completeData = $pdo->query('SELECT `keyid`, `active`, `style` FROM `'.PREFIX.'dataset` WHERE `accountid`="'.$accountID.'" ORDER BY `position` ASC')->fetchAll();

			foreach ($completeData as $data) {
				$this->Data[$data['keyid']] = $data;
			}

			Cache::set(self::CACHE_KEY, $this->Data, '600');
		}
	}

	/**
	 * Get all active keys
	 * @return array active keys in dataset, sorted by position
	 */
	public function activeKeys()
	{
		$activeKeys = array();

		foreach ($this->Data as $key => $keyData) {
			if ($keyData['active'] == 1) {
				$activeKeys[] = $key;
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
	 * Is this dataset active?
	 * @param string $key enum, see \Runalyze\Dataset\Keys or $this->allKeys()
	 * @return bool
	 * @throws \InvalidArgumentException
	 */
	public function isActive($key)
	{
		if (!isset($this->Data[$key])) {
			throw new \InvalidArgumentException('Unknown dataset key "'.$key.'".');
		}

		return ($this->Data[$key]['active'] == 1);
	}

	/**
	 * Get CSS inline style for dataset
	 * @param string $key enum, see \Runalyze\Dataset\Keys or $this->allKeys()
	 * @return string
	 * @throws \InvalidArgumentException
	 */
	public function getStyle($key)
	{
		if (!isset($this->Data[$key])) {
			throw new \InvalidArgumentException('Unknown dataset key "'.$key.'".');
		}

		return $this->Data[$key]['style'];
	}
}