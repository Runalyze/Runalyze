<?php

namespace Runalyze\Tests\Metrics\Energy;

use Runalyze\Metrics\Energy\Energy;

class EnergyTest extends \PHPUnit_Framework_TestCase
{
    /** @var Energy */
    protected $Energy;

    public function setUp()
    {
        $this->Energy = new Energy();
    }

    public function testSettingMetabolicEquivalent()
    {
        $this->assertEquals(40.0, $this->Energy->setByMetabolicEquivalent(5.0, 80.0, 0.1)->getValue());
    }
}
