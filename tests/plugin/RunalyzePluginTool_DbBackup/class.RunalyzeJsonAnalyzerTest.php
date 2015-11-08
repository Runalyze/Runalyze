<?php

require_once FRONTEND_PATH.'../plugin/RunalyzePluginTool_DbBackup/class.RunalyzeBackup.php';
require_once FRONTEND_PATH.'../plugin/RunalyzePluginTool_DbBackup/class.RunalyzeJsonAnalyzer.php';

class RunalyzeJsonAnalyzerTest extends PHPUnit_Framework_TestCase
{

	public function testVersionExtraction()
	{
		$Compare = [
			'' => '',
			'no version' => '',
			'.0.1' => '',
			'0' => '',
			'1.0' => '1.0',
			'2.34' => '2.34',
			'2.5-dev' => '2.5',
			'2.6rc' => '2.6',
			'3.0.0-alpha' => '3.0',
			'3.0.1' => '3.0',
			'45.6-beta' => '45.6'
		];

		foreach ($Compare as $string => $version) {
			$this->assertEquals($version, RunalyzeJsonAnalyzer::extractMajorAndMinorVersion($string));
		}
	}

	public function testVersionExtractionForUnknownVersion()
	{
		$Analyzer = new RunalyzeJsonAnalyzer('../tests/testfiles/backup/no-version.json.gz');

		$this->assertFalse($Analyzer->fileIsOkay());
		$this->assertFalse($Analyzer->versionIsOkay());
		$this->assertEquals('unknown', $Analyzer->fileVersion());
	}

	public function testVersionExtractionForWrongVersion()
	{
		$Analyzer = new RunalyzeJsonAnalyzer('../tests/testfiles/backup/wrong-version.json.gz');

		$this->assertFalse($Analyzer->fileIsOkay());
		$this->assertFalse($Analyzer->versionIsOkay());
		$this->assertEquals('v1.0-alpha', $Analyzer->fileVersion());
	}

	public function testVersionExtractionForDefaultEmpty()
	{
		$Analyzer = new RunalyzeJsonAnalyzer('../tests/testfiles/backup/default-empty.json.gz');

		$this->assertTrue($Analyzer->fileIsOkay());
		$this->assertTrue($Analyzer->versionIsOkay());
	}

	public function testVersionExtractionForDefaultInsert()
	{
		$Analyzer = new RunalyzeJsonAnalyzer('../tests/testfiles/backup/default-insert.json.gz');

		$this->assertTrue($Analyzer->fileIsOkay());
		$this->assertTrue($Analyzer->versionIsOkay());
	}

	public function testVersionExtractionForDefaultUpdate()
	{
		$Analyzer = new RunalyzeJsonAnalyzer('../tests/testfiles/backup/default-update.json.gz');

		$this->assertTrue($Analyzer->fileIsOkay());
		$this->assertTrue($Analyzer->versionIsOkay());
	}

	public function testVersionExtractionForWithEquipment()
	{
		$Analyzer = new RunalyzeJsonAnalyzer('../tests/testfiles/backup/with-equipment.json.gz');

		$this->assertTrue($Analyzer->fileIsOkay());
		$this->assertTrue($Analyzer->versionIsOkay());
	}

}
