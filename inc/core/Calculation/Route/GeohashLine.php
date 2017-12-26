<?php

namespace Runalyze\Calculation\Route;

use League\Geotools\Coordinate\Coordinate;
use League\Geotools\Geohash\Geohash;

class GeohashLine
{
    /**
     * @param array $geolineHashes
     * @return array
     */
    public static function extend(array $geolineHashes)
    {
        $geohashes = array();

        foreach ($geolineHashes as $hash) {
            if (empty($hash)) {
                $geohashes[] = end($geohashes) ?: '7zzzzzzzzzzz';
            } else {
                $geohashes[] = substr(end($geohashes), 0, 12 - strlen($hash)).$hash;
            }
        }

        return $geohashes;
    }

    /**
     * @param array $geolineHashes
     * @return array
     */
    public static function shorten(array $geolineHashes)
    {
        $last = '';
        $newgeoline = array();

        foreach ($geolineHashes as $hash) {
            if ($last == $hash) {
                $newgeoline[] = '';
            } elseif (empty($newgeoline)) {
                $newgeoline[] = $hash;
            } else {
                $newgeoline[] = substr($hash, strspn($last ^ $hash, "\0"));
            }

            $last = $hash;
        }

        return $newgeoline;
    }

    /**
     * @param array $geohashes
     * @return null|string
     */
    public static function findFirstNonNullGeohash(array $geohashes, $precision)
    {
        $nullGeohash = (new Geohash())->encode(new Coordinate([0.0, 0.0]), $precision)->getGeohash();

        foreach ($geohashes as $geohash) {
            $geohash = substr($geohash, 0, $precision);

            if ($geohash != $nullGeohash) {
                return $geohash;
            }
        }

        return null;
    }
}
