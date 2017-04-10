<?php

namespace Runalyze\Bundle\CoreBundle\Component\Notifications;

use Runalyze\Bundle\CoreBundle\Component\Notifications\Message\BackupReadyMessage;
use Runalyze\Bundle\CoreBundle\Component\Notifications\Message\MessageInterface;
use Runalyze\Bundle\CoreBundle\Component\Notifications\Message\PosterGeneratedMessage;
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
        switch ($notification->getMessageType()) {
            case MessageTypeProfile::TEMPLATE_BASED_MESSAGE:
                return new TemplateBasedMessage($notification->getData());
            case MessageTypeProfile::POSTER_GENERATED_MESSAGE:
                return new PosterGeneratedMessage($notification->getData());
            case MessageTypeProfile::BACKUP_READY_MESSAGE:
                return new BackupReadyMessage();
        }

        throw new \InvalidArgumentException('Given notification is of unknown type.');
    }
}
