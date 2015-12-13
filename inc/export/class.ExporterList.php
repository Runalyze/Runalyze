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

use Runalyze\View\Activity\Context;

class ExporterList {
	/**
	 * Exporter formats
	 * @var array
	 */
	protected $Formats = array();
	
	/**
	 * Activity context
	 * @var \Runalyze\View\Activity\Context
	 */
	protected $Context = null;

	/**
	 * Exporter
	 */
	public function __construct(Context $context) {
		$this->Context = $context;
		$this->readPossibleFiletypes();
	}

	/**
	 * Display
	 */
	public function display() {
		if (empty($this->Formats)) {
			$this->throwErrorForEmptyList();
		}
	}

	/**
	 * Throw error: empty list
	 */
	private function throwErrorForEmptyList() {
		echo HTML::info( __('No exporter could be located.') );
	}

	
	public function getList() {
	    return $this->Formats;
	}
	
	/**
	 * Read possible filetypes
	 */
	private function readPossibleFiletypes() {
		$dir = opendir(FRONTEND_PATH.'export/types/');

		while ($file = readdir($dir)) {
			if (substr($file, 0, 14) == 'class.Exporter') {
			    $this->Formats[ call_user_func( array(substr($file, 6, -4), 'Type')) ][] = substr($file, 6, -4);
			}
		}

		closedir($dir);
	}
}