<?php

namespace Runalyze\Bundle\CoreBundle\Command;

use Runalyze\Bundle\CoreBundle\Component\Activity\ActivityContext;
use Runalyze\Bundle\CoreBundle\Entity\Account;
use Runalyze\Bundle\CoreBundle\Entity\TrainingRepository;
use Runalyze\Bundle\CoreBundle\Services\Import\FileImportResult;
use Runalyze\Parser\Activity\Common\Data\ActivityDataContainer;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
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
     * @return TrainingRepository
     */
    protected function getTrainingRepository()
    {
        return $this->getContainer()->get('doctrine')->getRepository('CoreBundle:Training');
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
        $user = $repository->loadUserByUsername($input->getArgument('username'));

        if (null === $user) {
            $output->writeln('<fg=red>Unknown User</>');

            return 1;
        }

        $token = new UsernamePasswordToken($user, null, 'main', $user->getRoles());
        $this->getContainer()->get('security.token_storage')->setToken($token);

        $importer = $this->getContainer()->get('app.file_importer');
        $dataDirectory = $this->getContainer()->getParameter('data_directory');
        $path = $input->getArgument('path');
        $it = new \FilesystemIterator($path);
        $fs = new Filesystem();

        $files = [];

        foreach ($it as $fileinfo) {
            $file = $fileinfo->getFilename();

            if (!is_file($path.'/'.$file)) {
                continue;
            }

            $filename = 'bulk-import'.uniqid().$file;
            $fs->copy($path.'/'.$file, $dataDirectory.'/import/'.$filename);
            $files[] = $dataDirectory.'/import/'.$filename;
        }

        $importResult = $importer->importFiles($files);
        $importResult->completeAndFilterResults($this->getContainer()->get('app.activity_data_container.filter'));
        $contextAdapterFactory = $this->getContainer()->get('app.activity_context_adapter_factory');
        $defaultLocation = $this->getContainer()->get('app.configuration_manager')->getList()->getActivityForm()->getDefaultLocationForWeatherForecast();

        foreach ($importResult as $result) {
            /** @var $result FileImportResult */
            foreach ($result->getContainer() as $container) {
                $activity = $this->containerToActivity($container, $user);
                $context = new ActivityContext($activity, null, null, $activity->getRoute());
                $contextAdapter = $contextAdapterFactory->getAdapterFor($context);
                $output->writeln('<info>'.$result->getOriginalFileName().'</info>');

                if ($contextAdapter->isPossibleDuplicate()) {
                    $output->writeln('<fg=yellow> ... is a duplicate</>');
                    break;
                }

                $contextAdapter->guessWeatherConditions($defaultLocation);
                $this->getTrainingRepository()->save($activity);
                $output->writeln('<fg=green> ... successfully imported</>');
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

    /**
     * @param ActivityDataContainer $container
     * @param Account $account
     * @return \Runalyze\Bundle\CoreBundle\Entity\Training
     */
    protected function containerToActivity(ActivityDataContainer $container, Account $account)
    {
        return $this->getContainer()->get('app.activity_data_container.converter')->getActivityFor($container, $account);
    }

    private function addFailedFile($fileName, $error)
    {
        $this->FailedImports[$fileName] = $error;
    }
}
