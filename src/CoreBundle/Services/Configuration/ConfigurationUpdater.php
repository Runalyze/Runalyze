<?php

namespace Runalyze\Bundle\CoreBundle\Services\Configuration;

use Runalyze\Bundle\CoreBundle\Entity\Account;
use Runalyze\Bundle\CoreBundle\Entity\ConfRepository;

class ConfigurationUpdater
{
    /** @var ConfRepository */
    protected $Repository;

    /** @var ConfigurationManager */
    protected $Manager;

    public function __construct(ConfRepository $repository, ConfigurationManager $manager = null)
    {
        $this->Repository = $repository;
        $this->Manager = $manager;
    }

    /**
     * @param Account $account
     * @param string $configName
     * @param string $value
     */
    protected function updateInDataConfiguration(Account $account, $configName, $value)
    {
        $this->Repository->updateOrInsert($account, 'data', $configName, $value);
        $this->Manager->getList($account)->set('data.'.$configName, $value);
    }

    /**
     * @param Account $account
     * @param int $startTime
     */
    public function updateStartTime(Account $account, $startTime)
    {
        $this->updateInDataConfiguration($account, 'START_TIME', (string)$startTime);
    }

    /**
     * @param Account $account
     * @param int $bpm
     */
    public function updateMaximalHeartRate(Account $account, $bpm)
    {
        $this->updateInDataConfiguration($account, 'HF_MAX', (string)$bpm);
    }

    /**
     * @param Account $account
     * @param int $bpm
     */
    public function updateRestingHeartRate(Account $account, $bpm)
    {
        $this->updateInDataConfiguration($account, 'HF_REST', (string)$bpm);
    }

    /**
     * @param Account $account
     * @param float $shape [0.00, inf)
     */
    public function updateVO2maxShape(Account $account, $shape)
    {
        $this->updateInDataConfiguration($account, 'VO2MAX_FORM', (string)$shape);
    }

    /**
     * @param Account $account
     * @param float $factor [0.00 .. 2.00]
     */
    public function updateVO2maxCorrectionFactor(Account $account, $factor)
    {
        $this->updateInDataConfiguration($account, 'VO2MAX_CORRECTOR', (string)$factor);
    }

    /**
     * @param Account $account
     * @param int $shape [0, inf)
     */
    public function updateMarathonShape(Account $account, $shape)
    {
        $this->updateInDataConfiguration($account, 'BASIC_ENDURANCE', (string)$shape);
    }

    /**
     * @param Account $account
     * @param int $atl [0, inf)
     */
    public function updateMaximalAtl(Account $account, $atl)
    {
        $this->updateInDataConfiguration($account, 'MAX_ATL', (string)$atl);
    }

    /**
     * @param Account $account
     * @param int $ctl [0, inf)
     */
    public function updateMaximalCtl(Account $account, $ctl)
    {
        $this->updateInDataConfiguration($account, 'MAX_CTL', (string)$ctl);
    }

    /**
     * @param Account $account
     * @param int $trimp [0, inf)
     */
    public function updateMaximalTrimp(Account $account, $trimp)
    {
        $this->updateInDataConfiguration($account, 'MAX_TRIMP', (string)$trimp);
    }
}
