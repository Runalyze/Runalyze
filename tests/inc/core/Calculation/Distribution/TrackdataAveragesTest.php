<?php

namespace Runalyze\Calculation\Distribution;

use Runalyze\Model\Trackdata;

class TrackdataAveragesTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testNoTimeData()
    {
        new TrackdataAverages(new Trackdata\Entity([]), []);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testEmptyTimeData()
    {
        new TrackdataAverages(
            new Trackdata\Entity([
                Trackdata\Entity::TIME => [0, 0, 0, 0, 0]
            ]), []
        );
    }

    public function testNoRequestedKeys()
    {
        $Averages = new TrackdataAverages(
            new Trackdata\Entity([
                Trackdata\Entity::TIME => [1, 2, 3, 4, 5]
            ]), []
        );

        $this->assertEquals([], $Averages->averages());
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testInvalidAverageKey()
    {
        $Averages = new TrackdataAverages(
            new Trackdata\Entity([
                Trackdata\Entity::TIME => [1, 2, 3, 4, 5]
            ]), [
                Trackdata\Entity::HEARTRATE
            ]
        );

        $Averages->average(Trackdata\Entity::CADENCE);
    }

    public function testSimpleAverages()
    {
        $Averages = new TrackdataAverages(
            new Trackdata\Entity([
                Trackdata\Entity::TIME => [1, 3, 5, 10],
                Trackdata\Entity::HEARTRATE => [120, 130, 140, 150]
            ]), [
                Trackdata\Entity::HEARTRATE,
                Trackdata\Entity::CADENCE
            ]
        );

        $this->assertEquals(141, $Averages->average(Trackdata\Entity::HEARTRATE));
        $this->assertEquals(null, $Averages->average(Trackdata\Entity::CADENCE));
    }

    public function testAveragesWithZero()
    {
        $Averages = new TrackdataAverages(
            new Trackdata\Entity([
                Trackdata\Entity::TIME => [1, 3, 5, 10],
                Trackdata\Entity::HEARTRATE => [0, 0, 0, 0],
                Trackdata\Entity::CADENCE => [120, 120, 0, 0],
                Trackdata\Entity::STRIDE_LENGTH => [1, 1, 0, 0]
            ]), [
                Trackdata\Entity::HEARTRATE,
                Trackdata\Entity::CADENCE,
                Trackdata\Entity::STRIDE_LENGTH
            ]
        );

        $this->assertEquals(0, $Averages->average(Trackdata\Entity::HEARTRATE));
        $this->assertEquals(120, $Averages->average(Trackdata\Entity::CADENCE));
        $this->assertEquals(1, $Averages->average(Trackdata\Entity::STRIDE_LENGTH));
    }

}
