<?php
/**
 * This file contains class::ParserTCXMultiple
 * @package Runalyze\Import\Parser
 */
/**
 * Parser for TCX with multiple trainings
 *
 * @author Hannes Christiansen
 * @package Runalyze\Import\Parser
 */
class ParserTCXMultiple extends ParserAbstractMultipleXML {
	/**
	 * Parse XML
	 */
	protected function parseXML() {
		$this->parseMultiSportSessions();
		$this->parseStandardActivities();
	}

	/**
	 * Parse multi sport sessions
	 */
	protected function parseMultiSportSessions() {
		if (!isset($this->XML->Activities->MultiSportSession))
			return;

		if (isset($this->XML->Activities->MultiSportSession->FirstSport))
			foreach ($this->XML->Activities->MultiSportSession->FirstSport as $Sport)
				foreach ($Sport->Activity as $Training)
					$this->parseSingleTraining($Training);

		if (isset($this->XML->Activities->MultiSportSession->NextSport))
			foreach ($this->XML->Activities->MultiSportSession->NextSport as $Sport)
				foreach ($Sport->Activity as $Training)
					$this->parseSingleTraining($Training);
	}

	/**
	 * Parse standard activities
	 */
	protected function parseStandardActivities() {
		if (isset($this->XML->Activities->Activity))
			foreach ($this->XML->Activities->Activity as $Training)
				$this->parseSingleTraining($Training);
	}

	/**
	 * Parse single training
	 * @param SimpleXMLElement $Training
	 */
	protected function parseSingleTraining(SimpleXMLElement &$Training) {
		$Parser = new ParserTCXSingle('', $Training);
		$Parser->parse();

		if ($Parser->failed())
			$this->addErrors( $Parser->getErrors() );
		else
			$this->addObject( $Parser->object() );
	}
}