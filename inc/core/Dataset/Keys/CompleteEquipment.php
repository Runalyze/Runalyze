<?php
/**
 * This file contains class::CompleteEquipment
 * @package Runalyze
 */

namespace Runalyze\Dataset\Keys;

use Runalyze\Dataset\Context;

/**
 * Dataset key: Complete Equipment
 * 
 * @author Hannes Christiansen
 * @package Runalyze\Dataset\Keys
 */
class CompleteEquipment extends AbstractEquipment
{
	/**
	 * Enum id
	 * @return int
	 */
	public function id()
	{
		return \Runalyze\Dataset\Keys::COMPLETE_EQUIPMENT;
	}

	/**
	 * @return string
	 * @codeCoverageIgnore
	 */
	public function label()
	{
		return __('Complete equipment');
	}

	/**
	 * @return string
	 * @codeCoverageIgnore
	 */
	public function shortLabel()
	{
		return '';
	}

	/**
	 * @return string
	 * @codeCoverageIgnore
	 */
	public function description()
	{
		return __('Your complete equipment is shown as icon with tooltip.');
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
			$names = array();

			foreach (array_unique($ids) as $id) {
				$names[] = $Factory->equipment($id)->name();
			}

			$Icon = new \Runalyze\View\Icon('fa-cubes');
			$Icon->setTooltip(implode(', ', $names));

			return $Icon->code();
		}

		return '';
	}
}