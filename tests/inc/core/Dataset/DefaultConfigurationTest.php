<?php

namespace Runalyze\Dataset;

class DefaultConfigurationTest extends \PHPUnit_Framework_TestCase
{
	public function testThatAllKeysFromEnumAppearInDefaultConfiguration()
	{
		$allKeys = (new DefaultConfiguration)->allKeys();

		foreach (Keys::getEnum() as $key) {
			$this->assertTrue(in_array($key, $allKeys), 'Key '.$key.' is missing in default dataset config.');
		}
	}

	public function testThatAllKeysFromDefaultConfigurationAreValid()
	{
		$DefaultConfiguration = new DefaultConfiguration;

		foreach ($DefaultConfiguration->allKeys() as $key) {
			$this->assertTrue(Keys::isValidValue($key), 'Key '.$key.' is invalid.');
		}
	}
}
