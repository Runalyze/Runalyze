<?php
/**
 * Class: ExporterView
 * @author Hannes Christiansen <mail@laufhannes.de>
 */
class ExporterView {
	/**
	 * URL to call for exporting a training
	 * ID of training must be added
	 * @var string
	 */
	static public $URL = 'call/call.Exporter.export.php';

	/**
	 * ID of training to export
	 * @var int
	 */
	private $trainingId = 0;

	/**
	 * Type for exporter
	 * @var string
	 */
	private $exportType = '';

	/**
	 * Constructor 
	 */
	public function __construct() {
		
	}

	/**
	 * Destructor 
	 */
	public function __destruct() {
		
	}

	/**
	 * Display exporter 
	 */
	public function display() {
		$this->trainingId = Request::sendId();
		$this->exportType = Request::param('type');

		echo HTML::h1('Training exportieren');

		if (!empty($this->exportType))
			$this->displaySpecialExporter();
		else
			$this->displayPossibleExporter();
	}

	/**
	 * Display a given exporter 
	 */
	private function displaySpecialExporter() {
		$Exporter = Exporter::getInstance($this->exportType);

		if (is_null($Exporter)) {
			echo HTML::error('Der gew&auml;hlte Exporter konnte nicht gefunden werden.');
		} elseif ($this->trainingId <= 0) {
			echo HTML::error('Die Trainings-ID ist verloren gegangen. Bitte probiere es erneut.');
		} else {
			$Exporter->export($this->trainingId);

			$Errors = $Exporter->getAllErrors();

			if (empty($Errors) && !is_callable('Exporter'.$this->exportType.'::isWithoutFile'))
				echo HTML::info('
					<small>Das Training wurde erfolgreich exportiert.</small><br />
					<br />
					<a href="inc/export/files/'.$Exporter->getFilename().'"><strong>Herunterladen: '.$Exporter->getFilename().'</strong></a>
					');
			elseif (!empty($Errors))
				foreach ($Errors as $errorMessage)
					echo HTML::error($errorMessage);

			echo HTML::br();
			echo Ajax::window('<a href="'.self::$URL.'?id='.$this->trainingId.'">&laquo; zur&uuml;ck zur Auswahl</a>', 'small');
		}
	}

	/**
	 * Display possible exporter 
	 */
	private function displayPossibleExporter() {
		echo HTML::p('W&auml;hle ein Format aus:');

		$formats = Exporter::getFormats();

		if (empty($formats)) {
			echo HTML::info('Es konnten keine Exporter gefunden werden.');
		} else {
			$List = new BlocklinkList();

			foreach ($formats as $format => $className) {
				if (!is_callable($className.'::isWithoutFile'))
					$Name = '*.'.$format;
				else
					$Name = $format;

				$URL  = self::$URL.'?id='.Request::sendId().'&type='.$format;
				$Icon = 'inc/export/icons/'.strtolower($format).'.png';
				$Link = Ajax::window('<a href="'.$URL.'" style="background-image:url('.$Icon.');"><strong>'.$Name.'</strong></a>', 'small');
				$List->addCompleteLink($Link);
			}

			$List->display();
		}
	}
}