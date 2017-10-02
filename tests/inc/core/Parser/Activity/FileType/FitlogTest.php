<?php

namespace Runalyze\Tests\Parser\Activity\FileType;

use Runalyze\Import\Exception\UnsupportedFileException;
use Runalyze\Parser\Activity\FileType\Fitlog;
use Runalyze\Util\LocalTime;

/**
 * @group import
 */
class FitlogTest extends AbstractActivityParserTestCase
{
    /** @var Fitlog */
    protected $Parser;

    public function setUp()
    {
        $this->Parser = new Fitlog();
    }

    public function testIncorrectXmlFile()
    {
        $this->Parser->setFileContent('<any><xml><file></file></xml></any>');

        $this->setExpectedException(UnsupportedFileException::class);

        $this->Parser->parse();
    }

    public function testSingleActivityWithoutTrackdata()
    {
        $this->parseFileContent($this->Parser, '<?xml version="1.0"?>
<FitnessWorkbook xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns="http://www.zonefivesoftware.com/xmlschemas/FitnessLogbook/v2">
  <AthleteLog>
    <Activity StartTime="2013-12-10T00:00:00-03:30">
      <Duration TotalSeconds="2420"/>
      <Distance TotalMeters="10000"/>
      <Calories TotalCal="565"/>
      <Category Name="Laufen"/>
      <Location Name=""/>
    </Activity>
  </AthleteLog>
</FitnessWorkbook>');

        $this->assertEquals(1, $this->Parser->getNumberOfActivities());
        $this->assertEquals('2013-12-10 00:00', LocalTime::date('Y-m-d H:i', $this->Container->Metadata->getTimestamp()));
        $this->assertEquals(-210, $this->Container->Metadata->getTimezoneOffset());
        $this->assertEquals('Laufen', $this->Container->Metadata->getSportName());

        $this->assertEquals(2420, $this->Container->ActivityData->Duration);
        $this->assertEquals(10.0, $this->Container->ActivityData->Distance);
        $this->assertEquals(565, $this->Container->ActivityData->EnergyConsumption);
    }

    public function testFileThatFailedBecauseOfDivisionByZero()
    {
        $this->parseFile($this->Parser, 'sporttracks/20110411_Laufeinheit_division_by_zero.fitlog');

        $this->assertEquals('11-04-2011 18:52', LocalTime::date('d-m-Y H:i', $this->Container->Metadata->getTimestamp()));
        $this->assertEquals(120, $this->Container->Metadata->getTimezoneOffset());

        $this->assertEquals(1399, $this->Container->ActivityData->Duration, '', 30);
        $this->assertEquals(4.09, $this->Container->ActivityData->Distance, '', 0.1);
        $this->assertEquals(361, $this->Container->ActivityData->EnergyConsumption, '', 10);
        $this->assertEquals(161, $this->Container->ActivityData->AvgHeartRate, '', 2);
        $this->assertEquals(176, $this->Container->ActivityData->MaxHeartRate, '', 2);

        $this->checkExpectedRoundData([
            [0, 0.002],
            [331, 1.0],
            [340, 1.0],
            [355, 1.0],
            [332, 1.0],
            [39, 0.087]
        ]);
    }

    public function testIndoorSpinning()
    {
        $this->parseFile($this->Parser, 'sporttracks/spinning.fitlog');

        $this->assertEquals('24-12-2015 12:48', LocalTime::date('d-m-Y H:i', $this->Container->Metadata->getTimestamp()));
        $this->assertEquals(60, $this->Container->Metadata->getTimezoneOffset());

        $this->assertEquals(1803, $this->Container->ActivityData->Duration);
        $this->assertEquals(0.0, $this->Container->ActivityData->Distance);
        $this->assertEquals(108, $this->Container->ActivityData->AvgHeartRate, '', 0.5);
        $this->assertEquals(144, $this->Container->ActivityData->MaxHeartRate);

        $this->assertNotEmpty($this->Container->ContinuousData->Time);
        $this->assertNotEmpty($this->Container->ContinuousData->HeartRate);
    }

    public function testWithPauses()
    {
        $this->parseFile($this->Parser, 'sporttracks/with-pauses.fitlog');

        $this->assertEquals('01-08-2008 10:02', LocalTime::date('d-m-Y H:i', $this->Container->Metadata->getTimestamp()));
        $this->assertEquals(120, $this->Container->Metadata->getTimezoneOffset());

        $this->assertNotEmpty($this->Container->ContinuousData->Time);
        $this->assertNotEmpty($this->Container->ContinuousData->HeartRate);
        $this->assertNotEmpty($this->Container->ContinuousData->Latitude);
        $this->assertNotEmpty($this->Container->ContinuousData->Longitude);
        $this->assertNotEmpty($this->Container->ContinuousData->Altitude);

        $this->assertEquals(4384, $this->Container->ActivityData->Duration);
        $this->assertEquals(4384, end($this->Container->ContinuousData->Time));
        $this->assertEquals(4645, $this->Container->ActivityData->ElapsedTime);
        $this->assertEquals(14.67, $this->Container->ActivityData->Distance);

        $this->checkExpectedPauseData([
            [1408, 33, 150, 112],
            [1771, 228, 150, 108]
        ]);

        $this->assertEquals(4384, $this->Container->Rounds->getTotalDuration(), '', 30);
        $this->assertEquals(14.67, $this->Container->Rounds->getTotalDistance(), '', 1.0);
    }
}
