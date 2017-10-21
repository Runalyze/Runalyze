<?php

namespace Runalyze\Bundle\CoreBundle\Queue\Receiver;

use Bernard\Message\DefaultMessage;
use Runalyze\Bundle\CoreBundle\Component\Tool\Backup\FilenameHandler;
use Runalyze\Bundle\CoreBundle\Component\Tool\Backup\JsonBackup;
use Runalyze\Bundle\CoreBundle\Component\Tool\Backup\SqlBackup;
use Runalyze\Bundle\CoreBundle\Entity\Account;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Runalyze\Bundle\CoreBundle\Component\Notifications;
use Runalyze\Bundle\CoreBundle\Entity\Notification;
use Runalyze\Bundle\CoreBundle\Component\Notifications\Message\BackupReadyMessage;

class BackupReceiver
{
    /** @var ContainerInterface */
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @return string
     */
    protected function getPathToBackupFiles()
    {
        return $this->container->getParameter('data_directory').'/backup-tool/backup/';
    }

    public function userBackup(DefaultMessage $message)
    {
        $Frontend = new \FrontendShared(true);

        $fileHandler = new FilenameHandler($message->get('accountid'));
        $fileHandler->setRunalyzeVersion($this->container->getParameter('runalyze_version'));

        /** @var Account $account */
        $account = $this->container->get('doctrine.orm.entity_manager')->getRepository('CoreBundle:Account')->find($message->get('accountid'));

        if ('json' == $message->get('export-type')) {
            $Backup = new JsonBackup(
                $this->getPathToBackupFiles().$fileHandler->generateInternalFilename(FilenameHandler::JSON_FORMAT),
                $message->get('accountid'),
                \DB::getInstance(),
                $this->container->getParameter('database_prefix'),
                $this->container->getParameter('runalyze_version')
            );
            $Backup->run();
        } else {
            $Backup = new SqlBackup(
                $this->getPathToBackupFiles().$fileHandler->generateInternalFilename(FilenameHandler::SQL_FORMAT),
                $message->get('accountid'),
                \DB::getInstance(),
                $this->container->getParameter('database_prefix'),
                $this->container->getParameter('runalyze_version')
            );
            $Backup->run();
        }

        $this->container->get('doctrine')->getRepository('CoreBundle:Notification')->save(
            Notification::createFromMessage(new BackupReadyMessage(), $account)
        );
    }
}
