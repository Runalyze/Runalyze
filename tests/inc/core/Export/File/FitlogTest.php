<?php

namespace Runalyze\Export\File;

use Runalyze\View\Activity\FakeContext;

class FitlogTest extends \PHPUnit_Framework_TestCase
{
	public function testFileCreationForEmptyContext()
	{
		$Exporter = new Fitlog(FakeContext::emptyContext());
		$Exporter->createFileWithoutDirectDownload();
	}

	public function testFileCreationForIndoorActivity()
	{
		$Exporter = new Fitlog(FakeContext::indoorContext());
		$Exporter->createFileWithoutDirectDownload();
	}

	public function testFileCreationForOutdoorActivity()
	{
		$Exporter = new Fitlog(FakeContext::outdoorContext());
		$Exporter->createFileWithoutDirectDownload();
	}
}
