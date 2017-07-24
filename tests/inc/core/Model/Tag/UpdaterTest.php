<?php

namespace Runalyze\Model\Tag;

use PDO;
use DB;

/**
 * @group dependsOn
 * @group dependsOnOldDatabase
 */
class UpdaterTest extends \PHPUnit_Framework_TestCase {

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

		$this->Typeid = $this->PDO->lastInsertId();
	}

	protected function tearDown() {
		$this->PDO->exec('DELETE FROM `'.PREFIX.'equipment`');
	}

	public function testSimpleUpdate() {
		$Inserter = new Inserter($this->PDO);
		$Inserter->setAccountID(1);
		$Inserter->insert(new Entity(array(
			Entity::TAG => 'Tag'
		)));

		$Tag = new Entity($this->PDO->query('SELECT * FROM `'.PREFIX.'tag` WHERE `id`='.$Inserter->insertedID())->fetch(PDO::FETCH_ASSOC));
		
		
		$Changed = clone $Tag;
		$Changed->set(Entity::TAG, 'bahn');
		
		$Updater = new Updater($this->PDO, $Changed, $Tag);
		$Updater->setAccountID(1);
		$Updater->update();

		$Result = new Entity($this->PDO->query('SELECT * FROM `'.PREFIX.'tag` WHERE `id`='.$Inserter->insertedID())->fetch(PDO::FETCH_ASSOC));

		$this->assertEquals('bahn', $Result->tag());

	}

}
