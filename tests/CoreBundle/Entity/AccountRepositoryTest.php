<?php

namespace Runalyze\Bundle\CoreBundle\Tests\Entity;

use Doctrine\ORM\EntityManager;
use Runalyze\Bundle\CoreBundle\Entity\Account;
use Runalyze\Bundle\CoreBundle\Entity\AccountRepository;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;

class AccountRepositoryTest extends AbstractRepositoryTestCase
{
    /** @var AccountRepository */
    protected $AccountRepository;

    protected function setUp()
    {
        $this->FixtureClasses = [];

        parent::setUp();

        $this->AccountRepository = $this->EntityManager->getRepository('CoreBundle:Account');
    }

    /**
     * @param string $username
     * @param string $mail
     * @return Account
     */
    protected function getNewAccount($username, $mail = '')
    {
        if ('' == $mail) {
            $mail = $username.'@test.com';
        }

        return (new Account())
            ->setUsername($username)
            ->setMail($mail)
            ->setPassword('');
    }

    public function testLoadingUnknownUser()
    {
        $this->assertNull($this->AccountRepository->loadUserByUsername('foobar'));
    }

    public function testLoadingSimpleUser()
    {
        $this->EntityManager->persist($this->getNewAccount('foobar', 'foo@bar.com'));
        $this->EntityManager->flush();

        $accountInRepository = $this->AccountRepository->loadUserByUsername('foobar');

        $this->assertInstanceOf(Account::class, $accountInRepository);
        $this->assertEquals('foobar', $accountInRepository->getUsername());
        $this->assertEquals($accountInRepository, $this->AccountRepository->loadUserByUsername('foo@bar.com'));
    }

    public function testNumberOfActivatedUsers()
    {
        $activeAccounts = $this->AccountRepository->getAmountOfActivatedUsers(false);
        $activatedAccountNames = ['foo', 'bar', 'baz'];

        foreach ($activatedAccountNames as $name) {
            $this->EntityManager->persist($this->getNewAccount($name));
        }

        $this->EntityManager->persist($this->getNewAccount('foobar')->setActivationHash(bin2hex(random_bytes(16))));
        $this->EntityManager->flush();

        $this->assertEquals($activeAccounts + 3, $this->AccountRepository->getAmountOfActivatedUsers(false));
    }

    public function testDeletingNonActivatedUsers()
    {
        $notActivatedAccountNames = ['foo', 'bar', 'baz'];
        foreach ($notActivatedAccountNames as $name) {
            $this->EntityManager->persist(
                $this->getNewAccount($name)
                    ->setActivationHash(bin2hex(random_bytes(16)))
                    ->setRegisterdate(strtotime('today -30 days'))
            );
        }

        $this->EntityManager->persist($this->getNewAccount('foobar', 'foobar@test.com'));
        $this->EntityManager->persist($this->getNewAccount('todays_user')->setActivationHash(bin2hex(random_bytes(16)))->setRegisterdate(time()));
        $this->EntityManager->flush();

        $this->assertEquals(3, $this->AccountRepository->deleteNotActivatedAccounts(7));
        $this->assertNotNull($this->AccountRepository->loadUserByUsername('foobar'));
        $this->assertNotNull($this->AccountRepository->loadUserByUsername('todays_user'));
    }

    public function testDeletingByHash()
    {
        $deletionHash = bin2hex(random_bytes(16));
        $this->EntityManager->persist($this->getNewAccount('foo')->setDeletionHash($deletionHash));
        $this->EntityManager->persist($this->getNewAccount('bar'));
        $this->EntityManager->flush();

        $this->assertFalse($this->AccountRepository->deleteByHash('unknownHash'));
        $this->assertTrue($this->AccountRepository->deleteByHash($deletionHash));
        $this->assertNull($this->AccountRepository->loadUserByUsername('foo'));
        $this->assertNotNull($this->AccountRepository->loadUserByUsername('bar'));
    }

    public function testActivatingByHash()
    {
        $fooAccount = $this->getNewAccount('foo')->setNewActivationHash();
        $activationHash = $fooAccount->getActivationHash();

        $this->AccountRepository->save($fooAccount);

        $this->assertFalse($this->AccountRepository->activateByHash('unknownHash'));
        $this->assertTrue($this->AccountRepository->activateByHash($activationHash));
        $this->assertNull($fooAccount->getActivationHash());
    }

    public function testFindingByLanguage()
    {
        $english1 = $this->getNewAccount('en1')->setLanguage('en');
        $english2 = $this->getNewAccount('en2')->setLanguage('en');
        $german = $this->getNewAccount('de')->setLanguage('de');
        $swedish = $this->getNewAccount('sv')->setLanguage('sv');

        $this->EntityManager->persist($english1);
        $this->EntityManager->persist($english2);
        $this->EntityManager->persist($german);
        $this->EntityManager->persist($swedish);
        $this->EntityManager->flush();

        $this->assertEquals(
            [$english1->getId(), $english2->getId()],
            $this->AccountRepository->findAllByLanguage('en')
        );
        $this->assertEquals(
            [$english1->getId(), $english2->getId(), $german->getId()],
            $this->AccountRepository->findAllByLanguage(['en', 'de'])
        );
        $this->assertEquals(
            [$swedish->getId()],
            $this->AccountRepository->findAllByLanguage(['en', 'de'], true)
        );
        $this->assertEquals(
            [$german->getId(), $swedish->getId()],
            $this->AccountRepository->findAllByLanguage('en', true)
        );
    }
}
