<?php
/**
 * This file contains class::General
 * @package Runalyze\Configuration\Category
 */

namespace Runalyze\Configuration\Category;

use Runalyze\Configuration\Fieldset;
use Runalyze\Parameter\SelectRow;
use Runalyze\Parameter\Application\Gender;
use Runalyze\Parameter\Application\HeartRateUnit;
use Ajax;

/**
 * Configuration category: General
 * @author Hannes Christiansen
 * @package Runalyze\Configuration\Category
 */
class General extends \Runalyze\Configuration\Category {
	/**
	 * Internal key
	 * @return string
	 */
	protected function key() {
		return 'general';
	}

	/**
	 * Create handles
	 */
	protected function createHandles() {
		$this->createGender();
		$this->createHeartRateUnit();
		$this->createMainSport();
		$this->createRunningSport();
		$this->createCompetitionType();
	}

	/**
	 * Create: GENDER
	 */
	protected function createGender() {
		$this->createHandle('GENDER', new Gender());
	}

	/**
	 * Gender
	 * @return Gender
	 */
	public function gender() {
		return $this->object('GENDER');
	}

	/**
	 * Create: HEART_RATE_UNIT
	 */
	protected function createHeartRateUnit() {
		$this->createHandle('HEART_RATE_UNIT', new HeartRateUnit());
	}

	/**
	 * Heart rate unit
	 * @return HeartRateUnit
	 */
	public function heartRateUnit() {
		return $this->object('HEART_RATE_UNIT');
	}

	/**
	 * Create: MAINSPORT
	 */
	protected function createMainSport() {
		$this->createHandle('MAINSPORT', new SelectRow(1, array(
			'table'			=> 'sport',
			'column'		=> 'name'
		)));
	}

	/**
	 * Main sport
	 * @return int
	 */
	public function mainSport() {
		return $this->get('MAINSPORT');
	}

	/**
	 * Create: RUNNINGSPORT
	 */
	protected function createRunningSport() {
		$this->createHandle('RUNNINGSPORT', new SelectRow(1, array(
			'table'			=> 'sport',
			'column'		=> 'name'
		)));
	}

	/**
	 * Running sport
	 * @return int
	 */
	public function runningSport() {
		return $this->get('RUNNINGSPORT');
	}

	/**
	 * Create: TYPE_ID_RACE
	 */
	protected function createCompetitionType() {
		$this->createHandle('TYPE_ID_RACE', new SelectRow(5, array(
			'table'			=> 'type',
			'column'		=> 'name'
		)));
	}

	/**
	 * Competition type
	 * @return int
	 */
	public function competitionType() {
		return $this->get('TYPE_ID_RACE');
	}

	/**
	 * Update competition type
	 * @param int $typeid
	 */
	public function updateCompetitionType($typeid) {
		$this->object('TYPE_ID_RACE')->set($typeid);
		$this->updateValue($this->handle('TYPE_ID_RACE'));
	}

	/**
	 * Register onchange events
	 */
	protected function registerOnchangeEvents() {
		$this->handle('GENDER')->registerOnchangeFlag(Ajax::$RELOAD_ALL);
		$this->handle('HEART_RATE_UNIT')->registerOnchangeFlag(Ajax::$RELOAD_DATABROWSER);
		$this->handle('MAINSPORT')->registerOnchangeFlag(Ajax::$RELOAD_PAGE);
		$this->handle('TYPE_ID_RACE')->registerOnchangeFlag(Ajax::$RELOAD_PLUGINS);
	}

	/**
	 * Fieldset
	 * @return \Runalyze\Configuration\Fieldset
	 */
	public function Fieldset() {
		$Fieldset = new Fieldset( __('General settings') );

		$Fieldset->addHandle( $this->handle('GENDER'), array(
			'label'		=> __('Gender')
		));

		$Fieldset->addHandle( $this->handle('HEART_RATE_UNIT'), array(
			'label'		=> __('Heart rate unit')
		));

		$Fieldset->addHandle( $this->handle('MAINSPORT'), array(
			'label'		=> __('Main sport')
		));

		return $Fieldset;
	}
}