<?php

namespace Runalyze\Bundle\CoreBundle\Services\Configuration;

use Runalyze\Bundle\CoreBundle\Component\Configuration\RunalyzeConfigurationList;
use Runalyze\Bundle\CoreBundle\Entity\Account;
use Runalyze\Bundle\CoreBundle\Entity\ConfRepository;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;

class ConfigurationManager
{
    /** @var ConfRepository */
    protected $Repository;

    /** @var TokenStorage */
    protected $TokenStorage;

    /** @var null|RunalyzeConfigurationList */
    protected $CurrentConfigurationList = null;

    /** @var null|RunalyzeConfigurationList */
    protected $DefaultList = null;

    public function __construct(ConfRepository $repository, TokenStorage $tokenStorage)
    {
        $this->Repository = $repository;
        $this->TokenStorage = $tokenStorage;
    }

    /**
     * @param Account|null $account defaults to current user or default config, if there is no user logged in
     * @return RunalyzeConfigurationList
     */
    public function getList(Account $account = null)
    {
        if (null === $account) {
            if (null === $this->CurrentConfigurationList) {
                $this->setListForCurrentUser();
            }

            return $this->CurrentConfigurationList;
        }

        return $this->getListFor($account);
    }

    /**
     * @param Account $account
     * @return RunalyzeConfigurationList
     */
    protected function getListFor(Account $account)
    {
        $listData = [];

        foreach ($this->Repository->findByAccount($account) as $conf) {
            $listData[$conf->getCategory().'.'.$conf->getKey()] = $conf->getValue();
        }

        $list = new RunalyzeConfigurationList();
        $list->mergeWith($listData);

        return $list;
    }

    protected function setListForCurrentUser()
    {
        $user = $this->TokenStorage->getToken()->getUser();

        if ($user instanceof Account) {
            $this->CurrentConfigurationList = $this->getListFor($user);
        } else {
            $this->CurrentConfigurationList = new RunalyzeConfigurationList();
        }
    }
}
