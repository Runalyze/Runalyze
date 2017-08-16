<?php

namespace Runalyze\Bundle\CoreBundle\Model\Trackdata\Climb;

class ClimbCollection implements \Countable, \ArrayAccess
{
    /** @var Climb[] */
    protected $Elements = [];

    /**
     * @param Climb[] $elements
     */
    public function __construct(array $elements = [])
    {
        $this->Elements = $elements;
    }

    public function add(Climb $climb)
    {
        $this->Elements[] = $climb;
    }

    /**
     * @return Climb[]
     */
    public function getElements()
    {
        return $this->Elements;
    }

    /**
     * @return bool
     */
    public function isEmpty()
    {
        return empty($this->Elements);
    }

    public function count()
    {
        return count($this->Elements);
    }

    public function offsetExists($offset)
    {
        return isset($this->Elements[$offset]);
    }

    /**
     * @param int $offset
     * @return Climb
     */
    public function offsetGet($offset)
    {
        return $this->Elements[$offset];
    }

    /**
     * @param int $offset
     * @param Climb $value
     *
     * @throws \InvalidArgumentException
     */
    public function offsetSet($offset, $value)
    {
        if (!($value instanceof Climb)) {
            throw new \InvalidArgumentException('Climb collection does only accept instances of Climb as elements.');
        }

        $this->Elements[$offset] = $value;
    }

    public function offsetUnset($offset)
    {
        unset($this->Elements[$offset]);
    }
}
