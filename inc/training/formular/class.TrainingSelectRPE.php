<?php
/**
 * This file contains class::TrainingSelectRPE
 * @package Runalyze\DataObjects\Training\Formular
 */
use \Runalyze\Data\RPE;
/**
 * Class for input fields: RPE - Rating of perceived exertion
 * @author Hannes Christiansen
 * @author Hannes Christiansen
 * @package Runalyze\DataObjects\Training\Formular
 */
class TrainingSelectRPE extends FormularSelectBox {
	/**
	 * Construct new input field for: rpe
	 * @param string $name
	 * @param string $label
	 * @param string $value optional, default: loading from $_POST
	 */
	public function __construct($name, $label, $value = '') {
		parent::__construct($name, $label, $value);
		
		$this->addOption('', '-');
		
		foreach (RPE::completeList() as $key => $option) {
			$this->addOption($key, $option);
		}
	}
}
