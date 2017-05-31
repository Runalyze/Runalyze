<?php

namespace Runalyze\Model\Tag;

use PDO;
use DB;

/**
 * @group dependsOn
 * @group dependsOnOldDatabase
 */
class InserterTest extends \PHPUnit_Framework_TestCase {

	/**
	 * @var \PDO
	 */
	protected $PDO;

	/**
	 * @var int
	 */
	protected $Typeid;

	protected function setUp() {
		$this->PDO = DB::getInstance();
		$this->PDO->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		$this->PDO->exec('DELETE FROM `'.PREFIX.'tag`');
	}

	protected function tearDown() {
		$this->PDO->exec('DELETE FROM `'.PREFIX.'tag`');
	}

	public function testSimpleInsert() {
		$Tag = new Entity(array(
			Entity::TAG => 'Tag test'
		));

		$Inserter = new Inserter($this->PDO, $Tag);
		$Inserter->setAccountID(1);
		$Inserter->insert();

		$data = $this->PDO->query('SELECT * FROM `'.PREFIX.'tag` WHERE `accountid`=1')->fetch(PDO::FETCH_ASSOC);
		$New = new Entity($data);

		$this->assertEquals('Tag test', $New->tag());

	}

}
