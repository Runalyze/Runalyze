<?php
/**
 * This file contains class::FormularInputWithEqualityOption
 * @package Runalyze\HTML\Formular
 */
/**
 * Class for input field with equality option
 * @author Hannes Christiansen
 * @package Runalyze\HTML\Formular
 */
class FormularInputWithEqualityOption extends FormularInput {
	/**
	 * @var array
	 */
	protected $Options = array(
		'eq'	=> '=',
		'gt'	=> '&gt;',
		'ge'	=> '&ge;',
		'le'	=> '&le;',
		'lt'	=> '&lt;',
		'ne'	=> '&ne;',
		'like'	=> '&asymp;'
	);

	/**
	 * Allow only valid operators for numeric values
	 */
	public function setNumericOptions() {
		unset($this->Options['like']);
	}

	/**
	 * Allow only valid operators for strings
	 */
	public function setStringOptions() {
		$this->Options = array(
			'eq'	=> '=',
			'ne'	=> '&ne;',
			'like'	=> '&asymp;'
		);
	}

	/**
	 * Display this field
	 * @return string
	 */
	protected function getFieldCode() {
		$selected = isset($_POST['opt']) && isset($_POST['opt'][$this->name]) ? $_POST['opt'][$this->name] : 'eq';

		$label  = '<label>'.$this->label.'</label>';
		$input  = HTML::selectBox('opt['.$this->name.']', $this->Options, $selected);
		$input .= $this->wrapInputTagForUnit('<input '.$this->attributes().'>');

		return $label.' '.$input;
	}
}