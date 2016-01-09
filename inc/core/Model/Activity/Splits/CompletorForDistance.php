<?php
/**
 * This file contains class::CompletorForDistance
 * @package Runalyze\Model\Activity\Splits
 */

namespace Runalyze\Model\Activity\Splits;

/**
 * Complete splits: fill missing distances
 * 
 * @author Hannes Christiansen
 * @package Runalyze\Model\Activity\Splits
 */
class CompletorForDistance extends Completor {
	/**
	 * Mode
	 * @return string
	 */
	public function mode() {
		return parent::MODE_DISTANCE;
	}
}