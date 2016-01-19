<?php
/**
 * This file contains class::ConfigTabGeneral
 * @package Runalyze\System\Config
 */

use Runalyze\Configuration;

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
		$this->title = __('General settings');
	}

	/**
	 * All categories
	 * @return \Runalyze\Configuration\Category[]
	 */
	private function allCategories() {
		return array(
			Configuration::General(),
			Configuration::Privacy(),
			Configuration::ActivityView(),
			Configuration::ActivityForm(),
			Configuration::Design(),
			Configuration::DataBrowser(),
			Configuration::Vdot(),
			Configuration::Trimp(),
			Configuration::BasicEndurance(),
			Configuration::Misc()
		);
	}

	/**
	 * Set all fieldsets and fields
	 */
	public function setFieldsetsAndFields() {
		$IsFirst    = true;
		$Categories = $this->allCategories();

		foreach ($Categories as $Category) {
			$Fieldset = $Category->Fieldset();

			if (!is_null($Fieldset)) {
				if ($IsFirst)
					$IsFirst = false;
				else
					$Fieldset->setCollapsed();

				$this->Formular->addFieldset($Fieldset);
				$this->Formular->setLayoutForFields( FormularFieldset::$LAYOUT_FIELD_W100 );
			}
		}

		$this->Formular->allowOnlyOneOpenedFieldset();
	}

	/**
	 * Parse all post values 
	 */
	public function parsePostData() {
		$Categories = $this->allCategories();

		foreach ($Categories as $Category) {
			$Category->updateFromPost();
		}
	}
}