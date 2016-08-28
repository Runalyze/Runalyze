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
use Runalyze\Parameter\Application\DistanceUnitSystem;
use Runalyze\Parameter\Application\WeekStart;
use Runalyze\Parameter\Application\TemperatureUnit;
use Runalyze\Parameter\Application\WeightUnit;
use Runalyze\Parameter\Application\EnergyUnit;

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
                $this->createWeekStart();
		$this->createDistanceUnitSystem();
		$this->createWeightUnit();
		$this->createEnergyUnit();
                $this->createTemperatureUnit();
		$this->createHeartRateUnit();
		$this->createMainSport();
		$this->createRunningSport();
	}

	/**
	 * Create: GENDER
	 */
	protected function createGender() {
		$this->createHandle('GENDER', new Gender());
	}
	
	/**
	 * Create: Beginning of the week
	 */
	protected function createWeekStart() {
		$this->createHandle('WEEK_START', new WeekStart());
	}
        
	/**
	 * WeekStart
	 * @return \Runalyze\Parameter\Application\WeekStart
	 */
	public function weekStart() {
		return $this->object('WEEK_START');
	}
        
	/**
	 * Create: DISTANCE_UNIT_SYSTEM
	 */
	protected function createDistanceUnitSystem() {
		$this->createHandle('DISTANCE_UNIT_SYSTEM', new DistanceUnitSystem());
	}
	
	/**
	 * Unit system for distances
	 * @return \Runalyze\Parameter\Application\DistanceUnitSystem
	 */
	public function distanceUnitSystem() {
		return $this->object('DISTANCE_UNIT_SYSTEM');
	}

	/**
	 * Create: WeightUnit
	 */
	protected function createWeightUnit() {
		$this->createHandle('WEIGHT_UNIT', new WeightUnit());
	}
	
	/**
	 * weight Unit
	 * @return \Runalyze\Parameter\Application\WeightUnit
	 */
	public function weightUnit() {
		return $this->object('WEIGHT_UNIT');
	}
	
	/**
	 * Create: EnergyUnit
	 */
	protected function createEnergyUnit() {
		$this->createHandle('ENERGY_UNIT', new EnergyUnit());
	}
	
	
	/**
	 * energy Unit
	 * @return \Runalyze\Parameter\Application\EnergyUnit
	 */
	public function energyUnit() {
		return $this->object('ENERGY_UNIT');
	}

	/**
	 * Create: TemperatureUnit
	 */
	protected function createTemperatureUnit() {
		$this->createHandle('TEMPERATURE_UNIT', new TemperatureUnit());
	}
	
	/**
	 * temperature Unit
	 * @return \Runalyze\Parameter\Application\TemperatureUnit
	 */
	public function temperatureUnit() {
		return $this->object('TEMPERATURE_UNIT');
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
	 * Register onchange events
	 */
	protected function registerOnchangeEvents() {
		$this->handle('GENDER')->registerOnchangeFlag(Ajax::$RELOAD_ALL);
		$this->handle('DISTANCE_UNIT_SYSTEM')->registerOnchangeFlag(Ajax::$RELOAD_ALL);
		$this->handle('ENERGY_UNIT')->registerOnchangeFlag(Ajax::$RELOAD_ALL);
		$this->handle('DISTANCE_UNIT_SYSTEM')->registerOnchangeEvent('Runalyze\\Configuration\\Messages::adjustPacesInSportsConfiguration');
		$this->handle('WEIGHT_UNIT')->registerOnchangeFlag(Ajax::$RELOAD_PLUGINS);
                $this->handle('TEMPERATURE_UNIT')->registerOnchangeFlag(Ajax::$RELOAD_DATABROWSER);
		$this->handle('HEART_RATE_UNIT')->registerOnchangeFlag(Ajax::$RELOAD_DATABROWSER);
                $this->handle('WEEK_START')->registerOnchangeFlag(Ajax::$RELOAD_PAGE);
		$this->handle('MAINSPORT')->registerOnchangeFlag(Ajax::$RELOAD_PAGE);
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
                
		$Fieldset->addHandle( $this->handle('WEEK_START'), array(
			'label'		=> __('Beginning of the week')
		));

		$Fieldset->addHandle( $this->handle('DISTANCE_UNIT_SYSTEM'), array(
			'label'		=> __('Unit system for distances'),
			'tooltip'	=> __('Changing the unit system for distances does not change pace units. You have to adjust them in sports configuration.')
		));

		$Fieldset->addHandle( $this->handle('WEIGHT_UNIT'), array(
			'label'		=> __('Weight unit')
		));
		
		$Fieldset->addHandle( $this->handle('ENERGY_UNIT'), array(
			'label'		=> __('Energy unit')
		));	
                
		$Fieldset->addHandle( $this->handle('TEMPERATURE_UNIT'), array(
			'label'		=> __('Temperature unit')
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
