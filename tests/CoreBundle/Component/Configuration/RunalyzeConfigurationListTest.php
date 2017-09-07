<?php

namespace Runalyze\Bundle\CoreBundle\Tests\Component\Configuration;

use Runalyze\Bundle\CoreBundle\Component\Configuration\RunalyzeConfigurationList;

class RunalyzeConfigurationListTest extends \PHPUnit_Framework_TestCase
{
    public function testConstructor()
    {
        new RunalyzeConfigurationList();
    }

    public function testThatAllCategoriesAreAccessible()
    {
        $config = new RunalyzeConfigurationList();

        $config->getActivityForm();
        $config->getActivityView();
        $config->getBasicEndurance();
        $config->getData();
        $config->getDataBrowser();
        $config->getDesign();
        $config->getGeneral();
        $config->getPrivacy();
        $config->getTrimp();
        $config->getVO2maxCorrectionFactor();
    }

    public function testThatUnitSystemIsPersistent()
    {
        $config = new RunalyzeConfigurationList();

        $this->assertEquals($config->getUnitSystem(), $config->getUnitSystem());
    }
}
