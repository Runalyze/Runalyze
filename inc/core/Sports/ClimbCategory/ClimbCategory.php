<?php

namespace Runalyze\Sports\ClimbCategory;

class ClimbCategory
{
    /** @var int */
    const CATEGORY_HC = 0;

    /** @var int */
    const CATEGORY_1 = 1;

    /** @var int */
    const CATEGORY_2 = 2;

    /** @var int */
    const CATEGORY_3 = 3;

    /** @var int */
    const CATEGORY_4 = 4;

    /** @var int */
    const CATEGORY_5 = 5;

    /** @var int */
    const CATEGORY_NONE = 6;

    /**
     * @param int $category
     */
    public function __construct($category = self::CATEGORY_NONE)
    {
        $this->Category = $category;
    }

    /**
     * @param int $category
     * @return $this
     */
    public function setCategory($category)
    {
        if (!in_array($category, self::getOptions())) {
            throw new \InvalidArgumentException(sprintf('Invalid climb category "%s".', (string)$category));
        }

        $this->Category = $category;

        return $this;
    }

    /**
     * @return bool
     */
    public function isClassified()
    {
        return self::CATEGORY_NONE != $this->Category;
    }

    /**
     * @return string
     */
    public function getString()
    {
        $strings = ['hc', '1', '2', '3', '4', '5', '-'];

        return $strings[$this->Category];
    }

    public function __toString()
    {
        return $this->getString();
    }

    /**
     * @return int[]
     */
    public static function getOptions()
    {
        return [
            self::CATEGORY_HC,
            self::CATEGORY_1,
            self::CATEGORY_2,
            self::CATEGORY_3,
            self::CATEGORY_4,
            self::CATEGORY_5,
            self::CATEGORY_NONE
        ];
    }

    /**
     * @param int|float $score
     * @param int[]|float[] $lowerLimits lower limits for as many categories as supported (see static::getOptions)
     * @return ClimbCategory
     */
    public static function getCategoryFor($score, array $lowerLimits)
    {
        $categoryIndex = self::CATEGORY_NONE;

        foreach ($lowerLimits as $i => $limit) {
            if ($score >= $limit && $i < self::CATEGORY_NONE) {
                $categoryIndex = $i;

                break;
            }
        }

        return new self($categoryIndex);
    }
}
