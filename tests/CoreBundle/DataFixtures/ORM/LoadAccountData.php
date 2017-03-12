<?php

namespace Runalyze\Bundle\CoreBundle\Tests\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Runalyze\Bundle\CoreBundle\Component\Account\Registration;
use Runalyze\Bundle\CoreBundle\Entity\Account;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class LoadAccountData extends AbstractFixture implements OrderedFixtureInterface, ContainerAwareInterface
{
    /** @var ContainerInterface|null */
    protected $Container;

    public function setContainer(ContainerInterface $container = null)
    {
        $this->Container = $container;
    }

    public function load(ObjectManager $manager)
    {
        $this->addEmptyAccount($manager);
        $this->registerDefaultAccount($manager);
    }

    protected function addEmptyAccount(ObjectManager $manager)
    {
        $emptyAccount = new Account();
        $emptyAccount->setUsername('empty');
        $emptyAccount->setMail('empty@test.com');
        $emptyAccount->setPassword('emptyPassword');

        $manager->persist($emptyAccount);
        $manager->flush();

        $this->addReference('account-empty', $emptyAccount);
    }

    protected function registerDefaultAccount(ObjectManager $manager)
    {
        $defaultAccount = new Account();
        $defaultAccount->setUsername('default');
        $defaultAccount->setMail('default@test.com');

        $registration = $this->registerAccount($manager, $defaultAccount, 'defaultPassword');

        $this->addReference('account-default', $defaultAccount);
        $this->addReference('account-default.sport-running', $registration->getRegisteredSportForRunning());
        $this->addReference('account-default.sport-cycling', $registration->getRegisteredSportForCycling());
        $this->addReference('account-default.equipment-type-clothes', $registration->getRegisteredEquipmentTypeClothes());
    }

    protected function registerAccount(ObjectManager $manager, Account $account, $password)
    {
        $registration = new Registration($manager, $account);
        $registration->setPassword($password, $this->Container->get('security.encoder_factory'));
        $registration->registerAccount();

        return $registration;
    }

    public function getOrder()
    {
        return 1;
    }
}
