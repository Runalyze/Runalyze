<?php

namespace Runalyze\Bundle\CoreBundle\Component\Configuration\Category;

class Miscellaneous extends AbstractCategory
{
    /**
     * @return array
     */
    public function getDefaultVariables()
    {
        return [
            'SEARCH_RESULTS_PER_PAGE' => '15',
        ];
    }

    /**
     * @return string
     */
    protected function getLegacyCategoryName()
    {
        return \Runalyze\Configuration\Category\Misc::class;
    }
}
