<?php

namespace Runalyze\Bundle\CoreBundle\Component\Configuration\Category;

class Trimp extends AbstractCategory
{
    /**
     * @return array
     */
    public function getDefaultVariables()
    {
        return [
            'ATL_DAYS' => '7',
            'CTL_DAYS' => '42',
            'TRIMP_MODEL_IN_PERCENT' => 'true',
            'TSB_IN_PERCENT' => 'false',
        ];
    }

    /**
     * @return string
     */
    protected function getLegacyCategoryName()
    {
        return \Runalyze\Configuration\Category\Trimp::class;
    }
}
