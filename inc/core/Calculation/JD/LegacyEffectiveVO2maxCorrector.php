<?php

namespace Runalyze\Calculation\JD;

use PDO;
use Runalyze\Model\Activity;

/**
 * @deprecated since v3.3|4.0
 */
class LegacyEffectiveVO2maxCorrector
{
    /** @var int Number of (best) races to look at */
    const DB_LOOKUP_LIMIT = 3;

    /** @var float */
    private static $GlobalFactor = 1.0;

    /** @var float */
    protected $Factor;

    /**
     * @param float $factor
     */
    public static function setGlobalFactor($factor)
    {
        self::$GlobalFactor = $factor;
    }

    /**
     * @param float $factor [optional] static factor is used by default
     */
    public function __construct($factor = null)
    {
        $this->Factor = $factor ?: self::$GlobalFactor;
    }

    /**
     * @return float
     */
    public function factor()
    {
        return $this->Factor;
    }

    /**
     * Calculate factor from database
     *
     * Simply looks for the best ratio of vdot by time and by heart rate.
     * This method does not regard any other correction (e.g. elevation, ...).
     *
     * @param PDO $database
     * @param int $accountid
     * @param int $sportid
     * @return float
     */
    public function fromDatabase(PDO $database, $accountid, $sportid)
    {
        $factor = $database->query(
            'SELECT MAX(`factor`) AS `factor`
			FROM (
				SELECT `vdot_by_time`*1.0/`vdot` AS `factor` 
				FROM `'.PREFIX.'raceresult` r
				LEFT JOIN `'.PREFIX.'training` tr ON r.activity_id = tr.id
				    WHERE
					tr.`sportid` = '.(int)$sportid.' AND
					tr.`vdot` > 0 AND
					tr.`use_vdot` = 1 AND
					r.`accountid` = '.(int)$accountid.'
				ORDER BY  tr.`vdot_by_time` DESC 
				LIMIT '.self::DB_LOOKUP_LIMIT.'
			) AS T
			LIMIT 1'
        )->fetchColumn();

        if ($factor > 0.0) {
            $this->Factor = $factor;
        } else {
            $this->Factor = 1.0;
        }

        return $this->Factor;
    }

    /**
     * Calculate factor from activity
     *
     * Simply calculates the ratio of vdot by time and by heart rate.
     * This method does not regard any other correction (e.g. elevation, ...).
     *
     * @param \Runalyze\Model\Activity\Entity $activity
     * @return float
     */
    public function fromActivity(Activity\Entity $activity)
    {
        if ($activity->vo2maxByHeartRate() > 0.0) {
            $this->Factor = $activity->vo2maxByTime() / $activity->vo2maxByHeartRate();
        } else {
            $this->Factor = 1.0;
        }

        return $this->Factor;
    }
}
