<?php

namespace Runalyze\Export\File;

use Runalyze\View\Activity\FakeContext;

class TcxTest extends \PHPUnit_Framework_TestCase
{
	public function testFileCreationForOutdoorActivity()
	{
		$Exporter = new Tcx(FakeContext::outdoorContext());
		$Exporter->createFileWithoutDirectDownload();
	}
}
