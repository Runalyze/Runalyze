<?php

namespace Runalyze\Tests\Profile\Weather\Source;

use Runalyze\Profile\Weather\Source\SourceInterface;
use Runalyze\Profile\Weather\Source\WeatherSourceProfile;

class WeatherSourceProfileTest extends \PHPUnit_Framework_TestCase
{
    public function testThatAllClassesExist()
    {
        foreach (WeatherSourceProfile::getEnum() as $enum) {
            /** @var SourceInterface $source */
            $source = WeatherSourceProfile::get($enum);

            $this->assertInstanceOf(SourceInterface::class, $source);
            $this->assertEquals($enum, $source->getInternalProfileEnum());
            $this->assertTrue(is_bool($source->requiresAttribution()));

            if ($source->hasAttributionUrl()) {
                $this->assertNotEmpty($source->getAttributionUrl());
            }
        }
    }
}
