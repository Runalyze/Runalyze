<?php
/**
 * This file contains class::ExporterListView
 * @package Runalyze\Export
 */
/**
 * List of possible exporter
 *
 * @author Hannes Christiansen
 * @package Runalyze\Export
 */
class ExporterListView {
	/**
	 * Exporter formats
	 * @var array
	 */
	protected $Formats = array();

	/**
	 * Exporter
	 */
	public function __construct() {
		$this->readPossibleFiletypes();
	}

	/**
	 * Display
	 */
	public function display() {
		if (empty($this->Formats)) {
			$this->throwErrorForEmptyList();
		} else {
			$this->displayList();
		}
	}

	/**
	 * Throw error: empty list
	 */
	private function throwErrorForEmptyList() {
		echo HTML::info( __('No exporter could be located.') );
	}

	/**
	 * Display list
	 */
	private function displayList() {
		ksort($this->Formats);

		foreach ($this->Formats as $Type => $Formats) {
			echo '<p><strong>'.ExporterType::heading($Type).'</strong></p>';

			$List = new BlocklinkList();
			$List->addCSSclass('blocklist-inline clearfix');

			foreach ($Formats as $Format) {
				$URL  = ExporterWindow::$URL.'?id='.Request::sendId().'&type='.$Format;
				$List->addLinkWithIcon($URL, $Format, call_user_func( array('Exporter'.$Format, 'IconClass')));
			}

			$List->display();
		}
	}

	/**
	 * Read possible filetypes
	 */
	private function readPossibleFiletypes() {
		$dir = opendir(FRONTEND_PATH.'export/types/');

		while ($file = readdir($dir)) {
			if (substr($file, 0, 14) == 'class.Exporter') {
				$this->Formats[ call_user_func( array(substr($file, 6, -4), 'Type')) ][] = substr($file, 14, -4);
			}
		}

		closedir($dir);
	}
}