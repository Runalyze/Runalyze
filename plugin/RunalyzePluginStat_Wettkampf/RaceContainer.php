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
	protected $SportId;

	/**
	 * 
	 * @param PDO $pdo [optional]
	 * @param int $sportId
	 */
	public function __construct($sportId, PDO $pdo = null) {
		$this->PDO = (null == $pdo) ? DB::getInstance() : $pdo;
		$this->SportId = $sportId;
	}

	/**
	 * Fetch data
	 */
	public function fetchData() {
		$this->Data = $this->fetchDataFromDB();

		$this->indexDistances();
	}

	/**
	 * @return array
	 */
	protected function fetchDataFromDB() {
		return $this->PDO->query(
			'SELECT
				r.`official_time`, r.`official_distance`, r.`officially_measured`, r.`name`,
				r.`place_total`, r.`place_gender`, r.`place_ageclass`,
				r.`participants_total`,	r.`participants_gender`, r.`participants_ageclass`,
				tr.`id`, tr.`time`,	tr.`sportid`, tr.`typeid`, tr.`comment`, tr.`distance`,	tr.`s`,	tr.`is_track`, tr.`pulse_avg`, tr.`pulse_max`, tr.`weatherid`, tr.`temperature`
			FROM `'.PREFIX.'raceresult` as r
			LEFT JOIN `'.PREFIX.'training` as tr ON r.`activity_id` = tr.`id`
			WHERE r.`accountid`='.\SessionAccountHandler::getId().' AND tr.`sportid`='.$this->SportId.'
			ORDER BY tr.`time` DESC'
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
			$dist = (float)$data['official_distance'];

			if (!isset($this->DistanceIDs[(string)$dist])) {
				$this->DistanceIDs[(string)$dist] = array($index);
			} else {
				$this->DistanceIDs[(string)$dist][] = $index;
			}
		}
	}
}