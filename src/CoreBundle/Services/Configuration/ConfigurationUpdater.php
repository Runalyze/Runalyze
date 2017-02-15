<?php

namespace Runalyze\Bundle\CoreBundle\Services\Configuration;

use Runalyze\Bundle\CoreBundle\Entity\Account;
use Runalyze\Bundle\CoreBundle\Entity\ConfRepository;

class ConfigurationUpdater
{
    /** @var ConfRepository */
    protected $Repository;

    public function __construct(ConfRepository $repository)
    {
        $this->Repository = $repository;
    }

    /**
     * @param Account $account
     * @param int $bpm
     */
    public function updateMaximalHeartRate(Account $account, $bpm)
    {
        $this->Repository->updateOrInsert($account, 'data', 'HF_MAX', (string)$bpm);
    }

    /**
     * @param Account $account
     * @param int $bpm
     */
    public function updateRestingHeartRate(Account $account, $bpm)
    {
        $this->Repository->updateOrInsert($account, 'data', 'HF_REST', (string)$bpm);
    }
}
