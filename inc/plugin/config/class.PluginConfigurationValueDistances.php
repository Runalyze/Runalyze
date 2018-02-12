<?php
/**
 * This file contains class::PluginConfigurationValueDistances
 * @package Runalyze\Plugin
 */

use Runalyze\Configuration;

/**
 * Plugin configuration value: array
 * @author Hannes Christiansen
 * @package Runalyze\Plugin
 */
class PluginConfigurationValueDistances extends PluginConfigurationValueArray {
	/** @var int */
	const PRECISION = 2;

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

		$this->DefaultValue = $this->roundAndTransformValues($this->DefaultValue, true, false);
	}

	/**
	 * Set value from string
	 *
	 * Has to be overwritten in subclasses.
	 * @param string $Value
	 * @param bool $transformValues
	 */
	public function setValueFromString($Value, $transformValues = false) {
		parent::setValueFromString($Value);

		$this->Value = $this->roundAndTransformValues($this->Value, $transformValues, true);
	}

	/**
	 * Get value as string
	 * @param bool $transformValues
	 * @return string
	 */
	public function valueAsString($transformValues = false) {
		$values = $this->roundAndTransformValues($this->Value, $transformValues, false);

		return implode(', ', $values);
	}

	/**
	 * @param array $values
	 * @param bool $transformValues
	 * @param bool $toKm
	 * @return array
	 */
	protected function roundAndTransformValues(array $values, $transformValues = false, $toKm = false) {
		if ($transformValues) {
			$factor = $toKm ? $this->unitSystem()->distanceToKmFactor() : $this->unitSystem()->distanceToPreferredUnitFactor();

			return array_map(function($val) use ($factor){
				return round($val * $factor, self::PRECISION);
			}, $values);
		}

		return array_map(function($val){
			return round($val, self::PRECISION);
		}, $values);
	}

	/**
	 * Set value from post
	 */
	public function setValueFromPost() {
		if (isset($_POST[$this->Key])) {
			$this->setValueFromString($_POST[$this->Key], true);
		}
	}

	/**
	 * Display row for config form
	 * @return FormularInput
	 */
	public function getFormField() {
		$Field = new FormularInput($this->Key, $this->formLabel(), $this->valueAsString(true));
		$Field->setSize( FormularInput::$SIZE_FULL_INLINE );
		$Field->addAttribute('maxlength', PluginConfigurationValue::MAXLENGTH);

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
