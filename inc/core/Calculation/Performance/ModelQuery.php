<?php
/**
 * This file contains class::ModelQuery
 * @package Runalyze\Calculation\Performance
 */

namespace Runalyze\Calculation\Performance;

use Runalyze\Util\LocalTime;

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
	 * @param int|null $from [optional] timestamp
	 * @param int|null $to [optional] timestamp
	 */
	public function __construct($from = null, $to = null) {
		$this->setRange($from, $to);
	}

	/**
	 * Set time range
	 * @param int|null $from
	 * @param int|null $to
	 */
	public function setRange($from, $to) {
		$this->From = (null === $from) ? null : LocalTime::fromServerTime($from)->setTime(0, 0, 0)->getTimestamp();
		$this->To = (null === $to) ? null : LocalTime::fromServerTime($to)->setTime(23, 59, 50)->getTimestamp();
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
		$Today = LocalTime::fromString('today 23:59');

		$Statement = $DB->query($this->query());
		while ($row = $Statement->fetch()) {
			// Don't rely on MySQLs timezone => calculate diff based on timestamp
			$index = (int)$Today->diff(new LocalTime($row['time']))->format('%r%a');
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
			AND `accountid`='.\SessionAccountHandler::getId().'
			GROUP BY `date`';

		return $Query;
	}
}
