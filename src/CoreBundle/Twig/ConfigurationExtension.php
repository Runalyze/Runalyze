<?php

namespace Runalyze\Bundle\CoreBundle\Twig;

use Runalyze\Bundle\CoreBundle\Component\Configuration\RunalyzeConfigurationList;
use Runalyze\Bundle\CoreBundle\Entity\Account;
use Runalyze\Bundle\CoreBundle\Services\Configuration\ConfigurationManager;

class ConfigurationExtension extends \Twig_Extension
{
    /** @var ConfigurationManager */
    protected $ConfigurationManager;

    public function __construct(ConfigurationManager $configurationManager)
    {
        $this->ConfigurationManager = $configurationManager;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'runalyze.configuration_extension';
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
     * Get config variable from current user
     *
     * @param string $key
     * @return mixed
     */
    public function configVar($key)
    {
        return $this->ConfigurationManager->getList()->get($key);
    }

    /**
     * @param Account|null $account
     * @return RunalyzeConfigurationList
     */
    public function config(Account $account = null)
    {
        return $this->ConfigurationManager->getList($account);
    }
}
