<?php
/**
 * Class for input fields: sportid
 * @author Hannes Christiansen <mail@laufhannes.de>
 */
class TrainingInputSport extends FormularSelectBox {
	/**
	 * Construct new input field for: weather
	 * Using $_POST by default
	 * @param string $value [optional]
	 */
	public function __construct($value = '') {
		parent::__construct('sportid', 'Sportart', $value);

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