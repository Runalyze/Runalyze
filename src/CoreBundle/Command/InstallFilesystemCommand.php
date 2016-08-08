<?php

namespace Runalyze\Bundle\CoreBundle\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;

class InstallFilesystemCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('runalyze:install:filesystem')
            ->setDescription('Setup RUNALYZE file system.')
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
        $output->writeln('<info>Setup RUNALYZE file system...</info>');
        $output->writeln('');

        $this->tryToCopyHtaccess($output);

        $output->writeln('<info>... done.</info>');
        $output->writeln('');
    }

    /**
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     */
    protected function tryToCopyHtaccess(OutputInterface $output)
    {
        $output->writeln('  <info>Copying .htaccess.dist to .htaccess ...</info>');

        try {
            $root = $this->getContainer()->get('kernel')->getRootDir().'/../';
            $FileSystem = new Filesystem();
            $FileSystem->copy($root.'.htaccess.dist', $root.'.htaccess');
        } catch (IOException $e) {
            $output->writeln(sprintf('  <comment>%s</comment>', $e->getMessage()));
        }

        $output->writeln('');
    }
}
