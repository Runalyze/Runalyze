<?php

namespace Runalyze\Bundle\CoreBundle\Queue\Receiver;

use Bernard\Message\DefaultMessage;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Runalyze\Bundle\CoreBundle\Component\Tool\Backup\FilenameHandler;
use Runalyze\Bundle\CoreBundle\Component\Tool\Backup\JsonBackup;
use Runalyze\Bundle\CoreBundle\Component\Tool\Backup\SqlBackup;

class BackupReceiver
{

    /** @var ContainerInterface|null */
    private $container;

    /**
     * @return string
     */
    protected function getPathToBackupFiles()
    {
        return $this->container->getParameter('kernel.root_dir').'/../data/backup-tool/backup/';
    }

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function UserBackup($message) {
        $Frontend = new \FrontendShared(true);

        $fileHandler = new FilenameHandler($message->get('accountid'));
        $fileHandler->setRunalyzeVersion($this->container->getParameter('runalyze_version'));

        /** @var Account $account */
        $account = $this->container->get('doctrine.orm.entity_manager')->getRepository('CoreBundle:Account')->find($message->get('accountid'));

        if ($message->get('export-type') == 'json') {
            $Backup = new JsonBackup(
                $this->getPathToBackupFiles().$fileHandler->generateInternalFilename(FilenameHandler::JSON_FORMAT),
                $message->get('accountid'),
                \DB::getInstance(),
                $this->container->getParameter('database_prefix'),
                $this->container->getParameter('runalyze_version')
            );
            $Backup->run();
            $this->container->get('app.mailer.account')->sendBackupReadyTo($account);
        } else {
            $Backup = new SqlBackup(
                $this->getPathToBackupFiles().$fileHandler->generateInternalFilename(FilenameHandler::SQL_FORMAT),
                $message->get('accountid'),
                \DB::getInstance(),
                $this->container->getParameter('database_prefix'),
                $this->container->getParameter('runalyze_version')
            );
            $Backup->run();
            $this->container->get('app.mailer.account')->sendBackupReadyTo($account);
        }

    }

}