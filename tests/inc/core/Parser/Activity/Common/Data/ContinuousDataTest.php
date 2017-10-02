<?php

namespace Runalyze\Tests\Parser\Activity\Data;

use Runalyze\Parser\Activity\Common\Data\ContinuousData;

class ContinuousDataTest extends \PHPUnit_Framework_TestCase
{
    /** @var ContinuousData */
    protected $Data;

    public function setUp()
    {
        $this->Data = new ContinuousData();
    }

    public function testPropertyAccessByName()
    {
        foreach ($this->Data->getPropertyNamesOfArrays() as $name) {
            $this->assertTrue(is_array($this->Data->$name), 'Can\'t access property "'.$name.'".');
        }
    }
}
