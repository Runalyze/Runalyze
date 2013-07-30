<?php
/**
 * This file contains class::ExporterTCX
 * @package Runalyze\Export\Types
 */
/**
 * Exporter for: TCX
 * 
 * @author Hannes Christiansen
 * @package Runalyze\Export\Types
 */
class ExporterTCX extends ExporterAbstractFile {
	/**
	 * XML construct
	 * @var SimpleXMLElement
	 */
	private $XML = null;

	/**
	 * Activity-part of XML construct
	 * @var SimpleXMLElement 
	 */
	private $Activity = null;

	/**
	 * Get extension
	 * @return string 
	 */
	protected function getExtension() {
		return 'tcx';
	}

	/**
	 * Set file content
	 */
	protected function setFileContent() {
		if (!$this->Training->GpsData()->hasPositionData()) {
			$this->addError('Das Training enth&auml;lt keine GPS-Daten und kann daher nicht als *.tcx-Datei gespeichert werden.');

			return;
		}

		$this->XML = new SimpleXMLElement($this->getEmptyXml());
		$this->Activity = $this->XML->Activities->Activity;

		$this->setGeneralInfo();
		$this->setLaps();	

		$this->FileContent = $this->XML->asXML();

		$this->formatFileContentAsXML();
	}

	/**
	 * Get string for timestamp in xml
	 * @param int $time
	 * @return string 
	 */
	final protected function timeToString($time) {
		return date("c", $time);
	}

	/**
	 * Set general info 
	 */
	protected function setGeneralInfo() {
		if ($this->Training->Sport()->isRunning())
			$this->Activity->addAttribute('Sport', 'Running');

		$this->Activity->addChild('Id', $this->timeToString($this->Training->get('time')));
	}

	/**
	 * Add all laps to xml 
	 */
	protected function setLaps() {
		$Starttime = $this->Training->get('time');

		$GPS = $this->Training->GpsData();
		$GPS->startLoop();

		while ($GPS->nextKilometer()) {
			$TimeInSeconds = $GPS->getTimeOfStep();

			$Lap = $this->Activity->addChild('Lap');
			$Lap->addAttribute('StartTime', $this->timeToString($Starttime + $GPS->getTime() - $TimeInSeconds));
			$Lap->addChild('TotalTimeSeconds', $TimeInSeconds);
			$Lap->addChild('DistanceMeters', 1000*$GPS->getDistanceOfStep());

			$AvgBpm = $Lap->addChild('AverageHeartRateBpm');
			$AvgBpm->addChild('Value', $GPS->getAverageHeartrateOfStep());
			$MaxBpm = $Lap->addChild('MaximumHeartRateBpm');
			$MaxBpm->addChild('Value', $GPS->getMaximumHeartrateOfStep());

			$Lap->addChild('Intensity', 'Active');
			$Lap->addChild('TriggerMethod', 'Distance');
			$Lap->addChild('Track');

			// TODO: Calories?
		}

		$this->setTrack();
	}

	/**
	 * Add track to all laps to xml 
	 */
	protected function setTrack() {
		$Starttime = $this->Training->get('time');
		$GPS = $this->Training->GpsData();
		$GPS->startLoop();

		$hasElevation = $this->Training->hasArrayAltitude();
		$hasHeartrate = $this->Training->hasArrayHeartrate();

		while ($GPS->nextStep()) {
			$Trackpoint = $this->Activity->Lap[(int)$GPS->currentKilometer()]->Track->addChild('Trackpoint');
			$Trackpoint->addChild('Time', $this->timeToString($Starttime + $GPS->getTime()));

			$Position = $Trackpoint->addChild('Position');
			$Position->addChild('LatitudeDegrees', $GPS->getLatitude());
			$Position->addChild('LongitudeDegrees', $GPS->getLongitude());

			if ($hasElevation)
				$Trackpoint->addChild('AltitudeMeters', $GPS->getElevation());
			$Trackpoint->addChild('DistanceMeters', 1000*$GPS->getDistance());

			if ($hasHeartrate) {
				$Heartrate = $Trackpoint->addChild('HeartRateBpm');
				$Heartrate->addChild('Value', $GPS->getHeartrate());
			}
		}
	}

	/**
	 * Get empty xml
	 * @return string 
	 */
	protected function getEmptyXml() {
		return
'<TrainingCenterDatabase xmlns="http://www.garmin.com/xmlschemas/TrainingCenterDatabase/v2" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.garmin.com/xmlschemas/TrainingCenterDatabase/v2 http://www.garmin.com/xmlschemas/TrainingCenterDatabasev2.xsd">
	<Activities>
		<Activity>
		</Activity>
	</Activities>
	<Author xsi:type="Application_t">
		<Name>Runalyze</Name>
	</Author>
</TrainingCenterDatabase>';
	}
}