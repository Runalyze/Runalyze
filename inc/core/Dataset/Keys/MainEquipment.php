<?php
/**
 * This file contains class::MainEquipment
 * @package Runalyze
 */

namespace Runalyze\Dataset\Keys;

use Runalyze\Dataset\Context;

/**
 * Dataset key: Main Equipment
 * 
 * @author Hannes Christiansen
 * @package Runalyze\Dataset\Keys
 */
class MainEquipment extends AbstractEquipment
{
	/**
	 * Enum id
	 * @return int
	 */
	public function id()
	{
		return \Runalyze\Dataset\Keys::MAIN_EQUIPMENT;
	}

	/**
	 * @return string
	 * @codeCoverageIgnore
	 */
	public function label()
	{
		return __('Main Equipment');
	}

	/**
	 * @return string
	 * @codeCoverageIgnore
	 */
	public function shortLabel()
	{
		return __('Equipment');
	}

	/**
	 * @return string
	 * @codeCoverageIgnore
	 */
	public function description()
	{
		return __('You can choose a main equipment type for each sport in your sport configuration. '.
				'Equipment objects of this type will be shown with their names.');
	}

	/**
	 * Get string to display this dataset value
	 * @param \Runalyze\Dataset\Context $context
	 * @return string
	 */
	public function stringFor(Context $context)
	{
		if ($context->hasData(parent::CONCAT_EQUIPMENT_KEY) && $context->data(parent::CONCAT_EQUIPMENT_KEY) != '') {
			$ids = explode(',', $context->data(parent::CONCAT_EQUIPMENT_KEY));
			$Factory = new \Runalyze\Model\Factory(\SessionAccountHandler::getId());
			$mainTypeID = $context->sport()->mainEquipmentTypeID();
			$names = array();

			foreach (array_unique($ids) as $id) {
				$Equipment = $Factory->equipment($id);

				if ($Equipment->typeid() == $mainTypeID) {
					$names[] = $Factory->equipment($id)->name();
				}
			}

			return implode(', ', $names);
		}

		return '';
	}

	/**
	 * @return string
	 * @codeCoverageIgnore
	 */
	public function cssClass()
	{
		return 'small nowrap';
	}
}
