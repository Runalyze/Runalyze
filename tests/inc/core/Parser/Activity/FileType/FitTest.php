<?php

namespace Runalyze\Tests\Parser\Activity\FileType;

use Runalyze\Parser\Activity\Common\Data\ActivityDataContainer;
use Runalyze\Parser\Activity\Converter\FitConverter;
use Runalyze\Parser\Activity\FileType\Fit;
use Runalyze\Util\LocalTime;

/**
 * @group import
 */
class FitTest extends AbstractActivityParserTestCase
{
    /** @var Fit */
    protected $Parser;

    /** @var FitConverter */
    protected $Converter;

    public function setUp()
    {
        $this->Parser = new Fit();
        $this->Converter = new FitConverter(
            PERL_PATH,
            TESTS_ROOT.'/../call/perl/fittorunalyze.pl'
        );
    }

    /**
     * @param string $file file path relative to 'testfiles/'
     * @param bool $completeAfterwards
     * @param bool $runFilter
     */
    protected function convertAndParse($file, $completeAfterwards = true, $runFilter = true)
    {
        $outputFile = $this->Converter->convertFile($this->pathToTestFiles().$file);
        $this->FilesToClear[] = $outputFile;

        $this->Parser->setFileName($outputFile);
        $this->Parser->parse();

        $this->setContainerFrom($this->Parser, $completeAfterwards, $runFilter);
    }

    protected function checkThatTimeIsStrictIncreasing(ActivityDataContainer $container = null)
    {
        $container = $container ?: $this->Container;

		$num = count($container->ContinuousData->Time);

		for ($i = 2; $i < $num; ++$i) {
		    $this->assertGreaterThan(
		        $container->ContinuousData->Time[$i - 1],
		        $container->ContinuousData->Time[$i],
		        sprintf('Time array is not continuously increasing at index %u', $i)
	        );
		}
    }

    public function testStandardFile()
    {
        $this->convertAndParse('fit/Standard.fit');

        $this->assertEquals('2014-03-29 12:17', LocalTime::date('Y-m-d H:i', $this->Container->Metadata->getTimestamp()));
        $this->assertEquals(8.983, $this->Container->ActivityData->Distance, '', 0.001);
        $this->assertEquals(124, $this->Container->ActivityData->AvgHeartRate, '', 0.5);
        $this->assertEquals(146, $this->Container->ActivityData->MaxHeartRate);
        $this->assertEquals(305, $this->Container->ActivityData->EnergyConsumption);

        $this->assertNotEmpty($this->Container->ContinuousData->Time);
        $this->assertNotEmpty($this->Container->ContinuousData->Latitude);
        $this->assertNotEmpty($this->Container->ContinuousData->Longitude);
        $this->assertNotEmpty($this->Container->ContinuousData->Altitude);
        $this->assertNotEmpty($this->Container->ContinuousData->Distance);
        $this->assertNotEmpty($this->Container->ContinuousData->HeartRate);
        $this->assertEmpty($this->Container->ContinuousData->GroundContactTime);
        $this->assertEmpty($this->Container->ContinuousData->VerticalOscillation);

        $this->assertFalse($this->Container->Rounds->isEmpty());
    }

	public function testFenix2File()
	{
        $this->convertAndParse('fit/Fenix-2.fit');

        $this->assertEquals(975, $this->Container->ActivityData->Duration);
        $this->assertEquals(1210, $this->Container->ActivityData->ElapsedTime);
        $this->assertEquals(2.94, $this->Container->ActivityData->Distance, '', 0.01);
        $this->assertEquals(137, $this->Container->ActivityData->AvgHeartRate, '', 0.5);
        $this->assertEquals(169, $this->Container->ActivityData->MaxHeartRate);
        $this->assertEquals(159, $this->Container->ActivityData->EnergyConsumption);
        $this->assertEquals(216, $this->Container->ActivityData->AvgGroundContactTime, '', 0.5);
        $this->assertEquals(92, $this->Container->ActivityData->AvgVerticalOscillation, '', 0.5);

        $this->assertNotEmpty($this->Container->ContinuousData->Time);
        $this->assertNotEmpty($this->Container->ContinuousData->Latitude);
        $this->assertNotEmpty($this->Container->ContinuousData->Longitude);
        $this->assertNotEmpty($this->Container->ContinuousData->Altitude);
        $this->assertNotEmpty($this->Container->ContinuousData->Distance);
        $this->assertNotEmpty($this->Container->ContinuousData->HeartRate);
        $this->assertNotEmpty($this->Container->ContinuousData->Temperature);
        $this->assertNotEmpty($this->Container->ContinuousData->GroundContactTime);
        $this->assertNotEmpty($this->Container->ContinuousData->VerticalOscillation);

        $this->assertFalse($this->Container->Rounds->isEmpty());

		$this->assertEquals(53, $this->Container->FitDetails->VO2maxEstimate);
		$this->assertEquals(816, $this->Container->FitDetails->RecoveryTime);
	}

