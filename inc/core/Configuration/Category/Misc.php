<?php
/**
 * This file contains class::Misc
 * @package Runalyze\Configuration\Category
 */

namespace Runalyze\Configuration\Category;

use Runalyze\Configuration\Fieldset;
use Runalyze\Parameter\Integer;

/**
 * Configuration category: Miscellaneous
 * @author Hannes Christiansen
 * @package Runalyze\Configuration\Category
 */
class Misc extends \Runalyze\Configuration\Category {
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
		$this->createHandle('SEARCH_RESULTS_PER_PAGE', new Integer(15));
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
	 * @return \Runalyze\Configuration\Fieldset
	 */
	public function Fieldset() {
		$Fieldset = new Fieldset( __('Miscellaneous') );
		$Fieldset->addHandle( $this->handle('SEARCH_RESULTS_PER_PAGE'), array(
			'label'		=> __('Search: results per page'),
			'tooltip'	=> __('Number of results displayed on each page.')
		));

		return $Fieldset;
	}
}