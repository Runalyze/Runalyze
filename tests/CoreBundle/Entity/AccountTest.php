<?php

namespace Runalyze\Bundle\CoreBundle\Tests\Entity;

use Runalyze\Bundle\CoreBundle\Entity\Account;

class AccountTest extends \PHPUnit_Framework_TestCase
{
    /** @var Account */
    protected $Account;

    public function setUp()
    {
        $this->Account = new Account();
    }

    public function testDefaultValues()
    {
        $this->assertNotNull($this->Account->getSalt());

        $this->assertNull($this->Account->getChangepwHash());
        $this->assertNull($this->Account->getChangepwTimelimit());
        $this->assertNull($this->Account->getActivationHash());
        $this->assertNull($this->Account->getDeletionHash());
    }

    public function testChangePasswordHash()
    {
        $this->Account->setNewChangePasswordHash();

        $this->assertNotNull($this->Account->getChangepwHash());
        $this->assertNotNull($this->Account->getChangepwTimelimit());

        $this->Account->removeChangePasswordHash();

        $this->assertNull($this->Account->getChangepwHash());
        $this->assertNull($this->Account->getChangepwTimelimit());
    }

    public function testActivationHash()
    {
        $this->Account->setNewActivationHash();

        $this->assertNotNull($this->Account->getActivationHash());

        $this->Account->removeActivationHash();

        $this->assertNull($this->Account->getActivationHash());
    }
}
