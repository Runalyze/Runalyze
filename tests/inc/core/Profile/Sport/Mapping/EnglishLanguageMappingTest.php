<?php

namespace Runalyze\Tests\Profile\Sport\Mapping;

use Runalyze\Profile\FitSdk;
use Runalyze\Profile\Sport\Mapping\EnglishLanguageMapping;
use Runalyze\Profile\Sport\SportProfile;

class EnglishLanguageMappingTest extends \PHPUnit_Framework_TestCase
{
    /** @var EnglishLanguageMapping */
    protected $Mapping;

    public function setUp()
    {
        $this->Mapping = new EnglishLanguageMapping();
    }

    public function testInternalMapping()
    {
        $this->assertEquals(SportProfile::RUNNING, $this->Mapping->toInternal("Run"));
        $this->assertEquals(SportProfile::RUNNING, $this->Mapping->toInternal("running"));
        $this->assertEquals(SportProfile::CYCLING, $this->Mapping->toInternal("CYCLE"));
        $this->assertEquals(SportProfile::CYCLING, $this->Mapping->toInternal("Cycling"));
        $this->assertEquals(SportProfile::CYCLING, $this->Mapping->toInternal("Bike"));
        $this->assertEquals(SportProfile::CYCLING, $this->Mapping->toInternal("biking"));
        $this->assertEquals(SportProfile::CYCLING, $this->Mapping->toInternal("Ergometer"));
        $this->assertEquals(SportProfile::SWIMMING, $this->Mapping->toInternal("Swim"));
        $this->assertEquals(SportProfile::SWIMMING, $this->Mapping->toInternal("swimming"));
    }

    public function testDefaultInternalMapping()
    {
        $this->assertEquals(SportProfile::GENERIC, $this->Mapping->toInternal('foobar'));
    }
}
