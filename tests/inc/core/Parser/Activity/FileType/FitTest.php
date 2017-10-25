<?php

namespace Runalyze\Tests\Parser\Activity\FileType;

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
     */
    protected function convertAndParse($file, $completeAfterwards = true)
    {
        $outputFile = $this->Converter->convertFile($this->pathToTestFiles().$file);
        $this->FilesToClear[] = $outputFile;

        $this->Parser->setFileName($outputFile);
        $this->Parser->parse();

        $this->setContainerFrom($this->Parser, $completeAfterwards);
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
}