	public function testFenix2FileWithPauses()
	{
        $this->convertAndParse('fit/Fenix-2-pauses.fit');

        $this->assertEquals(2810, $this->Container->ActivityData->Duration);
        $this->assertEquals(3046, $this->Container->ActivityData->ElapsedTime);
        $this->assertEquals(10.547, $this->Container->ActivityData->Distance, '', 0.001);

        $this->assertEquals(2810, end($this->Container->ContinuousData->Time), '', 2);

		$this->assertEquals(65, $this->Container->FitDetails->VO2maxEstimate);
		$this->assertEquals(932, $this->Container->FitDetails->RecoveryTime);

        $this->checkExpectedPauseData([
			 [267, 14, 144, 130],
			 [465, 53, 151, 104],
			 [1491, 73, 145, 106],
			 [2575, 35, 139, 111],
			 [2804, 51, 136, 100],
			 [2970, 9, 150, 144]
        ], 1);
	}

	public function testFenix2FileWithNegativeTime()
	{
        $this->convertAndParse('fit/Fenix-2-negative-times.fit');

        $this->assertEquals('2014-08-28 09:32', LocalTime::date('Y-m-d H:i', $this->Container->Metadata->getTimestamp()));
        $this->assertEquals(2*3600 + 35*60 + 21, $this->Container->ActivityData->Duration);

        $this->assertNotEmpty($this->Container->ContinuousData->Time);
        $this->assertGreaterThanOrEqual(0, min($this->Container->ContinuousData->Time));
	}

	public function testOtherStartEventsInFR920()
	{
        $this->convertAndParse('fit/FR920-additional-start-events.fit');

        $this->assertEquals(1, $this->NumberOfActivities);

        $this->assertEquals(2*3600 + 47*60 + 22, $this->Container->ActivityData->Duration);

        $this->assertNotEmpty($this->Container->ContinuousData->Time);
        $this->assertGreaterThanOrEqual(0, min($this->Container->ContinuousData->Time));
        $this->assertEquals(2*3600 + 47*60 + 22, end($this->Container->ContinuousData->Time));
	}

	public function testMultisessionFile()
	{
        $this->convertAndParse('fit/Multisession.fit');

        $this->assertEquals(2, $this->NumberOfActivities);

        $this->assertEquals('2015-05-23 12:52', LocalTime::date('Y-m-d H:i', $this->Container[0]->Metadata->getTimestamp()));
        $this->assertEquals(1131, $this->Container[0]->ActivityData->Duration);
        $this->assertEquals(1173, $this->Container[0]->ActivityData->ElapsedTime);
        $this->assertEquals(4.111, $this->Container[0]->ActivityData->Distance, '', 0.001);

        $this->assertEquals('2015-05-23 14:31', LocalTime::date('Y-m-d H:i', $this->Container[1]->Metadata->getTimestamp()));
        $this->assertEquals(1001, $this->Container[1]->ActivityData->Duration);
        $this->assertEquals(1118, $this->Container[1]->ActivityData->ElapsedTime);
        $this->assertEquals(3.746, $this->Container[1]->ActivityData->Distance, '', 0.001);
        $this->assertEquals(1001, end($this->Container[1]->ContinuousData->Time), '', 2);
	}

	public function testSimplePauseExample()
	{
        $this->convertAndParse('fit/HRV-example.fit');

        $this->assertEquals(60, $this->Container->ActivityData->Duration);
        $this->assertEquals(129, $this->Container->ActivityData->ElapsedTime);
        $this->assertEquals(70, $this->Container->ActivityData->AvgHeartRate, '', 0.5);
        $this->assertEquals(100, $this->Container->ActivityData->MaxHeartRate);

        $this->assertEquals(60, end($this->Container->ContinuousData->Time), '', 2);

        $this->checkExpectedPauseData([
			 [41, 69, 100, 69]
        ]);
	}

	public function testSimpleSwimmingFileFromFR910XT()
	{
        $this->convertAndParse('fit/swim-25m-lane.fit');

        $this->assertEquals('2015-06-17 07:34', LocalTime::date('Y-m-d H:i', $this->Container->Metadata->getTimestamp()));
		$this->assertEquals(120, $this->Container->Metadata->getTimezoneOffset());

        $this->assertEquals(2116, $this->Container->ActivityData->Duration);
        $this->assertEquals(2354, $this->Container->ActivityData->ElapsedTime);
        $this->assertEquals(1.95, $this->Container->ActivityData->Distance);

		$this->assertEquals('fr910xt', $this->Container->Metadata->getCreator());
		$this->assertEquals(2500, $this->Container->ActivityData->PoolLength);
		$this->assertEquals(890, $this->Container->ActivityData->TotalStrokes);
		$this->assertEquals(25, $this->Container->ActivityData->AvgCadence, '', 0.5);

        $this->assertNotEmpty($this->Container->ContinuousData->Time);
        $this->assertNotEmpty($this->Container->ContinuousData->Strokes);
        $this->assertNotEmpty($this->Container->ContinuousData->StrokeType);

        $this->assertEmpty($this->Container->ContinuousData->Distance);

		$this->assertNotEquals(0, $this->Container->ContinuousData->Time[0]);
	}

