<?php
/**
 * Class: ImporterTCX
 * @author Hannes Christiansen <mail@laufhannes.de>
 */
class ImporterTCX extends Importer {
	/**
	 * Parser
	 * @var ParserTCX
	 */
	protected $Parser = null;

	/**
	 * Plugin for MultiEditor
	 * @var RunalyzePluginTool_MultiEditor
	 */
	protected $MultiEditor = null;

	/**
	 * Save sent TCX-data as file 
	 * @param string $fileName extension ".tcx" is added automatically
	 * @param string $content
	 */
	static public function saveTCX($fileName, $content) {
		Filesystem::writeFile('import/files/'.$fileName.'.tcx', $content);
	}

	/**
	 * Set values for training from file or post-data
	 */
	protected function setTrainingValues() {
		if ($this->hasMultipleFiles())
			$this->createTrainingsFromFiles($_POST['activityIds']);
		else
			$this->parseXML( $this->getFileContentAsString() );
	}

	/**
	 * Have multiple files been sent?
	 * @return boolean
	 */
	private function hasMultipleFiles() {
		return isset($_POST['data']) && $_POST['data'] == 'FINISHED' && is_array($_POST['activityIds']);
	}

	/**
	 * Overwrite standard method to have own formular
	 */
	public function displayHTMLformular() {
		if (!is_null($this->MultiEditor)) {
			$this->MultiEditor->showImportedMessage();
			$this->MultiEditor->display();
		} else {
			parent::displayHTMLformular();
		}
	}

	/**
	 * Parse internal XML-array
	 */
	protected function parseXML( $XML ) {
		$this->Parser = new ParserTCX($XML);

		if ($this->Parser->hasMultipleTrainings()) {
			$IDs = array();

			while ($this->Parser->nextTraining())
				$IDs[] = $this->insertCurrentParserData();

			$this->forwardToMultiEditor($IDs);
		} else {
			$this->Parser->parseTraining();
			$this->setTrainingDataFromParser();
		}

		if (!$this->Parser->worked())
			$this->throwErrorsFromParser();
	}

	/**
	 * Create all trainings from given files
	 * @param array $fileNames
	 */
	protected function createTrainingsFromFiles($fileNames) {
		$IDs   = array();

		foreach ($fileNames as $fileName) {
			$_POST           = array();
			$rawFileContent  = Filesystem::openFileAndDelete('import/files/'.$fileName.'.tcx');
			$this->Parser    = new ParserTCX( ImporterTCX::decodeCompressedData($rawFileContent) );
			$this->Parser->parseTraining();

			if ($this->Parser->worked())
				$IDs[] = $this->insertCurrentParserData();
			else
				$this->throwErrorsFromParser();
		}

		$this->forwardToMultiEditor($IDs);
	}

	/**
	 * Forward all errors from parser to parent class 
	 */
	protected function throwErrorsFromParser() {
		foreach ($this->Parser->getErrors() as $message)
			$this->addError($message);
	}

	/**
	 * Set internal training data from parser 
	 */
	protected function setTrainingDataFromParser() {
		$this->TrainingData = $this->Parser->getFullData();
	}

	/**
	 * Insert data from parser as new training and return new ID
	 * @return int
	 */
	protected function insertCurrentParserData() {
		$this->setTrainingDataFromParser();
		$this->transformTrainingDataToPostData();

		$Importer = new ImporterFormular();
		$Importer->setTrainingValues();
		$Importer->parsePostData();
		$Importer->insertTraining();

		return $Importer->insertedID;
	}

	/**
	 * Forward to MultiEditor
	 * @param array $IDs
	 */
	protected function forwardToMultiEditor($IDs) {
		if (empty($IDs))
			return;

		$_GET['ids'] = implode(',', $IDs);
		
		$this->inserted = true;
		$this->MultiEditor = Plugin::getInstanceFor('RunalyzePluginTool_MultiEditor');
	}

	/**
	 * Add information from temporary file to existing training
	 * @param int $id
	 * @param string $tempFileName
	 */
	public static function addTCXdataToTraining($id, $tempFileName) {
		$Training = new Training($id);
		$Importer = Importer::getInstance($tempFileName);
		$Data     = array();
		$Vars     = array();
		
		if ($Training->get('elevation') == 0)
			$Vars[] = 'elevation';
		
		$Vars[] = 'arr_time';
		$Vars[] = 'arr_lat';
		$Vars[] = 'arr_lon';
		$Vars[] = 'arr_alt';
		$Vars[] = 'arr_dist';
		$Vars[] = 'arr_heart';
		$Vars[] = 'arr_pace';
		
		if ($Training->get('pulse_avg') == 0 && $Training->get('pulse_max') == 0) {
			$Vars[] = 'pulse_avg';
			$Vars[] = 'pulse_max';
		}
			
		if ($Training->Sport()->hasTypes() && $Training->Type()->hasSplits() && strlen($Training->get('splits')) == 0)
			$Vars[] = 'splits';
		
		foreach ($Vars as $var)
			$Data[$var] = $Importer->get($var);
		
		$Editor = new Editor($id, $Data);
		$Editor->performUpdate();
		
		$Errors = $Editor->getErrorsAsArray();
		if (!empty($Errors))
			echo HTML::error(implode('<br />', $Errors));
	}

	/**
	 * Decode from Garmin-Communicator compressed data (base64, gzip)
	 * @param string $string
	 * @return string
	 */
	static public function decodeCompressedData($string) {
		$string = mb_substr($string, mb_strpos($string, "\n") + 1);
		return gzinflate(substr(base64_decode($string),10,-8));
	}
}