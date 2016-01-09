<?php
/**
 * This file contains class::CompletorForTime
 * @package Runalyze\Model\Activity\Splits
 */

namespace Runalyze\Model\Activity\Splits;

/**
 * Complete splits: fill missing times
 * 
 * @author Hannes Christiansen
 * @package Runalyze\Model\Activity\Splits
 */
class CompletorForTime extends Completor {
	/**
	 * Mode
	 * @return string
	 */
	public function mode() {
		return parent::MODE_TIME;
	}
}