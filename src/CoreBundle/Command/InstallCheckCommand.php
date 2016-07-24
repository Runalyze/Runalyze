<?php

namespace Runalyze\Bundle\CoreBundle\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;

class InstallCheckCommand extends ContainerAwareCommand
{
    /** @var string */
    const REQUIRED_PHP_VERSION = '5.5.9';

    /** @var int exit code */
    const CHECK_FAILED = 1;

    /** @var int bit flag for return code */
    const OKAY = 0x00;

    /** @var int bit flag for return code */
    const WARNING = 0x01;

    /** @var int bit flag for return code */
    const ERROR = 0x10;

    /** @var int current return code */
    protected $ReturnCode = 0x00;

    /** @var array */
    protected $Checks = [
        [
            'method' => 'checkPhpVersion',
            'message' => 'Check PHP version'
        ],
        [
            'method' => 'prefixIsNotUsed',
            'message' => 'Check that database prefix is not used',
            'hint' => [
                'There must not exist any tables with the chosen prefix.',
                'Maybe RUNALYZE is already installed.'
            ]
        ],
        [
            'method' => 'directoriesAreWritable',
            'message' => 'Check that directories for cache, log and import are writable'
        ]
    ];

    /** @var array */
    protected $DirectoriesThatMustBeWritable = [
        'data/cache/',
        'data/import/',
        'data/log/',
        'data/sessions/',
        'var/cache/',
        'var/logs/'
    ];

    protected function configure()
    {
        $this
            ->setName('runalyze:install:check')
            ->setDescription('Check requirements for installing RUNALYZE.')
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
        $output->writeln('<info>Check requirements...</info>');
        $output->writeln('');

        foreach ($this->Checks as $check) {
            $returnCode = $this->{$check['method']}();
            $this->ReturnCode |= $returnCode;

            $output->writeln(sprintf('  * %s ... %s', $check['message'], $this->styleReturnCode($returnCode)));

            if ($returnCode != self::OKAY && isset($check['hint'])) {
                $check['hint'] = !is_array($check['hint']) ? [$check['hint']] : $check['hint'];

                foreach ($check['hint'] as $hint) {
                    $output->writeln('    <comment>'.$hint.'</comment>');
                }
            }

            $output->writeln('');
        }

        $output->writeln('  '.$this->getFinalMessage());

        if ($this->ReturnCode == self::ERROR) {
            return self::CHECK_FAILED;
        }
    }

    /**
     * @return string
     */
    protected function getFinalMessage()
    {
        switch ($this->ReturnCode) {
            case self::ERROR:
                return '<error>Not all requirements are fulfilled, installation not possible.</error>';
            case self::WARNING:
                return '<warning>There were some warnings, installation may still be possible.</warning>';
            case self::OKAY:
            default:
                return '<info>All requirements are fulfilled.</info>';
        }
    }

    /**
     * @param int $returnCode
     * @param null|string $message
     * @return string
     */
    protected function styleReturnCode($returnCode, $message = null)
    {
        switch ($returnCode) {
            case self::WARNING:
                $tag = 'warning';
                break;
            case self::ERROR:
                $tag = 'error';
                break;
            case self::OKAY:
            default:
                $tag = 'info';
                $message = $message ? $message : 'ok';
        }

        return '<'.$tag.'>'.($message ? $message : $tag).'</'.$tag.'>';
    }

    /**
     * @return int
     */
    protected function checkPhpVersion()
    {
        if (version_compare(self::REQUIRED_PHP_VERSION, PHP_VERSION) == 1) {
            return self::ERROR;
        }

        return self::OKAY;
    }

    /**
     * @return int
     */
    protected function prefixIsNotUsed()
    {
        /** @var \Doctrine\DBAL\Connection $connection */
        $connection = $this->getContainer()->get('doctrine')->getConnection();
        $prefix = $this->getContainer()->getParameter('database_prefix');

        if (0 !== $connection->query('SHOW TABLES LIKE "'.$prefix.'%"')->rowCount()) {
            return self::ERROR;
        }

        return self::OKAY;
    }

    /**
     * @return int
     */
    protected function directoriesAreWritable()
    {
        $root = $this->getContainer()->getParameter('kernel.root_dir').'/../';

        foreach ($this->DirectoriesThatMustBeWritable as $directory) {
            if (!is_writable($root.$directory)) {
                return self::WARNING;
            }
        }

        return self::OKAY;
    }
}
