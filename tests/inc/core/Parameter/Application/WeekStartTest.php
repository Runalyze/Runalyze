<?php

namespace Runalyze\Parameter\Application;

class WeekStartTest extends \PHPUnit_Framework_TestCase
{
	/** @var \Runalyze\Parameter\Application\WeekStart */
	protected $Object;

    /** @var \PDO */
    protected $PDO;

    /** @var array ['date string' => [#week for Monday as start, #week for Sunday as start]] */
    protected $TestDates = [
        '3 January 2015' => [1, 1],
        '4 January 2015' => [1, 2],
        '26 October 2015' => [44, 44],
        '1 November 2015' => [44, 45],
        '2 November 2015' => [45, 45],
        '8 November 2015' => [45, 46],
        '3 January 2016' => [53, 1]
    ];

	protected function setUp()
    {
		$this->Object = new WeekStart;
        $this->PDO = \DB::getInstance();
	}

	public function testNoneSet()
    {
        $this->assertTrue($this->Object->isMonday());
        $this->assertEquals(1, $this->Object->value());
        $this->assertFalse($this->Object->isSaturday());
        $this->assertFalse($this->Object->isSunday());
    }

    public function testMonday() {
		$this->Object->set(WeekStart::MONDAY);

        $this->assertEquals(1, $this->Object->value());
		$this->assertTrue($this->Object->isMonday());
		$this->assertFalse($this->Object->isSaturday());
        $this->assertFalse($this->Object->isSunday());
    }

    public function testSunday()
    {
		$this->Object->set(WeekStart::SUNDAY);

        $this->assertEquals(0, $this->Object->value());
		$this->assertFalse($this->Object->isMonday());
		$this->assertFalse($this->Object->isSaturday());
        $this->assertTrue($this->Object->isSunday());
	}

	public function testWeekNumberForMonday()
    {
		$this->Object->set(WeekStart::MONDAY);

        $this->runTestsForTestData(0);
	}

	public function testWeekNumberForSunday()
    {
		$this->Object->set(WeekStart::SUNDAY);

        $this->runTestsForTestData(1);
	}

    protected function runTestsForTestData($expectedWeekIndex)
    {
        foreach ($this->TestDates as $dateString => $week) {
            $this->assertEquals(
                $week[$expectedWeekIndex],
                $this->Object->phpWeek(strtotime($dateString)),
                'PHP Week number check failed for "'.$dateString.'".'
            );

            $this->assertEquals(
                $week[$expectedWeekIndex],
                $this->PDO->query('SELECT '.$this->Object->mysqlWeek('"'.date('Y-m-d', strtotime($dateString)).'"'))->fetchColumn(),
                'SQL Week number check failed for "'.$dateString.'".'
            );
        }
    }

}
