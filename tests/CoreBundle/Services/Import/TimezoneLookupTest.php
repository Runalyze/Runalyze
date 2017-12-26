<?php

namespace Runalyze\Bundle\CoreBundle\Tests\Services\Import;

use Runalyze\Bundle\CoreBundle\Services\Import\TimezoneLookup;
use Runalyze\Bundle\CoreBundle\Services\Import\TimezoneLookupException;

/**
 * @group dependsOn
 * @group dependsOnTimezoneDatabase
 * @group requiresSqlite
 */
class TimezoneLookupTest extends \PHPUnit_Framework_TestCase
{
    /** @var TimezoneLookup */
    protected $Lookup;

    protected function setUp()
    {
        $this->Lookup = new TimezoneLookup(TESTS_ROOT.'/../data/timezone.sqlite', 'libspatialite.so.5');
    }

    public function testSilenceConstructor()
    {
        $lookup = new TimezoneLookup('here/is/no/timezone/database.sqlite', 'libspatialite.so.5');
        $lookup->silentExceptions();

        $this->assertFalse($lookup->isPossible());
        $this->assertNull($lookup->getTimezoneForCoordinate(13.41, 52.52));
    }

    public function testConstructorWithException()
    {
        $lookup = new TimezoneLookup('here/is/no/timezone/database.sqlite', 'libspatialite.so.5');

        $this->setExpectedException(TimezoneLookupException::class);

        $lookup->isPossible();
    }

    public function testConstructorWithWrongExtensionName()
    {
        $lookup = new TimezoneLookup(TESTS_ROOT.'/../data/timezone.sqlite', 'non-existant-extension.so');

        $this->setExpectedException(TimezoneLookupException::class);

        $lookup->isPossible();
    }

    public function testSimpleLocations()
    {
        try {
            $this->assertEquals('Europe/Berlin', $this->Lookup->getTimezoneForCoordinate(13.41, 52.52));
            $this->assertEquals('America/Los_Angeles', $this->Lookup->getTimezoneForCoordinate(-122.420706, 37.776685));
        } catch (TimezoneLookupException $e) {
            $this->markTestSkipped('Timezone lookup is not possible: '.$e->getMessage());
        }
    }

    public function testInvalidLocations()
    {
        try {
            $this->assertNull($this->Lookup->getTimezoneForCoordinate('foo', 'bar'));
        } catch (TimezoneLookupException $e) {
            $this->markTestSkipped('Timezone lookup is not possible: '.$e->getMessage());
        }
    }
}
