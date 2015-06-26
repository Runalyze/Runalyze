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
	 * Key: time
	 * @var string
	 */
	const SWIMTIME = 'swimtime';
        
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
	 * Key: swim cadence
	 * @var string
	 */
	const STROKETYPE = 'swimcadence';
        
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
                        self::SWIMTIME,
			self::ACTIVITYID,
			self::STROKE,
			self::STROKETYPE,
                        self::SWIMCADENCE
		);
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
	 * SWIMTIME
	 * @return int
	 */
	public function swimtime() {
		return $this->Data[self::SWIMTIME];
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
	 * SWIMCADENCE
	 * @return int
	 */
	public function swimcadence() {
		return $this->Data[self::SWIMCADENCE];
	}
        
	/**
	 * Number of points
	 * @return int
	 */
	public function num() {
		return $this->numberOfPoints;
	}
        
}