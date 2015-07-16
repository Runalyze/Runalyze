<?php

class ImporterFiletypeHRMandGPXTest extends PHPUnit_Framework_TestCase {

	/**
	 * @param string $hrmFile
	 * @param string $gpxFile
	 * @return \ImporterHRMandGPX
	 */
	protected function loadFiles($hrmFile, $gpxFile) {
		$HRMimporter = new ImporterFiletypeHRM();
		$HRMimporter->parseFile($hrmFile);

		$GPXimporter = new ImporterFiletypeGPX();
		$GPXimporter->parseFile($gpxFile);

		return new ImporterHRMandGPX($HRMimporter, $GPXimporter);
	}

	protected function checkArraySizes(\TrainingObject $object) {
		$arrays = array(
			'elevation'	=> $object->getArrayAltitudeOriginal(),
			'cadence'	=> $object->getArrayCadence(),
			'distance'	=> $object->getArrayDistance(),
			'heartrate'	=> $object->getArrayHeartrate(),
			'latitude'	=> $object->getArrayLatitude(),
			'longitude'	=> $object->getArrayLongitude(),
			'time'	=> $object->getArrayTime()
		);

		$num = 0;
		$fails = false;
		$result = array();
		foreach ($arrays as $key => $array) {
			$count = count($array);
			if ($count > 1) {
				if ($num == 0) {
					$num = $count;
				} elseif ($num != $count) {
					$fails = true;
				}

				$result[$key] = $count;
			}
		}

		$this->assertFalse($fails, print_r($result, true));
	}

	public function testFiles12010401() {
		$Importer = $this->loadFiles(
			'../tests/testfiles/hrm/12010401.hrm',
			'../tests/testfiles/hrm/12010401.gpx'
		);

		$this->checkArraySizes($Importer->object());
	}

	public function testFiles12010601() {
		$Importer = $this->loadFiles(
			'../tests/testfiles/hrm/12010601.hrm',
			'../tests/testfiles/hrm/12010601.gpx'
		);

		$this->checkArraySizes($Importer->object());
	}

	public function testFiles12011601() {
		$Importer = $this->loadFiles(
			'../tests/testfiles/hrm/12011601.hrm',
			'../tests/testfiles/hrm/12011601.gpx'
		);

		$this->checkArraySizes($Importer->object());
	}

	public function testFiles15031201() {
		$Importer = $this->loadFiles(
			'../tests/testfiles/hrm/15031201.hrm',
			'../tests/testfiles/hrm/15031201.gpx'
		);

		$this->checkArraySizes($Importer->object());
	}

	public function testFiles15031801() {
		$Importer = $this->loadFiles(
			'../tests/testfiles/hrm/15031801.hrm',
			'../tests/testfiles/hrm/15031801.gpx'
		);

		$this->checkArraySizes($Importer->object());
	}
}