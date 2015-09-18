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
	 * Number of equipment
	 * @return int
	 */
	static public function numberOfEquipment()
	{
		return count(self::AllEquipment());
	}
        
	/**
	 * Get all equipment types/categories
	 * @return array
	 */
	static public function AllTypes() {
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
                 
		$eqtypes = self::cacheAllTypes();
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
	 * Reinit all equipment
	 */
	static public function reInitAllEquipment() {
		Cache::delete(self::CACHE_KEY_EQ);
		self::initAllEquipment();
	}
        
       /**
	 * Reinit all equipment
	 */
	static public function reInitAllTypes() {
		Cache::delete(self::CACHE_KEY_EQT);
		self::initAllTypes();
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
	static private function cacheAllTypes() {
		$equipmenttype = Cache::get(self::CACHE_KEY_EQT);
		if (is_null($equipmenttype)) {
			$equipmenttype = DB::getInstance()->query('SELECT id, name, input, max_km, max_time FROM `'.PREFIX.'equipment_type` WHERE accountid = '.SessionAccountHandler::getId())->fetchAll();
			Cache::set(self::CACHE_KEY_EQT, $equipmenttype, '3600');
		}
		return $equipmenttype;
	}

	/**
	 * Clear internal array
	 */
	static private function clearAllEquipment()
	{
		self::$AllEquipment = null;
	}   
        
	/**
	 * Clear internal array
	 */
	static private function clearAllEquipmentType()
	{
		self::$AllEquipment = null;
	}  
        
	/**
	 * Recalculate all equipment
	 *
	 * Be sure that a complete recalculation is really needed.
	 * This task may take very long.
	 */
	static public function recalculateAllEquipment()
	{
		DB::getInstance()->exec(
			'UPDATE `'.PREFIX.'equipment`
			CROSS JOIN(
				SELECT
					`eqp`.`id` AS `eqpid`,
					SUM(`tr`.`distance`) AS `km`,
					SUM(`tr`.`s`) AS `s` 
				FROM `'.PREFIX.'equipment` AS `eqp` 
				LEFT JOIN `'.PREFIX.'activity_equipment` AS `aeqp` ON `eqp`.`id` = `aeqp`.`equipmentid` 
				LEFT JOIN `'.PREFIX.'training` AS `tr` ON `aeqp`.`activityid` = `tr`.`id`
				WHERE `eqp`.`accountid` = '.SessionAccountHandler::getId().'
				GROUP BY `eqp`.`id`
			) AS `new`
			SET
				`distance` = IFNULL(`new`.`km`, 0),
				`time` = IFNULL(`new`.`s`, 0)
			WHERE `id` = `new`.`eqpid`');

		self::clearAllEquipment();
		Cache::delete(self::CACHE_KEY_EQ);
	}

        /**
         * Get equipment array for formular
         */
        static public function getEquipmentforFormular() {
                $Statement = DB::getInstance()->query('SELECT eq.id, eq.name, eqt.id as `typeid`, eqt.name as `typename`, eqt.input as `typeinput`, eqs.sportid from `'.PREFIX.'equipment` eq LEFT JOIN `'.PREFIX.'equipment_type` eqt ON eq.typeid = eqt.id LEFT JOIN `'.PREFIX.'equipment_sport` eqs ON eqt.id = eqs.equipment_typeid WHERE eq.accountid = '.SessionAccountHandler::getId());
                $equipment = $Statement->fetchAll();
                $formdata = array();
                foreach($equipment as $eqt) {
                    $formdata[$eqt['sportid']][$eqt['typename']] = $eqt['typeinput'];
                }

                return $formdata;
        }
	/**
	 * Get search-link for one ID
	 * @param int $id
	 * @return string
	 */
	static public function getSearchLinkForSingleEquipment($id) {
		return SearchLink::to('equipmentid', $id, self::NameFor($id));
	}
        
}