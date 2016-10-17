<?php

namespace Runalyze\Bundle\CoreBundle\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;

class CleanupRegistrationsCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('runalyze:cleanup:registrations')
            ->setDescription('Delete not activated account (default: older than 7 days).')
            ->addArgument('days', InputArgument::OPTIONAL, 'min. age of not activated accounts')
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
        $days = $input->getArgument('days') ?: 7;

        $output->writeln('<info>Delete all not activated accounts older than '.$days.' days</info>');
        $output->writeln('');

        $repository = $this->getContainer()->get('doctrine')->getRepository('CoreBundle:Account');
        $delete =  $repository->deleteNotActivatedAccounts($days);
        $output->writeln('<info>'.$delete.' deleted accounts</info>');
        $output->writeln('');
    }

}
