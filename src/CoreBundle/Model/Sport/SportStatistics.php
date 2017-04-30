<?php

namespace Runalyze\Bundle\CoreBundle\Model\Sport;

use Runalyze\Bundle\CoreBundle\Entity\Sport;

class SportStatistics
{
    /** @var SportStatistic[] */
    protected $Sports = [];

    /** @var \DateTime */
    protected $StartDate;

    public function __construct(\DateTime $startDate, array $queryResult)
    {
        $this->StartDate = $startDate;

        foreach ($queryResult as $row) {
            /** @var Sport $sport */
            $sport = $row[0];

            $sportStatistic = new SportStatistic($sport);
            $sportStatistic->setNumberOfActivities($row['num'], $row['count_distance']);
            $sportStatistic->setTotalDistance($row['distance']);
            $sportStatistic->setTotalDuration($row['time_in_s']);

            $this->Sports[(string)$sport->getId()] = $sportStatistic;
        }
    }

    /**
     * @return \DateTime
     */
    public function getStartDate()
    {
        return $this->StartDate;
    }

    /**
     * @return SportStatistic[]
     */
    public function getStatistics()
    {
        return $this->Sports;
    }

    /**
     * @param Sport $sport
     * @return bool
     */
    public function hasStatisticFor(Sport $sport)
    {
        return isset($this->Sports[(string)$sport->getId()]);
    }

    /**
     * @param Sport $sport
     * @return SportStatistic
     */
    public function getStatisticFor(Sport $sport)
    {
        if ($this->hasStatisticFor($sport)) {
            return $this->Sports[(string)$sport->getId()];
        }

        return new SportStatistic($sport);
    }

    /**
     * @return int
     */
    public function getCount()
    {
        return count($this->Sports);
    }
}
