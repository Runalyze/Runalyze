<?php

namespace Runalyze\Bundle\CoreBundle\Command;

use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;

class QueueConsumeCommand extends ContainerAwareCommand
{
    /** @var array Array of all available queues has to be maintained manually */
    protected $InternalQueues = [
        'user-backup',
        'poster-generator'
    ];

    /** @var array */
    protected $BernardOptions = [
        'max-runtime',
        'max-messages',
        'stop-when-empty',
        'stop-on-error'
    ];

    protected function configure()
    {
        $this
            ->setName('runalyze:queue:consume')
            ->setDescription('Listen on all available queues')
            ->addOption('max-runtime', null, InputOption::VALUE_OPTIONAL, 'Maximum time in seconds the consumer will run.', null)
            ->addOption('max-messages', null, InputOption::VALUE_OPTIONAL, 'Maximum number of messages that should be consumed.', null)
            ->addOption('stop-when-empty', null, InputOption::VALUE_NONE, 'Stop consumer when queue is empty.', null)
            ->addOption('stop-on-error', null, InputOption::VALUE_NONE, 'Stop consumer when an error occurs.', null)
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
        $arguments = array(
            'command' => 'bernard:consume',
            'queue' => $this->InternalQueues,
        );

        foreach ($this->BernardOptions as $option) {
            if (null !== $input->getOption($option) && false !== $input->getOption($option)) {
               $arguments['--'.$option] = $input->getOption($option);
            }
        }

        return $this->getApplication()->find('bernard:consume')->run(new ArrayInput($arguments), $output);
    }
}
