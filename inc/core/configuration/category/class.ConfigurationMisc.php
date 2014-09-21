<?php
/**
 * This file contains class::ConfigurationMisc
 * @package Runalyze\Configuration\Category
 */
/**
 * Configuration category: Miscellaneous
 * @author Hannes Christiansen
 * @package Runalyze\Configuration\Category
 */
class ConfigurationMisc extends ConfigurationCategory {
	/**
	 * Internal key
	 * @return string
	 */
	protected function key() {
		return 'misc';
	}

	/**
	 * Create handles
	 */
	protected function createHandles() {
		$this->createHandle('SEARCH_RESULTS_PER_PAGE', new ParameterInt(15));
	}

	/**
	 * Search: results per page
	 * @return int
	 */
	public function searchResultsPerPage() {
		return $this->get('SEARCH_RESULTS_PER_PAGE');
	}

	/**
	 * Fieldset
	 * @return ConfigurationFieldset
	 */
	public function Fieldset() {
		$Fieldset = new ConfigurationFieldset( __('Miscellaneous') );
		$Fieldset->addHandle( $this->handle('SEARCH_RESULTS_PER_PAGE'), array(
			'label'		=> __('Search: results per page'),
			'tooltip'	=> __('Number of results displayed on each page.')
		));

		return $Fieldset;
	}
}