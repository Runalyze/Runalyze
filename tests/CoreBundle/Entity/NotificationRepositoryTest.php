<?php

namespace Runalyze\Bundle\CoreBundle\Tests\Entity;

use Runalyze\Bundle\CoreBundle\Entity\Account;
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

    public function testFindAllNotifications()
    {
        // TODO
    }

    public function testFindAllNotificationsSince()
    {
        // TODO
    }

    public function testMarkSingleNotificationAsRead()
    {
        // TODO
    }

    public function testMarkAllNotificationsAsRead()
    {
        // TODO
    }
}
