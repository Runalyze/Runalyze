<?php
/**
 * This file contains class::Object
 * @package Runalyze\Model\Swimdata
 */

namespace Runalyze\Model\Swimdata;

use Runalyze\Model;

/**
 * Swimdata object
 *  
 * @author Hannes Christiansen & Michael Pohl
 * @package Runalyze\Model\Swimdata
 */
/**
 * Stroke Type
 * 0 =  freestyle (kraulen?)
 * 1 = 
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
	 * Key: pool length
	 * @var string
	 */
	const POOL_LENGTH = 'pool_length';
        
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
                $this->checkArraySizes();
	}
        
	/**
	 * All properties
	 * @return array
	 */
	static public function allProperties() {
		return array(
			self::ACTIVITYID,
			self::STROKE,
			self::STROKETYPE,
                        self::POOL_LENGTH
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
                        case self::POOL_LENGTH:
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
		return ($key != self::ACTIVITYID && $key != 'pool_length');
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
	 * STROKETYPE
	 * @return int
	 */
	public function poollength() {
		return $this->Data[self::POOL_LENGTH];
	}

        /*
         * Calculate Distance based on pool length
         */
        public function fillDistanceArray($trackdata) {
            return $trackdata;
        }
        
        /**
	 * Number of points
	 * @return int
	 */
	public function num() {
		return $this->numberOfPoints;
	}
        
       
}