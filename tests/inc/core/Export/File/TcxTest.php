<?php

namespace Runalyze\Export\File;

use Runalyze\View\Activity\FakeContext;
use Runalyze\Model\Activity;
use Runalyze\Model\HRV;
use Runalyze\Model\Route;
use Runalyze\Model\Sport;
use Runalyze\Model\Swimdata;
use Runalyze\Model\Trackdata;

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

        $Importer = new \ImporterFiletypeTCX;
        $Importer->parseString($Exporter->fileContent());

        $this->assertFalse($Importer->failed());
        $this->assertTrue($Importer->object()->hasArrayTime());
        $this->assertFalse($Importer->object()->hasArrayDistance());
        $this->assertFalse($Importer->object()->hasArrayHeartrate());
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

        $Importer = new \ImporterFiletypeTCX;
        $Importer->parseString($Exporter->fileContent());

        $this->assertFalse($Importer->failed());
        $this->assertTrue($Importer->object()->hasArrayTime());
        $this->assertTrue($Importer->object()->hasArrayHeartrate());
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

        $Importer = new \ImporterFiletypeTCX;
        $Importer->parseString($Exporter->fileContent());

        $this->assertFalse($Importer->failed());
        $this->assertTrue($Importer->object()->hasArrayTime());
        $this->assertTrue($Importer->object()->hasArrayDistance());
        $this->assertTrue($Importer->object()->hasArrayHeartrate());
        $this->assertTrue($Importer->object()->hasArrayCadence());
        $this->assertTrue($Importer->object()->hasArrayPower());
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

        $Importer = new \ImporterFiletypeTCX;
        $Importer->parseString($Exporter->fileContent());

        $this->assertFalse($Importer->failed());
        $this->assertTrue($Importer->object()->hasArrayTime());
        $this->assertFalse($Importer->object()->hasArrayDistance());
        $this->assertTrue($Importer->object()->hasArrayHeartrate());
        $this->assertTrue($Importer->object()->hasArrayAltitude());
        $this->assertFalse($Importer->object()->hasPositionData());
    }
}
