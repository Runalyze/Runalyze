<?php

namespace Runalyze\Bundle\CoreBundle\Component\Configuration\Category;

class Data extends AbstractCategory
{
    /**
     * @return array
     */
    public function getDefaultVariables()
    {
        return [
            'START_TIME' => '0',
            'HF_MAX' => '200',
            'HF_REST' => '60',
            'VDOT_FORM' => '0.0',
            'VDOT_CORRECTOR' => '1.0',
            'BASIC_ENDURANCE' => '0',
            'MAX_ATL' => '0',
            'MAX_CTL' => '0',
            'MAX_TRIMP' => '0',
        ];
    }

    /**
     * @return int
     */
    public function getVdotShape()
    {
        return (int)$this->Variables['VDOT_FORM'];
    }

    /**
     * @return float
     */
    public function getVdotCorrector()
    {
        return (float)$this->Variables['VDOT_CORRECTOR'];
    }

    /**
     * @return string
     */
    protected function getLegacyCategoryName()
    {
        return \Runalyze\Configuration\Category\Data::class;
    }
}
