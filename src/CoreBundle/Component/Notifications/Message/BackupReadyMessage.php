<?php

namespace Runalyze\Bundle\CoreBundle\Component\Notifications\Message;

use Runalyze\Profile\Notifications\MessageTypeProfile;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Translation\TranslatorInterface;

class BackupReadyMessage implements MessageInterface
{
    public function getMessageType()
    {
        return MessageTypeProfile::BACKUP_READY_MESSAGE;
    }

    public function getData()
    {
        return '';
    }

    public function getLifetime()
    {
        return 5;
    }

    public function getText(TranslatorInterface $translator)
    {
        return $translator->trans('Your backup has been generated and is now available for download.');
    }

    public function hasLink()
    {
        return true;
    }

    public function getLink(RouterInterface $router)
    {
        return $router->generate('tools-backup');
    }
}