	public function testSwimmingFileFromFenix3()
	{
        $this->convertAndParse('fit/swim-fenix-50m.fit');

        $this->assertEquals('2015-07-18 08:29', LocalTime::date('Y-m-d H:i', $this->Container->Metadata->getTimestamp()));
		$this->assertEquals(120, $this->Container->Metadata->getTimezoneOffset());

        $this->assertEquals(3272, $this->Container->ActivityData->Duration);
        $this->assertEquals(3817, $this->Container->ActivityData->ElapsedTime);
        $this->assertEquals(2.05, $this->Container->ActivityData->Distance);

		$this->assertEquals('fenix3', $this->Container->Metadata->getCreator());
		$this->assertEquals(5000, $this->Container->ActivityData->PoolLength);
		$this->assertEquals(1750, $this->Container->ActivityData->TotalStrokes);
		$this->assertEquals(32, $this->Container->ActivityData->AvgCadence, '', 0.5);

        $this->assertNotEmpty($this->Container->ContinuousData->Time);
        $this->assertNotEmpty($this->Container->ContinuousData->Strokes);
        $this->assertNotEmpty($this->Container->ContinuousData->StrokeType);

        $this->assertEmpty($this->Container->ContinuousData->Distance);

        $this->assertEquals(
            [68, 68+80, 68+80+69, 68+80+69+86, 68+80+69+86+82, 68+80+69+86+82+91, 68+80+69+86+82+91+90, 68+80+69+86+82+91+90+98],
            array_slice($this->Container->ContinuousData->Time, 0, 8)
        );
	}

	public function testOutdoorSwimmingFileFromFR910XT()
	{
        $this->convertAndParse('fit/swim-outdoor.fit');

        $this->assertEquals(RUNALYZE_TEST_TZ_LOOKUP ? '2011-10-15 14:31' : '2011-10-15 21:31', LocalTime::date('Y-m-d H:i', $this->Container->Metadata->getTimestamp()));
		$this->assertEquals(RUNALYZE_TEST_TZ_LOOKUP ? -300 : 120, $this->Container->Metadata->getTimezoneOffset());

        $this->assertEquals(1007, $this->Container->ActivityData->Duration);
        $this->assertEquals(1007, $this->Container->ActivityData->ElapsedTime);
        $this->assertEquals(0.985, $this->Container->ActivityData->Distance, '', 0.001);

		$this->assertEquals('fr910xt', $this->Container->Metadata->getCreator());
		$this->assertNull($this->Container->ActivityData->PoolLength);
		$this->assertEquals(424, $this->Container->ActivityData->TotalStrokes);
		$this->assertEquals(25, $this->Container->ActivityData->AvgCadence, '', 0.5);

        $this->assertNotEmpty($this->Container->ContinuousData->Time);
        $this->assertNotEmpty($this->Container->ContinuousData->Distance);
        $this->assertNotEmpty($this->Container->ContinuousData->Latitude);
        $this->assertNotEmpty($this->Container->ContinuousData->Longitude);
        $this->assertNotEmpty($this->Container->ContinuousData->Cadence);
	}

	public function testFileWithHRVData()
	{
        $this->convertAndParse('fit/HRV-example.fit');

        $this->assertEquals('2015-06-13 11:03', LocalTime::date('Y-m-d H:i', $this->Container->Metadata->getTimestamp()));
		$this->assertEquals(120, $this->Container->Metadata->getTimezoneOffset());

        $this->assertNotEmpty($this->Container->RRIntervals);

        $this->assertEmpty($this->Container->ContinuousData->Distance);
	}

	public function testFileWithPowerDataFromEdge810()
	{
        $this->convertAndParse('fit/with-power.fit');

        $this->assertEquals('2015-07-29 15:23', LocalTime::date('Y-m-d H:i', $this->Container->Metadata->getTimestamp()));
		$this->assertEquals(120, $this->Container->Metadata->getTimezoneOffset());

        $this->assertEquals(3600 + 18*60 + 9, $this->Container->ActivityData->Duration);
        $this->assertEquals(39.023, $this->Container->ActivityData->Distance, '', 0.001);
		$this->assertEquals('edge810', $this->Container->Metadata->getCreator());

        $this->assertNotEmpty($this->Container->ContinuousData->Time);
        $this->assertNotEmpty($this->Container->ContinuousData->Distance);
        $this->assertNotEmpty($this->Container->ContinuousData->Latitude);
        $this->assertNotEmpty($this->Container->ContinuousData->Longitude);
        $this->assertNotEmpty($this->Container->ContinuousData->Altitude);
        $this->assertNotEmpty($this->Container->ContinuousData->Cadence);
        $this->assertNotEmpty($this->Container->ContinuousData->HeartRate);
        $this->assertNotEmpty($this->Container->ContinuousData->Temperature);
        $this->assertNotEmpty($this->Container->ContinuousData->Power);

		$this->assertEquals(47.64, $this->Container->FitDetails->VO2maxEstimate);
	}

