<?php
/**
 * This file contains class::ParserTCXruntasticMultiple
 * @package Runalyze\Import\Parser
 */
/**
 * Parser for TCX with multiple trainings
 *
 * @author Hannes Christiansen & Michael Pohl
 * @package Runalyze\Import\Parser
 */
class ParserTCXruntasticMultiple extends ParserTCXMultiple {
	/**
	 * Parse single training
	 * @param SimpleXMLElement $Training
	 */
	protected function parseSingleTraining(SimpleXMLElement &$Training) {
		$Parser = new ParserTCXruntasticSingle('', $Training);
		$Parser->parse();

		if ($Parser->failed())
			$this->addErrors( $Parser->getErrors() );
		else
			$this->addObject( $Parser->object() );
	}
}