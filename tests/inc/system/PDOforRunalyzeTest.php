<?php

/**
 * @group dependsOn
 * @group dependsOnOldDatabase
 */
class PDOforRunalyzeTest extends PHPUnit_Framework_TestCase
{
	/** @var PDOforRunalyze */
	protected $object;

	protected function setUp()
	{
		$this->object = DB::getInstance();
		$this->object->stopAddingAccountID();
	}

	protected function tearDown()
	{
		$this->object->exec('DELETE FROM `runalyze_training`');
	}

	public function testStartAddingAccountID()
	{
		$this->object->exec('INSERT INTO `runalyze_training` (`s`, `accountid`, `sportid`, `time`) VALUES(100, 1, 0, 1477843525)');
		$this->object->exec('INSERT INTO `runalyze_training` (`s`, `accountid`, `sportid`, `time`) VALUES(200, 1, 0, 1477843525)');
		$this->object->exec('INSERT INTO `runalyze_training` (`s`, `accountid`, `sportid`, `time`) VALUES(66, 3, 0, 1477843525)');

		$this->object->setAccountID(1);
		$this->object->startAddingAccountID();

		$this->assertEquals( 300, $this->object->query('SELECT SUM(`s`) FROM `runalyze_training`')->fetchColumn() );
		$this->assertEquals( 2, $this->object->query('SELECT COUNT(*) FROM `runalyze_training`')->fetchColumn() );

		$this->object->setAccountID(3);

		$this->assertEquals( 66, $this->object->query('SELECT SUM(`s`) FROM `runalyze_training`')->fetchColumn() );
		$this->assertEquals( 1, $this->object->query('SELECT COUNT(*) FROM `runalyze_training`')->fetchColumn() );

		$this->object->stopAddingAccountID();

		$this->assertEquals( 366, $this->object->query('SELECT SUM(`s`) FROM `runalyze_training`')->fetchColumn() );
		$this->assertEquals( 3, $this->object->query('SELECT COUNT(*) FROM `runalyze_training`')->fetchColumn() );

		$this->object->exec('DELETE FROM `runalyze_training`');

		$this->object->setAccountID(false);
	}

	public function testFetchByID()
	{
		$this->assertEquals( 0, $this->object->query('SELECT COUNT(*) FROM `runalyze_training`')->fetchColumn() );

		$this->object->exec('INSERT INTO `runalyze_training` (`id`, `s`, `accountid`, `sportid`, `time`) VALUES(1, 100, 0, 0, 1477843525)');
		$this->object->exec('INSERT INTO `runalyze_training` (`id`, `s`, `accountid`, `sportid`, `time`) VALUES(2, 200, 0, 0, 1477843525)');
		$this->object->exec('INSERT INTO `runalyze_training` (`id`, `s`, `accountid`, `sportid`, `time`) VALUES(3, 300, 0, 0, 1477843525)');

		$Training1 = $this->object->fetchByID('training', 1);
		$this->assertEquals( 100, $Training1['s'] );

		$Training2 = $this->object->fetchByID('training', 2);
		$this->assertEquals( 200, $Training2['s'] );

		$Training3 = $this->object->fetchByID('training', 3);
		$this->assertEquals( 300, $Training3['s'] );

		$this->assertEquals( 3, $this->object->query('SELECT COUNT(*) FROM `runalyze_training`')->fetchColumn() );

		$this->object->deleteByID('training', 2);
		$this->assertEquals( "1,3", $this->object->query('SELECT GROUP_CONCAT(`id`) FROM `runalyze_training` GROUP BY `accountid`')->fetchColumn() );

		$this->assertEquals( 2, $this->object->query('SELECT COUNT(*) FROM `runalyze_training`')->fetchColumn() );

		$this->object->deleteByID('training', 1);
		$this->object->deleteByID('training', 3);

		$this->assertEquals( 0, $this->object->query('SELECT COUNT(*) FROM `runalyze_training`')->fetchColumn() );

		$this->object->exec('DELETE FROM `runalyze_training`');
	}

