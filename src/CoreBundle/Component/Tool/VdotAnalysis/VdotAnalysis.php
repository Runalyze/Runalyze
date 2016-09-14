<?php

namespace Runalyze\Bundle\CoreBundle\Component\Tool\VdotAnalysis;

use Runalyze\Calculation\JD\Shape;
use Runalyze\Configuration;
use Runalyze\Model;

class VdotAnalysis
{
    /** @var float */
    protected $VdotFactor;

    /** @var int */
    protected $AccountId;

    /** @var int */
    protected $RunningSportId;

    /** @var Configuration\Category\Vdot */
    protected $VdotConfig;

    /**
     * @param Configuration\Category\Vdot $vdotConfig
     */
    public function __construct(Configuration\Category\Vdot $vdotConfig)
    {
        $this->VdotConfig = $vdotConfig;
    }

    /**
     * @return RaceAnalysis[]
     *
     * @TODO use Doctrine
     */
    public function getAnalysisForAllRaces($vdotFactor, $sportId, $accountId)
    {
        $this->VdotFactor = $vdotFactor;
        $this->RunningSportId = $sportId;
        $this->AccountId = $accountId;

        $analysisData = [];
        $statement = \DB::getInstance()->query($this->getQuery());

        while ($data = $statement->fetch()) {
            $analysisData[] = new RaceAnalysis(
                new Model\Activity\Entity($data),
                $this->VdotFactor,
                $this->loadUncorrectedShape($data['time']) * $this->VdotFactor
            );
        }

        return $analysisData;
    }

    /**
     * @return string
     */
    protected function getQuery()
    {
        return 'SELECT
				`tr`.`id`,
				`tr`.`time`,
				`tr`.`sportid`,
				`tr`.`distance`,
				`tr`.`s`,
				`tr`.`is_track`,
				`tr`.`comment`,
				`tr`.`pulse_avg`,
				`tr`.`pulse_max`,
				`tr`.`vdot`,
				`tr`.`vdot_by_time`
			FROM `'.PREFIX.'raceresult` `r` LEFT JOIN `'.PREFIX.'training` `tr` ON `tr`.`id` = `r`.`activity_id`
			WHERE
				`tr`.`pulse_avg` !=0 AND
				`tr`.`sportid` = '.$this->RunningSportId.' AND
				`r`.`accountid` = '.$this->AccountId.'
			ORDER BY `tr`.`time` DESC';
    }

    /**
     * Load shape
     * @param int $time
     * @return float
     *
     * @TODO use Doctrine
     */
    protected function loadUncorrectedShape($time) {
        $Shape = new Shape(
            \DB::getInstance(),
            $this->AccountId,
            $this->RunningSportId,
            $this->VdotConfig
        );
        $Shape->calculateAt($time);

        return $Shape->uncorrectedValue();
    }
}
