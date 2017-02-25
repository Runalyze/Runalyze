<?php

namespace Runalyze\Calculation\JD;

use Runalyze\Configuration;
use PDO;
use Runalyze\Util\LocalTime;

class Shape
{
    /** @var \PDO */
    protected $PDO;

    /** @var int */
    protected $AccountID;

    /** @var int */
    protected $RunningID;

    /** @var \Runalyze\Configuration\Category\VO2max */
    protected $Configuration;

    /** @var null|\Runalyze\Calculation\JD\LegacyEffectiveVO2maxCorrector */
    protected $Corrector = null;

    /** @var float [ml/kg/min] */
    protected $Value = null;

    /**
     * @param PDO $database
     * @param int $accountid
     * @param int $sportid for running
     * @param \Runalyze\Configuration\Category\VO2max $config
     */
    public function __construct(PDO $database, $accountid, $sportid, Configuration\Category\VO2max $config)
    {
        $this->PDO = $database;
        $this->AccountID = $accountid;
        $this->RunningID = $sportid;
        $this->Configuration = $config;
    }

    /**
     * @param \Runalyze\Calculation\JD\LegacyEffectiveVO2maxCorrector $corrector
     */
    public function setCorrector(LegacyEffectiveVO2maxCorrector $corrector)
    {
        $this->Corrector = $corrector;
    }

    public function calculate()
    {
        $this->calculateAt(time());
    }

    /**
     * @param int $timestampInServerTimezone timestamp
     */
    public function calculateAt($timestampInServerTimezone)
    {
        $time = LocalTime::fromServerTime($timestampInServerTimezone)->setTime(23, 59, 59)->getTimestamp();

        $data = $this->PDO->query(
            'SELECT
				SUM('.self::mysqlVO2maxSumTime($this->Configuration->useElevationCorrection()).') as `ssum`,
				SUM('.self::mysqlVO2maxSum($this->Configuration->useElevationCorrection()).') as `value`
			FROM `'.PREFIX.'training`
			WHERE
				`sportid`='.(int)$this->RunningID.' AND
				`time` BETWEEN '.($time - $this->Configuration->days() * DAY_IN_S).' AND '.$time.' AND
				`accountid`='.(int)$this->AccountID.'
			GROUP BY `sportid`
			LIMIT 1'
        )->fetch();

        if ($data !== false && $data['ssum'] > 0) {
            $this->Value = round($data['value'] / $data['ssum'], 5);
        } else {
            $this->Value = 0.0;
        }
    }

    /**
     * Get sum selector for VO2max for mysql
     *
     * Depends on configuration: `vdot`*`s`*`use_vdot` or `vdot_with_elevation`*`s`*`use_vdot`
     *
     * @param bool $withElevation
     * @return string
     */
    public static function mysqlVO2maxSum($withElevation = false)
    {
        return $withElevation ? '(CASE WHEN `vdot_with_elevation`>0 THEN `vdot_with_elevation` ELSE `vdot` END)*`s`*`use_vdot`' : '`vdot`*`s`*`use_vdot`';
    }

    /**
     * Get sum selector for time for mysql
     *
     * `s`*`use_vdot`
     *
     * @param bool $withElevation
     * @return string
     */
    public static function mysqlVO2maxSumTime($withElevation = false)
    {
        return '`s`*`use_vdot`*('.($withElevation ? '(CASE WHEN `vdot_with_elevation`>0 THEN `vdot_with_elevation` ELSE `vdot` END)' : '`vdot`').' > 0)';
    }

    /**
     * VO2max shape
     *
     * This value is already corrected.
     * If no corrector was set, the global/static one is used.
     *
     * @return float [ml/kg/min]
     */
    public function value()
    {
        if (is_null($this->Corrector)) {
            $this->Corrector = new LegacyEffectiveVO2maxCorrector;
        }

        return $this->uncorrectedValue() * $this->Corrector->factor();
    }

    /**
     * Uncorrected shape
     *
     * @return float [ml/kg/min]
     * @throws \RuntimeException
     */
    public function uncorrectedValue()
    {
        if (is_null($this->Value)) {
            throw new \RuntimeException('The value has to be calculated first.');
        }

        return $this->Value;
    }
}
