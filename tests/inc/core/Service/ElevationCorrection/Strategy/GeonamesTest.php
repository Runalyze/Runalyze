<?php

namespace Runalyze\Service\ElevationCorrection\Strategy;

class GeonamesTest extends \PHPUnit_Framework_TestCase
{

	public function testSimpleData()
	{
		$Corrector = new Geonames(
			array(49.444722),
			array(7.768889)
		);

		if ($Corrector->canHandleData()) {
			try {
				$Corrector->correctElevation();
				$this->assertEquals( array(237), $Corrector->getCorrectedElevation() );
			} catch (\RuntimeException $Exception) {
				$this->markTestSkipped('Geonames.org was not able to handle the request. Exception occured: '.$Exception->getMessage());
			}
		} else {
			$this->markTestSkipped('Geonames.org was not available.');
		}
	}

	public function testSimplePath()
	{
		$Corrector = new Geonames(
			array(49.440, 49.441, 49.442, 49.443, 49.444, 49.445, 49.446, 49.447, 49.448, 49.449, 49.450),
			array(7.760, 7.761, 7.762, 7.763, 7.764, 7.765, 7.766, 7.767, 7.768, 7.769, 7.770)
		);

		if ($Corrector->canHandleData()) {
			try {
				$Corrector->correctElevation();
				$this->assertEquals( array(239, 239, 239, 239, 239, 237, 237, 237, 237, 237, 264), $Corrector->getCorrectedElevation() );
			} catch (\RuntimeException $Exception) {
				$this->markTestSkipped('Geonames.org was not able to handle the request. Exception occured: '.$Exception->getMessage());
			}
		} else {
			$this->markTestSkipped('Geonames.org was not available.');
		}
	}

	public function testUnknown()
	{
		$Corrector = new Geonames(
			array( 0, 49.440, 49.441, 49.442, 49.443, 49.444, 49.445, 0, 0, 0, 0, 0, 0, 49.446, 49.447, 49.448, 49.449, 49.450),
			array( 0,  7.760,  7.761,  7.762,  7.763,  7.764,  7.765, 0, 0, 0, 0, 0, 0,  7.766,  7.767,  7.768,  7.769,  7.770)
		);

		if ($Corrector->canHandleData()) {
			try {
				$Corrector->correctElevation();
				$this->assertEquals( array(237, 237, 237, 237, 237, 237, 237, 237, 237, 237, 237, 237, 237, 237, 237, 248, 248, 248), $Corrector->getCorrectedElevation() );
			} catch (\RuntimeException $Exception) {
				$this->markTestSkipped('Geonames.org was not able to handle the request. Exception occured: '.$Exception->getMessage());
			}
		} else {
			$this->markTestSkipped('Geonames.org was not available.');
		}
	}

}
