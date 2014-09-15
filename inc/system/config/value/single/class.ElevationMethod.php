<?php
/**
 * This file contains class::ElevationMethod
 * @package Runalyze\System\Configuration\Value
 */
/**
 * Elevation method
 * @author Hannes Christiansen
 * @package Runalyze\System\Configuration\Value
 */
class ElevationMethod extends ConfigurationValueSelect {
	/**
	 * None
	 * @var string
	 */
	const NONE = 'none';

	/**
	 * Treshold
	 * @var string
	 */
	const TRESHOLD = 'treshold';

	/**
	 * Douglas-Peucker
	 * @var string
	 */
	const DOUGLAS_PEUCKER = 'douglas-peucker';

	/**
	 * Reumann-Witkamm
	 * @var string
	 */
	const REUMANN_WITKAMM = 'reumann-witkamm';

	/**
	 * Construct
	 * 
	 * The elevation method can be used without key as single configuration object.
	 * 
	 * @param string $Key [optional]
	 */
	public function __construct($Key = '') {
		parent::__construct($Key, array(
			'default'		=> self::TRESHOLD,
			'label'			=> __('Elevation: smoothing'),
			'tooltip'		=> __('Choose the algorithm to smooth the elevation data'),
			'options'		=> array(
				self::NONE				=> __('none'),
				self::TRESHOLD			=> __('Treshold method'),
				self::DOUGLAS_PEUCKER	=> __('Douglas-Peucker-Algorithm')//,
				//self::REUMANN_WITKAMM	=> __('Reumann-Witkamm-Algorithm')
			),
			'onchange_eval'	=> 'ConfigTabs::addMessage(HTML::warning("The tool <em>Datenbank-Cleanup</em> can be used to recalculate elevation values."));'
		));
	}

	/**
	 * Uses none
	 * @return bool
	 */
	public function usesNone() {
		return ($this->value() == self::NONE);
	}

	/**
	 * Uses: Treshold
	 * @return bool
	 */
	public function usesTreshold() {
		return ($this->value() == self::TRESHOLD);
	}

	/**
	 * Uses: Douglas-Peucker
	 * @return bool
	 */
	public function usesDouglasPeucker() {
		return ($this->value() == self::DOUGLAS_PEUCKER);
	}

	/**
	 * Uses: Reumann-Witkamm
	 * @return bool
	 */
	public function usesReumannWitkamm() {
		return ($this->value() == self::REUMANN_WITKAMM);
	}

	/**
	 * String for method
	 * @return string
	 */
	public function asString() {
		if ($this->usesTreshold()) {
			return __('Treshold method');
		} elseif ($this->usesDouglasPeucker()) {
			return __('Douglas-Peucker-algorithm');
		} elseif ($this->usesReumannWitkamm()) {
			return __('Reumann-Witkamm-algorithm');
		}

		return __('no smoothing');
	}
}