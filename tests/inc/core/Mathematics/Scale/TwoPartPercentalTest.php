<?php

namespace Runalyze\Tests\Mathematics\Scale;

use Runalyze\Mathematics\Scale\TwoPartPercental;

class TwoPartPercentalTest extends \PHPUnit_Framework_TestCase
{
    /** @var TwoPartPercental */
    protected $Scale;

    protected function setUp()
    {
        $this->Scale = new TwoPartPercental();
    }

    protected function tearDown()
    {
    }

    public function testNoTransformation()
    {
        $this->assertEquals(0, $this->Scale->transform(-10));
        $this->assertEquals(26, $this->Scale->transform(26));
        $this->assertEquals(50, $this->Scale->transform(50));
        $this->assertEquals(100, $this->Scale->transform(120));
    }

    public function testSimpleScale()
    {
        $this->Scale->setMinimum(1);
        $this->Scale->setInflectionPoint(2);
        $this->Scale->setMaximum(10);

        $this->assertEquals(0, $this->Scale->transform(1));
        $this->assertEquals(5, $this->Scale->transform(1.1));
        $this->assertEquals(25, $this->Scale->transform(1.5));
        $this->assertEquals(50, $this->Scale->transform(2.0));
        $this->assertEquals(50, $this->Scale->transform(2.0));
        $this->assertEquals(56, $this->Scale->transform(3.0), '', 0.5);
        $this->assertEquals(75, $this->Scale->transform(6.0));
    }
}
