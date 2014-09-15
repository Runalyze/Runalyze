<?php
/**
 * This file contains class::ConfigTabGeneral
 * @package Runalyze\System\Config
 */
/**
 * ConfigTabGeneral
 * @author Hannes Christiansen
 * @package Runalyze\System\Config
 */
class ConfigTabGeneral extends ConfigTab {
	/**
	 * Set key and title for form 
	 */
	protected function setKeyAndTitle() {
		$this->key = 'config_tab_general';
		$this->title = __('General Settings');
	}

	/**
	 * All categories
	 * @return ConfigurationCategory[]
	 */
	private function getAllCategories() {
		return array(
			Configuration::General(),
			Configuration::ActivityView()
		);
	}

	/**
	 * Set all fieldsets and fields
	 */
	public function setFieldsetsAndFields() {
		$IsFirst    = true;
		$Categories = ConfigCategory::getAllCategories();
		//$Categories = $this->getAllCategories();

		foreach ($Categories as $Category) {
			//$Fieldset = $Category->Fieldset();
			$Fieldset = $Category->getFieldset();

			if ($IsFirst)
				$IsFirst = false;
			else
				$Fieldset->setCollapsed();

			$this->Formular->addFieldset($Fieldset);
			$this->Formular->setLayoutForFields( FormularFieldset::$LAYOUT_FIELD_W50 );
		}

		$this->Formular->allowOnlyOneOpenedFieldset();
	}

	/**
	 * Parse all post values 
	 */
	public function parsePostData() {
		/*$Categories = $this->getAllCategories();

		foreach ($Categories as $Category) {
			$Category->updateFromPost();
		}*/

		$Categories = ConfigCategory::getAllCategories();

		foreach ($Categories as $Category)
			$Category->parseAllValues();
	}
}