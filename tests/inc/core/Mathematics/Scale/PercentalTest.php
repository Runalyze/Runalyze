<?php

namespace Runalyze\Tests\Mathematics\Scale;

use Runalyze\Mathematics\Scale\Percental;

class PercentalTest extends \PHPUnit_Framework_TestCase
{
    /** @var Percental */
    protected $Scale;

    protected function setUp()
    {
        $this->Scale = new Percental();
    }

    public function testNoTransformation()
    {
        $this->assertEquals(26, $this->Scale->transform(26));
    }

    public function testMinMax()
    {
        $this->assertEquals(0, $this->Scale->transform(-10));
        $this->assertEquals(0, $this->Scale->transform(0));
        $this->assertEquals(100, $this->Scale->transform(100));
        $this->assertEquals(100, $this->Scale->transform(120));
    }

    public function testNewMinimum()
    {
        $this->Scale->setMinimum(-400);

        $this->assertEquals(40, $this->Scale->transform(-200));
        $this->assertEquals(80, $this->Scale->transform(0));
    }

    public function testNewMaximum()
    {
        $this->Scale->setMaximum(200);

        $this->assertEquals(50, $this->Scale->transform(100));
        $this->assertEquals(75, $this->Scale->transform(150));
        $this->assertEquals(100, $this->Scale->transform(210));
    }

    public function testNewScale()
    {
        $this->Scale->setMinimum(1);
        $this->Scale->setMaximum(10);

        $this->assertEquals(0, $this->Scale->transform(0.9));
        $this->assertEquals(5, $this->Scale->transform(1.45));
        $this->assertEquals(50, $this->Scale->transform(5.5));
        $this->assertEquals(69, $this->Scale->transform(7.2), '', 0.5);
        $this->assertEquals(89, $this->Scale->transform(9.0), '', 0.5);
    }
}
