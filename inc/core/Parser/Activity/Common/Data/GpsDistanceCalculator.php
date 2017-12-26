<?php

namespace Runalyze\Parser\Activity\Common\Data;

class GpsDistanceCalculator
{
    public function calculateDistancesFor(ContinuousData $data)
    {
        $numPoints = count($data->Latitude);
        $data->Distance[0] = 0;

        for ($i = 1; $i < $numPoints; ++$i) {
            $data->Distance[$i] = $data->Distance[$i - 1] + self::gpsDistance(
                $data->Latitude[$i - 1],
                $data->Longitude[$i - 1],
                $data->Latitude[$i],
                $data->Longitude[$i]
            );
        }
    }

    /**
     * @param float $lat1
     * @param float $lon1
     * @param float $lat2
     * @param float $lon2
     * @return float
     */
    public static function gpsDistance($lat1, $lon1, $lat2, $lon2)
    {
		$rad1 = deg2rad($lat1);
		$rad2 = deg2rad($lat2);
		$dist = sin($rad1) * sin($rad2) +  cos($rad1) * cos($rad2) * cos(deg2rad($lon1 - $lon2));
		$dist = acos($dist);
		$dist = rad2deg($dist);
		$miles = $dist * 60 * 1.1515;

		if (is_nan($miles)) {
			return 0.0;
        }

		return ($miles * 1.609344);
	}
}
