<?php

namespace Runalyze\Bundle\CoreBundle\Command;

use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;

class InstallCommand extends ContainerAwareCommand
{
    /** @var \Symfony\Bundle\FrameworkBundle\Console\Application */
    protected $Application;

    /** @var array */
    protected $Commands = [
        [
            'command' => 'database',
            'message' => 'Setting up the database'
        ],
        [
            'command' => 'check',
            'message' => 'Check requirements'
        ]
    ];

    protected function configure()
    {
        $this
            ->setName('runalyze:install')
            ->setDescription('Install RUNALYZE and setup database.')
        ;
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     */
    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->Application = $this->getApplication();
        $this->Application->setCatchExceptions(false);
        $this->Application->setAutoExit(false);
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     *
     * @return null|int null or 0 if everything went fine, or an error code
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('<info>Installing RUNALYZE...</info>');
        $output->writeln('');

        foreach ($this->Commands as $step => $command) {
            $output->writeln(sprintf('<comment>Step %d of %d.</comment> <info>%s</info>', $step + 1, count($this->Commands), $command['message']));

            $subInput = new ArrayInput(['command' => 'runalyze:install:'.$command['command']]);
            $exitCode = $this->Application->run($subInput, $output);
            $output->writeln('');

            if ($exitCode > 0) {
                return $exitCode;
            }
        }

        $output->writeln('<info>RUNALYZE has been successfully installed.</info>');

        return 0;
    }
}
