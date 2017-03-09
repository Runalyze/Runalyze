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
            'VO2MAX_FORM' => '0.0',
            'VO2MAX_CORRECTOR' => '1.0',
            'BASIC_ENDURANCE' => '0',
            'MAX_ATL' => '0',
            'MAX_CTL' => '0',
            'MAX_TRIMP' => '0',
        ];
    }

    /**
     * @return int
     */
    public function getCurrentVO2maxShape()
    {
        return (int)$this->Variables['VO2MAX_FORM'];
    }

    /**
     * @return float
     */
    public function getVO2maxCorrectionFactor()
    {
        return (float)$this->Variables['VO2MAX_CORRECTOR'];
    }

    /**
     * @return string
     */
    protected function getLegacyCategoryName()
    {
        return \Runalyze\Configuration\Category\Data::class;
    }
}
