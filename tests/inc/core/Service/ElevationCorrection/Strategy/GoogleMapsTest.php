<?php

namespace Runalyze\Service\ElevationCorrection\Strategy;

/**
 * @group dependsOn
 * @group dependsOnService
 * @group dependsOnGoogleMaps
 */
class GoogleMapsTest extends \PHPUnit_Framework_TestCase
{

	public function testSimpleData()
	{
		$Corrector = new GoogleMaps(
			array(49.444722),
			array(7.768889)
		);

		if ($Corrector->canHandleData()) {
			$Corrector->correctElevation();
			$this->assertEquals( array(237), $Corrector->getCorrectedElevation() );
		} else {
			$this->markTestSkipped('Google Maps was not available.');
		}
	}

	public function testSimplePath()
	{
		$Corrector = new GoogleMaps(
			array(49.440, 49.441, 49.442, 49.443, 49.444, 49.445, 49.446, 49.447, 49.448, 49.449, 49.450),
			array(7.760, 7.761, 7.762, 7.763, 7.764, 7.765, 7.766, 7.767, 7.768, 7.769, 7.770)
		);

		if ($Corrector->canHandleData()) {
			$Corrector->correctElevation();
			$this->assertEquals( array(238, 238, 238, 238, 238, 237, 237, 237, 237, 237, 263), $Corrector->getCorrectedElevation() );
		} else {
			$this->markTestSkipped('Google Maps was not available.');
		}
	}

	public function testUnknown()
	{
		$Corrector = new GoogleMaps(
			array( 0, 49.440, 49.441, 49.442, 49.443, 49.444, 49.445, 0, 0, 0, 0, 0, 0, 49.446, 49.447, 49.448, 49.449, 49.450),
			array( 0,  7.760,  7.761,  7.762,  7.763,  7.764,  7.765, 0, 0, 0, 0, 0, 0,  7.766,  7.767,  7.768,  7.769,  7.770)
		);

		if ($Corrector->canHandleData()) {
			$Corrector->correctElevation();
			$this->assertEquals( array(238, 238, 238, 238, 238, 238, 238, 238, 238, 238, 238, 238, 238, 238, 238, 245, 245, 245), $Corrector->getCorrectedElevation() );
		} else {
			$this->markTestSkipped('Google Maps was not available.');
		}
	}
}
