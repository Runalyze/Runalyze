<?php

namespace Runalyze\Tests\Service\ElevationCorrection\Strategy;

use Runalyze\DEM\Interpolation\BilinearInterpolation;
use Runalyze\DEM\Provider\GeoTIFF\SRTM4Provider;
use Runalyze\DEM\Reader;
use Runalyze\Service\ElevationCorrection\Strategy\GeoTiff;

/**
 * @group dependsOn
 * @group dependsOnSRTM
 */
class GeoTiffTest extends \PHPUnit_Framework_TestCase
{
    /** @var GeoTiff */
    protected $GeoTiff;

    /** @var string */
    protected $SrtmRoot;

    protected function setUp()
    {
        $this->SrtmRoot = TESTS_ROOT.'/../data/srtm';
        $this->GeoTiff = new GeoTiff(
            new Reader(
                new SRTM4Provider(
                    $this->SrtmRoot,
                    new BilinearInterpolation()
                )
            )
        );
        $this->GeoTiff->setPointsToGroup(1);
    }

    /**
     * @param string $srtmFile
     *
     * @return bool
     */
    protected function checkExistenceOf($srtmFile)
    {
        $result = file_exists($this->SrtmRoot.'/'.$srtmFile);

        if (false === $result) {
            $this->markTestSkipped(sprintf('%s is not available for testing.', $srtmFile));
        }

        return $result;
    }

    public function testImpossibleData()
    {
        $this->assertFalse($this->GeoTiff->canHandleData([90.0], [0.1]));
        $this->assertNull($this->GeoTiff->loadAltitudeData([90.0], [0.1]));
    }

    public function testPossibleData()
    {
        if ($this->checkExistenceOf('srtm_38_03.tif')) {
            $this->assertEquals(
                [239],
                $this->GeoTiff->loadAltitudeData(
                    [49.444722],
                    [7.768889]
                )
            );
        }
    }

    public function testIgnoringEmptyPoints()
    {
        if ($this->checkExistenceOf('srtm_38_03.tif')) {
            $this->assertEquals(
                [239, 239, 239, 258, 258, 240, 239, 240, 240, 258, 239, 239],
                $this->GeoTiff->loadAltitudeData(
                    [0.0, 49.444722, 0.0, 49.450, 0.0, 49.440, 49.441, 49.442, 0.0, 49.45, 49.444722, 0.0],
                    [0.0, 7.768889, 0.0, 7.770, 0.0, 7.760, 7.761, 7.762, 0.0, 7.77, 7.768889, 0.0]
                )
            );
        }
    }

    public function testSmoothedPath()
    {
        if ($this->checkExistenceOf('srtm_38_03.tif')) {
            $this->GeoTiff->setUseSmoothing(false);

            $this->assertEquals(
                [240, 239, 240, 238, 238, 239, 236, 236, 240, 247, 258],
                $this->GeoTiff->loadAltitudeData(
                    [49.440, 49.441, 49.442, 49.443, 49.444, 49.445, 49.446, 49.447, 49.448, 49.449, 49.450],
                    [7.760, 7.761, 7.762, 7.763, 7.764, 7.765, 7.766, 7.767, 7.768, 7.769, 7.770]
                )
            );
        }
    }

    public function testSydneyWithGuessingUnknown()
    {
        if ($this->checkExistenceOf('srtm_67_19.tif')) {
            $this->GeoTiff->setUseSmoothing(false);

            $this->assertEquals(
                [5, 5, 5],
                $this->GeoTiff->loadAltitudeData(
                    [-33.8706555, -33.8705667, -33.8704860],
                    [151.1486918, 151.1486337, 151.1485585]
                )
            );
        }
    }

    public function testLondon()
    {
        if ($this->checkExistenceOf('srtm_36_02.tif')) {
            $this->GeoTiff->setUseSmoothing(false);

            $this->assertEquals(
                [18, 19, 19],
                $this->GeoTiff->loadAltitudeData(
                    [51.5073509, 51.5074509, 51.5075509],
                    [-0.1277583, -0.1278583, -0.1279583]
                )
            );
        }
    }

    public function testWindhoek()
    {
        if ($this->checkExistenceOf('srtm_36_02.tif')) {
            $this->GeoTiff->setUseSmoothing(false);

            $this->assertEquals(
                [1666, 1669, 1671],
                $this->GeoTiff->loadAltitudeData(
                    [-22.5700, -22.5705, -22.5710],
                    [17.0836, 17.0841, 17.0846]
                )
            );
        }
    }

    public function testNewYork()
    {
        if ($this->checkExistenceOf('srtm_36_02.tif')) {
            $this->GeoTiff->setUseSmoothing(false);

            $this->assertEquals(
                [22, 25, 42],
                $this->GeoTiff->loadAltitudeData(
                    [40.7127, 40.7132, 40.7137],
                    [-74.0059, -74.0064, -74.0069]
                )
            );
        }
    }
}
