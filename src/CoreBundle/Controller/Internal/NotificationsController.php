<?php

namespace Runalyze\Bundle\CoreBundle\Controller\Internal;

use Runalyze\Bundle\CoreBundle\Component\Notifications\MessageFactory;
use Runalyze\Bundle\CoreBundle\Entity\Account;
use Runalyze\Bundle\CoreBundle\Entity\Notification;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/_internal/notifications")
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
     * @Route("/read/all", name="internal-notifications-read-all")
     * @Security("has_role('ROLE_USER')")
     */
    public function readAllNotificationsAction(Account $account)
    {
        $this->getNotificationsRepository()->markAllAsRead($account);

        return new JsonResponse();
    }

    /**
     * @Route("/read/{id}", name="internal-notifications-read")
     * @ParamConverter("notification", class="CoreBundle:Notification")
     * @Security("has_role('ROLE_USER')")
     */
    public function readNotificationAction(Notification $notification, Account $account)
    {
        if ($notification->getAccount()->getId() != $account->getId()) {
            return $this->createAccessDeniedException();
        }

        $this->getNotificationsRepository()->markAsRead($notification);

        return new JsonResponse();
    }

    /**
     * @Route("", name="internal-notifications-list")
     * @Security("has_role('ROLE_USER')")
     */
    public function newNotificationsAction(Request $request, Account $account)
    {
        $messages = [];
        $factory = new MessageFactory();
        $router = $this->get('router');
        $translator = $this->get('translator.default');
        $notifications = $this->getNotificationsRepository()->findAllSince($request->query->getInt('last_request'), $account);

        foreach ($notifications as $notification) {
            $message = $factory->getMessage($notification);
            $messages[] = [
                'id' => $notification->getId(),
                'link' => $message->hasLink() ? $message->getLink($router) : '',
                'text' => $message->getText($translator),
                'size' => $message->isLinkInternal() ? $message->getWindowSizeForInternalLink() : 'external',
                'createdAt' => $notification->getCreatedAt()
            ];
        }

        return new JsonResponse($messages);
    }
}
