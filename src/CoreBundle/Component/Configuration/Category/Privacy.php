<?php

namespace Runalyze\Bundle\CoreBundle\Component\Configuration\Category;

class Privacy extends AbstractCategory
{
    /**
     * @return array
     */
    public function getDefaultVariables()
    {
        return [
            'TRAINING_MAKE_PUBLIC' => 'false',
            'TRAINING_LIST_PUBLIC' => 'false',
            'TRAINING_LIST_ALL' => 'false',
            'TRAINING_LIST_STATISTICS' => 'false',
            'TRAINING_MAP_PUBLIC_MODE' => 'always',
        ];
    }

    /**
     * @return string
     */
    protected function getLegacyCategoryName()
    {
        return \Runalyze\Configuration\Category\Privacy::class;
    }
}
