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
            ->addOption('lang', null, InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, 'Languages to select accounts')
            ->addOption('exclude-lang', null, InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, 'Excluded languages to select accounts')
            ->addOption('account', null, InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, 'Account ids')
            ->addOption('lifetime', null, InputOption::VALUE_REQUIRED, 'Lifetime [days]');
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     *
     * @return null|int null or 0 if everything went fine, or an error code
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if (!$this->validateInput($input, $output)) {
            return 1;
        }

        $notification = $this->createNotification($input->getArgument('template'), $input->getOption('lifetime'));

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
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return bool
     */
    protected function validateInput(InputInterface $input, OutputInterface $output)
    {
        return (
            $this->checkValidation($this->validateTemplate($input->getArgument('template')), $output, 'Template must exist in /data/views/notifications/.') &&
            $this->checkValidation($this->validateLanguage($input->getOption('lang')), $output, 'Language keys must be alphabetic strings.') &&
            $this->checkValidation($this->validateLanguage($input->getOption('exclude-lang')), $output, 'Language keys to exclude must be alphabetic strings.') &&
            $this->checkValidation($this->validateAccountIds($input->getOption('account')), $output, 'Account IDs must be integers.') &&
            $this->checkValidation($this->validateLifetime($input->getOption('lifetime')), $output, 'Lifetime must be an integer.')
        );
    }

    /**
     * @param bool $success
     * @param OutputInterface $output
     * @param string $messageOnError
     * @return bool
     */
    protected function checkValidation($success, OutputInterface $output, $messageOnError)
    {
        if (!$success) {
            $output->writeln(sprintf('<error>Invalid input: %s</error>', $messageOnError));
            $output->writeln('');

            return false;
        }

        return true;
    }

    /**
     * @param string $templateName
     * @return bool
     */
    protected function validateTemplate($templateName)
    {
        try {
            new TemplateBasedMessage($templateName);
        } catch (\InvalidArgumentException $e) {
            return false;
        }

        return true;
    }

    /**
     * @param array $lang
     * @return bool
     */
    protected function validateLanguage(array $lang)
    {
        return array_reduce($lang,
            function ($state, $value) {
                return $state && ctype_alpha($value);
            }, true
        );
    }

    /**
     * @param array $accountIds
     * @return bool
     */
    protected function validateAccountIds(array $accountIds)
    {
        return array_reduce($accountIds,
            function ($state, $value) {
                return $state && ctype_digit($value);
            }, true
        );
    }

    /**
     * @param null|string $lifetime
     * @return bool
     */
    protected function validateLifetime($lifetime)
    {
        return (null === $lifetime || ctype_digit($lifetime));
    }

    /**
     * @param string $template
     * @param int|null $lifetime [days]
     * @return Notification
     */
    protected function createNotification($template, $lifetime)
    {
        if (null !== $lifetime) {
            $lifetime = (int)$lifetime;
        }

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
            return '`a`.`language` '.($exclude ? 'NOT' : '').' IN ("'.implode('", "', $lang).'")';
        }

        return '1';
    }
}