	public function testMultisportTriathlonFileFromFenix3()
	{
        $this->convertAndParse('fit/multisport-triathlon-fenix3.fit');

        $this->assertEquals(5, $this->NumberOfActivities);

		$this->assertEquals('swimming', $this->Container[0]->Metadata->getSportName());
        $this->assertEquals('2015-08-09 09:13', LocalTime::date('Y-m-d H:i', $this->Container[0]->Metadata->getTimestamp()));
		$this->assertEquals(120, $this->Container[0]->Metadata->getTimezoneOffset());
        $this->assertEquals(2033, $this->Container[0]->ActivityData->Duration);
        $this->assertEquals(1.526, $this->Container[0]->ActivityData->Distance, '', 0.001);
        $this->assertNotEmpty($this->Container[0]->ContinuousData->Distance);
        $this->assertNotEmpty($this->Container[0]->ContinuousData->Cadence);

		$this->assertEquals('transition', $this->Container[1]->Metadata->getSportName());
        $this->assertEquals('2015-08-09 09:48', LocalTime::date('Y-m-d H:i', $this->Container[1]->Metadata->getTimestamp()));
		$this->assertEquals(120, $this->Container[1]->Metadata->getTimezoneOffset());
        $this->assertEquals(165, $this->Container[1]->ActivityData->Duration);
        $this->assertEquals(165, end($this->Container[1]->ContinuousData->Time), '', 1);
        $this->assertEquals(0.367, $this->Container[1]->ActivityData->Distance, '', 0.001);

		$this->assertEquals('cycling', $this->Container[2]->Metadata->getSportName());
        $this->assertEquals('2015-08-09 09:51', LocalTime::date('Y-m-d H:i', $this->Container[2]->Metadata->getTimestamp()));
		$this->assertEquals(120, $this->Container[2]->Metadata->getTimezoneOffset());
        $this->assertEquals(4455, $this->Container[2]->ActivityData->Duration);
        $this->assertEquals(4451, end($this->Container[2]->ContinuousData->Time), '', 5);
        $this->assertEquals(40.261, $this->Container[2]->ActivityData->Distance, '', 0.001);
        $this->assertNotEmpty($this->Container[2]->ContinuousData->Distance);
        $this->assertNotEmpty($this->Container[2]->ContinuousData->HeartRate);
        $this->assertNotEmpty($this->Container[2]->ContinuousData->Altitude);

		$this->assertEquals('transition', $this->Container[3]->Metadata->getSportName());
        $this->assertEquals('2015-08-09 11:05', LocalTime::date('Y-m-d H:i', $this->Container[3]->Metadata->getTimestamp()));
		$this->assertEquals(120, $this->Container[3]->Metadata->getTimezoneOffset());
        $this->assertEquals(109, $this->Container[3]->ActivityData->Duration);
        $this->assertEquals(109, end($this->Container[3]->ContinuousData->Time), '', 1);
        $this->assertEquals(0.419, $this->Container[3]->ActivityData->Distance, '', 0.001);

		$this->assertEquals('running', $this->Container[4]->Metadata->getSportName());
        $this->assertEquals('2015-08-09 11:07', LocalTime::date('Y-m-d H:i', $this->Container[4]->Metadata->getTimestamp()));
		$this->assertEquals(120, $this->Container[4]->Metadata->getTimezoneOffset());
        $this->assertEquals(2381, $this->Container[4]->ActivityData->Duration);
        $this->assertEquals(2381, end($this->Container[4]->ContinuousData->Time), '', 5);
        $this->assertEquals(9.317, $this->Container[4]->ActivityData->Distance, '', 0.001);
        $this->assertNotEmpty($this->Container[4]->ContinuousData->Distance);
        $this->assertNotEmpty($this->Container[4]->ContinuousData->Cadence);
        $this->assertNotEmpty($this->Container[4]->ContinuousData->HeartRate);
        $this->assertNotEmpty($this->Container[4]->ContinuousData->Altitude);
        $this->assertNotEmpty($this->Container[4]->ContinuousData->GroundContactTime);
        $this->assertNotEmpty($this->Container[4]->ContinuousData->VerticalOscillation);
	}

	/**
	 * @see https://github.com/Runalyze/Runalyze/issues/1991
	 */
	public function testMultisessionThatStopsDirectlyAfterTransition()
	{
        $this->convertAndParse('fit/Multisession-stop-after-transition.fit', true, false);

        $this->assertEquals(5, $this->NumberOfActivities);

		$this->assertEquals('running', $this->Container[0]->Metadata->getSportName());
        $this->assertEquals('2016-04-17 09:44', LocalTime::date('Y-m-d H:i', $this->Container[0]->Metadata->getTimestamp()));
        $this->assertEquals(1134, $this->Container[0]->ActivityData->Duration);
        $this->assertEquals(4.460, $this->Container[0]->ActivityData->Distance, '', 0.001);

		$this->assertEquals('transition', $this->Container[1]->Metadata->getSportName());
        $this->assertEquals('2016-04-17 10:03', LocalTime::date('Y-m-d H:i', $this->Container[1]->Metadata->getTimestamp()));
        $this->assertEquals(130, $this->Container[1]->ActivityData->Duration);
        $this->assertEquals(0.266, $this->Container[1]->ActivityData->Distance, '', 0.001);

		$this->assertEquals('cycling', $this->Container[2]->Metadata->getSportName());
        $this->assertEquals('2016-04-17 10:05', LocalTime::date('Y-m-d H:i', $this->Container[2]->Metadata->getTimestamp()));
        $this->assertEquals(2692, $this->Container[2]->ActivityData->Duration);
        $this->assertEquals(23.572, $this->Container[2]->ActivityData->Distance, '', 0.001);

		$this->assertEquals('transition', $this->Container[3]->Metadata->getSportName());
        $this->assertEquals('2016-04-17 10:50', LocalTime::date('Y-m-d H:i', $this->Container[3]->Metadata->getTimestamp()));
        $this->assertEquals(134, $this->Container[3]->ActivityData->Duration);
        $this->assertEquals(0.193, $this->Container[3]->ActivityData->Distance, '', 0.001);

		$this->assertEquals('running', $this->Container[4]->Metadata->getSportName());
        $this->assertEquals('2016-04-17 10:52', LocalTime::date('Y-m-d H:i', $this->Container[4]->Metadata->getTimestamp()));
        $this->assertEquals(544, $this->Container[4]->ActivityData->Duration);
        $this->assertEquals(2.083, $this->Container[4]->ActivityData->Distance, '', 0.001);
	}

