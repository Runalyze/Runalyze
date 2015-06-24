<?php
/**
 * This file contains class::Object
 * @package Runalyze\Model\Swim
 */

namespace Runalyze\Model\Swim;

use Runalyze\Model;

/**
 * Swim object
 *  
 * @author Hannes Christiansen & Michael Pohl
 * @package Runalyze\Model\Swim
 */

class Object extends Model\Object implements Model\Loopable {
	/**
	 * Key: activity id
	 * @var string
	 */
	const ACTIVITYID = 'activityid';
        
	/**
	 * Key: stroke
	 * @var string
	 */
	const STROKE = 'stroke';
        
	/**
	 * Key: stroketype
	 * @var string
	 */
	const STROKETYPE = 'stroketype';
        
        
	/**
	 * Stroke
	 * @return int
	 */
	public function stroke() {
		return $this->Data[self::STROKE];
	}
	/**
	 * STROKETYPE
	 * @return int
	 */
	public function strokeType() {
		return $this->Data[self::STROKETYPE];
	}
        
        
}