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
		$this->displayHeader();

		if (empty($this->Formats))
			$this->throwErrorForEmptyList();
		else
			$this->displayList();
	}

	/**
	 * Display header
	 */
	private function displayHeader() {
		echo HTML::p( __('Choose a format:') );
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
		$List = new BlocklinkList();
		$List->addCSSclass('blocklist-inline clearfix');

		foreach ($this->Formats as $Format) {
			$URL  = ExporterWindow::$URL.'?id='.Request::sendId().'&type='.$Format;
			$Icon = 'inc/export/icons/'.strtolower($Format).'.png';
			$Link = Ajax::window('<a href="'.$URL.'" style="background-image:url('.$Icon.');"><strong>'.$Format.'</strong></a>', 'small');
			$List->addCompleteLink($Link);
		}

		$List->display();
	}

	/**
	 * Read possible filetypes
	 */
	private function readPossibleFiletypes() {
		$dir = opendir(FRONTEND_PATH.'export/types/');

		while ($file = readdir($dir))
			if (substr($file, 0, 14) == 'class.Exporter')
				$this->Formats[] = substr($file, 14, -4);

		closedir($dir);
	}
}