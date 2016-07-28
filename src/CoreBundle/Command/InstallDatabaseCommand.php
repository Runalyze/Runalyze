<?php

namespace Runalyze\Bundle\CoreBundle\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;

class InstallDatabaseCommand extends ContainerAwareCommand
{
    /** @var string */
    const DATABASE_STRUCTURE_FILE = 'inc/install/structure.sql';

    protected function configure()
    {
        $this
            ->setName('runalyze:install:database')
            ->setDescription('Setup RUNALYZE database.')
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
        $output->writeln('<info>Setup RUNALYZE database...</info>');
        $output->writeln('');
        $output->writeln(sprintf('   <info>Importing %s ...</info>', self::DATABASE_STRUCTURE_FILE));
        $output->writeln('');

        $this->importDatabaseStructure();

        $output->writeln('   <info>Database has been successfully initialized.</info>');
    }

    /**
     * @throws \Exception
     */
    protected function importDatabaseStructure()
    {
        $fileName = $this->getContainer()->getParameter('kernel.root_dir').'/../'.self::DATABASE_STRUCTURE_FILE;
        $queries = $this->getSqlFileAsArray($fileName, $this->getContainer()->getParameter('database_prefix'));

        /** @var \Doctrine\DBAL\Connection $em */
        $em = $this->getContainer()->get('doctrine')->getConnection();
        $em->beginTransaction();

        try {
            foreach ($queries as $query) {
                $em->executeQuery($query);
            }

            $em->commit();
        } catch (\Exception $e) {
            $em->rollBack();

            throw $e;
        }
    }

    /**
     * Import a sql-file
     * @param string $filename
     * @param string $databasePrefix
     * @param bool $removeDelimiter
     * @return array
     */
    public function getSqlFileAsArray($filename, $databasePrefix, $removeDelimiter = true) {
        $MRK = array('DELIMITER', 'USE', 'SET', 'LOCK', 'SHOW', 'DROP', 'GRANT', 'ALTER', 'UNLOCK', 'CREATE', 'INSERT', 'UPDATE', 'DELETE', 'REVOKE', 'REPLACE', 'RENAME', 'TRUNCATE');
        $SQL = @file($filename);
        $query  = '';
        $array = array();
        $inDelimiter = false;

        if (!is_array($SQL)) {
            $SQL = array();
        }

        foreach ($SQL as $line) {
            $line = trim($line);
            $line = str_replace('runalyze_', $databasePrefix, $line);

            if ($inDelimiter) {
                if (mb_substr($line, 0, 9) == 'DELIMITER') {
                    $inDelimiter = false;
                    $query .= $removeDelimiter ? '' : ' '.$line;
                    $array[] = $query;
                } elseif (trim($line) != '//') {
                    $query .= ' '.$line;
                }
            } else {
                $AA = explode(' ', $line);
                if (in_array(strtoupper($AA[0]), $MRK)) {
                    if ($AA[0] == 'DELIMITER') {
                        $inDelimiter = true;
                        $query = $removeDelimiter ? '' : $line;
                    } else {
                        $query = $line;
                    }
                } elseif (strlen($query) > 1) {
                    $query .= " ".$line;
                }

                $x = strlen($query) - 1;
                if (mb_substr($query,$x) == ';') {
                    $array[] = $query;
                    $query = '';
                }
            }
        }

        return $array;
    }
}
