<?php

namespace Runalyze\Bundle\CoreBundle\Command;

use ImporterFactory;
use Runalyze\Activity\DuplicateFinder;
use Runalyze\Error;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

class ActivityBulkImportCommand extends ContainerAwareCommand
{
    /** @var array */
    protected $FailedImports = array();

    protected function configure()
    {
        $this
            ->setName('runalyze:activity:bulk-import')
            ->setDescription('Bulk import of activity files')
            ->addArgument('username', InputArgument::REQUIRED, 'username')
            ->addArgument('path', InputArgument::REQUIRED, 'Path to files');
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     *
     * @return null|int null or 0 if everything went fine, or an error code
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        Error::$MAX_NUM_OF_ERRORS = 10000;

        $repository = $this->getContainer()->get('doctrine')->getRepository('CoreBundle:Account');
        $user = $repository->loadUserByUsername($input->getArgument('username'));

        if (null === $user) {
            $output->writeln('<fg=red>Unknown User</>');

            return 1;
        }

        $token = new UsernamePasswordToken($user, null, 'main', $user->getRoles());
        $this->getContainer()->get('security.token_storage')->setToken($token);

        new \Frontend(true, $this->getContainer()->get('security.token_storage'));
        $DuplicateFinder = new DuplicateFinder(\DB::getInstance(), $user->getId());

        $path = $input->getArgument('path');
        $it = new \FilesystemIterator($path);

        foreach ($it as $fileinfo) {
            $file = $fileinfo->getFilename();

            if (!is_file($path.'/'.$file)) {
                break;
            }

            $output->writeln('<info>'.$file.'</info>');
            $filename = 'bulk-import'.uniqid().$file;
            copy($path.'/'.$file, DATA_DIRECTORY.'/import/'.$filename);

            try {
                $Factory = new ImporterFactory($filename);
            } catch (\Exception $e) {
                $output->writeln('<fg=red> ... failed</>');
                $this->addFailedFile($file, $e->getMessage());
                break;
            }

            foreach ($Factory->trainingObjects() as $training) {
                try {
                    if (!$DuplicateFinder->checkForDuplicate($training->getActivityid())) {
                        $training->setWeatherForecast();
                        $training->updateAfterParsing();
                        $training->insert();

                        $output->writeln('<fg=green> ... successfully imported</>');
                    } else {
                        $output->writeln('<fg=red> ... is a duplicate</>');
                    }
                } catch (\Exception $e) {
                    $this->addFailedFile($file, $e->getMessage());
                }
            }
        }

        if (!empty($this->FailedImports)) {
            $output->writeln('');
            $output->writeln('<fg=red>Failed imports:</>');

            foreach ($this->FailedImports as $fileName => $message) {
                $output->writeln('<fg=red> - '.$fileName.': '.$message.'</>');
            }
        }

        $output->writeln('');
        $output->writeln('Done.');
    }

    private function addFailedFile($fileName, $error)
    {
        $this->FailedImports[$fileName] = $error;
    }
}
