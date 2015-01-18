<?php
/**
 * This file contains class::Shape
 * @package Runalyze\Calculation\JD
 */

namespace Runalyze\Calculation\JD;

use Runalyze\Configuration;
use PDO;

/**
 * VDOT shape
 * 
 * @author Hannes Christiansen
 * @package Runalyze\Calculation\JD
 */
class Shape {
	/**
	 * PDO
	 * @var \PDO 
	 */
	protected $PDO;

	/**
	 * Account id
	 * @var int
	 */
	protected $AccountID;

	/**
	 * Sport id for running
	 * @var int
	 */
	protected $RunningID;

	/**
	 * Configuration
	 * @var \Runalyze\Configuration\Category\Vdot
	 */
	protected $Configuration;

	/**
	 * VDOT corrector
	 * @var \Runalyze\Calculation\JD\VDOTCorrector 
	 */
	protected $Corrector = null;

	/**
	 * VDOT value
	 * @var float
	 */
	protected $Value = null;

	/**
	 * Construct
	 * @param PDO $database
	 * @param int $accountid
	 * @param int $sportid for running
	 * @param \Runalyze\Configuration\Category\Vdot $config
	 */
	public function __construct(PDO $database, $accountid, $sportid, Configuration\Category\Vdot $config) {
		$this->PDO = $database;
		$this->AccountID = $accountid;
		$this->RunningID = $sportid;
		$this->Configuration = $config;
	}

	/**
	 * Set VDOT corrector
	 * @param \Runalyze\Calculation\JD\VDOTCorrector $corrector
	 */
	public function setCorrector(VDOTCorrector $corrector) {
		$this->Corrector = $corrector;
	}

	/**
	 * Calculate current shape
	 */
	public function calculate() {
		$this->calculateAt(time());
	}

	/**
	 * Calculate at given day
	 * @param int $time timestamp
	 */
	public function calculateAt($time) {
		$time = mktime(23, 59, 59, date('m', $time), date('d', $time), date('Y', $time));

		$data = $this->PDO->query(
			'SELECT
				SUM('.self::mysqlVDOTsumTime($this->Configuration->useElevationCorrection()).') as `ssum`,
				SUM('.self::mysqlVDOTsum($this->Configuration->useElevationCorrection()).') as `value`
			FROM `'.PREFIX.'training`
			WHERE
				`sportid`='.(int)$this->RunningID.' AND
				`time` BETWEEN '.($time - $this->Configuration->days()*DAY_IN_S).' AND '.$time.' AND
				`accountid`='.(int)$this->AccountID.'
			GROUP BY `sportid`
			LIMIT 1'
		)->fetch();

		if ($data !== false && $data['ssum'] > 0) {
			$this->Value = round($data['value']/$data['ssum'], 5);
		} else {
			$this->Value = 0;
		}
	}

	/**
	 * Get sum selector for VDOT for mysql
	 * 
	 * Depends on configuration: `vdot`*`s`*`use_vdot` or `vdot_with_elevation`*`s`*`use_vdot`
	 *
	 * @param bool $withElevation [optional] 
	 * @return string
	 */
	public static function mysqlVDOTsum($withElevation = false) {
		return $withElevation ? '(CASE WHEN `vdot_with_elevation`>0 THEN `vdot_with_elevation` ELSE `vdot` END)*`s`*`use_vdot`' : '`vdot`*`s`*`use_vdot`';
	}

	/**
	 * Get sum selector for time for mysql
	 * 
	 * `s`*`use_vdot`
	 * 
	 * @param bool $withElevation [optional]
	 * @return string
	 */
	public static function mysqlVDOTsumTime($withElevation = false) {
		return '`s`*`use_vdot`*('.($withElevation ? '(CASE WHEN `vdot_with_elevation`>0 THEN `vdot_with_elevation` ELSE `vdot` END)' : '`vdot`').' > 0)';
	}

	/**
	 * VDOT shape
	 * 
	 * This value is already corrected.
	 * If no VDOT corrector was set, the global/static one is used.
	 * 
	 * @return float
	 */
	public function value() {
		if (is_null($this->Corrector)) {
			$this->Corrector = new VDOTCorrector;
		}

		return $this->uncorrectedValue() * $this->Corrector->factor();
	}

	/**
	 * Uncorrected VDOT shape 
	 * @return float
	 * @throws \RuntimeException
	 */
	public function uncorrectedValue() {
		if (is_null($this->Value)) {
			throw new \RuntimeException('The value has to be calculated first.');
		}

		return $this->Value;
	}
}