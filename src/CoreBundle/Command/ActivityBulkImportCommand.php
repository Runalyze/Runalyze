<?php

namespace Runalyze\Bundle\CoreBundle\Command;

use Runalyze\Error;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use ImporterFactory;
use Runalyze\Activity\DuplicateFinder;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

class ActivityBulkImportCommand extends ContainerAwareCommand
{
    protected $failedImports = array();
    protected function configure()
    {
        $this
            ->setName('runalyze:activity:bulk-import')
            ->setDescription('Bulk import of activity files')
            ->addArgument('username', InputArgument::REQUIRED, 'username')
            ->addArgument('path', InputArgument::REQUIRED, 'Path to files')
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
        $repository = $this->getContainer()->get('doctrine')->getRepository('CoreBundle:Account');

        Error::$MAX_NUM_OF_ERRORS = 10000;

        /** @var Account|null $account */
        $user = $repository->loadUserByUsername($input->getArgument('username'));
        if (NULL === $user) {
            $output->writeln('<fg=red>Unknown User</>');
            return;
        }

        $token = new UsernamePasswordToken(
            $user,
            null,
            'main',
            $user->getRoles());
        $this->getContainer()->get('security.token_storage')->setToken($token);
        $path = $input->getArgument('path');
        new \Frontend(true, $this->getContainer()->get('security.token_storage'));
        $DuplicateFinder = new DuplicateFinder(\DB::getInstance(), $user->getId());

        $it = new \FilesystemIterator($path);
        foreach ($it as $fileinfo) {
            $file = $fileinfo->getFilename();
            if (! is_file($path.'/'.$file))
                break;

            $output->writeln('<info>File: '.$file.'</info>');
            $filename = 'bulk-import'.uniqid().$file;
            copy($path.'/'.$file, FRONTEND_PATH.'../data/import/'.$filename);

            try {
                $Factory = new ImporterFactory($filename);
            } catch(\Exception $e) {
                $output->writeln('<fg=red>Failed</>');
                $this->addFailedFile($file, $e->getMessage());
                break;
            }

            foreach ($Factory->trainingObjects() as $training) {
                try {
                    if (! $DuplicateFinder->checkForDuplicate($training->getActivityid())) {
                                $training->setWeatherForecast();
                                $training->insert();
                                $output->writeln('<fg=green>Successfully imported</>');
                    } else {
                                $output->writeln('<fg=red>' . $file . ' is a duplicate</>');
                    }

                } catch (\Exception $e) {
                    $this->addFailedFile($file, $e->getMessage());
                    $output->writeln('<fg=red>'.$e->getMessage().' </>');
                }
            }
        }

        if (!empty($this->failedImports)) {
            $output->writeln('<fg=red>Failed imports</>');
            foreach ($this->failedImports as $fileName => $message) {
                $output->writeln('<info>'.$fileName.' - '.$message.'</info>');
            }
        }

        $output->writeln('Done');
    }

    private function addFailedFile($fileName, $error) {
        $this->failedImports[$fileName] = $error;
    }

}
