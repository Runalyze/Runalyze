<?php
/**
 * This file contains class::DataBrowser
 * @package Runalyze\Configuration\Category
 */

namespace Runalyze\Configuration\Category;

use Runalyze\Configuration\Fieldset;
use Runalyze\Parameter\Boolean;
use Runalyze\Parameter\Application\DataBrowserMode;
use Ajax;

/**
 * Configuration category: Data browser
 * @author Hannes Christiansen
 * @package Runalyze\Configuration\Category
 */
class DataBrowser extends \Runalyze\Configuration\Category {
	/**
	 * Internal key
	 * @return string
	 */
	protected function key() {
		return 'data-browser';
	}

	/**
	 * Create handles
	 */
	protected function createHandles() {
		$this->createHandle('DB_DISPLAY_MODE', new DataBrowserMode());
		$this->createHandle('DB_SHOW_DATASET_LABELS', new Boolean(true));
		$this->createHandle('DB_SHOW_CREATELINK_FOR_DAYS', new Boolean(false));
		$this->createHandle('DB_SHOW_ACTIVE_DAYS_ONLY', new Boolean(false));
	}

	/**
	 * Mode
	 * @return DataBrowserMode
	 */
	public function mode() {
		return $this->object('DB_DISPLAY_MODE');
	}

	/**
	 * Show dataset labels
	 * @return bool
	 */
	public function showLabels() {
		return $this->get('DB_SHOW_DATASET_LABELS');
	}

	/**
	 * Show create link
	 * @return bool
	 */
	public function showCreateLink() {
		return $this->get('DB_SHOW_CREATELINK_FOR_DAYS');
	}
	
	/**
	 * Show days with activities only
	 * @return bool
	 */
	public function showActiveDaysOnly() {
		return $this->get('DB_SHOW_ACTIVE_DAYS_ONLY');
	}

	/**
	 * Register onchange events
	 */
	protected function registerOnchangeEvents() {
		$this->handle('DB_SHOW_DATASET_LABELS')->registerOnchangeFlag(Ajax::$RELOAD_DATABROWSER);
		$this->handle('DB_SHOW_CREATELINK_FOR_DAYS')->registerOnchangeFlag(Ajax::$RELOAD_DATABROWSER);
		$this->handle('DB_SHOW_ACTIVE_DAYS_ONLY')->registerOnchangeFlag(Ajax::$RELOAD_DATABROWSER);

		$this->handle('DB_DISPLAY_MODE')->registerOnchangeEvent('Runalyze\\Configuration\\Category\\DataBrowser::showNewTimerangeInDB');
	}

	/**
	 * Fieldset
	 * @return \Runalyze\Configuration\Fieldset
	 */
	public function Fieldset() {
		$Fieldset = new Fieldset( __('Calendar view') );

		$Fieldset->addHandle( $this->handle('DB_DISPLAY_MODE'), array(
			'label'		=> __('Calendar: mode'),
			'tooltip'	=> __('Default mode for the calendar')
		));

		$Fieldset->addHandle( $this->handle('DB_SHOW_DATASET_LABELS'), array(
			'label'		=> __('Calendar: show labels for dataset')
		));
		
		$Fieldset->addHandle( $this->handle('DB_SHOW_ACTIVE_DAYS_ONLY'), array(
			'label'		=> __('Calendar: show days with activities only')
		));

		$Fieldset->addHandle( $this->handle('DB_SHOW_CREATELINK_FOR_DAYS'), array(
			'label'		=> __('Calendar: create button'),
			'tooltip'	=> __('Add a link for every day to create a new activity.')
		));

		return $Fieldset;
	}

	/**
	 * Reload data browser for new timerange
	 */
	public static function showNewTimerangeInDB() {
		$mode = \Runalyze\Configuration::DataBrowser()->mode();

		$rel = $mode->showMonth() ? 'month-link' : 'week-link';

		echo Ajax::wrapJSasFunction('$("#data-browser .panel-heading a[rel=\''.$rel.'\']").click();');
	}
}