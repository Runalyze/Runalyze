<?php

namespace Runalyze\Calculation\JD;

use Runalyze\Model\Activity;
use Runalyze\Model\RaceResult;
use Runalyze\Configuration;

class LegacyVO2maxCorrectorTest extends \PHPUnit_Framework_TestCase
{
    /** @var \PDO */
    protected $PDO;

    /** @var int */
    protected $runningSportId;

    protected function setUp()
    {
        $this->PDO = \DB::getInstance();
        $this->PDO->exec('DELETE FROM `runalyze_training`');
        $this->PDO->exec('DELETE FROM `runalyze_raceresult`');
        $this->PDO->exec("DELETE FROM `runalyze_conf` WHERE `key` = 'RUNNINGSPORT'");
        $this->PDO->exec("INSERT INTO runalyze_conf (`category`, `key`, `value`, `accountid`) VALUES ('general', 'RUNNINGSPORT', 3, 1)");
        Configuration::loadAll(1);
        $this->runningSportId = Configuration::General()->runningSport();
        LegacyEffectiveVO2maxCorrector::setGlobalFactor(1);
    }

    protected function tearDown()
    {
        LegacyEffectiveVO2maxCorrector::setGlobalFactor(1);
        $this->PDO->exec('DELETE FROM `runalyze_training`');
        $this->PDO->exec('DELETE FROM `runalyze_raceresult`');
        $this->PDO->exec('DELETE FROM `runalyze_conf`');
        Configuration::loadAll(1);
    }

    public function testSimpleFactor()
    {
        $Corrector = new LegacyEffectiveVO2maxCorrector(0.95);

        $this->assertEquals(0.95, $Corrector->factor());
    }

    public function testGlobalFactor()
    {
        LegacyEffectiveVO2maxCorrector::setGlobalFactor(0.5);
        $Corrector = new LegacyEffectiveVO2maxCorrector;

        $this->assertEquals(0.5, $Corrector->factor());
    }

    public function testFromActivity()
    {
        $Corrector = new LegacyEffectiveVO2maxCorrector;
        $Corrector->fromActivity(
            $Activity = new Activity\Entity(array(
                Activity\Entity::VDOT_BY_TIME => 45,
                Activity\Entity::VDOT => 50
            ))
        );

        $this->assertEquals(0.9, $Corrector->factor());
    }

    public function testFromEmptyActivity()
    {
        $Corrector = new LegacyEffectiveVO2maxCorrector;
        $Corrector->fromActivity(
            $Activity = new Activity\Entity(array(
                Activity\Entity::VDOT_BY_TIME => 45
            ))
        );

        $this->assertEquals(1, $Corrector->factor());
    }

    protected function insert($vo2max, $vo2max_by_time, $accountid, $sportid, $useVO2max = true)
    {
        $this->PDO->exec('INSERT INTO `'.PREFIX.'training` (`vdot`, `vdot_by_time`, `sportid`, `accountid`, `s`, `use_vdot`, `time`) VALUES('.$vo2max.', '.$vo2max_by_time.', '.$sportid.', '.$accountid.', 2400, '.($useVO2max ? 1 : 0).', 1477843525)');
        $activityId = $this->PDO->lastInsertId();

        $RaceResult = new RaceResult\Entity(array(
            RaceResult\Entity::OFFICIAL_TIME => 2400,
            RaceResult\Entity::OFFICIAL_DISTANCE => '10',
            RaceResult\Entity::ACTIVITY_ID => $activityId
        ));
        $RaceResultInserter = new RaceResult\Inserter($this->PDO, $RaceResult);
        $RaceResultInserter->setAccountID($accountid);
        $RaceResultInserter->insert();
    }

    public function testFromDatabase()
    {
        $this->insert(0, 90, 0, $this->runningSportId);
        $this->insert(50, 25, 0, $this->runningSportId);
        $this->insert(50, 45, 0, $this->runningSportId);
        $this->insert(50, 50, 0, $this->runningSportId, false);
        $this->insert(100, 80, 0, $this->runningSportId);
        $this->insert(90, 90, 1, $this->runningSportId);
        $this->insert(90, 100, 1, $this->runningSportId, false);

        $this->assertEquals(0.9, (new LegacyEffectiveVO2maxCorrector)->fromDatabase($this->PDO, 0, $this->runningSportId));
        $this->assertEquals(1.0, (new LegacyEffectiveVO2maxCorrector)->fromDatabase($this->PDO, 1, $this->runningSportId));
    }
}
