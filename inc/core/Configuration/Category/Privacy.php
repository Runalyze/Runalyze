<?php
/**
 * This file contains class::Privacy
 * @package Runalyze\Configuration\Category
 */

namespace Runalyze\Configuration\Category;

use Runalyze\Configuration\Fieldset;
use Runalyze\Parameter\Boolean;
use Runalyze\Parameter\Application\ActivityRoutePrivacy;
use Ajax;

/**
 * Configuration category: Privacy
 * @author Hannes Christiansen
 * @package Runalyze\Configuration\Category
 */
class Privacy extends \Runalyze\Configuration\Category {
	/**
	 * Internal key
	 * @return string
	 */
	protected function key() {
		return 'privacy';
	}

	/**
	 * Create handles
	 */
	protected function createHandles() {
		$this->createHandle('TRAINING_MAKE_PUBLIC', new Boolean(false));
		$this->createHandle('TRAINING_LIST_PUBLIC', new Boolean(false));
		$this->createHandle('TRAINING_LIST_ALL', new Boolean(false));
		$this->createHandle('TRAINING_LIST_STATISTICS', new Boolean(false));
		$this->createHandle('TRAINING_MAP_PUBLIC_MODE', new ActivityRoutePrivacy());
	}

	/**
	 * Publish activity
	 * @return bool
	 */
	public function publishActivity() {
		return $this->get('TRAINING_MAKE_PUBLIC');
	}

	/**
	 * List is public
	 * @return bool
	 */
	public function listIsPublic() {
		return $this->get('TRAINING_LIST_PUBLIC');
	}

	/**
	 * Show private activities in list
	 * @return bool
	 */
	public function showPrivateActivitiesInList() {
		return $this->get('TRAINING_LIST_ALL');
	}

	/**
	 * Show statistics in list
	 * @return bool
	 */
	public function showStatisticsInList() {
		return $this->get('TRAINING_LIST_STATISTICS');
	}

	/**
	 * Route privacy
	 * @return ActivityRoutePrivacy
	 */
	public function RoutePrivacy() {
		return $this->object('TRAINING_MAP_PUBLIC_MODE');
	}

	/**
	 * Register onchange events
	 */
	protected function registerOnchangeEvents() {
		$this->handle('TRAINING_LIST_PUBLIC')->registerOnchangeFlag(Ajax::$RELOAD_DATABROWSER);
	}

	/**
	 * Fieldset
	 * @return \Runalyze\Configuration\Fieldset
	 */
	public function Fieldset() {
		$Fieldset = new Fieldset( __('Privacy') );

		$Fieldset->addHandle( $this->handle('TRAINING_MAKE_PUBLIC'), array(
			'label'		=> __('Publish activities'),
			'tooltip'	=> __('Automatically mark every activity after its creation as public.')
		));

		$Fieldset->addHandle( $this->handle('TRAINING_LIST_PUBLIC'), array(
			'label'		=> __('Public list: active'),
			'tooltip'	=> __('If activated: Everyone can see a list of all your (public) activities.')
		));

		$Fieldset->addHandle( $this->handle('TRAINING_LIST_ALL'), array(
			'label'		=> __('Public list: private workouts'),
			'tooltip'	=> __('If activated: Display a summary for each private activity in the public activity list.')
		));

		$Fieldset->addHandle( $this->handle('TRAINING_LIST_STATISTICS'), array(
			'label'		=> __('Public list: general statistics'),
			'tooltip'	=> __('Show some general statistics above the activity list')
		));

		$Fieldset->addHandle( $this->handle('TRAINING_MAP_PUBLIC_MODE'), array(
			'label'		=> __('Public view: show map'),
			'tooltip'	=> __('You can hide the map for the public view'),
		));

		return $Fieldset;
	}
}