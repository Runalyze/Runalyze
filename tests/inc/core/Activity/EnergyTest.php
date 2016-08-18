<?php

namespace Runalyze\Activity;

use Runalyze\Configuration;
use Runalyze\Parameter\Application\EnergyUnit;

class EnergyTest extends \PHPUnit_Framework_TestCase
{
	public function testConstructor()
	{
		$this->assertEquals('100', (new Energy(100, new EnergyUnit(EnergyUnit::KCAL)))->string(false));
		$this->assertEquals('419', (new Energy(100, new EnergyUnit(EnergyUnit::KJ)))->string(false));
	}

	public function testSettingInPreferredUnit()
	{
		$this->assertEquals(100, (new Energy(0, new EnergyUnit(EnergyUnit::KCAL)))->setInPreferredUnit(100)->kcal());
		$this->assertEquals(23, (new Energy(0, new EnergyUnit(EnergyUnit::KJ)))->setInPreferredUnit(100)->kcal());
	}

	public function testFromKilocalorie()
	{
		$Energy = new Energy();
		$Energy->set(100);

		$this->assertEquals(100, $Energy->kcal());
		$this->assertEquals(419, $Energy->kj());
	}

	public function testFromKilojule()
	{
		$Energy = new Energy();
		$Energy->setKJ(41868);

		$this->assertEquals(10000, $Energy->kcal());
		$this->assertEquals(41868, $Energy->kj());
	}

	public function testStaticMethod()
	{
		Configuration::General()->distanceUnitSystem()->set(EnergyUnit::KCAL);

		$this->assertEquals('123', Energy::format(123, false));
	}
}
