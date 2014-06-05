<?php
/**
 * This file contains class::ExporterFactory
 * @package Runalyze\Export
 */
/**
 * Create exporter for given type
 *
 * @author Hannes Christiansen
 * @package Runalyze\Export
 */
class ExporterFactory {
	/**
	 * Exporter
	 * @var ExporterAbstract
	 */
	protected $Exporter = null;

	/**
	 * Constructor
	 * @param string $Type
	 */
	public function __construct($Type) {
		$ExporterClass = 'Exporter'.$Type;

		if (class_exists($ExporterClass))
			$this->Exporter = new $ExporterClass( new TrainingObject(Request::sendId()) );
	}

	/**
	 * Display
	 */
	public function display() {
		if (is_null($this->Exporter))
			echo HTML::error( __('The chosen exporter could not be located.') );
		else
			$this->Exporter->display();
	}
}