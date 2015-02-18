<?php
/**
 * This file contains class::ExporterKML
 * @package Runalyze\Export\Types
 */

use Runalyze\Configuration;

/**
 * Exporter for: KML
 * 
 * @author Hannes Christiansen
 * @package Runalyze\Export\Types
 */
class ExporterKML extends ExporterAbstractFile {
	/**
	 * XML construct
	 * @var SimpleXMLElement
	 */
	private $XML = null;

	/**
	 * Get extension
	 * @return string 
	 */
	protected function getExtension() {
		return 'kml';
	}

	/**
	 * Set file content
	 */
	protected function setFileContent() {
		if (!$this->Context->hasRoute() || !$this->Context->route()->hasPositionData()) {
			$this->addError( __('The training does not contain gps-data and cannot be saved as kml-file.') );

			return;
		}

		$this->XML = new SimpleXMLElement($this->getEmptyXml());

		$this->setGeneralInfo();
		$this->setTrack();	

		$this->FileContent = $this->XML->asXML();

		$this->formatFileContentAsXML();
	}

	/**
	 * Set general info 
	 */
	protected function setGeneralInfo() {
		$this->XML->Folder->name = $this->getNameForKml();
		$this->XML->Folder->Placemark->name = $this->getNameForKml();
		$this->XML->Folder->Placemark->Style->geomColor = self::rgbToKmlColor( Configuration::ActivityView()->routeColor() );
	}

	/**
	 * Set track
	 */
	protected function setTrack() {
		$Track = '';

		$Loop = new Runalyze\Model\Route\Loop($this->Context->route());

		while ($Loop->nextStep()) {
			$Track .= $Loop->longitude().','.$Loop->latitude().NL;
		}

		$this->XML->Folder->Placemark->LineString->addChild('coordinates', $Track);
	}

	/**
	 * Get name for route in kml-file
	 * @return string
	 */
	protected function getNameForKml() {
		return $this->Context->dataview()->date('Y-m-d').': '.$this->Context->dataview()->titleWithComment();
	}

	/**
	 * Get empty xml
	 * @return string 
	 */
	protected function getEmptyXml() {
		return
'<kml xmlns="http://earth.google.com/kml/2.0">
  <Folder>
    <open>1</open>
    <name></name>
    <Placemark>
      <visibility>1</visibility>
      <name></name>
      <Style>
        <geomScale>2</geomScale>
        <geomColor></geomColor>
      </Style>
      <LineString>
      </LineString>
    </Placemark>
  </Folder>
</kml>';
	}

	/**
	 * Transform rgb-color (i.e. #FF7700 or FF7700) to abgr-color needed for kml
	 * @param string $rgb
	 * @return string 
	 */
	static protected function rgbToKmlColor($rgb) {
		if (strlen($rgb) == 7)
			$rgb = substr($rgb, 1);

		return 'ff'.substr($rgb, 4, 2).substr($rgb, 2, 2).substr($rgb, 0, 2);
	}
}