<?php
/**
 * This file contains class::MaximalHeartRateInBPM
 * @package Runalyze\View\Activity\Box
 */

namespace Runalyze\View\Activity\Box;

use Runalyze\Activity\HeartRate;

/**
 * Boxed value for maximal heart rate
 * 
 * @author Hannes Christiansen
 * @package Runalyze\View\Activity\Box
 */
class MaximalHeartRateInBPM extends MaximalHeartRate
{
    /**
     * @param \Runalyze\Activity\HeartRate $heartRate
     * @return string
     */
    protected function value(HeartRate $heartRate)
	{
        if ($heartRate->inBPM() > 0) {
            return $heartRate->inBPM();
        }

        return '-';
	}

    /**
     * @return string
     */
    protected function unit()
	{
        return 'bpm';
	}
}
