<?php

namespace Runalyze\Calculation\Performance;

class ModelQueryTest extends \PHPUnit_Framework_TestCase
{
    /** @var \PDOforRunalyze */
    protected $DB;

    protected function setUp()
    {
        $this->DB = \DB::getInstance();
        $this->DB->exec('DELETE FROM `runalyze_training`');

        $Date = new \DateTime('today 15:30');
        $this->DB->insert('training', array('time', 'trimp', 'sportid', 's'), array($Date->getTimestamp(), 100, 1, 2));

        $Date->modify('-1 day 10:00');
        $this->DB->insert('training', array('time', 'trimp', 'sportid', 's'), array($Date->getTimestamp(), 30, 1, 2));
        $Date->modify('-0 day 16:30');
        $this->DB->insert('training', array('time', 'trimp', 'sportid', 's'), array($Date->getTimestamp(), 20, 2, 2));

        $Date->modify('-2 days 07:00');
        $this->DB->insert('training', array('time', 'trimp', 'sportid', 's'), array($Date->getTimestamp(), 70, 2, 2));

        $Date->modify('-7 days 19:00');
        $this->DB->insert('training', array('time', 'trimp', 'sportid', 's'), array($Date->getTimestamp(), 150, 1, 2));
    }

    protected function tearDown()
    {
        $this->DB->exec('DELETE FROM `runalyze_training`');
    }

    public function testSimpleQuery()
    {
        $Query = new ModelQuery();
        $Query->execute($this->DB);

        $this->assertEquals([
            -10 => 150,
            -3 => 70,
            -1 => 50,
            0 => 100
        ], $Query->data());
    }

    public function testTimeRange()
    {
        $WeekAgo = new \DateTime('-1 week');
        $Yesterday = new \DateTime('yesterday');

        $Query = new ModelQuery();
        $Query->setRange($WeekAgo->getTimestamp(), $Yesterday->getTimestamp());
        $Query->execute($this->DB);

        $this->assertEquals([
            -3 => 70,
            -1 => 50
        ], $Query->data());
    }

    public function testSportid()
    {
        $Query = new ModelQuery();
        $Query->setSportid(1);
        $Query->execute($this->DB);

        $this->assertEquals([
            -10 => 150,
            -1 => 30,
            0 => 100
        ], $Query->data());

        $Query->setSportid(2);
        $Query->execute($this->DB);

        $this->assertEquals([
            -3 => 70,
            -1 => 20
        ], $Query->data());
    }
}
