<?php

namespace Runalyze\Export\File;

use Runalyze\Model\Activity;
use Runalyze\Model\Route;
use Runalyze\Model\Trackdata;
use Runalyze\View\Activity\FakeContext;
use Runalyze\Parser\Activity\FileType\Tcx as TcxParser;

class TcxTest extends \PHPUnit_Framework_TestCase
{
    public function testThatEmptyContextIsNotPossible()
    {
        $Exporter = new Tcx(FakeContext::emptyContext());

        $this->assertFalse($Exporter->isPossible());
    }

	public function testFileCreationForOutdoorActivity()
	{
		$Exporter = new Tcx(FakeContext::outdoorContext());
		$Exporter->createFileWithoutDirectDownload();
	}

	public function testFileCreationForIndoorActivity()
	{
		$Exporter = new Tcx(FakeContext::indoorContext());
		$Exporter->createFileWithoutDirectDownload();
	}

    public function testMinimalExampleWithOnlyTime()
    {
        $Exporter = new Tcx(FakeContext::withDefaultSport(
            new Activity\Entity(array(
                Activity\Entity::TIMESTAMP => time(),
                Activity\Entity::TIMESTAMP_CREATED => time(),
                Activity\Entity::TIME_IN_SECONDS => 600
            )),
            new Trackdata\Entity(array(
                Trackdata\Entity::TIME => array(100, 200, 300, 400, 500, 600)
            ))
        ));
        $Exporter->createFileWithoutDirectDownload();

        $parser = new TcxParser();
        $parser->setFileContent($Exporter->fileContent());
        $parser->parse();

        $this->assertEquals(1, $parser->getNumberOfActivities());
        $this->assertNotEmpty($parser->getActivityDataContainer()->ContinuousData->Time);
        $this->assertNotEmpty($parser->getActivityDataContainer()->ContinuousData->Distance);
        $this->assertNotEmpty($parser->getActivityDataContainer()->ContinuousData->HeartRate);
    }

    public function testMinimalExampleWithTimeAndHeartrate()
    {
        $Exporter = new Tcx(FakeContext::withDefaultSport(
            new Activity\Entity(array(
                Activity\Entity::TIMESTAMP => time(),
                Activity\Entity::TIMESTAMP_CREATED => time(),
                Activity\Entity::TIME_IN_SECONDS => 600
            )),
            new Trackdata\Entity(array(
                Trackdata\Entity::TIME => array(100, 200, 300, 400, 500, 600),
                Trackdata\Entity::HEARTRATE => array(120, 130, 140, 140, 150, 160)
            ))
        ));
        $Exporter->createFileWithoutDirectDownload();

        $parser = new TcxParser();
        $parser->setFileContent($Exporter->fileContent());
        $parser->parse();

        $this->assertEquals(1, $parser->getNumberOfActivities());
        $this->assertNotEmpty($parser->getActivityDataContainer()->ContinuousData->Time);
        $this->assertNotEmpty($parser->getActivityDataContainer()->ContinuousData->HeartRate);
    }

    public function testMinimalExampleWithCompleteTrackdata()
    {
        $Exporter = new Tcx(FakeContext::withDefaultSport(
            new Activity\Entity(array(
                Activity\Entity::TIMESTAMP => time(),
                Activity\Entity::TIMESTAMP_CREATED => time(),
                Activity\Entity::TIME_IN_SECONDS => 600
            )),
            new Trackdata\Entity(array(
                Trackdata\Entity::TIME => array(100, 200, 300, 400, 500, 600),
                Trackdata\Entity::HEARTRATE => array(120, 130, 140, 140, 150, 160),
                Trackdata\Entity::DISTANCE => array(0.4, 0.8, 1.2, 1.6, 2.0, 2.4),
                Trackdata\Entity::CADENCE => array(90, 90, 90, 90, 90, 90),
                Trackdata\Entity::POWER => array(100, 150, 150, 200, 150, 100)
            ))
        ));
        $Exporter->createFileWithoutDirectDownload();

        $parser = new TcxParser();
        $parser->setFileContent($Exporter->fileContent());
        $parser->parse();

        $this->assertEquals(1, $parser->getNumberOfActivities());
        $this->assertNotEmpty($parser->getActivityDataContainer()->ContinuousData->Time);
        $this->assertNotEmpty($parser->getActivityDataContainer()->ContinuousData->Distance);
        $this->assertNotEmpty($parser->getActivityDataContainer()->ContinuousData->HeartRate);
        $this->assertNotEmpty($parser->getActivityDataContainer()->ContinuousData->Cadence);
        $this->assertNotEmpty($parser->getActivityDataContainer()->ContinuousData->Power);
    }

    public function testMinimalExampleWithOnlyElevationForRoute()
    {
        $Exporter = new Tcx(FakeContext::withDefaultSport(
            new Activity\Entity(array(
                Activity\Entity::TIMESTAMP => time(),
                Activity\Entity::TIMESTAMP_CREATED => time(),
                Activity\Entity::TIME_IN_SECONDS => 600
            )),
            new Trackdata\Entity(array(
                Trackdata\Entity::TIME => array(100, 200, 300, 400, 500, 600),
                Trackdata\Entity::HEARTRATE => array(120, 130, 140, 140, 150, 160)
            )),
            new Route\Entity(array(
                Route\Entity::ELEVATIONS_ORIGINAL => array(230, 235, 232, 235, 225, 230)
            ))
        ));
        $Exporter->createFileWithoutDirectDownload();

        $parser = new TcxParser();
        $parser->setFileContent($Exporter->fileContent());
        $parser->parse();

        $this->assertEquals(1, $parser->getNumberOfActivities());
        $this->assertNotEmpty($parser->getActivityDataContainer()->ContinuousData->Time);
        $this->assertNotEmpty($parser->getActivityDataContainer()->ContinuousData->Distance);
        $this->assertNotEmpty($parser->getActivityDataContainer()->ContinuousData->HeartRate);
        $this->assertNotEmpty($parser->getActivityDataContainer()->ContinuousData->Altitude);
        $this->assertNotEmpty($parser->getActivityDataContainer()->ContinuousData->Latitude);
        $this->assertNotEmpty($parser->getActivityDataContainer()->ContinuousData->Longitude);
    }
}
