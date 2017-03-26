<?php

namespace Runalyze\Bundle\CoreBundle\Tests\Entity;

use Runalyze\Bundle\CoreBundle\Component\Notifications\Message\TemplateBasedMessage;
use Runalyze\Bundle\CoreBundle\Entity\Account;
use Runalyze\Bundle\CoreBundle\Entity\Notification;
use Runalyze\Bundle\CoreBundle\Entity\NotificationRepository;

class NotificationRepositoryTest extends AbstractRepositoryTestCase
{
    /** @var NotificationRepository */
    protected $NotificationRepository;

    /** @var Account */
    protected $Account;

    protected function setUp()
    {
        parent::setUp();

        $this->NotificationRepository = $this->EntityManager->getRepository('CoreBundle:Notification');
        $this->Account = $this->getDefaultAccount();
    }

    /**
     * @param Account $account
     * @param int|null $createdAt [timestamp]
     * @param int|null $expirationAt [timestamp]
     * @return int
     */
    protected function insertMessageFor(Account $account, $createdAt = null, $expirationAt = null)
    {
        $notification = Notification::createFromMessage(
            new TemplateBasedMessage('../../../tests/CoreBundle/DataFixtures/messages/test-message.yml'),
            $account
        );

        if (null !== $createdAt) {
            $notification->setCreatedAt($createdAt);
            $notification->setExpirationAt($expirationAt);
        }

        $this->NotificationRepository->save($notification);

        return $notification->getId();
    }

    public function testFindAllNotifications()
    {
        $this->insertMessageFor($this->Account);
        $this->insertMessageFor($this->getEmptyAccount());
        $this->insertMessageFor($this->Account);
        $this->insertMessageFor($this->Account);

        $this->assertEquals(3, count($this->NotificationRepository->findAll($this->Account)));
        $this->assertEquals(1, count($this->NotificationRepository->findAll($this->getEmptyAccount())));
    }

    public function testFindAllNotificationsSince()
    {
        $this->insertMessageFor($this->Account, 10);
        $this->insertMessageFor($this->Account, 20);
        $this->insertMessageFor($this->Account, 30);

        $this->assertEquals(3, count($this->NotificationRepository->findAllSince(5, $this->Account)));
        $this->assertEquals(2, count($this->NotificationRepository->findAllSince(15, $this->Account)));
        $this->assertEquals(1, count($this->NotificationRepository->findAllSince(25, $this->Account)));
        $this->assertEquals(0, count($this->NotificationRepository->findAllSince(35, $this->Account)));
    }

    public function testMarkSingleNotificationAsRead()
    {
        $idRead = $this->insertMessageFor($this->Account);
        $idNotRead = $this->insertMessageFor($this->Account);

        $this->NotificationRepository->markAsRead($this->NotificationRepository->find($idRead));

        $this->assertTrue($this->NotificationRepository->find($idRead)->wasRead());
        $this->assertFalse($this->NotificationRepository->find($idNotRead)->wasRead());
    }

    public function testMarkAllNotificationsAsRead()
    {
        $idFirst = $this->insertMessageFor($this->Account);
        $idSecond = $this->insertMessageFor($this->Account);
        $idOtherAccount = $this->insertMessageFor($this->getEmptyAccount());

        $this->NotificationRepository->markAllAsRead($this->Account);

        $this->assertTrue($this->NotificationRepository->find($idFirst)->wasRead());
        $this->assertTrue($this->NotificationRepository->find($idSecond)->wasRead());
        $this->assertFalse($this->NotificationRepository->find($idOtherAccount)->wasRead());
    }

    public function testRemovingExpiredNotifications()
    {
        $idFirst = $this->insertMessageFor($this->Account, 0, time() - 3600);
        $idSecond = $this->insertMessageFor($this->Account, 0, time() - 3600);
        $idRecent = $this->insertMessageFor($this->Account, 0, time() + 3600);
        $idOtherAccount = $this->insertMessageFor($this->getEmptyAccount(), 0, time() - 3600);

        $this->NotificationRepository->removeExpiredNotifications();

        $this->assertNull($this->NotificationRepository->find($idFirst));
        $this->assertNull($this->NotificationRepository->find($idSecond));
        $this->assertInstanceOf(Notification::class, $this->NotificationRepository->find($idRecent));
        $this->assertNull($this->NotificationRepository->find($idOtherAccount));
    }
}
