<?php

namespace Runalyze\Bundle\CoreBundle\Tests\Entity;

use Runalyze\Bundle\CoreBundle\Entity\Account;
use Runalyze\Profile\Athlete\Gender;

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
        $this->assertFalse($this->Account->knowsGender());
        $this->assertFalse($this->Account->knowsBirthYear());
        $this->assertNull($this->Account->getAge());
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

    public function testGender()
    {
        $this->Account->setGender(Gender::FEMALE);

        $this->assertTrue($this->Account->knowsGender());
        $this->assertTrue($this->Account->isFemale());
        $this->assertFalse($this->Account->isMale());

        $this->Account->setGender(Gender::MALE);

        $this->assertTrue($this->Account->knowsGender());
        $this->assertFalse($this->Account->isFemale());
        $this->assertTrue($this->Account->isMale());
    }

    public function testAge()
    {
        $this->Account->setBirthyear((int)date('Y') - 42);

        $this->assertTrue($this->Account->knowsBirthYear());
        $this->assertEquals(42, $this->Account->getAge());
    }
}
