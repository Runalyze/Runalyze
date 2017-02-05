<?php

namespace Runalyze\Bundle\CoreBundle\Tests\Services;

use Runalyze\Bundle\CoreBundle\Services\AutomaticReloadFlagSetter;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBag;

class AutomaticReloadFlagSetterTest extends \PHPUnit_Framework_TestCase
{
    /** @var FlashBag */
    protected $FlashBag;

    /** @var AutomaticReloadFlagSetter */
    protected $FlagSetter;

    public function setUp()
    {
        $this->FlashBag = new FlashBag();
        $this->FlagSetter = new AutomaticReloadFlagSetter($this->FlashBag);
    }

    public function testThatValueIsSetIntoFlashBag()
    {
        $this->assertEquals([], $this->FlashBag->get(AutomaticReloadFlagSetter::FLASH_BAG_KEY));

        $this->FlagSetter->set(AutomaticReloadFlagSetter::FLAG_TRAINING);

        $this->assertEquals([AutomaticReloadFlagSetter::FLAG_TRAINING], $this->FlashBag->get(AutomaticReloadFlagSetter::FLASH_BAG_KEY));
    }

    public function testThatAddingLowerFlagsDoesNotChangeFlag()
    {
        $this->assertEquals(AutomaticReloadFlagSetter::FLAG_ALL, $this->FlagSetter->set(AutomaticReloadFlagSetter::FLAG_ALL));
        $this->assertEquals(AutomaticReloadFlagSetter::FLAG_ALL, $this->FlagSetter->set(AutomaticReloadFlagSetter::FLAG_PLUGINS));
        $this->assertEquals(AutomaticReloadFlagSetter::FLAG_ALL, $this->FlagSetter->set(AutomaticReloadFlagSetter::FLAG_TRAINING_AND_DATA_BROWSER));
        $this->assertEquals(AutomaticReloadFlagSetter::FLAG_ALL, $this->FlagSetter->set(AutomaticReloadFlagSetter::FLAG_TRAINING));
        $this->assertEquals(AutomaticReloadFlagSetter::FLAG_ALL, $this->FlagSetter->set(AutomaticReloadFlagSetter::FLAG_DATA_BROWSER));
        $this->assertEquals(AutomaticReloadFlagSetter::FLAG_PAGE, $this->FlagSetter->set(AutomaticReloadFlagSetter::FLAG_PAGE));
    }

    public function testThatPluginAndDataBrowserWillBecomeAll()
    {
        $this->assertEquals(AutomaticReloadFlagSetter::FLAG_PLUGINS, $this->FlagSetter->set(AutomaticReloadFlagSetter::FLAG_PLUGINS));
        $this->assertEquals(AutomaticReloadFlagSetter::FLAG_ALL, $this->FlagSetter->set(AutomaticReloadFlagSetter::FLAG_DATA_BROWSER));
    }

    public function testThatTrainingAndDataBrowserWillBeMerged()
    {
        $this->assertEquals(AutomaticReloadFlagSetter::FLAG_TRAINING, $this->FlagSetter->set(AutomaticReloadFlagSetter::FLAG_TRAINING));
        $this->assertEquals(AutomaticReloadFlagSetter::FLAG_TRAINING_AND_DATA_BROWSER, $this->FlagSetter->set(AutomaticReloadFlagSetter::FLAG_DATA_BROWSER));
    }
}
