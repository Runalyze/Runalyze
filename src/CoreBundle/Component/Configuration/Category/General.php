<?php

namespace Runalyze\Bundle\CoreBundle\Component\Configuration\Category;

class General extends AbstractCategory
{
    /**
     * @return array
     */
    public function getDefaultVariables()
    {
        return [
            'WEEK_START' => '1',
            'DISTANCE_UNIT_SYSTEM' => 'metric',
            'WEIGHT_UNIT' => 'kg',
            'ENERGY_UNIT' => 'kcal',
            'TEMPERATURE_UNIT' => '°C',
            'HEART_RATE_UNIT' => 'hfmax',
            'MAINSPORT' => '1',
            'RUNNINGSPORT' => '1',
        ];
    }

    /**
     * @return int
     */
    public function getRunningSport()
    {
        return (int)$this->Variables['RUNNINGSPORT'];
    }

    /**
     * @return string
     */
    protected function getLegacyCategoryName()
    {
        return \Runalyze\Configuration\Category\General::class;
    }
}
