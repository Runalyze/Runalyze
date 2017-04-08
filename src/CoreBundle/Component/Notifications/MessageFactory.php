<?php

namespace Runalyze\Bundle\CoreBundle\Component\Notifications;

use Runalyze\Bundle\CoreBundle\Component\Notifications\Message\BackupReadyMessage;
use Runalyze\Bundle\CoreBundle\Component\Notifications\Message\MessageInterface;
use Runalyze\Bundle\CoreBundle\Component\Notifications\Message\TemplateBasedMessage;
use Runalyze\Bundle\CoreBundle\Entity\Notification;
use Runalyze\Profile\Notifications\MessageTypeProfile;

class MessageFactory
{
    /**
     * @param Notification $notification
     * @return MessageInterface
     */
    public function getMessage(Notification $notification)
    {
        if (MessageTypeProfile::TEMPLATE_BASED_MESSAGE == $notification->getMessageType()) {
            return new TemplateBasedMessage($notification->getData());
        }

        if (MessageTypeProfile::BACKUP_READY_MESSAGE == $notification->getMessageType()) {
            return new BackupReadyMessage($notification->getData());
        }


        throw new \InvalidArgumentException('Given notification is of unknown type.');
    }
}
