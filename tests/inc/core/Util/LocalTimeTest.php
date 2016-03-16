<?php

namespace Runalyze\Util;

use Runalyze\Configuration;
use Runalyze\Parameter\Application\WeekStart;

class LocalTimeTest extends \PHPUnit_Framework_TestCase
{
    /** @var string */
    protected $DefaultTimezone;

    public function setUp()
    {
        $this->DefaultTimezone = date_default_timezone_get();

        // Let's use Europe/Moscow as there's no summer/winter time. It's always +03:00
        date_default_timezone_set('Europe/Moscow');
    }

    public function tearDown()
    {
        date_default_timezone_set($this->DefaultTimezone);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testInvalidParameterForConstructor()
    {
        new LocalTime('today');
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testInvalidParameterForServerTime()
    {
        LocalTime::fromServerTime('today');
    }

    public function testThatConstructorDoesNotChangeTimestamps()
    {
        $this->assertEquals(time(), (new LocalTime())->getTimestamp(), '', 1);
        $this->assertEquals(time(), (new LocalTime(time()))->getTimestamp(), '', 1);
    }

	public function testThatServerTimezoneIsIgnored()
	{
		$this->assertEquals(date('d.m.Y H:i'), LocalTime::fromServerTime(time())->format('d.m.Y H:i'));
        $this->assertEquals('01.03.2016 12:34', LocalTime::fromServerTime(mktime(12, 34, 0, 3, 1, 2016))->format('d.m.Y H:i'));
        $this->assertEquals('01.04.2016 12:34', LocalTime::fromServerTime(mktime(12, 34, 0, 4, 1, 2016))->format('d.m.Y H:i'));
	}

    public function testThatCurrentHourKeepsCurrentHourWithDSTchange()
    {
        date_default_timezone_set('Europe/Berlin');

        $this->assertEquals('01.03.2016 12:34', LocalTime::fromServerTime(mktime(12, 34, 0, 3, 1, 2016))->format('d.m.Y H:i'));
        $this->assertEquals('01.04.2016 12:34', LocalTime::fromServerTime(mktime(12, 34, 0, 4, 1, 2016))->format('d.m.Y H:i'));
    }

    public function testThatObjectHasUTCTimezone()
    {
        $this->assertEquals('UTC', (new LocalTime())->getTimezone()->getName());
        $this->assertEquals('UTC', (new LocalTime(time()))->getTimezone()->getName());
        $this->assertEquals('UTC', LocalTime::fromServerTime(time())->getTimezone()->getName());
        $this->assertEquals('UTC', LocalTime::fromString('2016-01-01 12:00:00+06:00')->getTimezone()->getName());
    }

    public function testThatGivenTimezoneWillBeIgnored()
    {
        $this->assertEquals('13:54:17', LocalTime::fromString('13:54:17GMT-07:30')->format('H:i:s'));
        $this->assertEquals('13:54:17', LocalTime::fromString('13:54:17+0400')->format('H:i:s'));
    }

    public function testConversionToServerTime()
    {
        $Object = LocalTime::fromString('2016-01-31 12:34:56');

        $this->assertEquals('12:34:56 +00:00', $Object->format('H:i:s P'));
        $this->assertEquals('12:34:56 +03:00', $Object->toServerTime()->format('H:i:s P'));
        $this->assertNotEquals($Object->getTimestamp(), $Object->toServerTimestamp());
        $this->assertEquals($Object->getTimestamp() - 3*3600, $Object->toServerTimestamp());
    }

    public function testMonthStartAndEnd()
    {
        $testArray = [[
                'start' => '2014-01-01 00:00:00',
                'end' => '2014-01-31 23:59:59',
                'input' => [
                    '2014-01-01',
                    '2014-01-01 00:00:00',
                    '2014-01-15 12:34:56',
                    '2014-01-31 23:45:43',
                ]
            ], [
                'start' => '2016-02-01 00:00:00',
                'end' => '2016-02-29 23:59:59',
                'input' => ['2016-02-28']
            ], [
                'start' => '2017-03-01 00:00:00',
                'end' => '2017-03-31 23:59:59',
                'input' => ['2017-03-05']
            ]
        ];

        foreach ($testArray as $setup) {
            foreach ($setup['input'] as $input) {
                $date = LocalTime::fromString($input);
                $this->assertEquals($setup['start'], $date->monthStart(true)->format('Y-m-d H:i:s'), 'Input was "'.$input.'"');
                $this->assertEquals($setup['end'], $date->monthEnd(true)->format('Y-m-d H:i:s'), 'Input was "'.$input.'"');
            }
        }
    }

    public function testYearStartAndEnd()
    {
        $testArray = [[
            'start' => '2016-01-01 00:00:00',
            'end' => '2016-12-31 23:59:59',
            'input' => ['2016-01-01', '2016-02-29', '2016-06-21', '2016-12-30']
        ], [
            'start' => '2017-01-01 00:00:00',
            'end' => '2017-12-31 23:59:59',
            'input' => ['2017-03-14', '2017-05-21', '2017-08-01', '2017-11-12']
        ], [
            'start' => '2001-01-01 00:00:00',
            'end' => '2001-12-31 23:59:59',
            'input' => ['2001-02-03', '2001-04-25', '2001-06-18', '2001-12-31']
        ]
        ];

        foreach ($testArray as $setup) {
            foreach ($setup['input'] as $input) {
                $date = LocalTime::fromString($input);
                $this->assertEquals($setup['start'], $date->yearStart(true)->format('Y-m-d H:i:s'), 'Input was "'.$input.'"');
                $this->assertEquals($setup['end'], $date->yearEnd(true)->format('Y-m-d H:i:s'), 'Input was "'.$input.'"');
            }
        }
    }

    public function testWeekstartAndWeekendForMonday()
    {
        Configuration::General()->weekStart()->set(WeekStart::MONDAY);

        $today = mktime(10, 22, 20, 9, 17, 2015);

        $this->assertEquals('14.09.2015 00:00:00', LocalTime::fromServerTime($today)->weekstart(true)->format('d.m.Y H:i:s'));
        $this->assertEquals('20.09.2015 23:59:50', LocalTime::fromServerTime($today)->weekend(true)->format('d.m.Y H:i:s'));
    }

    public function testWeekstartAndWeekendForSunday()
    {
        Configuration::General()->weekStart()->set(WeekStart::SUNDAY);

        $today = mktime(10, 22, 20, 9, 17, 2015);

        $this->assertEquals('13.09.2015 00:00:00', LocalTime::fromServerTime($today)->weekstart(true)->format('d.m.Y H:i:s'));
        $this->assertEquals('19.09.2015 23:59:50', LocalTime::fromServerTime($today)->weekend(true)->format('d.m.Y H:i:s'));
    }

    public function testWeekstartAndWeekendForSundayThatWasABug()
    {
        Configuration::General()->weekStart()->set(WeekStart::SUNDAY);

        $today = mktime(0, 0, 0, 10, 25, 2015);
        $end = mktime(23, 59, 50, 10, 31, 2015);

        $this->assertEquals('25.10.2015 00:00:00', LocalTime::fromServerTime($today)->weekstart(true)->format('d.m.Y H:i:s'));
        $this->assertEquals('25.10.2015 00:00:00', LocalTime::fromServerTime($end)->weekstart(true)->format('d.m.Y H:i:s'));
        $this->assertEquals('31.10.2015 23:59:50', LocalTime::fromServerTime($today)->weekend(true)->format('d.m.Y H:i:s'));
        $this->assertEquals('31.10.2015 23:59:50', LocalTime::fromServerTime($end)->weekend(true)->format('d.m.Y H:i:s'));
    }

    public function testToday()
    {
        $this->assertTrue(LocalTime::fromServerTime(time())->isToday());
    }

}
