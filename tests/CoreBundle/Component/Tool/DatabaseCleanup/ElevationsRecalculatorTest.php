<?php

namespace Runalyze\Bundle\CoreBundle\Tests\Component\Tool\DatabaseCleanup;

use Runalyze\Bundle\CoreBundle\Component\Tool\DatabaseCleanup\ElevationsRecalculator;

class ElevationsRecalculatorTest extends \PHPUnit_Framework_TestCase
{
    /** @var \PDO */
	protected $PDO;

    /** @var int */
    protected $AccountId = 1;

	protected function setUp()
    {
		$this->PDO = new \PDO('sqlite::memory:');
		$this->PDO->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
		$this->PDO->exec('CREATE TABLE IF NOT EXISTS `runalyze_route` (
			`accountid` int,
			`id` int,
			`elevation` smallint,
			`elevation_up` smallint,
			`elevation_down` smallint,
			`elevations_original` text,
			`elevations_corrected` text
			);
		');
	}

	protected function tearDown()
    {
		$this->PDO->exec('DROP TABLE `runalyze_route`');
	}

	public function testSimpleRecalculations()
    {
		$this->PDO->exec('INSERT INTO `runalyze_route` VALUES('.$this->AccountId.', 1,   0,   0,  0, "100|200|150", "")');
		$this->PDO->exec('INSERT INTO `runalyze_route` VALUES('.$this->AccountId.', 2, 100, 100, 50, "100|200|150", "")');
		$this->PDO->exec('INSERT INTO `runalyze_route` VALUES('.$this->AccountId.', 3,   0,   0,  0, "", "150|200|100")');
		$this->PDO->exec('INSERT INTO `runalyze_route` VALUES('.$this->AccountId.', 4, 100,   0,  0, "", "")');

		$Job = new ElevationsRecalculator($this->PDO, $this->AccountId, 'runalyze_');
		$Job->run();

		$Fetch = $this->PDO->prepare('SELECT `elevation`, `elevation_up`, `elevation_down` FROM `runalyze_route` WHERE `id`=:id');
		$Fetch->setFetchMode(\PDO::FETCH_NUM);

		$this->assertEquals(2, $Job->numberOfRoutes());

		$Fetch->execute(array(':id' => 1));
		$this->assertEquals(array(100, 100, 50), $Fetch->fetch());

		$Fetch->execute(array(':id' => 2));
		$this->assertEquals(array(100, 100, 50), $Fetch->fetch());

		$Fetch->execute(array(':id' => 3));
		$this->assertEquals(array(100,  50, 100), $Fetch->fetch());

		$Fetch->execute(array(':id' => 4));
		$this->assertEquals(array(100,   0,   0), $Fetch->fetch());

		$this->assertEquals(array(
			1 => array(100, 100, 50),
			2 => array(100, 100, 50),
			3 => array(100, 50, 100)
		), $Job->results());
	}
}
