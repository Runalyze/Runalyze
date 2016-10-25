<?php

namespace Runalyze\Bundle\CoreBundle\Component\Configuration\Category;

class Vdot extends AbstractCategory
{
    /**
     * @return array
     */
    public function getDefaultVariables()
    {
        return [
            'VDOT_DAYS' => '30',
            'VDOT_MANUAL_CORRECTOR' => '',
            'VDOT_MANUAL_VALUE' => '',
            'VDOT_USE_CORRECTION_FOR_ELEVATION' => 'false',
            'VDOT_CORRECTION_POSITIVE_ELEVATION' => '2',
            'VDOT_CORRECTION_NEGATIVE_ELEVATION' => '-1',
        ];
    }

    /**
     * @return bool
     */
    public function useCorrectionForElevation()
    {
        return ('true' == $this->Variables['VDOT_USE_CORRECTION_FOR_ELEVATION']);
    }

    /**
     * @return \Runalyze\Configuration\Category\Vdot
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
        return \Runalyze\Configuration\Category\Vdot::class;
    }
}