	public function testOsynceTimeProblem()
	{
        $this->convertAndParse('fit/osynce-stop-bug.fit');

        $this->assertEquals('2015-11-04 17:06', LocalTime::date('Y-m-d H:i', $this->Container->Metadata->getTimestamp()));
		$this->assertEquals(60, $this->Container->Metadata->getTimezoneOffset());
		$this->assertEquals('osynce', $this->Container->Metadata->getCreator());

        $this->assertEquals(2826, $this->Container->ActivityData->Duration);
        $this->assertEquals(2826, end($this->Container->ContinuousData->Time), '', 1);
        $this->assertEquals(15.536, $this->Container->ActivityData->Distance, '', 0.001);

        $this->assertNotEmpty($this->Container->ContinuousData->Time);
        $this->assertNotEmpty($this->Container->ContinuousData->Distance);
        $this->assertNotEmpty($this->Container->ContinuousData->Latitude);
        $this->assertNotEmpty($this->Container->ContinuousData->Longitude);
        $this->assertNotEmpty($this->Container->ContinuousData->Altitude);
        $this->assertNotEmpty($this->Container->ContinuousData->Cadence);
        $this->assertNotEmpty($this->Container->ContinuousData->HeartRate);
        $this->assertNotEmpty($this->Container->ContinuousData->Temperature);

		$this->checkThatTimeIsStrictIncreasing();
	}

	public function testNewRunningDynamicsFromFenix3()
	{
        $this->convertAndParse('fit/with-new-dynamics.fit');

        $this->assertEquals('2015-11-21 09:25', LocalTime::date('Y-m-d H:i', $this->Container->Metadata->getTimestamp()));
		$this->assertEquals(60, $this->Container->Metadata->getTimezoneOffset());

        $this->assertEquals(8270, $this->Container->ActivityData->Duration);
        $this->assertEquals(23.509, $this->Container->ActivityData->Distance, '', 0.001);

        $this->assertNotEmpty($this->Container->ContinuousData->Time);
        $this->assertNotEmpty($this->Container->ContinuousData->Distance);
        $this->assertNotEmpty($this->Container->ContinuousData->Cadence);
        $this->assertNotEmpty($this->Container->ContinuousData->GroundContactTime);
        $this->assertNotEmpty($this->Container->ContinuousData->GroundContactBalance);

        $this->assertEquals(5199, $this->Container->ActivityData->AvgGroundContactBalance, '', 0.5);
        $this->assertEquals(3.6, $this->Container->FitDetails->TrainingEffect);
	}

	public function testDataFromFR70WithCompressedSpeedDistance()
	{
        $this->convertAndParse('fit/FR70-intervals.fit');

        $this->assertEquals('2013-05-27 08:52', LocalTime::date('Y-m-d H:i', $this->Container->Metadata->getTimestamp()));
		$this->assertEquals(120, $this->Container->Metadata->getTimezoneOffset());

        $this->assertEquals(3163, $this->Container->ActivityData->Duration);
        $this->assertEquals(6.240, $this->Container->ActivityData->Distance, '', 0.001);

        $this->assertEquals(130, $this->Container->ActivityData->AvgHeartRate, '', 0.5);
        $this->assertEquals(172, $this->Container->ActivityData->MaxHeartRate);

        $this->assertNull($this->Container->FitDetails->TrainingEffect);
        $this->assertNull($this->Container->FitDetails->PerformanceCondition);
        $this->assertNull($this->Container->FitDetails->PerformanceConditionEnd);

        $this->assertNotEmpty($this->Container->ContinuousData->Time);
        $this->assertNotEmpty($this->Container->ContinuousData->Distance);
        $this->assertNotEmpty($this->Container->ContinuousData->Cadence);
        $this->assertNotEmpty($this->Container->ContinuousData->HeartRate);
	}

    /**
     * @see https://github.com/Runalyze/Runalyze/issues/1798
     */
	public function testDataFromFR630WithFurtherRunningDataLikeLactateThreshold()
	{
        $this->convertAndParse('fit/FR630-with-lth.fit');

        $this->assertEquals('2015-11-27 21:02', LocalTime::date('Y-m-d H:i', $this->Container->Metadata->getTimestamp()));
		$this->assertEquals(60, $this->Container->Metadata->getTimezoneOffset());
		$this->assertEquals('fr630', $this->Container->Metadata->getCreator());

        $this->assertEquals(819, $this->Container->ActivityData->Duration);
        $this->assertEquals(2.029, $this->Container->ActivityData->Distance, '', 0.001);

        $this->assertEquals(40.62, $this->Container->FitDetails->VO2maxEstimate);
        $this->assertEquals(1307, $this->Container->FitDetails->RecoveryTime);
        $this->assertEquals(3.2, $this->Container->FitDetails->TrainingEffect);
        $this->assertEquals(100, $this->Container->FitDetails->PerformanceCondition);
        $this->assertEquals(98, $this->Container->FitDetails->PerformanceConditionEnd);

        $this->assertNull($this->Container->FitDetails->HrvAnalysis);

		// New values for later on:
		//  - lactate threshold: 163 bpm / 2.583 m/s
	}

