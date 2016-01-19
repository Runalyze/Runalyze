<?php

namespace Runalyze\Export\File;

use Runalyze\View\Activity\FakeContext;

class KmlTest extends \PHPUnit_Framework_TestCase
{
	public function testFileCreationForOutdoorActivity()
	{
		$Exporter = new Kml(FakeContext::outdoorContext());
		$Exporter->createFileWithoutDirectDownload();
	}
}
