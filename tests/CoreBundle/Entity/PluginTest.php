<?php

namespace Runalyze\Bundle\CoreBundle\Tests\Entity;

use Runalyze\Bundle\CoreBundle\Entity\Plugin;

class PluginTest extends \PHPUnit_Framework_TestCase
{
    /** @var Plugin */
    protected $Plugin;

    public function setUp()
    {
        $this->Plugin = new Plugin();
    }

    public function testMoving()
    {
        $this->Plugin->setOrder(4);

        $this->assertEquals(5, $this->Plugin->moveDown()->getOrder());
        $this->assertEquals(4, $this->Plugin->moveUp()->getOrder());
    }

    public function testTogglingState()
    {
        $this->Plugin->setActive(Plugin::STATE_ACTIVE);

        $this->assertEquals(Plugin::STATE_HIDDEN, $this->Plugin->toggleHidden()->getActive());
        $this->assertEquals(Plugin::STATE_ACTIVE, $this->Plugin->toggleHidden()->getActive());

        $this->assertEquals(Plugin::STATE_INACTIVE, $this->Plugin->setActive(Plugin::STATE_INACTIVE)->toggleHidden()->getActive());
    }
}
