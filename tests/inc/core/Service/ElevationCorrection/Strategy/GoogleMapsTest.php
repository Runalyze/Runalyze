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
		$corrector = new GoogleMaps(
			[49.444722],
			[7.768889]
		);

		if ($corrector->canHandleData()) {
		    try {
                $corrector->correctElevation();
                $this->assertEquals([237], $corrector->getCorrectedElevation());
            } catch (NoResponseException $e) {
                $this->markTestSkipped('Google Maps was not available.');
            } catch (OverQueryLimitException $e) {
                $this->markTestSkipped('Google Maps query limit reached.');
            }
		} else {
			$this->markTestSkipped('Google Maps was not available.');
		}
	}

	public function testSimplePath()
	{
		$corrector = new GoogleMaps(
			[49.440, 49.441, 49.442, 49.443, 49.444, 49.445, 49.446, 49.447, 49.448, 49.449, 49.450],
			[7.760, 7.761, 7.762, 7.763, 7.764, 7.765, 7.766, 7.767, 7.768, 7.769, 7.770]
		);

		if ($corrector->canHandleData()) {
            try {
			    $corrector->correctElevation();
			    $this->assertEquals(
			        [238, 238, 238, 238, 238, 237, 237, 237, 237, 237, 263],
                    $corrector->getCorrectedElevation()
                );
            } catch (NoResponseException $e) {
                $this->markTestSkipped('Google Maps was not available.');
            } catch (OverQueryLimitException $e) {
                $this->markTestSkipped('Google Maps query limit reached.');
            }
		} else {
			$this->markTestSkipped('Google Maps was not available.');
		}
	}

	public function testUnknown()
	{
		$corrector = new GoogleMaps(
			[0, 49.440, 49.441, 49.442, 49.443, 49.444, 49.445, 0, 0, 0, 0, 0, 0, 49.446, 49.447, 49.448, 49.449, 49.450],
			[0,  7.760,  7.761,  7.762,  7.763,  7.764,  7.765, 0, 0, 0, 0, 0, 0,  7.766,  7.767,  7.768,  7.769,  7.770]
		);

		if ($corrector->canHandleData()) {
            try {
    			$corrector->correctElevation();
	    		$this->assertEquals(
	    		    [238, 238, 238, 238, 238, 238, 238, 238, 238, 238, 238, 238, 238, 238, 238, 245, 245, 245],
                    $corrector->getCorrectedElevation()
                );
            } catch (NoResponseException $e) {
                $this->markTestSkipped('Google Maps was not available.');
            }
		} else {
			$this->markTestSkipped('Google Maps was not available.');
		}
	}
}
