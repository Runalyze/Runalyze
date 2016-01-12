<?php

namespace Runalyze\Dataset;

class KeysTest extends \PHPUnit_Framework_TestCase
{

	public function testAllIDs()
	{
		foreach (Keys::getEnum() as $id) {
			$this->assertEquals($id, Keys::get($id)->id());
		}
	}

	public function testThatIDsAreUnique()
	{
		$this->assertEquals(
			Keys::getEnum(),
			array_unique(Keys::getEnum())
		);
	}

	public function testThatSummaryWorksForAllKeys()
	{
		foreach (Keys::getEnum() as $key) {
			$KeyObject = Keys::get($key);

			if ($KeyObject->isInDatabase() && $KeyObject->isShownInSummary()) {
				SummaryMode::query(
					$KeyObject->summaryMode(),
					$KeyObject->column()
				);
			}
		}
	}

	public function testThatLabelIsNeverEmpty()
	{
		foreach (Keys::getEnum() as $id) {
			$this->assertNotEmpty(Keys::get($id)->label());
		}
	}

	public function testEmptyKeysForValuesNotInDatabase()
	{
		foreach (Keys::getEnum() as $id) {
			$Key = Keys::get($id);

			if (!$Key->isInDatabase()) {
				$this->assertEquals('', $Key->column());
			}
		}
	}

	/** @expectedException \InvalidArgumentException */
	public function testInvalidKeyForGet()
	{
		Keys::get('key-ids-are-integers');
	}

}
