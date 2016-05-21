<?php

namespace Runalyze\Data\Weather;

class SourcesTest extends \PHPUnit_Framework_TestCase
{

	public function testThatStringsAreDefined()
	{
		foreach (Sources::getEnum() as $id) {
			Sources::stringFor($id);
		}
	}

}
