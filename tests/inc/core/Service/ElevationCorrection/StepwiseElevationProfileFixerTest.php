<?php

namespace Runalyze\Tests\Service\ElevationCorrection\Strategy;

use Runalyze\Service\ElevationCorrection\StepwiseElevationProfileFixer;

class StepwiseElevationProfileFixerTest extends \PHPUnit_Framework_TestCase
{
    public function testEmptyProfile()
    {
        $fixer = new StepwiseElevationProfileFixer();

        $this->assertEquals([], $fixer->fixStepwiseElevations([]));
    }

    public function testThatNonStepwiseProfileIsNotChanged()
    {
        $fixer = new StepwiseElevationProfileFixer(5);
        $profile = [1, 1, 1, 1, 1, 2, 2, 2, 42, 2, 3, 3, 3, 3, 3];

        $this->assertFalse($fixer->isProfileStepwise($profile));
        $this->assertEquals($profile, $fixer->fixStepwiseElevations($profile));
    }

    public function testThatShortProfileIsNotChanged()
    {
        $fixer = new StepwiseElevationProfileFixer(8);
        $profile = [6, 6, 6, 6, 6, 6];

        $this->assertTrue($fixer->isProfileStepwise($profile));
        $this->assertEquals($profile, $fixer->fixStepwiseElevations($profile));
    }

    public function testShortExample()
    {
        $fixer = new StepwiseElevationProfileFixer(3);
        $profile = [6, 6, 6, 9, 9, 9, 15, 15, 15, 0, 0, 0];

        $this->assertTrue($fixer->isProfileStepwise($profile));
        $this->assertEquals([6, 6, 7, 8, 9, 11, 13, 15, 10, 5, 0, 0], $fixer->fixStepwiseElevations($profile));
    }

    public function testShortExampleWithEvenGroupSize()
    {
        $fixer = new StepwiseElevationProfileFixer(4);
        $profile = [0, 0, 0, 0, 4, 4, 4, 4, 0, 0];

        $this->assertTrue($fixer->isProfileStepwise($profile));
        $this->assertEquals([0, 0, 1, 2, 3, 4, 3, 2, 1, 0], $fixer->fixStepwiseElevations($profile));
    }

    public function testShortExampleWithDistances()
    {
        $fixer = new StepwiseElevationProfileFixer(3);
        $profile = [0, 0, 0, 9, 9, 9, 15, 15, 15, 0, 0, 0];
        $distances = [0, 1, 2.5, 3, 4, 6, 6.5, 7, 8, 10, 10, 11];

        $this->assertTrue($fixer->isProfileStepwise($profile));
        $this->assertEquals([0, 0, 5, 6, 9, 13, 14, 15, 10, 0, 0, 0], $fixer->fixStepwiseElevations($profile, $distances));
    }

    public function testVariableGroupSize()
    {
        $fixer = new StepwiseElevationProfileFixer(3, StepwiseElevationProfileFixer::METHOD_VARIABLE_GROUP_SIZE);
        $profile = [0, 0, 0, 0, 0, 0, 15, 15, 15, 0, 0, 0];

        $this->assertTrue($fixer->isProfileStepwise($profile));
        $this->assertEquals([0, 0, 0, 3, 6, 9, 12, 15, 8, 0, 0, 0], $fixer->fixStepwiseElevations($profile));
    }
}
