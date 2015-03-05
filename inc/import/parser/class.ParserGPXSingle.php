<?php
/**
 * This file contains class::ParserGPXSingle
 * @package Runalyze\Import\Parser
 */

use Runalyze\Configuration;

/**
 * Parser for GPX files
 * 
 * @see http://www.topografix.com/GPX/1/1/gpx.xsd
 *
 * @author Hannes Christiansen
 * @package Runalyze\Import\Parser
 */
class ParserGPXSingle extends ParserAbstractSingleXML {
	/**
	 * Last timestamp
	 * @var int
	 */
	protected $lastTimestamp = 0;

	/**
	 * Additional extensions
	 * @var SimpleXMLElement
	 */
	protected $ExtensionXML = null;

	/**
	 * Set extension XML
	 * @param SimpleXMLElement $XML
	 */
	public function setExtensionXML(SimpleXMLElement $XML) {
		$this->ExtensionXML = $XML;
	}

	/**
	 * Parse
	 */
	protected function parseXML() {
		if ($this->isCorrectGPX()) {
			$this->parseGeneralValues();
			$this->parseTrack();
			$this->setGPSarrays();
		} else {
			$this->throwNoGPXError();
		}
	}

	/**
	 * Is a correct file given?
	 * @return bool
	 */
	protected function isCorrectGPX() {
		return !empty($this->XML->trkseg);
	}

	/**
	 * Add error: incorrect file
	 */
	protected function throwNoGPXError() {
		$this->addError( __('Given XML object does not contain any track. &lt;trkseg&gt;-tag could not be located.') );
	}

	/**
	 * Parse general values
	 */
	protected function parseGeneralValues() {
		$this->TrainingObject->setTimestamp( strtotime((string)$this->XML->trkseg->trkpt[0]->time) );
		$this->TrainingObject->setSportid( Configuration::General()->mainSport() );

		if (!empty($this->XML->desc))
			$this->TrainingObject->setComment(strip_tags((string)$this->XML->desc));
	}

	/**
	 * Parse all log entries
	 */
	protected function parseTrack() {
		foreach ($this->XML->trkseg as $TrackSegment) {
			$this->lastTimestamp = 0;
			foreach ($TrackSegment->trkpt as $Point)
				$this->parseTrackpoint($Point);
		}

		$this->parseSpoQExtension();

		if ($this->lastTimestamp > 0 && $this->lastTimestamp > $this->TrainingObject->getTimestamp())
			$this->TrainingObject->setElapsedTime( $this->lastTimestamp - $this->TrainingObject->getTimestamp() );
	}

	/**
	 * Parse trackpoint
	 * @param SimpleXMLElement $Point 
	 */
	protected function parseTrackpoint($Point) {
		if ($this->lastTimestamp == 0) {
			$this->lastTimestamp = strtotime((string)$Point->time);
		}

		if (!empty($Point['lat'])) {
			$lat  = round((double)$Point['lat'], 7);
			$lon  = round((double)$Point['lon'], 7);
			$dist = empty($this->gps['latitude'])
					? 0
					: round(GpsData::distance($lat, $lon, end($this->gps['latitude']), end($this->gps['longitude'])), ParserAbstract::DISTANCE_PRECISION);
		} elseif (count($this->gps['latitude'])) {
			$lat  = end($this->gps['latitude']);
			$lon  = end($this->gps['longitude']);
			$dist = 0;
		} else
			return;

		$this->gps['time_in_s'][] = $this->getTimeOfPoint($Point);
		$this->gps['km'][]        = empty($this->gps['km']) ? $dist : $dist + end($this->gps['km']);
		$this->gps['pace'][]      = $this->getCurrentPace();
		$this->gps['latitude'][]  = $lat;
		$this->gps['longitude'][] = $lon;
		$this->gps['altitude'][]  = (isset($Point->ele)) ? (int)$Point->ele : 0;

		$this->parseExtensionValues($Point);
	}

	/**
	 * Get time of point
	 * @param SimpleXMLElement $Point
	 * @return int
	 */
	private function getTimeOfPoint(SimpleXMLElement &$Point) {
		$newTimestamp        = strtotime((string)$Point->time);
		$timeToAdd           = $newTimestamp - $this->lastTimestamp;
		$this->lastTimestamp = $newTimestamp;

		if (!empty($this->gps['time_in_s']))
			return end($this->gps['time_in_s']) + $timeToAdd;

		return $timeToAdd;
	}

	/**
	 * Parse extension values
	 * @param SimpleXMLElement $Point
	 * @return int
	 */
	private function parseExtensionValues(SimpleXMLElement &$Point) {
		$bpm  = 0;
		$rpm  = 0;
		$temp = 0;

		if (isset($Point->extensions)) {
			if (count($Point->extensions->children('gpxtpx',true)) > 0) {
				if (isset($Point->extensions->children('gpxtpx',true)->TrackPointExtension)) {
					$TPE = $Point->extensions->children('gpxtpx',true)->TrackPointExtension;
					if (count($TPE->children('gpxtpx',true)) > 0 && isset($TPE->children('gpxtpx',true)->hr))
						$bpm = (int)$TPE->children('gpxtpx',true)->hr;

                    if (count($TPE->children('gpxtpx',true)) > 0 && isset($TPE->children('gpxtpx',true)->cad))
						$rpm = (int)$TPE->children('gpxtpx',true)->cad;

                    if (count($TPE->children('gpxtpx',true)) > 0 && isset($TPE->children('gpxtpx',true)->atemp))
						$temp = (float)$TPE->children('gpxtpx',true)->atemp;
				}
			}

			if (count($Point->extensions->children('gpxdata',true)) > 0) {
				if (isset($Point->extensions->children('gpxdata',true)->hr))
					$bpm = (int)$Point->extensions->children('gpxdata',true)->hr;

				if (isset($Point->extensions->children('gpxdata',true)->cadence))
					$rpm = (int)$Point->extensions->children('gpxdata',true)->cadence;

				if (isset($Point->extensions->children('gpxdata',true)->temp))
					$temp = (int)$Point->extensions->children('gpxdata',true)->temp;
			}
		}

		$this->gps['heartrate'][] = $bpm;
		$this->gps['rpm'][]       = $rpm;
		$this->gps['temp'][]      = $temp;
	}

	/**
	 * Parse extension format from SpoQ
	 */
	protected function parseSpoQExtension() {
		if (!is_null($this->ExtensionXML) && count($this->ExtensionXML->children('st',true)) > 0) {
			$Activity = $this->ExtensionXML->children('st',true)->activity;

			if (isset($Activity) && count($Activity->children('st',true)) > 0) {
				$Track = $Activity->children('st',true)->heartRateTrack;

				if (isset($Track)) {
					$num = count($this->gps['time_in_s']);
					$i = 0;

					foreach ($Track->children('st',true)->heartRate as $HR) {
						$attr = $HR->attributes();

						if ($i < $num) {
							$this->gps['heartrate'][$i] = (int)$attr->bpm;
							$i++;
						}
					}
				}
			}
		}
	}
}
