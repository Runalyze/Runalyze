<?php
/**
 * This file contains class::ExporterWindow
 * @package Runalyze\Export
 */
/**
 * Window for exporting a training.
 *
 * @author Hannes Christiansen
 * @package Runalyze\Export
 */
class ExporterWindow {
	/**
	 * URL for window
	 * @var string
	 */
	static public $URL = 'call/call.Exporter.export.php';

	/**
	 * Training ID
	 * @var int
	 */
	protected $TrainingID = 0;

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->TrainingID = Request::sendId();

		$this->handleRequest();
	}

	/**
	 * Handle request
	 */
	private function handleRequest() {
		if (strlen(Request::param('public')) > 0)
			Mysql::getInstance()->update(PREFIX.'training', $this->TrainingID, 'is_public', Request::param('public') == 'true' ? 1 : 0);
	}

	/**
	 * Display
	 */
	public function display() {
		echo HTML::h1('Training exportieren');

		if ($this->exporterIsChosen())
			$this->displayChosenExporter();
		else
			$this->displayExporterList();
	}

	/**
	 * Display chosen exporter
	 */
	protected function displayChosenExporter() {
		$Exporter = new ExporterFactory( Request::param('type') );
		$Exporter->display();

		echo HTML::br();
		echo Ajax::window('<a href="'.self::$URL.'?id='.$this->TrainingID.'">&laquo; zur&uuml;ck zur Auswahl</a>', 'small');
	}

	/**
	 * Display list
	 */
	protected function displayExporterList() {
		$ListView = new ExporterListView();
		$ListView->display();

		$this->displayPrivacyInfo();
	}

	/**
	 * Display privacy information
	 */
	protected function displayPrivacyInfo() {
		$Training = new TrainingObject($this->TrainingID);

		if (!$Training->isPublic()) {
			echo HTML::info('Das Training ist derzeit <strong>privat</strong>.<br />
				'.Ajax::window('<a href="'.self::$URL.'?id='.$this->TrainingID.'&public=true">&nbsp;&raquo; jetzt &ouml;ffentlich machen</a>', 'small'));
		} else {
			echo HTML::info('Das Training ist derzeit <strong>&ouml;ffentlich</strong>.<br />
				'.Ajax::window('<a href="'.self::$URL.'?id='.$this->TrainingID.'&public=false">&nbsp;&raquo; jetzt privat machen</a>', 'small'));
		}
	}

	/**
	 * Is an exporter chosen?
	 * @return boolean
	 */
	private function exporterIsChosen() {
		return strlen(Request::param('type')) > 0;
	}
}