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
	 * Factor to guess pause limit
	 * @var int
	 */
	public static $PAUSE_FACTOR_FROM_AVERAGE_INTERVAL = 10;

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
	 * Boolean flag: look for pauses
	 * @var boolean
	 */
	protected $lookForPauses = false;

	/**
	 * Limit in seconds
	 * Gaps larger than this value will be recognized as pause if activated
	 * @var int [s]
	 */
	protected $limitForPauses = 0;

	/**
	 * Was there a pause currently?
	 * @var bool
	 */
	protected $wasPaused = false;

	/**
	 * @var int
	 */
	protected $pauseDuration = 0;

	/**
	 * Set extension XML
	 * @param SimpleXMLElement $XML
	 */
	public function setExtensionXML(SimpleXMLElement $XML) {
		$this->ExtensionXML = $XML;
	}

	/**
	 * Enable/Disable looking for pauses
	 * @param boolean $flag
	 */
	public function lookForPauses($flag = true) {
		$this->lookForPauses = $flag;
	}

	/**
	 * Parse
	 */
	protected function parseXML() {
		if ($this->isCorrectGPX()) {
			$this->guessLimitForPauses();
			$this->parseGeneralValues();
			$this->parseTrack();
			$this->correctCadenceIfNeeded();
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
	 * Guess limit for pauses
	 */
	protected function guessLimitForPauses() {
		$totalPoints = 0;
		$totalTime = 0;
		$numSegments = count($this->XML->trkseg);

		for ($seg = 0; $seg < $numSegments; ++$seg) {
			$numPoints = count($this->XML->trkseg[$seg]->trkpt);
			$totalPoints += $numPoints;
			$totalTime += strtotime((string)$this->XML->trkseg[$seg]->trkpt[$numPoints-1]->time) - strtotime((string)$this->XML->trkseg[$seg]->trkpt[0]->time);
		}

		$this->limitForPauses = round(self::$PAUSE_FACTOR_FROM_AVERAGE_INTERVAL * $totalTime / $totalPoints);
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
					: round(Runalyze\Model\Route\Entity::gpsDistance($lat, $lon, end($this->gps['latitude']), end($this->gps['longitude'])), ParserAbstract::DISTANCE_PRECISION);
		} elseif (count($this->gps['latitude'])) {
			$lat  = end($this->gps['latitude']);
			$lon  = end($this->gps['longitude']);
			$dist = 0;
		} else
			return;

		$newTime = $this->getTimeOfPoint($Point);

		if ($this->lookForPauses && $this->limitForPauses > 0 && ($newTime - end($this->gps['time_in_s'])) > $this->limitForPauses) {
			$this->wasPaused = true;
			$this->pauseDuration += $newTime - end($this->gps['time_in_s']);

			return;
		}

		$this->gps['time_in_s'][] = $newTime;
		$this->gps['km'][]        = empty($this->gps['km']) ? $dist : $dist + end($this->gps['km']);
		$this->gps['latitude'][]  = $lat;
		$this->gps['longitude'][] = $lon;
		$this->gps['altitude'][]  = (isset($Point->ele)) ? (int)$Point->ele : 0;

		$this->parseExtensionValues($Point);

		if ($this->wasPaused) {
			$num = count($this->gps['heartrate']);

			$this->TrainingObject->Pauses()->add(
				new \Runalyze\Model\Trackdata\Pause(
					$this->gps['time_in_s'][$num-2],
					$this->pauseDuration,
					$this->gps['heartrate'][$num-2],
					$this->gps['heartrate'][$num-1]
				)
			);
			
			$this->wasPaused = false;
			$this->pauseDuration = 0;
		}
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
  	 * Parse metadata
  	 */
  	public function parseMetadata($metadata) {
  	    if(isset($metadata->name))
  		$this->TrainingObject->setComment((string)$metadata->name);
  	    
  	    if(isset($metadata->desc))
  		$this->TrainingObject->setNotes((string)$metadata->desc);
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