	public function testUpdate()
	{
		$this->object->insert('training', array('id', 's', 'distance', 'sportid', 'time'), array(1, 600, 1, 0, 1477843525));
		$this->object->insert('training', array('id', 's', 'distance', 'sportid', 'time'), array(2, 900, 1, 0, 1477843525));
		$this->object->insert('training', array('id', 's', 'distance', 'sportid', 'time'), array(3, 300, 1, 0, 1477843525));

		$this->object->update('training', 1, 'distance', 2);
		$this->assertEquals( array(600, 2), $this->object->query('SELECT `s`, `distance` FROM `runalyze_training` WHERE `id`=1 LIMIT 1')->fetch(PDO::FETCH_NUM) );

		$this->object->update('training', 2, 'distance', 3);
		$this->assertEquals( array(900, 3), $this->object->query('SELECT `s`, `distance` FROM `runalyze_training` WHERE `id`=2 LIMIT 1')->fetch(PDO::FETCH_NUM) );

		$this->object->update('training', 3, array('s', 'distance'), array(150, 0.5));
		$this->assertEquals( array(150, 0.5), $this->object->query('SELECT `s`, `distance` FROM `runalyze_training` WHERE `id`=3 LIMIT 1')->fetch(PDO::FETCH_NUM) );

		$this->object->updateWhere('training', '`distance` > 1', 'title', 'Super weit.');
		$this->assertEquals( "1,2", $this->object->query('SELECT GROUP_CONCAT(`id`) FROM `runalyze_training` WHERE `title`="Super weit." GROUP BY `accountid`')->fetchColumn() );

		$this->assertEquals( 3, $this->object->exec('DELETE FROM `runalyze_training`') );
		$this->assertEquals( 0, $this->object->query('SELECT COUNT(*) FROM `runalyze_training`')->fetchColumn() );

		$this->object->exec('DELETE FROM `runalyze_training`');
	}

	public function testPrepare()
	{
		$Insert = $this->object->prepare('INSERT INTO `runalyze_training` (`id`, `s`, `distance`, `accountid`, `sportid`, `time`) VALUES (:id, :s, :distance, :accountid, :sportid, :ttime)');
		$Insert->bindValue('id', 1);
		$Insert->bindValue('s', 300);
		$Insert->bindValue('distance', 1);
        $Insert->bindValue('sportid', 0);
        $Insert->bindValue('accountid', 0);
        $Insert->bindValue('ttime', 1477843525);
		$Insert->execute();

		$Insert->bindValue('id', 2);
		$Insert->bindValue('s', 610);
		$Insert->bindValue('distance', 2.1);
        $Insert->bindValue('sportid', 0);
        $Insert->bindValue('accountid', 0);
        $Insert->bindValue('ttime', 1477843525);
		$Insert->execute();

		$Insert->bindValue('id', 3);
		$Insert->bindValue('s', 3111);
		$Insert->bindValue('distance', 11.23);
        $Insert->bindValue('sportid', 0);
        $Insert->bindValue('accountid', 0);
        $Insert->bindValue('ttime', 1477843525);
		$Insert->execute();

		$id = 0;
		$RequestDistance = $this->object->prepare('SELECT `distance` FROM `runalyze_training` WHERE `id`=:id');
		$RequestDistance->bindParam('id', $id);

		$id = 1;
		$RequestDistance->execute();
		$this->assertEquals( 1, $RequestDistance->fetchColumn() );

		$id = 2;
		$RequestDistance->execute();
		$this->assertEquals( 2.1, $RequestDistance->fetchColumn() );

		$id = 3;
		$RequestDistance->execute();
		$this->assertEquals( 11.23, $RequestDistance->fetchColumn() );

		$this->object->exec('DELETE FROM `runalyze_training`');
	}

	public function testEscape()
	{
		$this->assertEquals( 'NULL', $this->object->escape(null) );
		$this->assertEquals( 1, $this->object->escape(true) );
		$this->assertEquals( 0, $this->object->escape(false) );

		$this->assertEquals( array(0.123, '\'5\\" OR 1=1\''), $this->object->escape(array(0.123, '5" OR 1=1')));
	}
}
