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
		$this->From = $this->setHour($from,"0:00");
		$this->To = $this->setHour($to,"23:59");
	}

	/**
	 * set hour of timestamp
	 */
	public function setHour($timestamp, $hour="0:00") {
		if ($timestamp==null) return null;
		return strtotime($hour, $timestamp);
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
		$Today = new \DateTime('today 23:59');

		$Statement = $DB->query($this->query());
		while ($row = $Statement->fetch()) {
			// Don't rely on MySQLs timezone => calculate diff based on timestamp
			$index = (int)$Today->diff(new \DateTime('@'.$row['time']))->format('%r%a');
			$this->Data[$index] = $row['trimp'];
		}
	}

	/**
	 * Get query
	 *
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
				`time`,
				DATE(FROM_UNIXTIME(`time`)) as `date`,
				SUM(`trimp`) as `trimp`
			FROM `'.PREFIX.'training`
			WHERE '.$Where.'
			GROUP BY `date`';

		return $Query;
	}
}