	/**
	 * @see https://github.com/Runalyze/Runalyze/issues/1886
	 */
	public function testDataFromSuuntoAmbitPeakWithoutFinalLap()
	{
        $this->convertAndParse('fit/Suunto-Ambit-3-Peak-without-final-lap.fit');

        $this->assertEquals('2016-06-29 17:43', LocalTime::date('Y-m-d H:i', $this->Container->Metadata->getTimestamp()));
		$this->assertEquals(120, $this->Container->Metadata->getTimezoneOffset());
		$this->assertEquals('suunto', $this->Container->Metadata->getCreator());

        $this->assertEquals(4961, $this->Container->ActivityData->Duration);
        $this->assertEquals(15.220, $this->Container->ActivityData->Distance, '', 0.001);

        $this->assertEquals(22, $this->Container->Rounds->count());
        $this->assertEquals(4961, $this->Container->Rounds->getTotalDuration());
        $this->assertEquals(15.220, $this->Container->Rounds->getTotalDistance());
	}

	/**
	 * @see https://github.com/Runalyze/Runalyze/issues/1917
	 */
	public function testThatIrregularTimestampsAreIgnored()
	{
        $this->convertAndParse('fit/One-second-jump-to-past.fit');

		$this->assertEquals('fenix2', $this->Container->Metadata->getCreator());

        $this->assertEquals(7115, $this->Container->ActivityData->Duration);
        $this->assertEquals(42.801, $this->Container->ActivityData->Distance, '', 0.001);

        $this->checkThatTimeIsStrictIncreasing();
	}

	/**
	 * @see https://github.com/Runalyze/Runalyze/issues/1919
	 */
	public function testDeveloperFieldsFromDisabledMoxy()
	{
        $this->convertAndParse('fit/Fenix-3-with-inactive-Moxy.fit');

		$this->assertEquals('fenix3', $this->Container->Metadata->getCreator());

        $this->assertEquals(1401, $this->Container->ActivityData->Duration);
        $this->assertEquals(3.60, $this->Container->ActivityData->Distance, '', 0.01);

        $this->assertNotEmpty($this->Container->ContinuousData->Time);
        $this->assertNotEmpty($this->Container->ContinuousData->Distance);
        $this->assertNotEmpty($this->Container->ContinuousData->Latitude);
        $this->assertNotEmpty($this->Container->ContinuousData->Longitude);
        $this->assertNotEmpty($this->Container->ContinuousData->Altitude);
        $this->assertNotEmpty($this->Container->ContinuousData->HeartRate);
        $this->assertNotEmpty($this->Container->ContinuousData->Cadence);
        $this->assertNotEmpty($this->Container->ContinuousData->Temperature);
        $this->assertNotEmpty($this->Container->ContinuousData->GroundContactTime);
        $this->assertNotEmpty($this->Container->ContinuousData->GroundContactBalance);
        $this->assertNotEmpty($this->Container->ContinuousData->VerticalOscillation);
	}

	/**
	 * @see https://github.com/Runalyze/Runalyze/issues/1919
	 */
	public function testDeveloperFieldsFromStryd()
	{
        $this->convertAndParse('fit/FR920xt-with-Stryd.fit');

		$this->assertEquals('fr920xt', $this->Container->Metadata->getCreator());

        $this->assertEquals(1931, $this->Container->ActivityData->Duration);
        $this->assertEquals(7.255, $this->Container->ActivityData->Distance, '', 0.001);

        $this->assertEquals(214, $this->Container->ActivityData->AvgGroundContactTime, '', 0.5);
        $this->assertEquals(97, $this->Container->ActivityData->AvgVerticalOscillation, '', 0.5);

        $this->assertNotEmpty($this->Container->ContinuousData->Time);
        $this->assertNotEmpty($this->Container->ContinuousData->Distance);
        $this->assertNotEmpty($this->Container->ContinuousData->Latitude);
        $this->assertNotEmpty($this->Container->ContinuousData->Longitude);
        $this->assertNotEmpty($this->Container->ContinuousData->Altitude);
        $this->assertNotEmpty($this->Container->ContinuousData->HeartRate);
        $this->assertNotEmpty($this->Container->ContinuousData->Cadence);
        $this->assertNotEmpty($this->Container->ContinuousData->GroundContactTime);
        $this->assertNotEmpty($this->Container->ContinuousData->VerticalOscillation);
	}

    public function testDeveloperFieldsFromMoxy()
    {
        $this->convertAndParse('fit/moxy-2sensors.fit');

        $this->assertEquals(83, $this->Container->ActivityData->Duration);
        $this->assertEquals(0.117, $this->Container->ActivityData->Distance, '', 0.001);

        $this->assertEquals(
            [57, 57, 57, 56, 57, 57, 58, 58, 59, 59],
            array_slice($this->Container->ContinuousData->MuscleOxygenation, 0, 10)
        );
        $this->assertEquals(
            [51, 51, 51, 52, 52, 52, 53, 53, 53, 53],
            array_slice($this->Container->ContinuousData->MuscleOxygenation_2, 0, 10)
        );

        $this->assertEquals(
            [1231, 1231, 1231, 1231, 1229, 1229, 1227, 1227, 1225, 1225],
            array_slice($this->Container->ContinuousData->TotalHaemoglobin, 0, 10)
        );
        $this->assertEquals(
            [1277, 1277, 1277, 1277, 1277, 1277, 1276, 1276, 1275, 1275],
            array_slice($this->Container->ContinuousData->TotalHaemoglobin_2, 0, 10)
        );
    }

