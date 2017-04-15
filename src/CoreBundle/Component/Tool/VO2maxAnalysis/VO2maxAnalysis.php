<?php

namespace Runalyze\Bundle\CoreBundle\Component\Tool\VO2maxAnalysis;

use Runalyze\Calculation\JD\Shape;
use Runalyze\Configuration;
use Runalyze\Model;

class VO2maxAnalysis
{
    /** @var float */
    protected $VO2maxFactor;

    /** @var int */
    protected $AccountId;

    /** @var int */
    protected $RunningSportId;

    /** @var Configuration\Category\VO2max */
    protected $VO2maxConfig;

    /**
     * @param Configuration\Category\VO2max $vo2maxConfig
     */
    public function __construct(Configuration\Category\VO2max $vo2maxConfig)
    {
        $this->VO2maxConfig = $vo2maxConfig;
    }

    /**
     * @return RaceAnalysis[]
     *
     * @TODO use Doctrine
     */
    public function getAnalysisForAllRaces($vo2maxFactor, $sportId, $accountId)
    {
        $this->VO2maxFactor = $vo2maxFactor;
        $this->RunningSportId = $sportId;
        $this->AccountId = $accountId;

        $analysisData = [];
        $statement = \DB::getInstance()->query($this->getQuery());

        while ($data = $statement->fetch()) {
            $analysisData[] = new RaceAnalysis(
                new Model\Activity\Entity($data),
                $this->VO2maxFactor,
                $this->loadUncorrectedShape($data['time']) * $this->VO2maxFactor
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
				`tr`.`title`,
				`tr`.`pulse_avg`,
				`tr`.`pulse_max`,
				`tr`.`vo2max`,
				`tr`.`vo2max_by_time`
			FROM `'.PREFIX.'raceresult` `r` LEFT JOIN `'.PREFIX.'training` `tr` ON `tr`.`id` = `r`.`activity_id`
			WHERE
				`tr`.`pulse_avg` !=0 AND
				`tr`.`sportid` = '.$this->RunningSportId.' AND
				`r`.`accountid` = '.$this->AccountId.'
			ORDER BY `tr`.`time` DESC';
    }

    /**
     * Load shape
     *
     * @param int $time
     * @return float
     *
     * @TODO use Doctrine
     */
    protected function loadUncorrectedShape($time)
    {
        $Shape = new Shape(
            \DB::getInstance(),
            $this->AccountId,
            $this->RunningSportId,
            $this->VO2maxConfig
        );
        $Shape->calculateAt($time);

        return $Shape->uncorrectedValue();
    }
}
