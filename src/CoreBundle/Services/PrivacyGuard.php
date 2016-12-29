<?php

namespace Runalyze\Bundle\CoreBundle\Services;

use Runalyze\Bundle\CoreBundle\Entity\Account;
use Runalyze\Bundle\CoreBundle\Entity\Raceresult;
use Runalyze\Bundle\CoreBundle\Entity\Training;
use Runalyze\Bundle\CoreBundle\Services\Configuration\ConfigurationManager;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;

class PrivacyGuard
{
    use TokenStorageAwareServiceTrait;

    /** @var ConfigurationManager */
    protected $ConfigurationManager;

    public function __construct(TokenStorage $tokenStorage, ConfigurationManager $configurationManager)
    {
        $this->TokenStorage = $tokenStorage;
        $this->ConfigurationManager = $configurationManager;
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
