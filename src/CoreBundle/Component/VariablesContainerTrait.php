<?php

namespace Runalyze\Bundle\CoreBundle\Component;

trait VariablesContainerTrait
{
    /** @var array */
    protected $Variables = [];

    /**
     * @param string $key
     * @param mixed $value
     */
    public function set($key, $value)
    {
        $this->Variables[$key] = $value;
    }

    /**
     * @param string $key
     * @return mixed
     */
    public function get($key)
    {
        if (!isset($this->Variables[$key])) {
            throw new \InvalidArgumentException('Provided key "'.$key.'" does not exist in this container.');
        }

        return $this->Variables[$key];
    }
}
