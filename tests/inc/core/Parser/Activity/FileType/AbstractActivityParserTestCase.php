<?php

namespace Runalyze\Tests\Parser\Activity\FileType;

use Runalyze\Parser\Activity\Common\Data\ActivityDataContainer;
use Runalyze\Parser\Common\FileContentAwareParserInterface;

abstract class AbstractActivityParserTestCase extends \PHPUnit_Framework_TestCase
{
    /** @var null|ActivityDataContainer|ActivityDataContainer[] */
    protected $Container;

    /**
     * @param FileContentAwareParserInterface $parser
     * @param string $file file path relative to 'testfiles/'
     * @param bool $completeAfterwards
     */
    protected function parseFile(FileContentAwareParserInterface $parser, $file, $completeAfterwards = true)
    {
        $this->parseFileContent($parser, file_get_contents(__DIR__.'/../../../../../testfiles/'.$file), $completeAfterwards);
    }

    /**
     * @param FileContentAwareParserInterface $parser
     * @param string $fileContent
     * @param bool $completeAfterwards
     */
    protected function parseFileContent(FileContentAwareParserInterface $parser, $fileContent, $completeAfterwards = true)
    {
        $parser->setFileContent($fileContent);
        $parser->parse();

        $numActivities = $parser->getNumberOfActivities();

        if ($numActivities <= 1) {
            $this->Container = $parser->getActivityDataContainer();

            if ($completeAfterwards) {
                $this->Container->completeActivityData();
            }
        } else {
            $this->Container = [];

            for ($i = 0; $i < $numActivities; ++$i) {
                $this->Container[] = $parser->getActivityDataContainer($i);

                if ($completeAfterwards) {
                    $this->Container[$i]->completeActivityData();
                }
            }
        }
    }

    /**
     * @param array $expectedRounds [[duration [s], distance [km]], ...]
     * @param int $deltaDuration [s]
     * @param float $deltaDistance [km]
     */
    protected function checkExpectedRoundData(array $expectedRounds, $deltaDuration = 0, $deltaDistance = 0.0)
    {
        $this->checkExpectedRoundDataFor($this->Container, $expectedRounds, $deltaDuration, $deltaDistance);
    }

    /**
     * @param ActivityDataContainer $container
     * @param array $expectedRounds [[duration [s], distance [km]], ...]
     * @param int $deltaDuration [s]
     * @param float $deltaDistance [km]
     */
    protected function checkExpectedRoundDataFor(ActivityDataContainer $container, array $expectedRounds, $deltaDuration = 0, $deltaDistance = 0.0)
    {
        $this->assertEquals(count($expectedRounds), $container->Rounds->count());

        foreach ($expectedRounds as $i => $expectedRoundData) {
            $this->assertEquals($expectedRoundData[0], $container->Rounds[$i]->getDuration(), 'Round #'.$i.' has wrong duration.', $deltaDuration);
            $this->assertEquals($expectedRoundData[1], $container->Rounds[$i]->getDistance(), 'Round #'.$i.' has wrong distance.', $deltaDistance);
        }
    }

    /**
     * @param array $expectedPauses [[time index [s], duration [km](, hr start [bpm], hr end [bpm])], ...]
     * @param int $deltaDuration [s]
     */
    protected function checkExpectedPauseData(array $expectedPauses, $deltaDuration = 0)
    {
        $this->assertEquals(count($expectedPauses), $this->Container->Pauses->count());

        foreach ($expectedPauses as $i => $expectedPauseData) {
            $this->assertEquals($expectedPauseData[0], $this->Container->Pauses[$i]->getTimeIndex(), 'Pause #'.$i.' has wrong time index.', $deltaDuration);
            $this->assertEquals($expectedPauseData[1], $this->Container->Pauses[$i]->getDuration(), 'Pause #'.$i.' has wrong duration.', $deltaDuration);

            if (isset($expectedPauseData[2]) && isset($expectedPauseData[3])) {
                $this->assertEquals($expectedPauseData[2], $this->Container->Pauses[$i]->getHeartRateAtStart(), 'Pause #'.$i.' has wrong heart rate at start.');
                $this->assertEquals($expectedPauseData[3], $this->Container->Pauses[$i]->getHeartRateAtEnd(), 'Pause #'.$i.' has wrong heart rate at end.');
            }
        }
    }
}
