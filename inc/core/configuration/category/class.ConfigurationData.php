<?php
/**
 * This file contains class::ConfigurationData
 * @package Runalyze\Configuration\Category
 */
/**
 * Configuration category: Data
 * @author Hannes Christiansen
 * @package Runalyze\Configuration\Category
 */
class ConfigurationData extends ConfigurationCategory {
	/**
	 * Internal key
	 * @return string
	 */
	protected function key() {
		return 'data';
	}

	/**
	 * Create handles
	 */
	protected function createHandles() {
		$this->createHandle('START_TIME', new ParameterInt(0));

		$this->createHandle('HF_MAX', new ParameterInt(200));
		$this->createHandle('HF_REST', new ParameterInt(60));

		$this->createHandle('VDOT_FORM', new ParameterFloat(0.0));
		$this->createHandle('VDOT_CORRECTOR', new ParameterFloat(1.0));

		$this->createHandle('BASIC_ENDURANCE', new ParameterInt(0));

		$this->createHandle('MAX_ATL', new ParameterInt(0));
		$this->createHandle('MAX_CTL', new ParameterInt(0));
		$this->createHandle('MAX_TRIMP', new ParameterInt(0));
	}

	/**
	 * Starttime
	 * @return int
	 */
	public function startTime() {
		return $this->get('START_TIME');
	}

	/**
	 * Update starttime
	 * @param int $starttime timestamp
	 */
	public function updateStartTime($starttime) {
		$this->object('START_TIME')->set($starttime);
		$this->updateValue( $this->handle('START_TIME') );
	}

	/**
	 * Maximal heart rate
	 * @return int
	 */
	public function HRmax() {
		return $this->get('HF_MAX');
	}

	/**
	 * Update maximal heart rate
	 * @param int $heartrate in [bpm]
	 */
	public function updateHRmax($heartrate) {
		$this->object('HF_MAX')->set($heartrate);
		$this->updateValue( $this->handle('HF_MAX') );
	}

	/**
	 * Resting heart rate
	 * @return int
	 */
	public function HRrest() {
		return $this->get('HF_REST');
	}

	/**
	 * Update resting heart rate
	 * @param int $heartrate in [bpm]
	 */
	public function updateHRrest($heartrate) {
		$this->object('HF_REST')->set($heartrate);
		$this->updateValue( $this->handle('HF_REST') );
	}

	/**
	 * Vdot shape
	 * @return float
	 */
	public function vdotShape() {
		return $this->get('VDOT_FORM');
	}

	/**
	 * Update vdot shape
	 * @param float $shape
	 */
	public function updateVdotShape($shape) {
		$this->object('VDOT_FORM')->set($shape);
		$this->updateValue( $this->handle('VDOT_FORM') );
	}

	/**
	 * Vdot corrector
	 * @return float
	 */
	public function vdotCorrector() {
		return $this->get('VDOT_CORRECTOR');
	}

	/**
	 * Update vdot corrector
	 * @param float $factor
	 */
	public function updateVdotCorrector($factor) {
		$this->object('VDOT_CORRECTOR')->set($factor);
		$this->updateValue( $this->handle('VDOT_CORRECTOR') );
	}

	/**
	 * Basic endurance
	 * @return int
	 */
	public function basicEndurance() {
		return $this->get('BASIC_ENDURANCE');
	}

	/**
	 * Update basic endurance
	 * @param int $basicEndurance
	 */
	public function updateBasicEndurance($basicEndurance) {
		$this->object('BASIC_ENDURANCE')->set($basicEndurance);
		$this->updateValue( $this->handle('BASIC_ENDURANCE') );
	}

	/**
	 * Maximal ATL
	 * @return int
	 */
	public function maxATL() {
		return $this->get('MAX_ATL');
	}

	/**
	 * Update maximal ATL
	 * @param int $atl
	 */
	public function updateMaxATL($atl) {
		$this->object('MAX_ATL')->set($atl);
		$this->updateValue( $this->handle('MAX_ATL') );
	}

	/**
	 * Maximal CTL
	 * @return int
	 */
	public function maxCTL() {
		return $this->get('MAX_CTL');
	}

	/**
	 * Update maximal CTL
	 * @param int $ctl
	 */
	public function updateMaxCTL($ctl) {
		$this->object('MAX_CTL')->set($ctl);
		$this->updateValue( $this->handle('MAX_CTL') );
	}

	/**
	 * Maximal TRIMP
	 * @return int
	 */
	public function maxTrimp() {
		return $this->get('MAX_TRIMP');
	}

	/**
	 * Update maximal TRIMP
	 * @param int $trimp
	 */
	public function updateMaxTrimp($trimp) {
		$this->object('MAX_TRIMP')->set($trimp);
		$this->updateValue( $this->handle('MAX_TRIMP') );
	}
}