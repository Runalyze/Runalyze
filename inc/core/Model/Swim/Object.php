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
/**
 * Stroke Type
 * 0 =  freestyle (kraulen?)
 * 2 = breaststroke 
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
	 * Construct
	 * @param array $data
	 */
	public function __construct(array $data = array()) {
		parent::__construct($data);
	}
        
	/**
	 * All properties
	 * @return array
	 */
	static public function allProperties() {
		return array(
			self::ACTIVITYID,
			self::STROKE,
			self::STROKETYPE
		);
	}
        
	/**
	 * Can be null?
	 * @param string $key
	 * @return boolean
	 */
	protected function canBeNull($key) {
		switch ($key) {
                        case self::ACTIVITYID:
                        case self::STROKE:
                        case self::STROKETYPE:
				return true;
		}
		return false;
	}
        
	/**
	 * Is the property an array?
	 * @param string $key
	 * @return bool
	 */
	public function isArray($key) {
		return ($key != self::ACTIVITYID);
	}
        /**
	 * Properties
	 * @return array
	 */
	public function properties() {
		return static::allProperties();
	}
        
	/**
	 * Value at
	 * 
	 * Remark: This method may throw index offsets.
	 * @param int $index
	 * @param enum $key
	 * @return mixed
	 */
	public function at($index, $key) {
		return $this->Data[$key][$index];
	}
   
	/**
	 * STROKE
	 * @return int
	 */
	public function stroke() {
		return $this->Data[self::STROKE];
	}
        
	/**
	 * STROKETYPE
	 * @return int
	 */
	public function stroketype() {
		return $this->Data[self::STROKETYPE];
	}
        
        
	/**
	 * Number of points
	 * @return int
	 */
	public function num() {
		return $this->numberOfPoints;
	}
        
}