<?php

namespace Runalyze\Calculation\Route;

class GeohashLine
{
    public static function extend($geolineHashes) {
        $geohashes = array();
        foreach ($geolineHashes as $hash) {
            if (empty($hash)) {
                $geohashes[] = end($geohashes);
            } else {
                $newhash = substr(end($geohashes),0, 12-strlen($hash)).$hash;
                $geohashes[] = $newhash;
            }
        }
        return $geohashes;
    }

    public static function shorten($geolineHashes) {
        $newgeoline = array();
        foreach($geolineHashes as $hash) {
            if ($hash == '7zzzzzzzzzzz') {
                $newgeoline[] = '';
                $last = '';
            } elseif (empty($newgeoline)) {
                $newgeoline[] = $hash;
                $last = $hash;
            } elseif ($last == $hash) {
                $newgeoline[] = '';
                $last = '';
            } else {
                $pos = strspn($last ^ $hash, "\0");
                $newgeoline[] = substr($hash,$pos);
            }
            $last = $hash;
        }
        return $newgeoline;
    }
}
