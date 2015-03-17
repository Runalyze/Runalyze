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
		if (strlen(Request::param('public')) > 0) {
			$Updater = new Activity\Updater(DB::getInstance());
			$Updater->setAccountID(SessionAccountHandler::getId());
			$Updater->update(new Activity\Object(array(
				'id' => $this->TrainingID,
				Activity\Object::IS_PUBLIC => Request::param('public') == 'true' ? 1 : 0
			)), array(
				Activity\Object::IS_PUBLIC
			));
		}
	}

	/**
	 * Display
	 */
	public function display() {
		echo '<div class="panel-heading">';
		echo HTML::h1( __('Export your training') );
		echo '</div>';

		echo '<div class="panel-content">';
		if ($this->exporterIsChosen())
			$this->displayChosenExporter();
		else
			$this->displayExporterList();
		echo '</div>';
	}

	/**
	 * Display chosen exporter
	 */
	protected function displayChosenExporter() {
		$Exporter = new ExporterFactory( Request::param('type') );
		$Exporter->display();

		echo '<p class="text">&nbsp;</p>';
		echo '<p class="text">'.Ajax::window('<a href="'.self::$URL.'?id='.$this->TrainingID.'">&laquo; '.__('back to list').'</a>', 'small').'</p>';
	}

	/**
	 * Display list
	 */
	protected function displayExporterList() {
		$ListView = new ExporterListView();
		$ListView->display();

		$this->displayPrivacyInfo();
		echo HTML::p('');
		$this->displayExportedFiles();

		Filesystem::checkWritePermissions('inc/export/files/');
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
	 * Display exported files
	 */
	protected function displayExportedFiles() {
		$ListOfFiles = $this->getExistingFiles();
		$Fieldset   = new FormularFieldset( sprintf( __('Up to now you have exported <strong>%d</strong> trainings.'), count($ListOfFiles)) );

		if (strlen(Request::param('delete')) > 0) {
			$index = (int)Request::param('delete');
			if (!isset($ListOfFiles[$index-1])) {
				$Fieldset->addWarning('Don\' do that!');
			} else {
				$Fieldset->addInfo( __('The file has been removed.') );
				Filesystem::deleteFile('export/files/'.$ListOfFiles[$index-1]);
				unset($ListOfFiles[$index-1]);
			}
		} else {
			$Fieldset->setCollapsed();
		}

		if (empty($ListOfFiles)) {
			$Fieldset->addFileBlock('<em>'.__('You did not export any training.').'</em>');
		} else {
			foreach ($ListOfFiles as $i => $File) {
				$String = $File.', '.Filesystem::getFilesize(FRONTEND_PATH.'export/files/'.$File);
				$Link   = '<a href="inc/export/files/'.$File.'" target="_blank">'.$String.'</a>';
				$Delete = Ajax::window('<a class="right small" href="'.self::$URL.'?id='.$this->TrainingID.'&delete='.($i+1).'">['.__('delete').']</a>', 'small');

				$Fieldset->addFileBlock($Delete.$Link);
			}
		}

		$Formular = new Formular();
		$Formular->setId('export-list');
		$Formular->addFieldset($Fieldset);
		$Formular->display();
	}

	/**
	 * Get array with all existing 
	 * @return array 
	 */
	protected function getExistingFiles() {
		$Files = array();
		if ($handle = opendir(FRONTEND_PATH.'export/files/')) {
			while (false !== ($file = readdir($handle))) {
				if (substr($file,0,1) != ".") {
					if (strpos($file, ExporterAbstractFile::fileNameStart()) === 0)
						$Files[] = $file;
				}
			}

			closedir($handle);
		}

		return $Files;
	}

	/**
	 * Is an exporter chosen?
	 * @return boolean
	 */
	private function exporterIsChosen() {
		return strlen(Request::param('type')) > 0;
	}
}