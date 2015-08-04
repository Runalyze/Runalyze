<?php
/**
 * This file contains class::ParserTCXSingleCourse
 * @package Runalyze\Import\Parser
 */
/**
 * Parser for TCX course from Garmin
 *
 * @author Hannes Christiansen
 * @package Runalyze\Import\Parser
 */
class ParserTCXSingleCourse extends ParserAbstractSingleXML {
	/**
	 * Constructor
	 *
	 * This parser reimplements constructor to force $XML-parameter to be given.
	 *
	 * @param string $FileContent file content
	 * @param SimpleXMLElement $XML XML element with <Course> as root tag
	 */
	public function __construct($FileContent, SimpleXMLElement $XML) {
		parent::__construct($FileContent, $XML);
	}

	/**
	 * Parse
	 */
	protected function parseXML() {
		if ($this->isGarminXML()) {
			$this->parseCourse();
			$this->setGPSarrays();
		} else {
			$this->throwNoGarminError();
		}
	}

	/**
	 * Is given XML from garmin?
	 * @return bool
	 */
	protected function isGarminXML() {
		return isset($this->XML->Track);
	}

	/**
	 * Add error: no garmin file
	 */
	protected function throwNoGarminError() {
		$this->addError( __('Given XML object is not from Garmin. &lt;Track&gt;-tag could not be located.') );
	}

	/**
	 * Parse all laps
	 */
	protected function parseCourse() {
		foreach ($this->XML->Track as $Track) {
			foreach ($Track->Trackpoint as $TP) {
				$this->parseTrackpoint($TP);
			}
		}
	}

	/**
	 * Parse one trackpoint
	 * @param SimpleXMLElement $TP
	 */
	protected function parseTrackpoint(&$TP) {
		if (!empty($TP->Position)) {
			$this->gps['latitude'][]  = (double)$TP->Position->LatitudeDegrees;
			$this->gps['longitude'][] = (double)$TP->Position->LongitudeDegrees;
		} elseif (!empty($this->gps['latitude'])) {
			$this->gps['latitude'][]  = end($this->gps['latitude']);
			$this->gps['longitude'][] = end($this->gps['longitude']);
		} else {
			$this->gps['latitude'][]  = 0;
			$this->gps['longitude'][] = 0;
		}

		$this->gps['km'][] = round((float)$TP->DistanceMeters/1000, ParserAbstract::DISTANCE_PRECISION);
		$this->gps['altitude'][] = (int)$TP->AltitudeMeters;
	}
}