    public function testDeveloperFieldsFromMoxyByFR735()
    {
        $this->convertAndParse('fit/moxy-fr735.fit');

        $this->assertEquals(61, $this->Container->ActivityData->Duration);
        $this->assertEquals(0.100, $this->Container->ActivityData->Distance, '', 0.001);
        $this->assertEquals(61, $this->Container->ActivityData->AvgCadence, '', 0.5);

        $this->assertNotEmpty($this->Container->ContinuousData->MuscleOxygenation);
        $this->assertEmpty($this->Container->ContinuousData->MuscleOxygenation_2);
        $this->assertNotEmpty($this->Container->ContinuousData->TotalHaemoglobin);
        $this->assertEmpty($this->Container->ContinuousData->TotalHaemoglobin_2);

        $this->assertEquals(
            [57, 0, 62, 64, 63],
            array_slice($this->Container->ContinuousData->MuscleOxygenation, 0, 5)
        );
        $this->assertEquals(
            [1249, 0, 1245, 1234, 1233],
            array_slice($this->Container->ContinuousData->TotalHaemoglobin, 0, 5)
        );
    }

	public function testThatBadTrainingEffectValuesAreIgnored()
	{
        $this->convertAndParse('fit/Zwift-bad-training-effect.fit');

        $this->assertEquals(2764, $this->Container->ActivityData->Duration);
        $this->assertEquals(16.721, $this->Container->ActivityData->Distance, '', 0.001);

        $this->assertNull($this->Container->FitDetails->TrainingEffect);
	}

	public function testDeveloperFieldsInSwimFileFromDaniel()
	{
        $this->convertAndParse('fit/swim-via-iq.fit');

        $this->assertEquals(83, $this->Container->ActivityData->Duration);
        $this->assertEquals(0.15, $this->Container->ActivityData->Distance, '', 0.001);
        $this->assertEquals(2500, $this->Container->ActivityData->PoolLength);
	}

	public function testDeveloperFieldsInPoolSwimFileFromDaniel()
	{
        $this->convertAndParse('fit/swim-pool-via-iq.fit');

        $this->assertEquals(2095, $this->Container->ActivityData->Duration);
        $this->assertEquals(1.25, $this->Container->ActivityData->Distance, '', 0.001);
        $this->assertEquals(2500, $this->Container->ActivityData->PoolLength);

        $this->assertNotEmpty($this->Container->ContinuousData->Time);
        $this->assertNotEmpty($this->Container->ContinuousData->Distance);

        $this->checkExpectedRoundData([
            [0, 0.25],
            [0, 0.25],
            [0, 0.25],
            [0, 0.25],
            [0, 0.15],
            [0, 0.10]
        ], 500);
	}

	public function testDeveloperFieldsInNewFormatFromMoxy()
	{
        $this->convertAndParse('fit/moxy-float.fit');

        $this->assertNotEmpty($this->Container->ContinuousData->MuscleOxygenation);
        $this->assertEmpty($this->Container->ContinuousData->MuscleOxygenation_2);
        $this->assertNotEmpty($this->Container->ContinuousData->TotalHaemoglobin);
        $this->assertEmpty($this->Container->ContinuousData->TotalHaemoglobin_2);

        $this->assertEquals(
            [0, 36, 36, 37, 37, 38, 38, 38, 38, 38],
            array_slice($this->Container->ContinuousData->MuscleOxygenation, 0, 10)
        );

        $this->assertEquals(
            [0, 1309, 1309, 1310, 1310, 1310, 1310, 1311, 1311, 1314],
            array_slice($this->Container->ContinuousData->TotalHaemoglobin, 0, 10)
        );
	}

    /**
     * @see https://github.com/Runalyze/Runalyze/issues/2066
     */
    public function testThatZerosInCadenceAreIgnoredForAverage()
    {
        $this->convertAndParse('fit/IPBike-cadence.fit');

        $this->assertNotEmpty($this->Container->ContinuousData->Cadence);
        $this->assertEquals(76, $this->Container->ActivityData->AvgCadence, '', 0.5);
    }

    public function testThatInvalidAltitudeAndEmptyRecordAreIgnored()
    {
        $this->convertAndParse('fit/invalid-altitude-and-empty-record-at-end.fit');

        $this->assertNotEmpty($this->Container->ContinuousData->Altitude);
        $this->assertNull($this->Container->ContinuousData->Altitude[0]);
        $this->assertNotNull(end($this->Container->ContinuousData->Altitude));
    }

    /**
     * @see https://github.com/Runalyze/Runalyze/issues/2077
     */
    public function testThatTimeJumpIsHandledCorrectly()
    {
        $this->convertAndParse('fit/time-jump.fit');

        $this->assertEquals(3389, $this->Container->ActivityData->Duration);
        $this->assertEquals(3389, $this->Container->ActivityData->ElapsedTime);

        $this->assertEquals(3389, end($this->Container->ContinuousData->Time), '', 10);
    }

