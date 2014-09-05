<?php
/**
 * This file contains class::ConfigurationGeneral
 * @package Runalyze\System\Configuration\Category
 */
/**
 * Configuration category: General
 * @author Hannes Christiansen
 * @package Runalyze\System\Configuration\Category
 */
class ConfigurationGeneral extends ConfigurationCategory {
	/**
	 * Internal key
	 * @return string
	 */
	protected function key() {
		return 'general';
	}

	/**
	 * Gender
	 * @return ConfigurationValueSelect
	 */
	public function gender() {
		return $this->get('GENDER');
	}

	/**
	 * Is gender set?
	 * @return bool
	 */
	public function hasGender() {
		return $this->isMale() || $this->isFemale();
	}

	/**
	 * Is male?
	 * @return bool
	 */
	public function isMale() {
		return ($this->gender() == 'm');
	}

	/**
	 * Is female?
	 * @return bool
	 */
	public function isFemale() {
		return ($this->gender() == 'f');
	}

	/**
	 * Create values
	 */
	protected function createValues() {
		$this->createGender();
	}

	/**
	 * Create: GENDER
	 */
	protected function createGender() {
		// TODO: add default value 'none' or similar
		$this->createValue(new ConfigurationValueSelect('GENDER', array(
			'default'		=> 'none',
			'label'			=> __('Gender'),
			'options'		=> array(
				'none' => '--- '.__('please choose'),
				'm' => __('male'),
				'f' => __('female')
			),
			'onchange'		=> Ajax::$RELOAD_ALL
		)));
	}
}