<?php

namespace Runalyze\Util;

/**
 * @group dependsOn
 * @group dependsOnTimezoneDatabase
 * @group requiresSqlite
 */
class TimezoneLookupTest extends \PHPUnit_Framework_TestCase
{
    public function testSilenceConstructor()
    {
        new TimezoneLookup(true, 'here/is/no/timezone/database.sqlite');
    }

    /**
     * @expectedException \Runalyze\Util\TimezoneLookupException
     */
    public function testConstructorWithException()
    {
        new TimezoneLookup(false, 'here/is/no/timezone/database.sqlite');
    }

    /**
     * @expectedException \Runalyze\Util\TimezoneLookupException
     */
    public function testConstructorWithWrongExtensionName()
    {
        new TimezoneLookup(false, DATA_DIRECTORY.'/timezone.sqlite', 'non-existant-extension.so');
    }

    public function testSimpleLocations()
    {
        try {
            $Lookup = new TimezoneLookup(false);

            $this->assertEquals('Europe/Berlin', $Lookup->getTimezoneForCoordinate(13.41, 52.52));
            $this->assertEquals('America/Los_Angeles', $Lookup->getTimezoneForCoordinate(-122.420706, 37.776685));
        } catch (TimezoneLookupException $e) {
            $this->markTestSkipped('Timezone lookup is not possible: '.$e->getMessage());
        }
    }

    public function testInvalidLocations()
    {
        try {
            $Lookup = new TimezoneLookup(false);

            $this->assertEquals(null, $Lookup->getTimezoneForCoordinate('foo', 'bar'));
        } catch (TimezoneLookupException $e) {
            $this->markTestSkipped('Timezone lookup is not possible: '.$e->getMessage());
        }
    }
}
