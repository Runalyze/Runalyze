<?php
/**
 * This file contains class::ParserKMLSingle
 * @package Runalyze\Import\Parser
 */

use Runalyze\Configuration;

/**
 * Parser for general KML files
 *
 * @author Hannes Christiansen
 * @package Runalyze\Import\Parser
 */
class ParserKMLSingle extends ParserAbstractSingleXML {
	/**
	 * Parse
	 */
	protected function parseXML() {
		if ($this->isCorrectXML()) {
			$this->parseCoordinates();
			$this->setGPSarrays();
		} else {
			$this->throwNoXMLError();
		}
	}

	/**
	 * Is a correct file given?
	 * @return bool
	 */
	protected function isCorrectXML() {
		$coordinates = $this->XML->xpath('//coordinates');

		return !empty($coordinates);
	}

	/**
	 * Add error: incorrect file
	 */
	protected function throwNoXMLError() {
		$this->addError( __('Given XML object does not contain any coordinates.') );
	}

	/**
	 * Parse coordinates
	 */
	protected function parseCoordinates() {
		foreach ($this->XML->xpath('//coordinates') as $coordinates) {
			$lines = preg_split('/\r\n|\r|\n/', (string)$coordinates);

			foreach ($lines as $line) {
				$parts = explode(',', $line);

				if (count($parts) == 3) {
					if (empty($this->gps['km'])) {
						$this->gps['km'][] = 0;
					} else {
						$this->gps['km'][] = end($this->gps['km']) + round(
							GpsData::distance(
								$parts[1],
								$parts[0],
								end($this->gps['latitude']),
								end($this->gps['longitude'])
							),
							ParserAbstract::DISTANCE_PRECISION
						);
					}

					$this->gps['latitude'][]  = $parts[1];
					$this->gps['longitude'][] = $parts[0];
					$this->gps['altitude'][]  = $parts[2];
				}
			}
		}
	}
}