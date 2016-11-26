<?php

namespace Runalyze\Bundle\CoreBundle\Services;

use Runalyze\Bundle\CoreBundle\Entity\Account;
use Runalyze\Bundle\CoreBundle\Entity\Raceresult;
use Runalyze\Bundle\CoreBundle\Entity\Training;
use Runalyze\Bundle\CoreBundle\Services\Configuration\ConfigurationManager;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;

class PrivacyGuard
{
    /** @var TokenStorage */
    protected $TokenStorage;

    /** @var ConfigurationManager */
    protected $ConfigurationManager;

    public function __construct(TokenStorage $tokenStorage, ConfigurationManager $configurationManager)
    {
        $this->TokenStorage = $tokenStorage;
        $this->ConfigurationManager = $configurationManager;
    }

    /**
     * @return bool
     */
    protected function knowsUser()
    {
        return null !== $this->TokenStorage->getToken() && $this->TokenStorage->getToken()->getUser() instanceof Account;
    }

    /**
     * @return Account|null
     */
    protected function getUser()
    {
        return $this->knowsUser() ? $this->TokenStorage->getToken()->getUser() : null;
    }

    /**
     * @param Account $account
     * @return \Runalyze\Configuration\Category\Privacy
     */
    protected function getPrivacyFor(Account $account)
    {
        return $this->ConfigurationManager->getList($account)->getPrivacy()->getLegacyCategory();
    }

    /**
     * @param Training $activity
     * @param Raceresult|null $raceresult
     * @return bool
     */
    public function isMapVisible(Training $activity, Raceresult $raceresult = null)
    {
        if ($this->knowsUser() && $this->getUser()->getId() == $activity->getAccount()->getId()) {
            return true;
        }

        $mapVisibility = $this->getPrivacyFor($activity->getAccount())->RoutePrivacy();

        if ($mapVisibility->showRace()) {
            return null !== $raceresult;
        }

        return $mapVisibility->showAlways();
    }
}
