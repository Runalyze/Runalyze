<?php

namespace Runalyze\Dataset;

class DefaultConfigurationTest extends \PHPUnit_Framework_TestCase
{

	public function testThatAllKeysFromEnumAppearInDefaultConfiguration()
	{
		$allKeys = (new DefaultConfiguration)->allKeys();

		foreach (Keys::getEnum() as $key) {
			in_array($key, $allKeys);
		}
	}

	public function testThatAllKeysFromDefaultConfigurationAreValid()
	{
		$DefaultConfiguration = new DefaultConfiguration;

		foreach ($DefaultConfiguration->allKeys() as $key) {
			$this->assertTrue(Keys::isValidValue($key));
		}
	}

}
