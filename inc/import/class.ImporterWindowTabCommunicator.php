<?php
/**
 * This file contains class::ImporterWindowTabCommunicator
 * @package Runalyze\Import
 */
/**
 * Importer tab: Garmin Communicator
 *
 * @author Hannes Christiansen
 * @package Runalyze\Import
 */
class ImporterWindowTabCommunicator extends ImporterWindowTab {
	/**
	 * CSS id
	 * @return string
	 */
	public function cssID() {
		return 'garmin';
	}

	/**
	 * Title
	 * @return string
	 */
	public function title() {
		return __('Garmin Communicator');
	}

	/**
	 * Display tab content
	 */
	public function displayTab() {
		include 'tpl/tpl.Importer.garminCommunicator.php';

		$this->checkPermissions();
	}
}