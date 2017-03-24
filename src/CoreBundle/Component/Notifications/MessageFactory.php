<?php

namespace Runalyze\Bundle\CoreBundle\Component\Notifications;

use Runalyze\Bundle\CoreBundle\Component\Notifications\Message\TemplateBasedMessage;
use Runalyze\Bundle\CoreBundle\Entity\Notification;
use Runalyze\Profile\Notifications\MessageTypeProfile;

class MessageFactory
{
    /**
     * @param Notification $notification
     * @return TemplateBasedMessage
     */
    public function getMessage(Notification $notification)
    {
        if (MessageTypeProfile::TEMPLATE_BASED_MESSAGE === $notification->getMessageType()) {
            return new TemplateBasedMessage($notification->getData());
        }

        throw new \InvalidArgumentException();
    }
}
