<?php
/**
 * This file contains class::TrainingSelectSport
 * @package Runalyze\DataObjects\Training\Formular
 */

use Runalyze\Configuration;

/**
 * Class for input fields: sportid
 * @author Hannes Christiansen
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

		foreach (SportFactory::AllSports() as $id => $sport) {
			$attributes = array();
			$attributes['data-kcal'] = $sport['kcal'];

			if ($sport['id'] == Configuration::General()->runningSport())
				$attributes['data-running'] = 'true';
			if ($sport['outside'] == 1)
				$attributes['data-outside'] = 'true';
			if ($sport['distances'] == 1)
				$attributes['data-distances'] = 'true';
			if ($sport['power'] == 1)
				$attributes['data-power'] = 'true';

			$this->addOption($id, $sport['name'], $attributes);
		}
	}
}