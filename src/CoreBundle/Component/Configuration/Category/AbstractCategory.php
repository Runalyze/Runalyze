<?php

namespace Runalyze\Bundle\CoreBundle\Component\Configuration\Category;

use Runalyze\Bundle\CoreBundle\Component\VariablesContainerTrait;

abstract class AbstractCategory
{
    use VariablesContainerTrait;

    public function __construct(array $variables = [])
    {
        $this->Variables = array_merge(
            $this->getDefaultVariables(),
            $variables
        );
    }

    /**
     * @return array ['key' => 'value']
     */
    abstract public function getDefaultVariables();

    /**
     * @return string
     */
    abstract protected function getLegacyCategoryName();

    /**
     * @return \Runalyze\Configuration\Category
     */
    public function getLegacyCategory()
    {
        $className = $this->getLegacyCategoryName();

        /** @var \Runalyze\Configuration\Category $category */
        $category = new $className;
        $category->setValues($this->Variables);

        return $category;
    }
}
