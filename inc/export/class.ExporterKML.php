<?php
/**
 * Exporter for: KML 
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
		$this->FileContent = 'Test-KML';

		$this->addError('Der KML-Exporter funktioniert noch nicht.');
	}
}