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
		$this->createHandle('JD_USE_VDOT_CORRECTOR', new ParameterBool(true));
		$this->createHandle('VDOT_MANUAL_CORRECTOR', new ParameterString(''));
		$this->createHandle('VDOT_MANUAL_VALUE', new ParameterString(''));

		$this->createHandle('JD_USE_VDOT_CORRECTION_FOR_ELEVATION', new ParameterBool(false));
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
		return $this->get('JD_USE_VDOT_CORRECTOR');
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
		return $this->get('JD_USE_VDOT_CORRECTION_FOR_ELEVATION');
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
}