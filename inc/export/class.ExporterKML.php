<?php
/**
 * Exporter for: KML 
 * @author Hannes Christiansen <mail@laufhannes.de>
 */
class ExporterKML extends Exporter {
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
		if (!$this->Training->GpsData()->hasPositionData()) {
			$this->addError('Das Training enth&auml;lt keine GPS-Daten und kann daher nicht als *.kml-Datei gespeichert werden.');

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
		$this->XML->Folder->Placemark->Style->geomColor = self::rgbToKmlColor(CONF_TRAINING_MAP_COLOR);
	}

	/**
	 * Set track
	 */
	protected function setTrack() {
		$Track = '';

		$this->Training->GpsData()->startLoop();
		while ($this->Training->GpsData()->nextStep())
			$Track .= $this->Training->GpsData()->getLongitude().','.$this->Training->GpsData()->getLatitude().NL;

		$this->XML->Folder->Placemark->LineString->addChild('coordinates', $Track);
	}

	/**
	 * Get name for route in kml-file
	 * @return string
	 */
	protected function getNameForKml() {
		return date('Y-m-d', $this->Training->get('time')).': '.$this->Training->getTitle();
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