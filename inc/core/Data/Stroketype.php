<?php
/**
 * This file contains class::Stroketype
 * @package Runalyze\Data
 */
namespace Runalyze\Data;

/**
 * Weather
 * 
 * @author Michael Pohl
 * @package Runalyze\Data
 */
class Stroketype {
	/**
	 * @var int
	 */
	const FREESTYLE = 0;
        
	/**
	 * @var int
	 */
	const BACK = 1;
 
	/**
	 * @var int
	 */
	const BREAST = 2;
        
        /**
         * @var int
         */
        const BUTTERFLY = 3;
        
        /**
         * @var int
         */
        const DRILL = 4;
        
        /**
         * @var int
         */
        const MIXED = 5;
    
	/**
	 * Identifier
	 * @var int
	 */
	protected $identifier;
        
	/**
	 * Complete list
	 * @return array
	 */
	public static function completeList() {
		return array(
			self::FREESTYLE,
			self::BACK,
			self::BREAST,
			self::BUTTERFLY,
                        self::DRILL,
                        self::MIXED
		);
	}
        
	/**
	 * Stroketype 
	 * @param int $identifier a class constant
	 */
	public function __construct($identifier) {
		$this->set($identifier);
	}
	/**
	 * Set
	 * @param int $identifier a class constant
	 */
	public function set($identifier) {
		if (in_array($identifier, self::completeList())) {
			$this->identifier = $identifier;
		}
	}

	/**
	 * Identifier
	 * @return int
	 */
	public function id() {
		return $this->identifier;
	}
        
	/**
	 * String
	 * @return string
	 */
	public function string() {
		switch ($this->identifier) {
			case self::FREESTYLE:
				return __('Freestyle');
			case self::BACK:
				return __('Backstroke');
			case self::BREAST:
				return __('Breaststroke');
			case self::BUTTERFLY:
				return __('Butterfly');
                        case self::DRILL:
				return __('Drill');
                        case self::MIXED:
				return __('Mixed');   
		}

		return '';
	}
        
	/**
	 * ShortString
	 * @return string
	 */
	public function shortstring() {
		switch ($this->identifier) {
			case self::FREESTYLE:
				return __('Freestyle');
			case self::BACK:
				return __('Back');
			case self::BREAST:
				return __('Breast');
			case self::BUTTERFLY:
				return __('Butterfly');
                        case self::DRILL:
				return __('Drill');
                        case self::MIXED:
				return __('Mixed');   
		}

		return '';
	}
}

