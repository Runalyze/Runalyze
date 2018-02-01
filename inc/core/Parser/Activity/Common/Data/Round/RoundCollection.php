<?php

namespace Runalyze\Parser\Activity\Common\Data\Round;

class RoundCollection implements \Countable, \ArrayAccess, \Iterator
{
    /** @var Round[] */
    protected $Elements = [];

    /** @var int */
    protected $CurrentOffset = 0;

    /**
     * @param Round[] $elements
     */
    public function __construct(array $elements = [])
    {
        foreach ($elements as $offset => $value) {
            $this->offsetSet($offset, $value);
        }
    }

    public function __clone()
    {
        foreach ($this->Elements as $i => $element) {
            $this->Elements[$i] = clone $element;
        }
    }

    public function clear()
    {
        $this->Elements = [];
        $this->CurrentOffset = 0;
    }

    public function add(Round $round)
    {
        $this->Elements[] = $round;
    }

    /**
     * @return Round[]
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
     * @return Round
     */
    public function offsetGet($offset)
    {
        return $this->Elements[$offset];
    }

    /**
     * @param int $offset
     * @param Round $value
     *
     * @throws \InvalidArgumentException
     */
    public function offsetSet($offset, $value)
    {
        if (!($value instanceof Round)) {
            throw new \InvalidArgumentException('Round collection does only accept instances of Round as elements.');
        }

        if (null === $offset) {
            $this->Elements[] = $value;
        } else {
            $this->Elements[$offset] = $value;
        }
    }

    public function offsetUnset($offset)
    {
        unset($this->Elements[$offset]);
    }

    public function current()
    {
        return $this->Elements[$this->CurrentOffset];
    }

    public function key()
    {
        return $this->CurrentOffset;
    }

    public function next()
    {
        ++$this->CurrentOffset;
    }

    public function rewind()
    {
        $this->CurrentOffset = 0;
    }

    public function valid()
    {
        return isset($this->Elements[$this->CurrentOffset]);
    }

    /**
     * @return int
     */
    public function getTotalDuration()
    {
        return array_reduce(
            $this->getElements(),
            function ($carry, Round $round) {
                return $carry + $round->getDuration();
            },
            0
        );
    }

    /**
     * @return float
     */
    public function getTotalDistance()
    {
        return array_reduce(
            $this->getElements(),
            function ($carry, Round $round) {
                return $carry + $round->getDistance();
            },
            0.0
        );
    }

    public function roundDurations()
    {
        foreach ($this->Elements as $round) {
            $round->roundDuration();
        }
    }

    /**
     * @return bool
     */
    public function isEqualTo(RoundCollection $other)
    {
        if ($this->count() != $other->count()) {
            return false;
        }

        foreach ($this->Elements as $key => $round) {
            if (!$round->isEqualTo($other->offsetGet($key))) {
                return false;
            }
        }

        return true;
    }
}
