<?php

namespace Runalyze\Tests\Sports\ClimbScore;

use Runalyze\Sports\ClimbScore\ClimbScore;

class ClimbScoreTest extends \PHPUnit_Framework_TestCase
{
    /** @var ClimbScore */
    protected $Score;

    protected function setUp()
    {
        $this->Score = new ClimbScore();
    }

    public function testEmptyConstructor()
    {
        $score = new ClimbScore();

        $this->assertFalse($score->isKnown());
        $this->assertNull($score->getScore());
    }

    public function testManualScore()
    {
        $this->assertEquals(3.1, (new ClimbScore(3.1))->getScore());
        $this->assertEquals(4.2, (new ClimbScore())->setScore(4.2)->getScore());
    }

    public function testExampleForCyclingTourInSwitzerland()
    {
        $this->Score->setScoreFromClassifiedClimbs(
            [0.6, 0.3, 7.7, 5.9, 0.6, 2.8],
            105.8,
            0.43
        );

        $this->assertEquals(5.9, $this->Score->getScore(), '', 0.05);
    }

    public function testSumOfFietsIndices()
    {
        $this->assertEquals(6.0, $this->Score->getSumOfScoresForClassifiedClimbs([1.0, 2.0, 3.0], 20.0));
        $this->assertEquals(3.0, $this->Score->getSumOfScoresForClassifiedClimbs([1.0, 2.0, 3.0], 80.0));
    }

    public function testThatVeryShortDistancesDoNotDisturbTheScore()
    {
        $this->assertEquals(2.7, $this->Score->getSumOfScoresForClassifiedClimbs([2.7], 0.01));
        $this->assertEquals(3.0, $this->Score->getSumOfScoresForClassifiedClimbs([3.0], 3.0));
        $this->assertEquals(4.2, $this->Score->getSumOfScoresForClassifiedClimbs([4.2], 20.0));
    }

    public function testScaleForSumOfScores()
    {
        $this->assertEquals(1.17, $this->Score->getScoreForSumOfSingleScores(0.0), '', 0.01);
        $this->assertEquals(2.0, $this->Score->getScoreForSumOfSingleScores(0.5));
        $this->assertEquals(4.0, $this->Score->getScoreForSumOfSingleScores(2.5));
        $this->assertEquals(5.17, $this->Score->getScoreForSumOfSingleScores(4.5), '', 0.01);
        $this->assertEquals(6.0, $this->Score->getScoreForSumOfSingleScores(6.5));
        $this->assertEquals(8.0, $this->Score->getScoreForSumOfSingleScores(14.5));
        $this->assertEquals(10.0, $this->Score->getScoreForSumOfSingleScores(30.5));
        $this->assertEquals(10.0, $this->Score->getScoreForSumOfSingleScores(100.0));
    }

    public function testCompensationFactorForFlatParts()
    {
        $this->assertEquals(0.0, $this->Score->getCompensationForFlatParts(2.50));
        $this->assertEquals(0.0, $this->Score->getCompensationForFlatParts(-1.23));
        $this->assertEquals(0.0, $this->Score->getCompensationForFlatParts(1.00));
        $this->assertEquals(1.0, $this->Score->getCompensationForFlatParts(0.00));

        $this->assertEquals(0.99, $this->Score->getCompensationForFlatParts(0.10));
        $this->assertEquals(0.75, $this->Score->getCompensationForFlatParts(0.50));
        $this->assertEquals(0.64, $this->Score->getCompensationForFlatParts(0.60));
        $this->assertEquals(0.36, $this->Score->getCompensationForFlatParts(0.80));
    }
}
