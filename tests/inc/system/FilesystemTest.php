<?php

require_once dirname(__FILE__) . '/../../../inc/system/class.Filesystem.php';

class FilesystemTest extends PHPUnit_Framework_TestCase
{
	public function testStringToBytes()
	{
		$this->assertEquals( Filesystem::stringToBytes("8M"), 8388608 );
		$this->assertEquals( Filesystem::stringToBytes("1k"), 1024 );
		$this->assertEquals( Filesystem::stringToBytes("1024"), 1024 );
		$this->assertEquals( Filesystem::stringToBytes("0"), 0 );
	}

	public function testBytesToString()
	{
		$this->assertEquals( Filesystem::bytesToString(8388608), "8.00 MB" );
		$this->assertEquals( Filesystem::bytesToString(1024*1024), "1.00 MB" );
		$this->assertEquals( Filesystem::bytesToString(1024), "1.00 kB" );
		$this->assertEquals( Filesystem::bytesToString(1023), "1023 B" );
		$this->assertEquals( Filesystem::bytesToString(0), "0 B" );
	}
}
