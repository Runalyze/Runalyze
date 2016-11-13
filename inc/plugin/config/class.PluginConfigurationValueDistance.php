<?php
/**
 * This file contains class::PluginConfigurationValueDistance
 * @package Runalyze\Plugin
 */

use Runalyze\Configuration;

/**
 * Plugin configuration value: float [km|miles]
 * @author Hannes Christiansen
 * @package Runalyze\Plugin
 */
class PluginConfigurationValueDistance extends PluginConfigurationValueFloat {
	/** @var int */
	const PRECISION = 1;

	/**
	 * @var null|\Runalyze\Parameter\Application\DistanceUnitSystem
	 */
	protected $UnitSystem = null;

	/**
	 * Construct
	 * @param string $Key
	 * @param string $Label [optional]
	 * @param string $Tooltip [optional]
	 * @param string $DefaultValue [optional]
	 */
	public function __construct($Key, $Label = '', $Tooltip = '', $DefaultValue = '') {
		parent::__construct($Key, $Label, $Tooltip, $DefaultValue);

		$this->DefaultValue *= $this->unitSystem()->distanceToPreferredUnitFactor();
	}

	/**
	 * Get value as string
	 * @param bool $transformValue
	 * @return string
	 */
	public function valueAsString($transformValue = false) {
		if ($transformValue) {
			return round($this->value() * $this->unitSystem()->distanceToPreferredUnitFactor(), self::PRECISION);
		}

		return round($this->value(), self::PRECISION);
	}

	/**
	 * Set value from post
	 */
	public function setValueFromPost() {
		if (isset($_POST[$this->Key])) {
			$this->Value = (float)$_POST[$this->Key] * $this->unitSystem()->distanceToKmFactor();
		}
	}

	/**
	 * Display row for config form
	 * @return FormularInput
	 */
	public function getFormField() {
		$Field = new FormularInput($this->Key, $this->formLabel(), $this->valueAsString(true));
		$Field->setSize( FormularInput::$SIZE_SMALL );

		if ($this->unitSystem()->isImperial()) {
			$Field->setUnit(FormularUnit::$MILES);
		} else {
			$Field->setUnit(FormularUnit::$KM);
		}

		return $Field;
	}

	/**
	 * @return \Runalyze\Parameter\Application\DistanceUnitSystem
	 */
	protected function unitSystem() {
		if (null === $this->UnitSystem) {
			$this->UnitSystem = Configuration::General()->distanceUnitSystem();
		}

		return $this->UnitSystem;
	}
}
