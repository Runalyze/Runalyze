<?php

namespace Runalyze\Export\File;

use Runalyze\View\Activity\FakeContext;

class GpxTest extends \PHPUnit_Framework_TestCase
{
	public function testFileCreationForOutdoorActivity()
	{
		$Exporter = new Gpx(FakeContext::outdoorContext());
		$Exporter->createFileWithoutDirectDownload();
	}
}