    /**
     * @see https://github.com/Runalyze/Runalyze/issues/2253
     */
    public function testThatGarminRunPowerIsReadFromDeveloperFields()
    {
        $this->convertAndParse('fit/garmin-runPower.fit');

        $this->assertNotEmpty($this->Container->ContinuousData->Power);
        $this->assertEquals(360, $this->Container->ActivityData->AvgPower, '', 5);
    }

    /**
     * @see https://github.com/Runalyze/Runalyze/issues/2253
     */
    public function testThatGarminRunPowerIsReadFromDeveloperFieldsIfDeveloperDataIndexIsNotZero()
    {
        $this->convertAndParse('fit/garmin-runPower-2.fit');

        $this->assertNotEmpty($this->Container->ContinuousData->Power);
        $this->assertEquals(335, $this->Container->ActivityData->AvgPower, '', 5);
    }

    public function testRunScribeDataForAdditionalFields()
    {
        $this->convertAndParse('fit/Fenix-3-with-runscribe-v1-38.fit');

        $this->assertNotEmpty($this->Container->ContinuousData->ImpactGsLeft);
        $this->assertNotEmpty($this->Container->ContinuousData->ImpactGsRight);
        $this->assertNotEmpty($this->Container->ContinuousData->BrakingGsLeft);
        $this->assertNotEmpty($this->Container->ContinuousData->BrakingGsRight);
        $this->assertNotEmpty($this->Container->ContinuousData->FootstrikeTypeLeft);
        $this->assertNotEmpty($this->Container->ContinuousData->FootstrikeTypeRight);
        $this->assertNotEmpty($this->Container->ContinuousData->PronationExcursionLeft);
        $this->assertNotEmpty($this->Container->ContinuousData->PronationExcursionRight);

        $this->assertEquals(11.3, $this->Container->ActivityData->AvgImpactGsLeft, '', 0.1);
        $this->assertEquals(10.7, $this->Container->ActivityData->AvgImpactGsRight, '', 0.1);
        $this->assertEquals(12.2, $this->Container->ActivityData->AvgBrakingGsLeft, '', 0.1);
        $this->assertEquals(12.5, $this->Container->ActivityData->AvgBrakingGsRight, '', 0.1);
        $this->assertEquals(11.9, $this->Container->ActivityData->AvgFootstrikeTypeLeft, '', 0.1);
        $this->assertEquals(10.1, $this->Container->ActivityData->AvgFootstrikeTypeRight, '', 0.1);
        $this->assertEquals(-10.0, $this->Container->ActivityData->AvgPronationExcursionLeft, '', 0.1);
        $this->assertEquals(-7.0, $this->Container->ActivityData->AvgPronationExcursionRight, '', 0.1);
    }

    public function testRunScribeDataFromPlusDatafieldForAdditionalFields()
    {
        $this->convertAndParse('fit/FR920xt-with-runscribe-plus.fit');

        $this->assertNotEmpty($this->Container->ContinuousData->ImpactGsLeft);
        $this->assertNotEmpty($this->Container->ContinuousData->ImpactGsRight);
        $this->assertNotEmpty($this->Container->ContinuousData->BrakingGsLeft);
        $this->assertNotEmpty($this->Container->ContinuousData->BrakingGsRight);
        $this->assertNotEmpty($this->Container->ContinuousData->FootstrikeTypeLeft);
        $this->assertNotEmpty($this->Container->ContinuousData->FootstrikeTypeRight);
        $this->assertNotEmpty($this->Container->ContinuousData->PronationExcursionLeft);
        $this->assertNotEmpty($this->Container->ContinuousData->PronationExcursionRight);

        $this->assertEquals(13.1, $this->Container->ActivityData->AvgImpactGsLeft, '', 0.1);
        $this->assertEquals(13.7, $this->Container->ActivityData->AvgImpactGsRight, '', 0.1);
        $this->assertEquals(7.3, $this->Container->ActivityData->AvgBrakingGsLeft, '', 0.1);
        $this->assertEquals(8.0, $this->Container->ActivityData->AvgBrakingGsRight, '', 0.1);
        $this->assertEquals(1.4, $this->Container->ActivityData->AvgFootstrikeTypeLeft, '', 0.1);
        $this->assertEquals(1.9, $this->Container->ActivityData->AvgFootstrikeTypeRight, '', 0.1);
        $this->assertEquals(-12.6, $this->Container->ActivityData->AvgPronationExcursionLeft, '', 0.1);
        $this->assertEquals(-10.2, $this->Container->ActivityData->AvgPronationExcursionRight, '', 0.1);

        $this->assertEquals(253, $this->Container->ActivityData->AvgGroundContactTime, '', 0.5);
        $this->assertEquals(4940, $this->Container->ActivityData->AvgGroundContactBalance, '', 0.5);

        $this->assertEquals([652, 653, 654, 658, 655, 510, 328], array_slice($this->Container->ContinuousData->GroundContactTime, 0, 7));
        $this->assertEquals([5027, 5031, 5023, 5034, 5053, 4975, 4975], array_slice($this->Container->ContinuousData->GroundContactBalance, 0, 7));
    }

    public function testEmptyHeartRateSeries()
    {
        $this->convertAndParse('fit/hr-only-zeros.fit');

        $this->assertEmpty($this->Container->ContinuousData->HeartRate);
        $this->assertNull($this->Container->ActivityData->AvgHeartRate);
        $this->assertNull($this->Container->ActivityData->MaxHeartRate);
    }
}
