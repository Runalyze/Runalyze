<?php

namespace Runalyze\Bundle\CoreBundle\Component\Configuration\Category;

class BasicEndurance extends AbstractCategory
{
    /** @var float [ml/kg/min] */
    const MINIMAL_EFFECTIVE_VO2MAX = 25.0;

    /**
     * @return array
     */
    public function getDefaultVariables()
    {
        return [
            'BE_MIN_KM_FOR_LONGJOG' => '13',
            'BE_DAYS_FOR_LONGJOGS' => '70',
            'BE_DAYS_FOR_WEEK_KM' => '182',
            'BE_DAYS_FOR_WEEK_KM_MIN' => '70',
            'BE_PERCENTAGE_WEEK_KM' => '0.67',
        ];
    }

    /**
     * @return int
     */
    public function getDaysToConsider()
    {
        return max($this->getDaysToConsiderForWeeklyMileage(), $this->getDaysToConsiderForLongJogs());
    }

    /**
     * @return int
     */
    public function getDaysToConsiderForLongJogs()
    {
        return (int)$this->Variables['BE_DAYS_FOR_LONGJOGS'];
    }

    /**
     * @return int
     */
    public function getDaysToConsiderForWeeklyMileage()
    {
        return (int)$this->Variables['BE_DAYS_FOR_WEEK_KM'];
    }

    /**
     * @return int [km]
     */
    public function getMinimalDistanceForLongJog()
    {
        return (int)$this->Variables['BE_MIN_KM_FOR_LONGJOG'];
    }

    /**
     * @return float [0.00 .. 1.00]
     */
    public function getPercentageForWeeklyMileage()
    {
        return (float)$this->Variables['BE_PERCENTAGE_WEEK_KM'];
    }

    /**
     * @return float [0.00 .. 1.00]
     */
    public function getPercentageForLongJogs()
    {
        return 1.0 - $this->getPercentageForWeeklyMileage();
    }

    /**
     * @return string
     */
    protected function getLegacyCategoryName()
    {
        return \Runalyze\Configuration\Category\BasicEndurance::class;
    }
}
