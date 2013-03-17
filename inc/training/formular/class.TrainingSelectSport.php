<?php
/**
 * This file contains class::TrainingSelectSport
 * @package Runalyze\DataObjects\Training\Formular
 */
/**
 * Class for input fields: sportid
 * @author Hannes Christiansen <mail@laufhannes.de>
 * @package Runalyze\DataObjects\Training\Formular
 */
class TrainingSelectSport extends FormularSelectBox {
	/**
	 * Construct new input field for: sportid
	 * @param string $name
	 * @param string $label
	 * @param string $value optional, default: loading from $_POST
	 */
	public function __construct($name, $label, $value = '') {
		parent::__construct($name, $label, $value);

		foreach (Sport::getSports() as $id => $sport) {
			$attributes = array();
			$attributes['data-kcal'] = $sport['kcal'];

			if ($sport['id'] == CONF_RUNNINGSPORT)
				$attributes['data-running'] = 'true';
			if ($sport['outside'] == 1)
				$attributes['data-outside'] = 'true';
			if ($sport['types'] == 1)
				$attributes['data-types'] = 'true';
			if ($sport['distances'] == 1)
				$attributes['data-distances'] = 'true';

			$this->addOption($id, $sport['name'], $attributes);
		}
	}
}