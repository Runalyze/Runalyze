<?php

namespace Runalyze\Bundle\CoreBundle\Services\Configuration;

use Runalyze\Bundle\CoreBundle\Component\Configuration\RunalyzeConfigurationList;
use Runalyze\Bundle\CoreBundle\Entity\Account;
use Runalyze\Bundle\CoreBundle\Entity\ConfRepository;

class ConfigurationManager
{
    /** @var ConfRepository */
    protected $Repository;

    public function __construct(ConfRepository $repository)
    {
        $this->Repository = $repository;
    }

    /**
     * @param Account $account
     * @return RunalyzeConfigurationList
     */
    public function getList(Account $account)
    {
        $listData = [];

        foreach ($this->Repository->findByAccount($account) as $conf) {
            $listData[$conf->getCategory().'.'.$conf->getKey()] = $conf->getValue();
        }

        $list = new RunalyzeConfigurationList();
        $list->mergeWith($listData);

        return $list;
    }
}
