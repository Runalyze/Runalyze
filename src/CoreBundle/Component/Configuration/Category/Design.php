<?php

namespace Runalyze\Bundle\CoreBundle\Component\Configuration\Category;

class Design extends AbstractCategory
{
    /**
     * @return array
     */
    public function getDefaultVariables()
    {
        return [
            'DESIGN_BG_FILE' => 'runalyze.jpg',
        ];
    }

    /**
     * @return string
     */
    protected function getLegacyCategoryName()
    {
        return \Runalyze\Configuration\Category\Design::class;
    }
}
