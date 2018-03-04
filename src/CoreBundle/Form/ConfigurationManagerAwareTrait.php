<?php

namespace Runalyze\Bundle\CoreBundle\Form;

use Runalyze\Bundle\CoreBundle\Services\Configuration\ConfigurationManager;

trait ConfigurationManagerAwareTrait
{
    /** @var ConfigurationManager */
    protected $ConfigurationManager;

    public function setConfigurationManager(ConfigurationManager $manager)
    {
        $this->ConfigurationManager = $manager;
    }

    /**
     * @return \Runalyze\Bundle\CoreBundle\Component\Configuration\RunalyzeConfigurationList
     */
    public function getConfigurationList()
    {
        return $this->ConfigurationManager->getList();
    }
}
