<?php
/**
 * This file contains class::EquipmentFactory
 * @package Runalyze\Data\Equipment
 */
/**
 * Factory serving static methods for equipment
 *
 * @author Hannes Christiansen & Michael Pohl
 * @package Runalyze\Data\Equipment
 */
class EquipmentFactory {
    
        /**
         * @var string
         */
        const CACHE_KEY_EQ = 'equipment';
        
        /**
         * @var string
         */
        const CACHE_KEY_EQT = 'equipmentType';
        
        /*
         * @var int
         */
        const TYPE_INPUT_SINGLE = 0;
        
        /*
         * @var int
         */
        const TYPE_INPUT_CHOICE = 1;
             
        
	/**
	 * All clothes as array
	 * @var array
	 */
	static private $AllTypes = null;
        
	/**
	 * All clothes as array
	 * @var array
	 */
	static private $AllEquipment = null;

	/**
	 * Get all equipment types/categories
	 * @return array
	 */
	static public function AllEquipmentTypes() {
		if (is_null(self::$AllTypes))
			self::initAllTypes();

		return self::$AllTypes;
	}
        
	/**
	 * Get all equipment types/categories
	 * @return array
	 */
	static public function AllEquipment() {
		if (is_null(self::$AllEquipment))
			self::initAllEquipment();

		return self::$AllEquipment;
	}
        
        
	/**
	 * Init all types
	 */
	static private function initAllTypes() {
		self::$AllTypes = array();
                 
		$eqtypes = self::cacheAllEquipmentTypes();
		foreach ($eqtypes as $data)
			self::$AllTypes[$data['id']] = $data;
	}   
        
	/**
	 * Init all types
	 */
	static private function initAllEquipment() {
		self::$AllEquipment = array();

		$equipment = self::cacheAllEquipment();
		foreach ($equipment as $data)
			self::$AllEquipment[$data['id']] = $data;
	}   
        
	/**
	 * Cache equipment type
	 */
	static private function cacheAllEquipment() {
		$equipment = Cache::get(self::CACHE_KEY_EQ);
		if (is_null($equipment)) {
			$equipment = DB::getInstance()->query('SELECT id, name, typeid, notes, distance, time, additional_km, date_start, date_end FROM `'.PREFIX.'equipment` WHERE accountid = '.SessionAccountHandler::getId().' ORDER BY typeid ASC')->fetchAll();
			Cache::set(self::CACHE_KEY_EQ, $equipment, '3600');
		}
		return $equipment;
	}
        
	/**
	 * Cache Clothes
	 */
	static private function cacheAllEquipmentTypes() {
		$equipmenttype = Cache::get(self::CACHE_KEY_EQT);
		if (is_null($equipmenttype)) {
			$equipmenttype = DB::getInstance()->query('SELECT id, name, input, max_km, max_time FROM `'.PREFIX.'equipment_type` WHERE accountid = '.SessionAccountHandler::getId())->fetchAll();
			Cache::set(self::CACHE_KEY_EQT, $equipmenttype, '3600');
		}
		return $equipmenttype;
	}
}