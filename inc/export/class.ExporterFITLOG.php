<?php
/**
 * Exporter for: FITLOG 
 * @author Hannes Christiansen <mail@laufhannes.de>
 */
class ExporterFITLOG extends Exporter {
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
		return 'fitlog';
	}

	/**
	 * Set file content
	 */
	protected function setFileContent() {
		$this->XML = new SimpleXMLElement($this->getEmptyXml());
		$this->Activity = $this->XML->AthleteLog->Activity;

		$this->setGeneralInfo();
		$this->setTrack();	

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
		if (strlen(SessionHandler::getName()) > 0)
			$this->XML->AthleteLog->Athlete->addAttribute('Name', SessionHandler::getName());

		$this->Activity->addAttribute('StartTime', $this->timeToString($this->Training->get('time')));

		$this->Activity->Duration->addAttribute('TotalSeconds', (int)$this->Training->get('s'));
		$this->Activity->Distance->addAttribute('TotalMeters', 1000*$this->Training->get('distance'));
		$this->Activity->Calories->addAttribute('TotalCal', $this->Training->get('kcal'));
		$this->Activity->Category->addAttribute('Name', $this->Training->Sport()->name());
		$this->Activity->Location->addAttribute('Name', $this->Training->get('route'));
	}

	/**
	 * Add track to xml 
	 */
	protected function setTrack() {
		if (!$this->Training->hasPositionData())
			return;

		$Starttime = $this->Training->get('time');
		$GPS = $this->Training->GpsData();
		$GPS->startLoop();

		$Track = $this->Activity->addChild('Track');
		$Track->addAttribute('StartTime', $this->timeToString($Starttime));

		while ($GPS->nextStep()) {
			$Point = $Track->addChild('pt');
			$Point->addAttribute('tm', $GPS->getTime());
			$Point->addAttribute('lat', $GPS->getLatitude());
			$Point->addAttribute('lon', $GPS->getLongitude());
			$Point->addAttribute('ele', $GPS->getElevation());
			$Point->addAttribute('hr', $GPS->getHeartrate());
		}
	}

	/**
	 * Get empty xml
	 * @return string 
	 */
	protected function getEmptyXml() {
		return
'<FitnessWorkbook xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns="http://www.zonefivesoftware.com/xmlschemas/FitnessLogbook/v2">
 <AthleteLog>
  <Athlete />
  <Activity>
   <Duration />
   <Distance />
   <Calories />
   <Category />
   <Location />
  </Activity>
 </AthleteLog>
</FitnessWorkbook>';
	}
}