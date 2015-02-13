<?php
/**
 * This file contains class::ParserLOGBOOKSingle
 * @package Runalyze\Import\Parser
 */

use Runalyze\Configuration;

/**
 * Parser for LOGBOOK files from SportTracks
 *
 * @author Hannes Christiansen
 * @package Runalyze\Import\Parser
 */
class ParserLOGBOOKSingle extends ParserAbstractSingleXML {
	/**
	 * Parse
	 */
	protected function parseXML() {
		if ($this->isCorrectLOGBOOK()) {
			$this->parseValues();
		} else {
			$this->throwNoLOGBOOKError();
		}
	}

	/**
	 * Is a correct file given?
	 * @return bool
	 */
	protected function isCorrectLOGBOOK() {
		return !empty($this->XML->attributes()->startTime);
	}

	/**
	 * Add error: incorrect file
	 */
	protected function throwNoLOGBOOKError() {
		$this->addError( __('Given XML object is not from SportTracks. &lt;Activity&gt;-tag has no attribute \'startTime\'.') );
	}

	/**
	 * Parse general values
	 */
	protected function parseValues() {
		$this->TrainingObject->setTimestamp( strtotime((string)$this->XML['startTime']) );

		if (!empty($this->XML['categoryName']))
			$this->guessSportID( (string)$this->XML['categoryName'] );
		else
			$this->TrainingObject->setSportid( Configuration::General()->mainSport() );

		if (!empty($this->XML['totalTime']))
			$this->TrainingObject->setTimeInSeconds( (int)$this->XML['totalTime'] );

		if (!empty($this->XML['totalDistance']))
			$this->TrainingObject->setDistance( ((int)$this->XML['totalDistance']) / 1000 );

		if (!empty($this->XML['averageHeartRate']))
			$this->TrainingObject->setPulseAvg( (int)$this->XML['averageHeartRate'] );

		if (!empty($this->XML['maximumHeartRate']))
			$this->TrainingObject->setPulseMax( (int)$this->XML['maximumHeartRate'] );

		if (!empty($this->XML['totalCalories']))
			$this->TrainingObject->setCalories( (int)$this->XML['totalCalories'] );

		if (!empty($this->XML['name']))
			$this->TrainingObject->setComment( (string)$this->XML['name'] );

		if (!empty($this->XML['notes']))
			$this->TrainingObject->setNotes( (string)$this->XML['notes'] );

		if (!empty($this->XML['location']))
			$this->TrainingObject->setRoute( (string)$this->XML['location'] );

		if (!empty($this->XML['totalAscend']))
			$this->TrainingObject->setElevation( (int)$this->XML['totalAscend'] );

		$this->parseSplits();
	}

	/**
	 * Parse splits
	 */
	protected function parseSplits() {
		$Times = array();
		$Dists = array();
		$totalDist = 0;
		$hrWithTime = 0;

		if (isset($this->XML->Laps) && !empty($this->XML->Laps)) {
			foreach ($this->XML->Laps->Lap as $Lap) {
				$Times[] = (int)$Lap['totalTime'];

				if (isset($Lap['avgHeartRate']) && (int)$Lap['avgHeartRate'] > 0)
				$hrWithTime += (int)$Lap['totalTime'] * (int)$Lap['avgHeartRate'];
			}
		}

		if (isset($this->XML->DistanceMarkers) && !empty($this->XML->DistanceMarkers)) {
			foreach ($this->XML->DistanceMarkers->DistanceMarker as $Marker) {
				$Dists[] = (int)$Marker['distance']/1000 - $totalDist;
				$totalDist += end($Dists);
			}

			$Dists[] = $this->TrainingObject->getDistance() - $totalDist;
		}

		if (count($Times) > 0 && count($Times) == count($Dists)) {
			for ($i = 0; $i < count($Times); $i++)
				$this->TrainingObject->Splits()->addSplit($Dists[$i], $Times[$i]);
		}

		if ($hrWithTime > 0 && $this->TrainingObject->getPulseAvg() == 0 && $this->TrainingObject->getTimeInSeconds() > 0) {
			$this->TrainingObject->setPulseAvg( round($hrWithTime / $this->TrainingObject->getTimeInSeconds()) );
		}
	}
}