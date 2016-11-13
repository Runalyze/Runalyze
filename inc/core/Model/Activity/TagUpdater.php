<?php
/**
 * This file contains class::TagUpdater
 * @package Runalyze\Model\Activity
 */

namespace Runalyze\Model\Activity;

/**
 * Update relations between activity and tag
 * 
 * @author Hannes Christiansen
 * @package Runalyze\Model\Activity
 */
class TagUpdater extends \Runalyze\Model\RelationUpdater {
	/**
	 * Tablename without prefix
	 * @return string
	 */
	protected function table() {
		return 'activity_tag';
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
		return 'tagid';
	}

}
