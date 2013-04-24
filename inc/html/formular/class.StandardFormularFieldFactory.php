<?php
/**
 * This file contains class::StandardFormularFieldFactory
 * @package Runalyze\HTML\Formular
 */
/**
 * Factory for fields, using array from DatabaseScheme
 *
 * @author Hannes Christiansen
 * @package Runalyze\HTML\Formular
 */
class StandardFormularFieldFactory {
	/**
	 * Database scheme
	 * @var DatabaseScheme
	 */
	private $DatabaseScheme = null;

	/**
	 * Constructor
	 * @param DatabaseScheme $DatabaseScheme
	 */
	public function __construct(DatabaseScheme &$DatabaseScheme) {
		$this->DatabaseScheme = $DatabaseScheme;
	}

	/**
	 * Add all fieldsets to given fieldset
	 * @param FormularFieldset $Fieldset
	 * @param array $fieldKeys
	 */
	public function addFields(FormularFieldset &$Fieldset, $fieldKeys) {
		foreach ($fieldKeys as $key)
			if (!$this->DatabaseScheme->fieldIsHidden($key))
				$Fieldset->addField( $this->getFieldFor($key) );
	}

	/**
	 * Get Field for key
	 * @param string $Key
	 * @return FormularField
	 */
	private function getFieldFor($Key) {
		$FieldArray = $this->DatabaseScheme->field($Key);

		$Field = $this->createFieldFor($Key, $FieldArray);
		$this->setAttributesToField($Field, $FieldArray);

		return $Field;
	}

	/**
	 * Create a field
	 * @param string $Key
	 * @param array $FieldArray
	 * @return object
	 */
	private function createFieldFor($Key, &$FieldArray) {
		$ClassName = $this->fieldClass($FieldArray);

		return new $ClassName($Key, $FieldArray['formular']['label']);
	}

	/**
	 * Get class name for a field
	 * @param array $FieldArray
	 * @return string
	 */
	private function fieldClass(&$FieldArray) {
		if (isset($FieldArray['formular']['class']))
			if (class_exists($FieldArray['formular']['class']))
				return $FieldArray['formular']['class'];

		return 'FormularInput';
	}

	/**
	 * Set attributes to field
	 * @param FormularField $Field
	 * @param array $FieldArray
	 */
	private function setAttributesToField(FormularField &$Field, &$FieldArray) {
		if (isset($FieldArray['formular']['parser'])) {
			$Options = array();

			if (isset($FieldArray['formular']['required']))
				$Options['required'] = $FieldArray['formular']['required'];

			if (isset($FieldArray['formular']['parserOptions']))
				$Options = array_merge($Options, $FieldArray['formular']['parserOptions']);

			$Field->setParser( $FieldArray['formular']['parser'], $Options );
		}

		if (isset($FieldArray['formular']['unit']))
			$Field->setUnit($FieldArray['formular']['unit']);

		if (isset($FieldArray['formular']['size']))
			$Field->setSize($FieldArray['formular']['size']);

		if (isset($FieldArray['formular']['css']))
			$Field->addLayoutClass($FieldArray['formular']['css']);

		if (isset($FieldArray['formular']['layout']))
			$Field->setLayout($FieldArray['formular']['layout']);

		if ($this->fieldClass($FieldArray) == 'FormularSelectDb')
			$Field->loadOptionsFrom($FieldArray['formular']['table'], $FieldArray['formular']['column']);
	}
}