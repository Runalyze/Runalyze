<?php
/**
 * This file contains class::StandardFormularFieldsetFactory
 * @package Runalyze\HTML\Formular
 */
/**
 * Factory for fieldsets, using array from DatabaseScheme
 *
 * @author Hannes Christiansen
 * @package Runalyze\HTML\Formular
 */
class StandardFormularFieldsetFactory {
	/**
	 * Database scheme
	 * @var DatabaseScheme
	 */
	private $DatabaseScheme = null;

	/**
	 * Constructor
	 * @param DatabaseScheme $DatabaseScheme
	 */
	public function __construct(DatabaseScheme $DatabaseScheme) {
		$this->DatabaseScheme = $DatabaseScheme;
	}

	/**
	 * Add all fieldsets to given Formular
	 * @param Formular $Formular
	 */
	public function addFieldsets(Formular $Formular) {
		foreach ($this->DatabaseScheme->fieldsets() as $FieldsetArray)
			$Formular->addFieldset( $this->createFieldset($FieldsetArray) );
	}

	/**
	 * Create a fieldset
	 * @param array $FieldsetArray
	 * @return \FormularFieldset
	 */
	private function createFieldset(array &$FieldsetArray) {
		$Fieldset = new FormularFieldset();

		$FieldFactory = new StandardFormularFieldFactory($this->DatabaseScheme);
		$FieldFactory->addFields($Fieldset, $FieldsetArray['fields']);

		$this->setAttributesToFieldset($Fieldset, $FieldsetArray);

		return $Fieldset;
	}

	/**
	 * Set attributes to fieldset
	 * @param FormularFieldset $Fieldset
	 * @param array $FieldsetArray
	 */
	private function setAttributesToFieldset(FormularFieldset $Fieldset, array $FieldsetArray) {
		$Fieldset->setTitle($FieldsetArray['legend']);
		$Fieldset->setId('fieldset-'.$FieldsetArray['id']);

		if (isset($FieldsetArray['layout']))
			$Fieldset->setLayoutForFields($FieldsetArray['layout']);

		if (isset($FieldsetArray['css']))
			$Fieldset->addCSSclass($FieldsetArray['css']);

		if (isset($FieldsetArray['conf']))
			$Fieldset->setConfValueToSaveStatus($FieldsetArray['conf']);
	}
}
