<?php

namespace Runalyze\Model\Trackdata;

/**
 * Generated by PHPUnit_SkeletonGenerator 1.2.0 on 2014-11-10 at 18:38:24.
 */
class LoopTest extends \PHPUnit_Framework_TestCase {

	public function testSimpleLoop() {
		$Loop = new Loop(new Entity(array(
			Entity::TIME => array(0,1,2,3,4,5)
		)));

		$i = 0;
		while ($Loop->nextStep()) {
			$i++;
			$this->assertEquals($i, $Loop->time());
		}

		$this->assertTrue($Loop->isAtEnd());
	}

	public function testReset() {
		$Loop = new Loop(new Entity(array(
			Entity::TIME => array(0,1,2,3,4,5)
		)));

		$Loop->setStepSize(2);

		$Loop->nextStep();
		$this->assertEquals(2, $Loop->current(Entity::TIME));

		$Loop->reset();
		$Loop->nextStep();
		$this->assertEquals(2, $Loop->current(Entity::TIME));
	}

	public function testStepSize() {
		$Loop = new Loop(new Entity(array(
			Entity::TIME => array(0,1,2,3,4,5)
		)));

		$Loop->setStepSize(2);

		$Loop->nextStep();
		$this->assertEquals(2, $Loop->current(Entity::TIME));

		$Loop->nextStep();
		$this->assertEquals(4, $Loop->current(Entity::TIME));

		$Loop->nextStep();
		$this->assertEquals(5, $Loop->current(Entity::TIME));

		$Loop->nextStep();
		$this->assertEquals(5, $Loop->current(Entity::TIME));
	}

	public function testStatistics() {
		$Loop = new Loop(new Entity(array(
			Entity::TIME => array(0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10),
			Entity::HEARTRATE => array(0, 80, 85, 90, 95, 100, 90, 90, 90, 90, 90)
		)));
		$Loop->setStepSize(5);
		$Loop->nextStep();
		$this->assertEquals(5, $Loop->current(Entity::TIME));
		$this->assertEquals(5, $Loop->difference(Entity::TIME));
		$this->assertEquals(15, $Loop->sum(Entity::TIME));
		$this->assertEquals(90, $Loop->average(Entity::HEARTRATE));
		$this->assertEquals(0, $Loop->distance());
		$this->assertEquals(0, $Loop->difference(Entity::DISTANCE));
		$this->assertEquals(0, $Loop->sum(Entity::DISTANCE));
		$this->assertEquals(0, $Loop->average(Entity::DISTANCE));

		$Loop->nextStep();
	}

	public function testDistanceMove() {
		$Loop = new Loop(new Entity(array(
			Entity::DISTANCE => array(0, 0.5, 1.0, 2.0, 3.0, 3.2, 3.5, 3.7, 4.0)
		)));

		$Loop->nextKilometer();
		$this->assertEquals(1.0, $Loop->distance());
		$Loop->nextKilometer();
		$this->assertEquals(2.0, $Loop->distance());
		$Loop->moveDistance(0.4);
		$this->assertEquals(3.0, $Loop->distance());
		$Loop->moveDistance(0.4);
		$this->assertEquals(3.5, $Loop->distance());

		$Loop->reset();
		$Loop->moveToDistance(2.9);
		$this->assertEquals(3.0, $Loop->current(Entity::DISTANCE));
		$Loop->moveToDistance(3.7);
		$this->assertEquals(3.7, $Loop->current(Entity::DISTANCE));
	}

	public function testTimeMove() {
		$Loop = new Loop(new Entity(array(
			Entity::TIME => array(0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10)
		)));

		$Loop->moveTime(2);
		$this->assertEquals(2, $Loop->current(Entity::TIME));
		$Loop->moveTime(5);
		$this->assertEquals(7, $Loop->current(Entity::TIME));

		$Loop->reset();
		$Loop->moveToTime(7);
		$this->assertEquals(7, $Loop->current(Entity::TIME));
		$Loop->moveToTime(10);
		$this->assertEquals(10, $Loop->current(Entity::TIME));
	}

	/**
	 * @expectedException \InvalidArgumentException
	 */
	public function testInvalidMoving() {
		$Loop = new Loop(new Entity());
		$Loop->moveTime(10);
	}

	/**
	 * @expectedException \InvalidArgumentException
	 */
	public function testInvalidMovingTo() {
		$Loop = new Loop(new Entity());
		$Loop->moveToTime(100);
	}

}
