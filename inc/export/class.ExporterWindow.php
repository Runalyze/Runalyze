<?php
/**
 * This file contains class::ExporterWindow
 * @package Runalyze\Export
 */

use Runalyze\Model\Activity;

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
	public static $URL = 'call/call.Exporter.export.php';

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
		if (strlen(Request::param('public')) > 0) {
			$Updater = new Activity\Updater(DB::getInstance());
			$Updater->setAccountID(SessionAccountHandler::getId());
			$Updater->update(new Activity\Entity(array(
				'id' => $this->TrainingID,
				Activity\Entity::IS_PUBLIC => Request::param('public') == 'true' ? 1 : 0
			)), array(
				Activity\Entity::IS_PUBLIC
			));
		}
	}

	/**
	 * Display
	 */
	public function display() {
		if ($this->exporterIsChosen())
			$this->displayChosenExporter();
	}

	/**
	 * Display chosen exporter
	 */
	protected function displayChosenExporter() {
		$Exporter = new ExporterFactory( Request::param('type') );
		//print_r($Exporter);
		$Exporter->display();

	}


	/**
	 * Display privacy information
	 */
	protected function displayPrivacyInfo() {
		$Factory = Runalyze\Context::Factory();
		$Activity = $Factory->activity($this->TrainingID);

		if (!$Activity->isPublic()) {
			echo HTML::info( __('The training is currently <strong>private</strong>').'<br>
				'.Ajax::window('<a href="'.self::$URL.'?id='.$this->TrainingID.'&public=true">&nbsp;&raquo; '.__('make it public').'</a>', 'small'));
		} else {
			echo HTML::info( __('The training is currently <strong>public</strong>').'<br>
				'.Ajax::window('<a href="'.self::$URL.'?id='.$this->TrainingID.'&public=false">&nbsp;&raquo; '.__('make it private').'</a>', 'small'));
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