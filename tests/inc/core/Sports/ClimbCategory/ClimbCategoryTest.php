<?php

namespace Runalyze\Tests\Sports\ClimbCategory;

use Runalyze\Sports\ClimbCategory\ClimbCategory;

class ClimbCategoryTest extends \PHPUnit_Framework_TestCase
{
    public function testThatAllCategoriesCanBeConvertedToString()
    {
        $object = new ClimbCategory();

        foreach (ClimbCategory::getOptions() as $category) {
            $object->setCategory($category);

            $this->assertEquals((string)$object, $object->getString());
        }
    }

    public function testThatInvalidCategoryIsFetched()
    {
        $object = new ClimbCategory();

        $this->setExpectedException(\InvalidArgumentException::class);

        $object->setCategory(-1);
    }

    public function testCategoryMapping()
    {
        $categoryLimits = [10.0, 7.5, 5.0, 3.0, 2.0, 1.0];

        $this->assertEquals('hc', ClimbCategory::getCategoryFor(12.3, $categoryLimits)->getString());
        $this->assertEquals('1', ClimbCategory::getCategoryFor(9.9, $categoryLimits)->getString());
        $this->assertEquals('2', ClimbCategory::getCategoryFor(5.0, $categoryLimits)->getString());
        $this->assertEquals('3', ClimbCategory::getCategoryFor(3.3, $categoryLimits)->getString());
        $this->assertEquals('4', ClimbCategory::getCategoryFor(2.7, $categoryLimits)->getString());
        $this->assertEquals('5', ClimbCategory::getCategoryFor(1.6, $categoryLimits)->getString());
        $this->assertEquals('-', ClimbCategory::getCategoryFor(0.4, $categoryLimits)->getString());
    }

    public function testThatCategoryMappingIgnoresAdditionalLimits()
    {
        $this->assertFalse(ClimbCategory::getCategoryFor(0.5, [10, 7, 5, 3, 2, 1, 0, -2])->isClassified());
    }
}
