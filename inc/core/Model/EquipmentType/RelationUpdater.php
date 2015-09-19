<?php
/**
 * This file contains class::RelationUpdater
 * @package Runalyze\Model\EquipmentType
 */

namespace Runalyze\Model\EquipmentType;

/**
 * Update relations between equipment type and sport
 * 
 * @author Hannes Christiansen
 * @package Runalyze\Model\EquipmentType
 */
class RelationUpdater extends \Runalyze\Model\RelationUpdater {
	/**
	 * Tablename without prefix
	 * @return string
	 */
	protected function table() {
		return 'equipment_sport';
	}

	/**
	 * Column name for 'this' object
	 * @return string
	 */
	protected function selfColumn() {
		return 'equipment_typeid';
	}

	/**
	 * Column name for 'related' objects
	 * @return string
	 */
	protected function otherColumn() {
		return 'sportid';
	}
}