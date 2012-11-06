<?php
/**
 * Class: ImporterSLF
 * @author Hannes Christiansen <mail@laufhannes.de>
 */
class ImporterSLF extends Importer {
	/**
	 * Parser
	 * @var ParserSLF
	 */
	protected $Parser = null;

	/**
	 * Set values for training from file or post-data
	 */
	protected function setTrainingValues() {
		$this->parseXML( $this->getFileContentAsString() );
	}

	/**
	 * Parse internal XML-array
	 */
	protected function parseXML( $XML ) {
		$this->Parser = new ParserSLF($XML);

		$this->Parser->parseTraining();
		$this->setTrainingDataFromParser();

		if (!$this->Parser->worked())
			$this->throwErrorsFromParser();
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
		$creator = $this->get('creator');
		$details = $this->get('creator_details');

		$this->TrainingData = $this->Parser->getFullData();

		if (!empty($creator))
			$this->set('creator', $creator);
		if (!empty($details)) {
			if ($this->get('creator_details') != '')
				$this->set('creator_details', $this->get('creator_details').NL.$details);
			else
				$this->set('creator_details', $details);
		}
	}
}