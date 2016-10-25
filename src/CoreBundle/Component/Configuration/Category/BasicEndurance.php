<?php

namespace Runalyze\Bundle\CoreBundle\Component\Configuration\Category;

class BasicEndurance extends AbstractCategory
{
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
     * @return string
     */
    protected function getLegacyCategoryName()
    {
        return \Runalyze\Configuration\Category\BasicEndurance::class;
    }
}
