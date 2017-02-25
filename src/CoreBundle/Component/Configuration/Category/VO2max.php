<?php

namespace Runalyze\Bundle\CoreBundle\Component\Configuration\Category;

class VO2max extends AbstractCategory
{
    /**
     * @return array
     */
    public function getDefaultVariables()
    {
        return [
            'VO2MAX_DAYS' => '30',
            'VO2MAX_MANUAL_CORRECTOR' => '',
            'VO2MAX_MANUAL_VALUE' => '',
            'VO2MAX_USE_CORRECTION_FOR_ELEVATION' => 'false',
            'VO2MAX_CORRECTION_POSITIVE_ELEVATION' => '2',
            'VO2MAX_CORRECTION_NEGATIVE_ELEVATION' => '-1',
        ];
    }

    /**
     * @return bool
     */
    public function useCorrectionForElevation()
    {
        return ('true' == $this->Variables['VO2MAX_USE_CORRECTION_FOR_ELEVATION']);
    }

    /**
     * @return \Runalyze\Configuration\Category\VO2max
     */
    public function getLegacyCategory()
    {
        return parent::getLegacyCategory();
    }

    /**
     * @return string
     */
    protected function getLegacyCategoryName()
    {
        return \Runalyze\Configuration\Category\VO2max::class;
    }
}
