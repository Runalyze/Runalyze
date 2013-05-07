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
	 * Display this field
	 * @return string
	 */
	protected function getFieldCode() {
		$options = array(
			'eq'	=> '=',
			'gt'	=> '&gt;',
			'ge'	=> '&ge;',
			'le'	=> '&le;',
			'lt'	=> '&lt;',
			'ne'	=> '&ne;',
			'like'	=> '&asymp;'
		);

		$selected = isset($_POST['opt']) && isset($_POST['opt'][$this->name]) ? $_POST['opt'][$this->name] : 'eq';

		$label  = '<label>'.$this->label.'</label>';
		$input  = HTML::selectBox('opt['.$this->name.']', $options, $selected);
		$input .= '<input '.$this->attributes().' />';

		return $label.' '.$input;
	}
}