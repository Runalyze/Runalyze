<?php

namespace Runalyze\Bundle\CoreBundle\Tests\Bridge\Activity\Calculation;

use Runalyze\Bundle\CoreBundle\Bridge\Activity\Calculation\TrimpCalculator;
use Runalyze\Bundle\CoreBundle\Entity\Sport;
use Runalyze\Bundle\CoreBundle\Entity\Training;
use Runalyze\Profile\Athlete\Gender;

class TrimpCalculatorTest extends \PHPUnit_Framework_TestCase
{
    /** @var TrimpCalculator */
    protected $Calculator;

    /** @var Training */
    protected $Activity;

    protected function setUp()
    {
        $this->Activity = new Training();
        $this->Activity->setSport(new Sport());
        $this->Calculator = new TrimpCalculator(10000, 400);
    }

    public function testEmptyActivity()
    {
        $this->Calculator->calculateFor($this->Activity, Gender::MALE, 200, 60);

        $this->assertEquals(0, $this->Activity->getTrimp());
    }

    public function testInvalidActivity()
    {
        $this->Activity->setS(1);
        $this->Activity->setPulseAvg(400);

        $this->Calculator->calculateFor($this->Activity, Gender::MALE, 200, 60);

        $this->assertNull($this->Activity->getTrimp());
    }
}
