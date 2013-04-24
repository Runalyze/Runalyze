<?php
/**
 * This file contains class::TrainingSelectShoe
 * @package Runalyze\DataObjects\Training\Formular
 */
/**
 * Class for input field: shoeid
 * @author Hannes Christiansen
 * @package Runalyze\DataObjects\Training\Formular
 */
class TrainingSelectShoe extends FormularSelectBox {
	/**
	 * Construct new input field for: shoeid
	 * @param string $name
	 * @param string $label
	 * @param string $value optional, default: loading from $_POST
	 */
	public function __construct($name, $label, $value = '') {
		parent::__construct($name, $label, $value);

		$this->addLayoutClass( TrainingFormular::$ONLY_RUNNING_CLASS );
		$this->addOption(0, '---- Laufschuh ausw&auml;hlen');

		foreach (ShoeFactory::NamesAsArray( !$this->showAll() ) as $id => $name)
			$this->addOption($id, $name);
	}

	/**
	 * Boolean flag: show unused shoes too?
	 * @return boolean
	 */
	protected function showAll() {
		return !empty($this->value);
	}

	/**
	 * Display field
	 * 
	 * This method overwrites parent display method to include some hidden values
	 */
	public function display() {
		parent::display();

		if ($this->value > 0 && isset($_POST['s']) && isset($_POST['distance'])) {
			echo HTML::hiddenInput('s_old', $_POST['s']);
			echo HTML::hiddenInput('dist_old', $_POST['distance']);
			echo HTML::hiddenInput('shoeid_old', $this->value);
		}
	}
}