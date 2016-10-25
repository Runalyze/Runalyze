<?php

namespace Runalyze\Bundle\CoreBundle\Twig;

use Runalyze\Bundle\CoreBundle\Component\Configuration\RunalyzeConfigurationList;
use Runalyze\Bundle\CoreBundle\Entity\Account;
use Runalyze\Bundle\CoreBundle\Services\Configuration\ConfigurationManager;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;

class ConfigurationExtension extends \Twig_Extension
{
    /** @var ConfigurationManager */
    protected $ConfigurationManager;

    /** @var TokenStorage */
    protected $TokenStorage;

    /** @var RunalyzeConfigurationList|null */
    protected $CurrentConfigurationList = null;

    public function __construct(ConfigurationManager $configurationManager, TokenStorage $tokenStorage)
    {
        $this->ConfigurationManager = $configurationManager;
        $this->TokenStorage = $tokenStorage;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'runalyze.configuration_extension';
    }

    /**
     * @return Account|null
     */
    protected function getUser()
    {
        $user = $this->TokenStorage->getToken()->getUser();

        if ($user instanceof Account) {
            return $user;
        }

        return null;
    }

    /**
     * @param Account|null $account
     * @return RunalyzeConfigurationList
     */
    protected function getConfigurationList(Account $account = null)
    {
        if (null === $account) {
            return (new RunalyzeConfigurationList());
        }

        if (null === $this->CurrentConfigurationList) {
            $this->CurrentConfigurationList = $this->ConfigurationManager->getList($account);
        }

        return $this->CurrentConfigurationList;
    }

    /**
     * @return \Twig_SimpleFunction[]
     */
    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('configVar', array($this, 'configVar')),
            new \Twig_SimpleFunction('config', array($this, 'config'))
        );
    }

    /**
     * @param string $key
     * @param Account|null $account
     * @return mixed
     */
    public function configVar($key, Account $account = null)
    {
        $account = $account ?: $this->getUser();

        return $this->getConfigurationList($account)->get($key);
    }

    /**
     * @param Account|null $account
     * @return RunalyzeConfigurationList
     */
    public function config(Account $account = null)
    {
        $account = $account ?: $this->getUser();

        return $this->getConfigurationList($account);
    }
}
