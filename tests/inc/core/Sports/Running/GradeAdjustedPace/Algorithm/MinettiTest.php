<?php

namespace Runalyze\Tests\Sports\Running\GradeAdjustedPace\Algorithm;

use Runalyze\Sports\Running\GradeAdjustedPace\Algorithm\Minetti;

class MinettiTest extends \PHPUnit_Framework_TestCase
{
    /** @var Minetti */
    protected $Minetti;

    public function setUp()
    {
        $this->Minetti = new Minetti();
    }

    public function testThatFlatGroundDoesNotChangePace()
    {
        for ($secPerKm = 150.0; $secPerKm <= 480.0; $secPerKm += 15.0) {
            $this->assertEquals($secPerKm, $this->Minetti->adjustPace($secPerKm, 0.0));
        }
    }

    public function testSomeEnergyCostsVerifiedByOwnSpreadsheet()
    {
        $this->assertEquals(0.5099, $this->Minetti->getEnergyCost(-0.15), '', 0.0001);
        $this->assertEquals(0.5977, $this->Minetti->getEnergyCost(-0.10), '', 0.0001);
        $this->assertEquals(0.7628, $this->Minetti->getEnergyCost(-0.05), '', 0.0001);
        $this->assertEquals(0.8047, $this->Minetti->getEnergyCost(-0.04), '', 0.0001);
        $this->assertEquals(0.8969, $this->Minetti->getEnergyCost(-0.02), '', 0.0001);
        $this->assertEquals(0.9471, $this->Minetti->getEnergyCost(-0.01), '', 0.0001);
        $this->assertEquals(1.0554, $this->Minetti->getEnergyCost(0.01), '', 0.0001);
        $this->assertEquals(1.1134, $this->Minetti->getEnergyCost(0.02), '', 0.0001);
        $this->assertEquals(1.2365, $this->Minetti->getEnergyCost(0.04), '', 0.0001);
        $this->assertEquals(1.3014, $this->Minetti->getEnergyCost(0.05), '', 0.0001);
        $this->assertEquals(1.6578, $this->Minetti->getEnergyCost(0.10), '', 0.0001);
        $this->assertEquals(2.0603, $this->Minetti->getEnergyCost(0.15), '', 0.0001);
    }

    public function testSomeTimeFactorsVerifiedByOwnSpreadsheet()
    {
        $this->assertEquals(1.7489, $this->Minetti->getTimeFactor(-0.15), '', 0.0001);
        $this->assertEquals(1.5329, $this->Minetti->getTimeFactor(-0.10), '', 0.0001);
        $this->assertEquals(1.2520, $this->Minetti->getTimeFactor(-0.05), '', 0.0001);
        $this->assertEquals(1.1977, $this->Minetti->getTimeFactor(-0.04), '', 0.0001);
        $this->assertEquals(1.0945, $this->Minetti->getTimeFactor(-0.02), '', 0.0001);
        $this->assertEquals(1.0461, $this->Minetti->getTimeFactor(-0.01), '', 0.0001);
        $this->assertEquals(0.9562, $this->Minetti->getTimeFactor(0.01), '', 0.0001);
        $this->assertEquals(0.9147, $this->Minetti->getTimeFactor(0.02), '', 0.0001);
        $this->assertEquals(0.8385, $this->Minetti->getTimeFactor(0.04), '', 0.0001);
        $this->assertEquals(0.8036, $this->Minetti->getTimeFactor(0.05), '', 0.0001);
        $this->assertEquals(0.6573, $this->Minetti->getTimeFactor(0.10), '', 0.0001);
        $this->assertEquals(0.5488, $this->Minetti->getTimeFactor(0.15), '', 0.0001);
    }

    public function testSomeExemplaryPacesVerifiedByOwnSpreadsheet()
    {
        $this->assertPaceEquivalence('3:00', -0.05, '3:45');
        $this->assertPaceEquivalence('3:30', -0.03, '4:00');
        $this->assertPaceEquivalence('3:45', +0.01, '3:35');
        $this->assertPaceEquivalence('4:00', +0.01, '3:49');
        $this->assertPaceEquivalence('4:00', +0.02, '3:40');
        $this->assertPaceEquivalence('4:00', -0.01, '4:11');
        $this->assertPaceEquivalence('4:00', -0.05, '5:00');
        $this->assertPaceEquivalence('4:15', -0.08, '6:02');
        $this->assertPaceEquivalence('4:30', +0.03, '3:56');
        $this->assertPaceEquivalence('4:45', +0.10, '3:07');
        $this->assertPaceEquivalence('5:00', +0.05, '4:01');
        $this->assertPaceEquivalence('5:00', -0.04, '5:59');
        $this->assertPaceEquivalence('5:15', +0.02, '4:48');
        $this->assertPaceEquivalence('5:30', -0.04, '6:35');
        $this->assertPaceEquivalence('5:45', -0.03, '6:35');
        $this->assertPaceEquivalence('6:00', +0.01, '5:44');
        $this->assertPaceEquivalence('6:00', -0.02, '6:34');
        $this->assertPaceEquivalence('6:00', +0.05, '4:49');
        $this->assertPaceEquivalence('6:15', -0.06, '8:10');
        $this->assertPaceEquivalence('6:30', +0.12, '3:58');
    }

    /**
     * @param string $achievedPace as 'm:ss' [/km]
     * @param float $gradient in percent [-1.00 1.00]
     * @param string $flatPace as 'm:ss' [/km]
     */
    protected function assertPaceEquivalence($achievedPace, $gradient, $flatPace)
    {
        $this->assertEquals(
            60 * (int)$flatPace[0] + (int)($flatPace[2].$flatPace[3]),
            $this->Minetti->adjustPace(60 * (int)$achievedPace[0] + (int)($achievedPace[2].$achievedPace[3]), $gradient),
            sprintf('Pace equivalence does not match for %s @ %d %%', $achievedPace, 100 * $gradient),
            0.5
        );
    }
}
