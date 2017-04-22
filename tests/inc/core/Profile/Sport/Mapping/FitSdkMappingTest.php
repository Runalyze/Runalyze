<?php

namespace Runalyze\Tests\Profile\Sport\Mapping;

use Runalyze\Profile\FitSdk;
use Runalyze\Profile\Sport\Mapping\FitSdkMapping;
use Runalyze\Profile\Sport\SportProfile;

class FitSdkMappingTest extends \PHPUnit_Framework_TestCase
{
    /** @var FitSdkMapping */
    protected $Mapping;

    public function setUp()
    {
        $this->Mapping = new FitSdkMapping();
    }

    public function testInternalMapping()
    {
        $this->assertEquals(SportProfile::GENERIC, $this->Mapping->toInternal(FitSdk\SportProfile::GENERIC));
        $this->assertEquals(SportProfile::RUNNING, $this->Mapping->toInternal(FitSdk\SportProfile::RUNNING));
        $this->assertEquals(SportProfile::CYCLING, $this->Mapping->toInternal(FitSdk\SportProfile::CYCLING));
        $this->assertEquals(SportProfile::CYCLING, $this->Mapping->toInternal(FitSdk\SportProfile::E_BIKING));
        $this->assertEquals(SportProfile::SWIMMING, $this->Mapping->toInternal(FitSdk\SportProfile::SWIMMING));
        $this->assertEquals(SportProfile::ROWING, $this->Mapping->toInternal(FitSdk\SportProfile::ROWING));
        $this->assertEquals(SportProfile::HIKING, $this->Mapping->toInternal(FitSdk\SportProfile::HIKING));
        $this->assertEquals(SportProfile::HIKING, $this->Mapping->toInternal(FitSdk\SportProfile::WALKING));
    }

    public function testDefaultInternalMapping()
    {
        $this->assertEquals(SportProfile::GENERIC, $this->Mapping->toInternal('foobar'));
    }

    public function testExternalMapping()
    {
        $this->assertEquals(FitSdk\SportProfile::GENERIC, $this->Mapping->toExternal(SportProfile::GENERIC));
        $this->assertEquals(FitSdk\SportProfile::RUNNING, $this->Mapping->toExternal(SportProfile::RUNNING));
        $this->assertEquals(FitSdk\SportProfile::CYCLING, $this->Mapping->toExternal(SportProfile::CYCLING));
        $this->assertEquals(FitSdk\SportProfile::SWIMMING, $this->Mapping->toExternal(SportProfile::SWIMMING));
        $this->assertEquals(FitSdk\SportProfile::ROWING, $this->Mapping->toExternal(SportProfile::ROWING));
        $this->assertEquals(FitSdk\SportProfile::HIKING, $this->Mapping->toExternal(SportProfile::HIKING));
    }

    public function testDefaultExternalMapping()
    {
        $this->assertEquals(FitSdk\SportProfile::GENERIC, $this->Mapping->toExternal('foobar'));
    }
}
