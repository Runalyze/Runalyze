<?php
/**
 * This file contains class::ExporterTCX
 * @package Runalyze\Export\Types
 */

use Runalyze\Model\Trackdata;
use Runalyze\Model\Route;

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
		if (!$this->Context->hasRoute() || !$this->Context->route()->hasPositionData()) {
			$this->addError( __('The training does not contain gps-data and cannot be saved as tcx-file.') );

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
		$this->Activity->addChild('Id', $this->timeToString($this->Context->activity()->timestamp()));
	}

	/**
	 * Add all laps to xml 
	 */
	protected function setLaps() {
		$Starttime = $this->Context->activity()->timestamp();

		$Loop = new Trackdata\Loop($this->Context->trackdata());

		while (!$Loop->isAtEnd()) {
			$Loop->nextKilometer();
			$TimeInSeconds = $Loop->difference(Trackdata\Object::TIME);

			$Lap = $this->Activity->addChild('Lap');
			$Lap->addAttribute('StartTime', $this->timeToString($Starttime + $Loop->time() - $TimeInSeconds));
			$Lap->addChild('TotalTimeSeconds', $TimeInSeconds);
			$Lap->addChild('DistanceMeters', 1000*$Loop->difference(Trackdata\Object::DISTANCE));

			if ($this->Context->trackdata()->has(Trackdata\Object::HEARTRATE)) {
				$AvgBpm = $Lap->addChild('AverageHeartRateBpm');
				$AvgBpm->addChild('Value', $Loop->average(Trackdata\Object::HEARTRATE));
				$MaxBpm = $Lap->addChild('MaximumHeartRateBpm');
				$MaxBpm->addChild('Value', $Loop->max(Trackdata\Object::HEARTRATE));
			}

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
		$Starttime = $this->Context->activity()->timestamp();

		$Trackdata = new Trackdata\Loop($this->Context->trackdata());
		$Route = new Route\Loop($this->Context->route());

		$hasElevation = $this->Context->route()->hasOriginalElevations();
		$hasHeartrate = $this->Context->trackdata()->has(Trackdata\Object::HEARTRATE);

		while ($Trackdata->nextStep()) {
			$Route->nextStep();

			if ($this->Activity->Lap[(int)floor($Trackdata->distance())]) {
				$Trackpoint = $this->Activity->Lap[(int)floor($Trackdata->distance())]->Track->addChild('Trackpoint');
				$Trackpoint->addChild('Time', $this->timeToString($Starttime + $Trackdata->time()));

				$Position = $Trackpoint->addChild('Position');
				$Position->addChild('LatitudeDegrees', $Route->latitude());
				$Position->addChild('LongitudeDegrees', $Route->longitude());

				if ($hasElevation) {
					$Trackpoint->addChild('AltitudeMeters', $Route->current(Route\Object::ELEVATIONS_ORIGINAL));
				}

				$Trackpoint->addChild('DistanceMeters', 1000*$Trackdata->distance());

				if ($hasHeartrate) {
					$Heartrate = $Trackpoint->addChild('HeartRateBpm');
					$Heartrate->addChild('Value', $Trackdata->current(Trackdata\Object::HEARTRATE));
				}
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