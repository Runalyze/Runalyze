<?php

namespace Runalyze\Bundle\CoreBundle\Component\Notifications\Message;

use Runalyze\Profile\Notifications\MessageTypeProfile;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Yaml\Yaml;

class BackupReadyMessage implements MessageInterface
{


    public function getMessageType()
    {
        return MessageTypeProfile::BACKUP_READY_MESSAGE;
    }

    public function getData()
    {
        return 'test';
    }

    public function getLifetime()
    {
        return 5;
    }

    public function getText(TranslatorInterface $translator = null)
    {
        return $translator->trans('Your posters have been generated and are now available for download.');
    }

    public function hasLink()
    {

        return true;
    }

    public function getLink(RouterInterface $router = null)
    {
        return $router->generate('tools-backup');
    }

}
