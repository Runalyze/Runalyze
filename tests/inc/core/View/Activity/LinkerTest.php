<?php

namespace Runalyze\View\Activity;

/**
 * @group dependsOn
 * @group dependsOnOldDatabase
 */
class LinkerTest extends \PHPUnit_Framework_TestCase {

	/**
	 * @var \PDOforRunalyze
	 */
	protected $PDO;

	/**
	 * @var int 
	 */
	protected $ThisAccountID;

	/**
	 * @var int 
	 */
	protected $OtherAccountID;

	/**
	 * @var array
	 */
	protected $ThisIDs = [];

	protected function setUp() {
		$this->PDO = \DB::getInstance();
		$this->PDO->exec('DELETE FROM `runalyze_training`');
		$this->PDO->exec('DELETE FROM `runalyze_account` WHERE `username` = "LinkerOther"');
		$this->ThisAccountID = \SessionAccountHandler::getId();
		$this->PDO->exec('INSERT INTO `runalyze_account` (`username`, `name`, `mail`) VALUES ("LinkerOther", "LinkerOther", "linker@other.com")');
		$this->OtherAccountID = $this->PDO->lastInsertId();

		$activities = [
			[0, $this->OtherAccountID],
			[1, $this->ThisAccountID],
			[2, $this->OtherAccountID],
			[3, $this->ThisAccountID],
			[3, $this->ThisAccountID],
			[4, $this->OtherAccountID],
			[5, $this->ThisAccountID],
			[6, $this->OtherAccountID]
		];
		foreach ($activities as $data) {
			$this->PDO->exec('INSERT INTO `runalyze_training` (`time`, `accountid`, `sportid`, `s`) VALUES ('.$data[0].', '.$data[1].', 0, 2)');

			if ($data[1] == $this->ThisAccountID) {
				$this->ThisIDs[] = $this->PDO->lastInsertId();
			}
		}
	}

	protected function tearDown() {
		$this->PDO->exec('DELETE FROM `runalyze_training`');
	}

	public function testPrevAndNext() {
		$this->assertEquals('', Linker::prevId($this->ThisIDs[0], 1));
		$this->assertEquals($this->ThisIDs[0], Linker::prevId($this->ThisIDs[1], 3));
		$this->assertEquals($this->ThisIDs[1], Linker::prevId($this->ThisIDs[2], 3));
		$this->assertEquals($this->ThisIDs[2], Linker::prevId($this->ThisIDs[3], 3));

		$this->assertEquals($this->ThisIDs[1], Linker::nextId($this->ThisIDs[0], 3));
		$this->assertEquals($this->ThisIDs[2], Linker::nextId($this->ThisIDs[1], 3));
		$this->assertEquals($this->ThisIDs[3], Linker::nextId($this->ThisIDs[2], 3));
		$this->assertEquals('', Linker::nextId($this->ThisIDs[3], 5));
	}

	public function testPrevAndNextAtEqualTimestamp() {
		$this->assertEquals($this->ThisIDs[1], Linker::prevId($this->ThisIDs[2], 3));
		$this->assertEquals($this->ThisIDs[2], Linker::nextId($this->ThisIDs[1], 3));
	}

}
