<?php
/**
 * This file contains class::EquipmentUpdater
 * @package Runalyze\Model\Activity
 */

namespace Runalyze\Model\Activity;

/**
 * Update relations between activity and equipment
 * 
 * @author Hannes Christiansen
 * @package Runalyze\Model\Activity
 */
class EquipmentUpdater extends \Runalyze\Model\RelationUpdater {
	/**
	 * @var \Runalyze\Model\Activity\Entity
	 */
	protected $NewActivity = null;

	/**
	 * @var \Runalyze\Model\Activity\Entity
	 */
	protected $OldActivity = null;

	/**
	 * Set related activity objects
	 * 
	 * These objects are needed to keep equipment statistics up to date
	 * 
	 * @param \Runalyze\Model\Activity\Entity $newActivity
	 * @param \Runalyze\Model\Activity\Entity $oldActivity
	 */
	public function setActivityObjects(Entity $newActivity, Entity $oldActivity = null) {
		$this->NewActivity = $newActivity;
		$this->OldActivity = $oldActivity;
	}

	/**
	 * Tablename without prefix
	 * @return string
	 */
	protected function table() {
		return 'activity_equipment';
	}

	/**
	 * Column name for 'this' object
	 * @return string
	 */
	protected function selfColumn() {
		return 'activityid';
	}

	/**
	 * Column name for 'related' objects
	 * @return string
	 */
	protected function otherColumn() {
		return 'equipmentid';
	}

	/**
	 * Tasks to run before update
	 */
	protected function beforeUpdate() {
		if (null !== $this->OldActivity) {
			$this->updateEquipment(
				array_diff($this->OtherIDsOld, $this->OtherIDsNew),
				-$this->OldActivity->distance(),
				-$this->OldActivity->duration()
			);
		}
	}

	/**
	 * Tasks to run after update
	 */
	protected function afterUpdate() {
		if (null !== $this->NewActivity) {
			$this->updateEquipment(
				array_diff($this->OtherIDsNew, $this->OtherIDsOld),
				$this->NewActivity->distance(),
				$this->NewActivity->duration()
			);
		}

		if (null !== $this->NewActivity && null !== $this->OldActivity) {
			$this->updateEquipment(
				array_intersect($this->OtherIDsNew, $this->OtherIDsOld),
				$this->NewActivity->distance() - $this->OldActivity->distance(),
				$this->NewActivity->duration() - $this->OldActivity->duration()
			);
		}
	}

	/**
	 * Update equipment
	 * @param array $ids
	 * @param float $addDistance can be negative
	 * @param int $addTime can be negative
	 */
	protected function updateEquipment(array $ids, $addDistance, $addTime) {
		if (!empty($ids) && ($addDistance != 0 || $addTime != 0)) {
			$this->PDO->exec(
				'UPDATE `'.PREFIX.'equipment`
				SET
					`distance` = `distance` + '.(float)$addDistance.',
					`time` = `time` + '.(float)$addTime.'
				WHERE '.$this->where($ids)
			);
		}
	}

	/**
	 * Where condition for ids
	 * @param array $ids
	 * @return string
	 */
	protected function where(array $ids) {
		$string = '';

		foreach ($ids as $id) {
			if (empty($string)) {
				$string = '`id`='.(int)$id;
			} else {
				$string .= ' OR `id`='.(int)$id;
			}
		}

		return $string;
	}
}