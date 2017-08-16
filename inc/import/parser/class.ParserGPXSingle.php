<?php
/**
 * This file contains class::ParserGPXSingle
 * @package Runalyze\Import\Parser
 */

use Runalyze\Configuration;
use Runalyze\Import\Exception\UnsupportedFileException;

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

	/** @var int */
	protected $LastValidHR = 0;

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
	 * @throws \Runalyze\Import\Exception\UnsupportedFileException
	 */
	protected function throwNoGPXError() {
		throw new UnsupportedFileException('Given XML object does not contain any track. &lt;trkseg&gt;-tag could not be located.');
	}

	/**
	 * Parse general values
	 */
	protected function parseGeneralValues() {
		$this->setTimestampAndTimezoneOffsetWithUtcFixFrom((string)$this->XML->trkseg->trkpt[0]->time);
		$this->TrainingObject->setSportid( Configuration::General()->mainSport() );

		if (!empty($this->XML->desc))
			$this->TrainingObject->setTitle(strip_tags((string)$this->XML->desc));
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
			$totalTime += $this->strtotime((string)$this->XML->trkseg[$seg]->trkpt[$numPoints-1]->time) - $this->strtotime((string)$this->XML->trkseg[$seg]->trkpt[0]->time);
		}

		$this->limitForPauses = round(self::$PAUSE_FACTOR_FROM_AVERAGE_INTERVAL * $totalTime / $totalPoints);
	}

	/**
	 * Parse all log entries
	 */
	protected function parseTrack() {
	    $i = 0;
	    $j = 0;
        $this->lastTimestamp = 0;
        $lookForPauses = $this->lookForPauses;

		foreach ($this->XML->trkseg as $TrackSegment) {
			foreach ($TrackSegment->trkpt as $Point) {
			    if ($i > 0 && !$lookForPauses) {
			        $this->lookForPauses = $j == 0;
                }

				$this->parseTrackpoint($Point);

			    ++$j;
            }

            ++$i;
			$j = 0;
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
			$this->lastTimestamp = $this->strtotime((string)$Point->time);
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
	private function getTimeOfPoint(SimpleXMLElement $Point) {
		$newTimestamp        = $this->strtotime((string)$Point->time);
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
  		$this->TrainingObject->setTitle((string)$metadata->name);

  	    if(isset($metadata->desc))
  		$this->TrainingObject->setNotes((string)$metadata->desc);
  	}

	/**
	 * Parse extension values
	 * @param SimpleXMLElement $Point
	 */
	private function parseExtensionValues(SimpleXMLElement $Point) {
	    $currentNum = count($this->gps['time_in_s']);
		$bpm  = 0;
		$rpm  = 0;
		$temp = 0;
		$altitude = 0;

		if (isset($Point->extensions)) {
			foreach ($this->getExtensionNodesOf($Point->extensions) as $extensionNode) {
                if ($extensionNode instanceof SimpleXMLElement) {
                    if (isset($extensionNode->hr)) {
                        $bpm = (int)$extensionNode->hr;
                    }

                    if (isset($extensionNode->cad)) {
                        $rpm = (int)$extensionNode->cad;
                    } elseif (isset($extensionNode->cadence)) {
                        $rpm = (int)$extensionNode->cadence;
                    }

                    if (isset($extensionNode->temp)) {
                        $temp = (int)$extensionNode->temp;
                    } elseif (isset($extensionNode->atemp)) {
                        $temp = (int)$extensionNode->atemp;
                    }

                    if (isset($extensionNode->altitude)) {
                        $altitude = (int)$extensionNode->altitude;
                    }
                }
            }
		}

		if ($bpm > 0) {
			$this->LastValidHR = $bpm;
		}

		$this->gps['heartrate'][] = $this->LastValidHR;
		$this->gps['rpm'][]       = $rpm;
		$this->gps['temp'][]      = $temp;

		if (0 == $this->gps['altitude'][$currentNum - 1]) {
            $this->gps['altitude'][$currentNum - 1] = $altitude;
        }
	}

	/**
	 * @param SimpleXMLElement $parentNode
	 * @return SimpleXMLElement[]
	 */
	protected function getExtensionNodesOf(SimpleXMLElement $parentNode) {
	    $childNodes = [];

	    foreach (['gpxtpx', 'ns3', 'ns2'] as $namespace) {
            if (
                count($parentNode->children($namespace, true)) > 0 &&
                isset($parentNode->children($namespace, true)->TrackPointExtension) &&
                count($parentNode->children($namespace, true)->TrackPointExtension) > 0
            ) {
                $childNodes[] = $parentNode->children($namespace, true)->TrackPointExtension->children($namespace, true);
            }
        }

		if (count($parentNode->children('gpxdata',true)) > 0) {
            $childNodes[] = $parentNode->children('gpxdata', true);
		}

		return $childNodes;
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
