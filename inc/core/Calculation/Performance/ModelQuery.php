<?php
/**
 * This file contains class::ModelQuery
 * @package Runalyze\Calculation\Performance
 */

namespace Runalyze\Calculation\Performance;

/**
 * Query for performance model
 * 
 * @author Hannes Christiansen
 * @package Runalyze\Calculation\TrainingLoad
 */
class ModelQuery {
	/**
	 * Timestamp: from
	 * @var int
	 */
	protected $From = null;

	/**
	 * Timestamp: to
	 * @var int
	 */
	protected $To = null;

	/**
	 * Sportid
	 * @var int
	 */
	protected $Sportid = null;

	/**
	 * Result data
	 * @var array
	 */
	protected $Data = array();

	/**
	 * Construct
	 * @param int $from [optional] timestamp
	 * @param int $to [optional] timestamp
	 */
	public function __construct($from = null, $to = null) {
		$this->setRange($from, $to);
	}

	/**
	 * Set time range
	 * @param int $from
	 * @param int $to
	 */
	public function setRange($from, $to) {
		$this->From = $from;
		$this->To = $to;
	}

	/**
	 * Set sportid
	 * @param int $id
	 */
	public function setSportid($id) {
		$this->Sportid = $id;
	}

	/**
	 * Get data
	 * @return array
	 */
	public function data() {
		return $this->Data;
	}

	/**
	 * Execute
	 * @param \PDOforRunalyze $DB
	 */
	public function execute(\PDOforRunalyze $DB) {
		$this->Data = array();
		$Today = new \DateTime('today');

		$Statement = $DB->query($this->query());
		while ($row = $Statement->fetch()) {
			$index = (int)$Today->diff(new \DateTime($row['date']))->format('%r%a');
			$this->Data[$index] = $row['trimp'];
		}

		ksort($this->Data);
	}

	/**
	 * Get query
	 * @return string
	 */
	private function query() {
		if (is_null($this->From) && is_null($this->To)) {
			$Where = '1';
		} else {
			$Where = '`time` BETWEEN '.(int)$this->From.' AND '.(int)$this->To;
		}

		if (!is_null($this->Sportid)) {
			$Where .= ' AND `sportid`='.(int)$this->Sportid;
		}

		$Query = '
			SELECT
				DATE(FROM_UNIXTIME(`time`)) as `date`,
				SUM(`trimp`) as `trimp`
			FROM `'.PREFIX.'training`
			WHERE '.$Where.'
			GROUP BY DATE(FROM_UNIXTIME(`time`))';

		return $Query;
	}
}