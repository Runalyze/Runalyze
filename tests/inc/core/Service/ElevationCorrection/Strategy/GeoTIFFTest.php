<?php

namespace Runalyze\Service\ElevationCorrection\Strategy;

use Runalyze\Model\Route;

class GeoTIFFwithoutSmoothing extends GeoTIFF
{
	/** @var int */
	protected $POINTS_TO_GROUP = 1;
}

/**
 * @group dependsOn
 * @group dependsOnSRTM
 */
class GeoTIFFTest extends \PHPUnit_Framework_TestCase
{

	public function testImpossibleData()
	{
		$FirstCorrector = new GeoTIFF(
			array(90),
			array(0.1)
		);

		$this->assertFalse( $FirstCorrector->canHandleData() );
	}

	public function testPossibleData()
	{
		if (file_exists(FRONTEND_PATH.'../data/srtm/srtm_38_03.tif')) {
			$Corrector = new GeoTIFF(
				array(49.444722),
				array(7.768889)
			);

			$this->assertTrue( $Corrector->canHandleData() );
			$Corrector->correctElevation();
			$this->assertEquals( array(239), $Corrector->getCorrectedElevation() );
		} else {
			$this->markTestSkipped('srtm_38_03.tif is not available.');
		}
	}

	public function testIgnoringEmptyPoints()
	{
		if (file_exists(FRONTEND_PATH.'../data/srtm/srtm_38_03.tif')) {
			$Route = new Route\Entity([]);
			$Route->setLatitudesLongitudes(
				[0.0, 49.444722, 0.0, 49.450, 0.0, 49.440, 49.441, 49.442, 0.0, 49.45, 49.444722, 0.0],
				[0.0, 7.768889, 0.0, 7.770, 0.0, 7.760, 7.761, 7.762, 0.0, 7.77, 7.768889, 0.0]
			);
			$coords = $Route->latitudesAndLongitudesFromGeohash();
			$Corrector = new GeoTIFFwithoutSmoothing($coords['lat'], $coords['lng']);

			$this->assertTrue( $Corrector->canHandleData() );
			$Corrector->correctElevation();
			$this->assertEquals( array(239, 239, 239, 258, 258, 240, 239, 240, 240, 258, 239, 239), $Corrector->getCorrectedElevation() );
		} else {
			$this->markTestSkipped('srtm_38_03.tif is not available.');
		}
	}

	public function testPossiblePath()
	{
		if (file_exists(FRONTEND_PATH.'../data/srtm/srtm_38_03.tif')) {
			$Corrector = new GeoTIFF(
				array(49.440, 49.441, 49.442, 49.443, 49.444, 49.445, 49.446, 49.447, 49.448, 49.449, 49.450),
				array(7.760, 7.761, 7.762, 7.763, 7.764, 7.765, 7.766, 7.767, 7.768, 7.769, 7.770)
			);

			$this->assertTrue( $Corrector->canHandleData() );
			$Corrector->correctElevation();

			$this->assertEquals( array(240.0, 240.0, 240.0, 240.0, 240.0, 239.0, 239.0, 239.0, 239.0, 239.0, 258.0), $Corrector->getCorrectedElevation() );
		} else {
			$this->markTestSkipped('srtm_38_03.tif is not available.');
		}
	}

	public function testSmoothedPath()
	{
		if (file_exists(FRONTEND_PATH.'../data/srtm/srtm_38_03.tif')) {
			$Corrector = new GeoTIFF(
				array(49.440, 49.441, 49.442, 49.443, 49.444, 49.445, 49.446, 49.447, 49.448, 49.449, 49.450),
				array(7.760, 7.761, 7.762, 7.763, 7.764, 7.765, 7.766, 7.767, 7.768, 7.769, 7.770)
			);

			$this->assertTrue( $Corrector->canHandleData() );
			$Corrector->setUseSmoothing(false);
			$Corrector->correctElevation();

			$this->assertEquals( array(240.0, 239.0, 240.0, 238.0, 238.0, 239.0, 236.0, 236.0, 240.0, 247.0, 258.0), $Corrector->getCorrectedElevation() );
		} else {
			$this->markTestSkipped('srtm_38_03.tif is not available.');
		}
	}

	public function testSydneyWithGuessingUnknown()
	{
		if (file_exists(FRONTEND_PATH.'../data/srtm/srtm_67_19.tif')) {
			$Corrector = new GeoTIFF(
				array(-33.8706555, -33.8705667, -33.8704860),
				array(151.1486918, 151.1486337, 151.1485585)
			);

			$this->assertTrue( $Corrector->canHandleData() );
			$Corrector->setGuessUnknown(true);
			$Corrector->setUseSmoothing(false);
			$Corrector->correctElevation();

			$this->assertEquals( array(5.0, 5.0, 5.0), $Corrector->getCorrectedElevation() );
		} else {
			$this->markTestSkipped('srtm_67_19.tif is not available.');
		}
	}

	public function testLondon()
	{
		if (file_exists(FRONTEND_PATH.'../data/srtm/srtm_36_02.tif')) {
			$Corrector = new GeoTIFF(
				array(51.5073509, 51.5074509, 51.5075509),
				array(-0.1277583, -0.1278583, -0.1279583)
			);

			$this->assertTrue( $Corrector->canHandleData() );
			$Corrector->setUseSmoothing(false);
			$Corrector->correctElevation();

			$this->assertEquals( array(18.0, 19.0, 19.0), $Corrector->getCorrectedElevation() );
		} else {
			$this->markTestSkipped('srtm_36_02.tif is not available.');
		}
	}

	public function testWindhoek()
	{
		if (file_exists(FRONTEND_PATH.'../data/srtm/srtm_40_17.tif')) {
			$Corrector = new GeoTIFF(
				array(-22.5700, -22.5705, -22.5710),
				array( 17.0836,  17.0841,  17.0846)
			);

			$this->assertTrue( $Corrector->canHandleData() );
			$Corrector->setUseSmoothing(false);
			$Corrector->correctElevation();

			$this->assertEquals( array(1666.0, 1669.0, 1671.0), $Corrector->getCorrectedElevation() );
		} else {
			$this->markTestSkipped('srtm_40_17.tif is not available.');
		}
	}

	public function testNewYork()
	{
		if (file_exists(FRONTEND_PATH.'../data/srtm/srtm_22_04.tif')) {
			$Corrector = new GeoTIFF(
				array( 40.7127,  40.7132,  40.7137),
				array(-74.0059, -74.0064, -74.0069)
			);

			$this->assertTrue( $Corrector->canHandleData() );
			$Corrector->setUseSmoothing(false);
			$Corrector->correctElevation();

			$this->assertEquals( array(22.0, 25.0, 42.0), $Corrector->getCorrectedElevation() );
		} else {
			$this->markTestSkipped('srtm_22_04.tif is not available.');
		}
	}

}
