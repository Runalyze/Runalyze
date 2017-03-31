<?php

namespace Runalyze\Bundle\CoreBundle\Command;

use Runalyze\Bundle\CoreBundle\Component\Notifications\Message\TemplateBasedMessage;
use Runalyze\Bundle\CoreBundle\Entity\Account;
use Runalyze\Bundle\CoreBundle\Entity\AccountRepository;
use Runalyze\Bundle\CoreBundle\Entity\Notification;
use Runalyze\Bundle\CoreBundle\Entity\NotificationRepository;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;

class CreateNotificationCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('runalyze:notifications:create')
            ->setDescription('Create global notifications')
            ->addArgument('template', InputArgument::REQUIRED, 'Template file')
            ->addOption('lang', null, InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY, 'Languages to select accounts')
            ->addOption('exclude-lang', null, InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY, 'Excluded languages to select accounts')
            ->addOption('account', null, InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY, 'Account ids')
            ->addOption('lifetime', null, InputOption::VALUE_OPTIONAL, 'Lifetime [days]')
        ;
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     *
     * @return null|int null or 0 if everything went fine, or an error code
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $notification = $this->createNotification($input->getArgument('template'), $input->getOption('lifetime'));
        $num = 0;

        if (!empty($input->getOption('account'))) {
            $num = $this->insertSingleNotifications($notification, $input->getOption('account'));
        } else {
            $num = $this->insertNotificationsWithSubquery($notification, $input->getOption('lang'), $input->getOption('exclude-lang'));
        }

        $output->writeln(sprintf('<info>%u notifications have been created.</info>', $num));
        $output->writeln('');

        return null;
    }

    /**
     * @param string $template
     * @param int|null $lifetime [days]
     * @return Notification
     */
    protected function createNotification($template, $lifetime)
    {
        return Notification::createFromMessage(
            new TemplateBasedMessage($template, $lifetime),
            new Account()
        );
    }

    protected function insertSingleNotifications(Notification $notification, array $accountIds)
    {
        /** @var NotificationRepository $notificationRepository */
        $notificationRepository = $this->getContainer()->get('doctrine')->getRepository('CoreBundle:Notification');

        /** @var AccountRepository $accountRepository */
        $accountRepository = $this->getContainer()->get('doctrine')->getRepository('CoreBundle:Account');
        $num = 0;

        foreach ($accountIds as $id) {
            $account = $accountRepository->find($id);

            if (null !== $account) {
                $accountsNotification = clone $notification;
                $accountsNotification->setAccount($account);

                $notificationRepository->save($accountsNotification);
                $num++;
            }
        }

        return $num;
    }

    protected function insertNotificationsWithSubquery(Notification $notification, array $lang, array $excludeLang)
    {
        $prefix = $this->getContainer()->getParameter('database_prefix');
        $accountWhere = $this->getWhereToFindRelevantAccounts($lang, $excludeLang);

        $statement = $this->getContainer()->get('doctrine.dbal.default_connection')->prepare(
            'INSERT INTO `'.$prefix.'notification` (`messageType`, `createdAt`, `expirationAt`, `data`, `account_id`) '.
            'SELECT ?, ?, ?, ?, `a`.`id` FROM `'.$prefix.'account` AS `a` WHERE '.$accountWhere
        );

        $statement->execute([
            $notification->getMessageType(),
            $notification->getCreatedAt(),
            $notification->getExpirationAt(),
            $notification->getData()
        ]);

        return $statement->rowCount();
    }

    /**
     * @param array $lang
     * @param array $excludeLang
     * @return string
     */
    protected function getWhereToFindRelevantAccounts(array $lang, array $excludeLang)
    {
        $exclude = false;

        if (!empty($excludeLang)) {
            $exclude = true;
            $lang = $excludeLang;
        }

        if (!empty($lang)) {
            return '`a`.`language` '.($exclude ? 'NOT' : '').' IN ('.implode(', ', array_map(function($var){
                return '"'.str_replace(
                    array('\\', "\0", "\n", "\r", "'", '"', "\x1a"),
                    array('\\\\', '\\0', '\\n', '\\r', "\\'", '\\"', '\\Z'),
                    $var
                ).'"';
            }, $lang)).')';
        }

        return '1';
    }
}
