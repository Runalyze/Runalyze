<?php

namespace Runalyze\Calculation\Route;

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
                $geohashes[] = '7zzzzzzzzzzz';
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
            if ($hash == '7zzzzzzzzzzz' || $last == $hash) {
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
}
