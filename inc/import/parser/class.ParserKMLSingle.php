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
	/** @var string */
	protected $CoordinatesXPath = '//coordinates';

	/**
	 * @param string $ns
	 */
	public function setNamespace($ns = 'kml') {
		$this->CoordinatesXPath = '//'.$ns.':coordinates';
	}

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
		$coordinates = $this->XML->xpath($this->CoordinatesXPath);

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
		foreach ($this->XML->xpath($this->CoordinatesXPath) as $coordinates) {
			$lines = preg_split('/\r\n|\r|\n/', (string)$coordinates);

			foreach ($lines as $lineIndex => $line) {
				$parts = explode(',', $line);
				$num = count($parts);

				if ($num == 3 || $num == 2) {
					if (empty($this->gps['km'])) {
						$this->gps['km'][] = 0;
					} elseif ($lineIndex > 0) {
						$this->gps['km'][] = end($this->gps['km']) + round(
							Runalyze\Model\Route\Entity::gpsDistance(
								$parts[1],
								$parts[0],
								end($this->gps['latitude']),
								end($this->gps['longitude'])
							),
							ParserAbstract::DISTANCE_PRECISION
						);
					} else {
						$this->gps['km'][] = end($this->gps['km']);
					}

					$this->gps['latitude'][]  = $parts[1];
					$this->gps['longitude'][] = $parts[0];
					$this->gps['altitude'][]  = ($num > 2) ? $parts[2] : 0;
				}
			}
		}
	}
}