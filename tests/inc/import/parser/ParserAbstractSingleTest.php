<?php

use Runalyze\Util\LocalTime;
use Runalyze\Util\TimezoneLookup;
use Runalyze\Util\TimezoneLookupException;

class ParserAbstractSingle_MockTester extends ParserAbstractSingle
{
	public function __construct($GPSdata, $useGetCurrentPace = false)
	{
		parent::__construct('');

		if ($useGetCurrentPace) {
			$num = count($GPSdata['km']);
			$keys = array_keys($GPSdata);

			for ($i = 0; $i < $num; ++$i) {
				foreach ($keys as $key) {
					$this->gps[$key][] = $GPSdata[$key][$i];
				}

			}
		} else {
			$this->gps = array_merge($this->gps, $GPSdata);
		}
	}

	public function setTimestampAndTimezoneOffsetFrom_Mock($string)
	{
		$this->setTimestampAndTimezoneOffsetFrom($string);
	}

	public function parse()
	{
		$this->setGPSarrays();
	}
}


class ParserAbstractSingleTest extends PHPUnit_Framework_TestCase
{

	public function testGetCurrentPace()
	{
		$Parser = new ParserAbstractSingle_MockTester(array(
			'km'		=> array(0,  0, 0.1, 0.1, 0.1, 0.3, 0.5, 0.5, 0.5, 1.0),
			'time_in_s'	=> array(5, 10,  20,  31,  39,  58,  82, 120, 190, 260)
		), true);
		$Parser->parse();

		$this->assertEquals( array(0,  0, 0.1, 0.1, 0.1, 0.3, 0.5, 0.5, 0.5, 1.0), $Parser->object()->getArrayDistance() );
		$this->assertEquals( array(5, 10,  20,  31,  39,  58,  82, 120, 190, 260), $Parser->object()->getArrayTime() );
	}

	public function testSetPaceFromDistanceAndTime()
	{
		$Parser = new ParserAbstractSingle_MockTester(array(
			'km'		=> array(0,  0, 0.1, 0.1, 0.1, 0.3, 0.5, 0.5, 0.5, 1.0),
			'time_in_s'	=> array(5, 10,  20,  31,  39,  58,  82, 120, 190, 260)
		), false);
		$Parser->parse();

		$this->assertEquals( array(0,  0, 0.1, 0.1, 0.1, 0.3, 0.5, 0.5, 0.5, 1.0), $Parser->object()->getArrayDistance() );
		$this->assertEquals( array(5, 10,  20,  31,  39,  58,  82, 120, 190, 260), $Parser->object()->getArrayTime() );
	}

	public function testThatTimestampAndOffsetAreCorrectedForBerlinInSummer()
	{
		$this->internalTestThatTimestampAndOffsetCorrectionFor(
			[0.0, 52.5243], [0.0, 13.4063],
			'2016-05-01 12:00:00 -01:00',
			'2016-05-01 15:00', 120
		);
	}

	public function testThatTimestampAndOffsetAreCorrectedForBerlinInWinter()
	{
		$this->internalTestThatTimestampAndOffsetCorrectionFor(
			[0.0, 52.5243], [0.0, 13.4063],
			'2016-01-01 12:00:00 -01:00',
			'2016-01-01 14:00', 60
		);
	}

	public function testThatNothingWillChangeIfOffsetsAreEqual()
	{
		$this->internalTestThatTimestampAndOffsetCorrectionFor(
			[0.0, 52.5243], [0.0, 13.4063],
			'2016-01-01 12:00:00 +01:00',
			'2016-01-01 12:00', 60
		);
	}

	protected function internalTestThatTimestampAndOffsetCorrectionFor(array $latitudes, array $longitudes, $originalTimeString, $expectedTimeString, $expectedOffset) {
		if (RUNALYZE_TEST_TZ_LOOKUP) {
			$Parser = new ParserAbstractSingle_MockTester([
				'latitude' => $latitudes,
				'longitude' => $longitudes
			]);
			$Parser->setTimestampAndTimezoneOffsetFrom_Mock($originalTimeString);
			$Parser->parse();

			$this->assertEquals($expectedTimeString, LocalTime::date('Y-m-d H:i', $Parser->object()->getTimestamp()));
			$this->assertEquals($expectedOffset, $Parser->object()->getTimezoneOffset());
		}
	}

}
