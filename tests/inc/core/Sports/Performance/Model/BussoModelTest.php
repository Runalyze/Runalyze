<?php

namespace Runalyze\Tests\Sports\Performance\Model;

use Runalyze\Sports\Performance\Model\BanisterModel;
use Runalyze\Sports\Performance\Model\BussoModel;

class BussoModelTest extends \PHPUnit_Framework_TestCase
{
    public function testEquivalenceWithBanister()
    {
        $data = [
            0 => 150,
            1 => 50,
            2 => 40,
            5 => 60,
            6 => 30,
            7 => 100
        ];

        $Banister = new BanisterModel($data, 42, 7, 1, 2);
        $Banister->calculate();
        $Busso = new BussoModel($data, 42, 7, 4, 1, 2);
        $Busso->calculate();

        for ($i = 0; $i <= 7; ++$i) {
            $this->assertEquals($Banister->fitnessAt($i), $Busso->fitnessAt($i), 'Banister and Busso fitness is not equal at day ' . $i);
            $this->assertEquals($Banister->fatigueAt($i), $Busso->fatigueAt($i), 'Banister and Busso fatigue is not equal at day ' . $i);
        }
    }
}
