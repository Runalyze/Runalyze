<?php

namespace Runalyze\Bundle\CoreBundle\Controller;

use Runalyze\Bundle\CoreBundle\Entity\Account;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * @Route("/notifications")
 */
class NotificationsController extends Controller
{
    /**
     * @return \Runalyze\Bundle\CoreBundle\Entity\NotificationRepository
     */
    protected function getNotificationsRepository()
    {
        return $this->getDoctrine()->getRepository('CoreBundle:Notification');
    }

    /**
     * @Route("", name="notifications-list")
     * @Security("has_role('ROLE_USER')")
     */
    public function newNotificationsAction(Account $account)
    {
        return $this->render('notifications.html.twig', [
            'notifications' => $this->getNotificationsRepository()->findAll($account),
            'router' => $this->get('router'),
            'translator' => $this->get('translator.default')
        ]);
    }
}
