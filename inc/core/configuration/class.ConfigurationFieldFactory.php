<?php
/**
 * This file contains class::ConfigurationFieldFactory
 * @package Runalyze\Configuration
 */
/**
 * Configuration fieldset
 * @author Hannes Christiansen
 * @package Runalyze\Configuration
 */
class ConfigurationFieldFactory {
	/**
	 * Add field
	 * @param ConfigurationHandle $Handle
	 * @param array $options
	 */
	public function FieldFor(ConfigurationHandle $Handle, array $options = array()) {
		$options = array_merge(array(
			'label'		=> $Handle->key(),
			'tooltip'	=> '',
			'unit'		=> '',
			'size'		=> '',
			'css'		=> '',
			'layout'	=> ''
		), $options);

		$label = !empty($options['tooltip']) ? Ajax::tooltip($options['label'], $options['tooltip']) : $options['label'];

		$Field = $this->createFieldFor($Handle, $label);
		$this->setAttributesToField($Field, $options);

		return $Field;
	}

	/**
	 * Create a field
	 * @param ConfigurationHandle $Handle
	 * @param string $label
	 * @return FormularField
	 */
	private function createFieldFor(ConfigurationHandle $Handle, $label) {
		$Parameter = $Handle->object();
		$Class = 'FormularInput';

		if ($Parameter instanceof ParameterSelectRow) {
			$Field = new FormularSelectDb($Handle->key(), $label, $Handle->value());
			$Field->loadOptionsFrom($Parameter->table(), $Parameter->column());

			return $Field;
		} elseif ($Parameter instanceof ParameterSelect) {
			$Field = new FormularSelectBox($Handle->key(), $label, $Handle->value());
			$Field->setOptions($Parameter->options());

			return $Field;
		} elseif ($Parameter instanceof ParameterBool) {
			$Field = new FormularCheckbox($Handle->key(), $label, $Handle->value());
			$Field->addHiddenSentValue();

			return $Field;
		}

		return new $Class($Handle->key(), $label, $Handle->object()->valueAsString());
	}

	/**
	 * Set attributes to field
	 * @param FormularField $Field
	 * @param array $options
	 */
	private function setAttributesToField(FormularField &$Field, array &$options) {
		if (!empty($options['unit']))
			$Field->setUnit($options['unit']);

		if (!empty($options['size']))
			$Field->setSize($options['size']);

		if (!empty($options['css']))
			$Field->addLayoutClass($options['css']);

		if (!empty($options['layout']))
			$Field->setLayout($options['layout']);
	}
}