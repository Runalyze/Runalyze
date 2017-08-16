<?php

namespace Runalyze\Calculation\JD;

use Runalyze\Configuration\Category;
use PDO;

class ShapeFake extends Shape {
	public function calculate() {
		$this->Value = 50;
	}
}

class CategoryFake extends Category\VO2max {
	public function __construct($vo2maxDays = 30, $elevationCorrector = false) {
		parent::__construct();

		$this->object('VO2MAX_DAYS')->set($vo2maxDays);
		$this->object('VO2MAX_USE_CORRECTION_FOR_ELEVATION')->set($elevationCorrector);
	}
}

/**
 * @group requiresSqlite
 */
class ShapeTest extends \PHPUnit_Framework_TestCase {

	/**
	 * @var \PDO
	 */
	protected $PDO;

	protected function setUp() {
		$this->PDO = new PDO('sqlite::memory:');
		$this->PDO->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		$this->PDO->exec('CREATE TABLE IF NOT EXISTS `'.PREFIX.'training` (
			`accountid` int(10),
			`sportid` int(10),
			`time` int(10),
			`s` int(10),
			`use_vo2max` tinyint(1),
			`vo2max` decimal(5,2),
			`vo2max_with_elevation` decimal(5,2)
			);
		');

		LegacyEffectiveVO2maxCorrector::setGlobalFactor(1.0);
	}
	protected function tearDown() {
		$this->PDO->exec('DROP TABLE `'.PREFIX.'training`');
	}

	public function testWithoutData() {
		$Shape = new Shape($this->PDO, 1, 1, new CategoryFake());
		$Shape->calculate();

		$this->assertEquals(0, $Shape->value());
	}

	public function testSimpleCalculation() {
		$this->PDO->exec('INSERT INTO `'.PREFIX.'training` VALUES(1, 1, '.time().', 1, 1, 50, 60)');
		$this->PDO->exec('INSERT INTO `'.PREFIX.'training` VALUES(1, 1, '.time().', 2, 1, 60, 70)');
		$this->PDO->exec('INSERT INTO `'.PREFIX.'training` VALUES(1, 1, '.time().', 1, 1, 80, 80)');

		$Shape = new Shape($this->PDO, 1, 1, new CategoryFake());
		$Shape->calculate();

		$this->assertEquals(62.5, $Shape->value());
	}

	public function testSimpleCalculationWithElevation() {
		$this->PDO->exec('INSERT INTO `'.PREFIX.'training` VALUES(1, 1, '.time().', 1, 1, 50, 60)');
		$this->PDO->exec('INSERT INTO `'.PREFIX.'training` VALUES(1, 1, '.time().', 2, 1, 60, 70)');
		$this->PDO->exec('INSERT INTO `'.PREFIX.'training` VALUES(1, 1, '.time().', 1, 1, 80, 80)');

		$Shape = new Shape($this->PDO, 1, 1, new CategoryFake(5, true));
		$Shape->calculate();

		$this->assertEquals(70, $Shape->value());
	}

	public function testIDs() {
		$this->PDO->exec('INSERT INTO `'.PREFIX.'training` VALUES(1, 1, '.time().', 1, 1, 50, 0)');
		$this->PDO->exec('INSERT INTO `'.PREFIX.'training` VALUES(1, 2, '.time().', 1, 1, 80, 0)');
		$this->PDO->exec('INSERT INTO `'.PREFIX.'training` VALUES(2, 1, '.time().', 1, 1, 80, 0)');

		$Shape = new Shape($this->PDO, 1, 1, new CategoryFake());
		$Shape->calculate();

		$this->assertEquals(50, $Shape->value());
	}

	public function testVO2maxDays() {
		$this->PDO->exec('INSERT INTO `'.PREFIX.'training` VALUES(1, 1, '.time().', 1, 1, 50, 0)');
		$this->PDO->exec('INSERT INTO `'.PREFIX.'training` VALUES(1, 1, '.(time() - 10*DAY_IN_S).', 1, 1, 80, 0)');

		$Shape = new Shape($this->PDO, 1, 1, new CategoryFake(5));
		$Shape->calculate();

		$this->assertEquals(50, $Shape->value());
	}

	public function testUseFlag() {
		$this->PDO->exec('INSERT INTO `'.PREFIX.'training` VALUES(1, 1, '.time().', 1, 1, 50, 0)');
		$this->PDO->exec('INSERT INTO `'.PREFIX.'training` VALUES(1, 1, '.time().', 1, 0, 80, 0)');

		$Shape = new Shape($this->PDO, 1, 1, new CategoryFake());
		$Shape->calculate();

		$this->assertEquals(50, $Shape->value());
	}

	/**
	 * @expectedException \RuntimeException
	 */
	public function testCallWithoutCalculation() {
		$Shape = new Shape($this->PDO, 1, 1, new CategoryFake());
		$Shape->value();
	}

	public function testCorrector() {
		$Shape = new ShapeFake($this->PDO, 1, 1, new CategoryFake());
		$Shape->setCorrector(new LegacyEffectiveVO2maxCorrector(0.9));
		$Shape->calculate();

		$this->assertEquals(50, $Shape->uncorrectedValue());
		$this->assertEquals(45, $Shape->value());
	}

}
