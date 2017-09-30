<?php

namespace Runalyze\Parser\Activity\Common\Data\Round;

class RoundCollection implements \Countable, \ArrayAccess
{
    /** @var Round[] */
    protected $Elements = [];

    /**
     * @param Round[] $elements
     */
    public function __construct(array $elements = [])
    {
        foreach ($elements as $offset => $value) {
            $this->offsetSet($offset, $value);
        }
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
}
