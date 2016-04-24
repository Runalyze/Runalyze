<?php
/**
 * This file contains class::TrainingSelectType
 * @package Runalyze\DataObjects\Training\Formular
 */

use Runalyze\Context;

/**
 * Class for input field: typeid
 * @author Hannes Christiansen
 * @package Runalyze\DataObjects\Training\Formular
 */
class TrainingSelectType extends FormularSelectBox {
	/**
	 * Construct new input field for: typeid
	 * @param string $name
	 * @param string $label
	 * @param string $value optional, default: loading from $_POST
	 */
	public function __construct($name, $label, $value = '') {
		parent::__construct($name, $label, $value);

		$this->addOption(0, '---- '.__('select type'), array('data-sport' => 'all'));

		foreach (Context::Factory()->allTypes() as $Type) {
			$this->addOption(
				$Type->id(),
				$Type->name(),
				['data-sport' => $Type->sportid()]
			);
		}
	}
}