<?php

namespace Runalyze\Bundle\CoreBundle\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;

class QueueConsumeCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('runalyze:queue:consume')
            ->setDescription('Listen on all available queues')
        ;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return null|int null or 0 if everything went fine, or an error code
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $command = $this->getApplication()->find('bernard:consume');

        $arguments = array(
            'command' => 'bernard:consume',
            //Add here all available queues manually
            'queue' => ['user-backup', 'poster-generator'],
        );

        $consumeInput = new ArrayInput($arguments);
        $returnCode = $command->run($consumeInput, $output);
        return $returnCode;
    }
}
