<?php
/**
 * This file contains class::RaceContainer
 * @package Runalyze\Plugin\Statistic\Races
 */

namespace Runalyze\Plugin\Statistic\Races;

use Runalyze\Configuration;

use PDO;
use DB;
use Cache;

/**
 * Race container
 * 
 * @author Hannes Christiansen
 * @package Runalyze\Plugin\Statistic\Races
 */
class RaceContainer {
	/**
	 * @var string
	 */
	const CACHE_KEY = 'plugin-races-data';

	/**
	 * Data
	 * @var array
	 */
	protected $Data = array();

	/**
	 * @var array
	 */
	protected $DistanceIDs = array();

	/**
	 * @var \PDO
	 */
	protected $PDO;

	/**
	 * @var int
	 */
	protected $ComeptitionType;

	/**
	 * @var boolean
	 */
	protected $UseCache;

	/**
	 * 
	 * @param PDO $pdo [optional]
	 * @param int $competitionType [optional]
	 * @param boolean $useCache [optional]
	 */
	public function __construct(PDO $pdo = null, $competitionType = false, $useCache = true) {
		$this->PDO = (NULL == $pdo) ? DB::getInstance() : $pdo;
		$this->ComeptitionType = (false === $competitionType) ? Configuration::General()->competitionType() : $competitionType;
		$this->UseCache = $useCache;
	}

	/**
	 * Fetch data
	 */
	public function fetchData() {
		if ($this->UseCache) {
			$this->Data = Cache::get(self::CACHE_KEY);

			if (NULL == $this->Data) {
				$this->Data = $this->fetchDataFromDB();
				Cache::set(self::CACHE_KEY, $this->Data, 600);
			}
		} else {
			$this->Data = $this->fetchDataFromDB();
		}

		$this->indexDistances();
	}

	/**
	 * @return array
	 */
	protected function fetchDataFromDB() {
		return $this->PDO->query(
			'SELECT
				`'.implode('`,`', $this->columns()).'`
			FROM `'.PREFIX.'training`
			WHERE `typeid`='.$this->ComeptitionType.'
			ORDER BY `time` DESC'
		)->fetchAll();
	}

	/**
	 * @return array
	 */
	public function allRaces() {
		return $this->Data;
	}

	/**
	 * @return int
	 */
	public function num() {
		return count($this->Data);
	}

	/**
	 * @param float $distance [km]
	 * @return array
	 */
	public function races($distance) {
		if (!isset($this->DistanceIDs[(string)(float)$distance])) {
			return array();
		}

		$races = array();

		foreach ($this->DistanceIDs[(string)(float)$distance] as $id) {
			$races[] = $this->Data[$id];
		}

		return $races;
	}

	/**
	 * @return array
	 */
	public function distances() {
		return array_keys($this->DistanceIDs);
	}

	/**
	 * Index distances
	 */
	protected function indexDistances() {
		foreach ($this->Data as $index => $data) {
			$dist = (float)$data['distance'];

			if (!isset($this->DistanceIDs[(string)$dist])) {
				$this->DistanceIDs[(string)$dist] = array($index);
			} else {
				$this->DistanceIDs[(string)$dist][] = $index;
			}
		}
	}

	/**
	 * @return array
	 */
	protected function columns() {
		return array(
			'id',
			'time',
			'sportid',
			'typeid',
			'comment',
			'distance',
			's',
			'is_track',
			'pulse_avg',
			'pulse_max',
			'weatherid',
			'temperature'
		);
	}
}