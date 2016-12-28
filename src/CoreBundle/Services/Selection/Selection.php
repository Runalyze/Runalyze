<?php

namespace Runalyze\Bundle\CoreBundle\Services\Selection;

class Selection
{
    /** @var string[] */
    protected $List;

    /** @var mixed */
    protected $CurrentKey;

    /**
     * @param string[] $list
     * @param mixed $currentKey
     */
    public function __construct(array $list, $currentKey = null)
    {
        $this->List = $list;
        $this->CurrentKey = $currentKey;
    }

    /**
     * @return string[]
     */
    public function getList()
    {
        return $this->List;
    }

    /**
     * @param mixed $key
     */
    public function setCurrentKey($key)
    {
        $this->CurrentKey = $key;
    }

    /**
     * @return mixed
     */
    public function getCurrentKey()
    {
        return $this->CurrentKey;
    }

    /**
     * @return bool
     */
    public function hasCurrentKey()
    {
        return isset($this->List[$this->CurrentKey]);
    }

    /**
     * @return string
     */
    public function getCurrentLabel()
    {
        if ($this->hasCurrentKey()) {
            return $this->List[$this->CurrentKey];
        }

        return reset($this->List);
    }
}
