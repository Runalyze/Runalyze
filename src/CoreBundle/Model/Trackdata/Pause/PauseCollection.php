<?php

namespace Runalyze\Bundle\CoreBundle\Model\Trackdata\Pause;

class PauseCollection implements \Countable, \ArrayAccess
{
    /** @var Pause[] */
    protected $Elements = [];

    /**
     * @param Pause[] $elements
     */
    public function __construct(array $elements = [])
    {
        $this->Elements = $elements;
    }

    public function add(Pause $pause)
    {
        $this->Elements[] = $pause;
    }

    /**
     * @return Pause[]
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
     * @return Pause
     */
    public function offsetGet($offset)
    {
        return $this->Elements[$offset];
    }

    /**
     * @param int $offset
     * @param Pause $value
     *
     * @throws \InvalidArgumentException
     */
    public function offsetSet($offset, $value)
    {
        if (!($value instanceof Pause)) {
            throw new \InvalidArgumentException('Pause collection does only accept instances of Pause as elements.');
        }

        $this->Elements[$offset] = $value;
    }

    public function offsetUnset($offset)
    {
        unset($this->Elements[$offset]);
    }
}
