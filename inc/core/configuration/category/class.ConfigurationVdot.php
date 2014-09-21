<?php
/**
 * This file contains class::ConfigurationVdot
 * @package Runalyze\Configuration\Category
 */
/**
 * Configuration category: Vdot
 * @author Hannes Christiansen
 * @package Runalyze\Configuration\Category
 */
class ConfigurationVdot extends ConfigurationCategory {
	/**
	 * Internal key
	 * @return string
	 */
	protected function key() {
		return 'vdot';
	}

	/**
	 * Create handles
	 */
	protected function createHandles() {
		$this->createHandle('VDOT_HF_METHOD', new VdotMethod());
		$this->createHandle('VDOT_DAYS', new ParameterInt(30));
		$this->createHandle('VDOT_USE_CORRECTION', new ParameterBool(true));
		$this->createHandle('VDOT_MANUAL_CORRECTOR', new ParameterString(''));
		$this->createHandle('VDOT_MANUAL_VALUE', new ParameterString(''));

		$this->createHandle('VDOT_USE_CORRECTION_FOR_ELEVATION', new ParameterBool(false));
		$this->createHandle('VDOT_CORRECTION_POSITIVE_ELEVATION', new ParameterInt(2));
		$this->createHandle('VDOT_CORRECTION_NEGATIVE_ELEVATION', new ParameterInt(-1));
	}

	/**
	 * Used method
	 * @return VdotMethod
	 */
	public function method() {
		return $this->object('VDOT_HF_METHOD');
	}

	/**
	 * Days for shape
	 * @return int
	 */
	public function days() {
		return $this->get('VDOT_DAYS');
	}

	/**
	 * Uses a correction factor
	 * @return bool
	 */
	public function useCorrectionFactor() {
		return $this->get('VDOT_USE_CORRECTION');
	}

	/**
	 * Manual factor
	 * @return float
	 */
	public function manualFactor() {
		return (float)Helper::CommaToPoint($this->get('VDOT_MANUAL_CORRECTOR'));
	}

	/**
	 * Uses a manual factor
	 * @return bool
	 */
	public function useManualFactor() {
		return (1 >= $this->manualFactor() && $this->manualFactor() > 0);
	}

	/**
	 * Manual value
	 * @return float
	 */
	public function manualValue() {
		return (float)Helper::CommaToPoint($this->get('VDOT_MANUAL_VALUE'));
	}

	/**
	 * Uses a manual value
	 * @return bool
	 */
	public function useManualValue() {
		return ($this->manualValue() > 0);
	}

	/**
	 * Uses a correction for elevation
	 * @return bool
	 */
	public function useElevationCorrection()  {
		return $this->get('VDOT_USE_CORRECTION_FOR_ELEVATION');
	}

	/**
	 * Correction for positive elevation
	 * @return int
	 */
	public function correctionForPositiveElevation() {
		return $this->get('VDOT_CORRECTION_POSITIVE_ELEVATION');
	}

	/**
	 * Correction for negative elevation
	 * @return int
	 */
	public function correctionForNegativeElevation() {
		return $this->get('VDOT_CORRECTION_NEGATIVE_ELEVATION');
	}

	/**
	 * Register onchange events
	 */
	protected function registerOnchangeEvents() {
		$this->handle('VDOT_HF_METHOD')->registerOnchangeEvent('ConfigurationMessages::useCleanup');

		$this->handle('VDOT_USE_CORRECTION')->registerOnchangeEvent('ConfigurationMessages::useCleanup');
		$this->handle('VDOT_USE_CORRECTION')->registerOnchangeFlag(Ajax::$RELOAD_ALL);

		$this->handle('VDOT_DAYS')->registerOnchangeEvent('ConfigurationMessages::useCleanup');
		$this->handle('VDOT_DAYS')->registerOnchangeFlag(Ajax::$RELOAD_PLUGINS);

		$this->handle('VDOT_MANUAL_CORRECTOR')->registerOnchangeEvent('ConfigurationMessages::useCleanup');
		$this->handle('VDOT_MANUAL_CORRECTOR')->registerOnchangeFlag(Ajax::$RELOAD_PLUGINS);

		$this->handle('VDOT_MANUAL_VALUE')->registerOnchangeFlag(Ajax::$RELOAD_PLUGINS);

		$this->handle('VDOT_USE_CORRECTION_FOR_ELEVATION')->registerOnchangeEvent('ConfigurationMessages::useCleanup');
		$this->handle('VDOT_CORRECTION_POSITIVE_ELEVATION')->registerOnchangeEvent('ConfigurationMessages::useCleanup');
		$this->handle('VDOT_CORRECTION_NEGATIVE_ELEVATION')->registerOnchangeEvent('ConfigurationMessages::useCleanup');
	}

	/**
	 * Fieldset
	 * @return ConfigurationFieldset
	 */
	public function Fieldset() {
		$Fieldset = new ConfigurationFieldset( __('VDOT') );

		$Fieldset->addHandle( $this->handle('VDOT_HF_METHOD'), array(
			'label'		=> __('Estimation formula'),
			'tooltip'	=> __('Formula to estimate the vdot value. The old method is only listed for compatibility reasons.')
		));

		$Fieldset->addHandle( $this->handle('VDOT_DAYS'), array(
			'label'		=> __('Days for VDOT shape'),
			'tooltip'	=> __('Number of days to recognize for VDOT')
		));

		$Fieldset->addHandle( $this->handle('VDOT_USE_CORRECTION'), array(
			'label'		=> __('Use correction factor'),
			'tooltip'	=> __('Use a correction factor based on your best competition. (recommended)')
		));

		$Fieldset->addHandle( $this->handle('VDOT_MANUAL_CORRECTOR'), array(
			'label'		=> __('Manual correction factor'),
			'tooltip'	=> __('Manual correction factor (e.g. 0.9), if the automatic factor does not fit. Can be left empty.')
		));

		$Fieldset->addHandle( $this->handle('VDOT_MANUAL_VALUE'), array(
			'label'		=> __('Use fixed VDOT value'),
			'tooltip'	=> __('Fixed vdot value (e.g. 55), if the estimation does not fit. Can be left empty.')
		));

		$Fieldset->addHandle( $this->handle('VDOT_USE_CORRECTION_FOR_ELEVATION'), array(
			'label'		=> __('Adapt for elevation'),
			'tooltip'	=> __('The distance can be corrected by a formula from Peter Greif to adapt for elevation.')
		));

		$Fieldset->addHandle( $this->handle('VDOT_CORRECTION_POSITIVE_ELEVATION'), array(
			'label'		=> __('Correction per positive elevation'),
			'tooltip'	=> __('Add for each meter upwards X meter to the distance. (Only for the vdot calculation)'),
			'unit'		=> FormularUnit::$M
		));

		$Fieldset->addHandle( $this->handle('VDOT_CORRECTION_NEGATIVE_ELEVATION'), array(
			'label'		=> __('Correction per negative elevation'),
			'tooltip'	=> __('Add for each meter downwards X meter to the distance. (Only for the vdot calculation)'),
			'unit'		=> FormularUnit::$M
		));

		return $Fieldset;
	}
}