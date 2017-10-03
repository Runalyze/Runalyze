<?php

namespace Runalyze\Bundle\CoreBundle\Command;

use Runalyze\Bundle\CoreBundle\Entity\Account;
use Runalyze\Bundle\CoreBundle\Entity\Training;
use Runalyze\Bundle\CoreBundle\Entity\TrainingRepository;
use Runalyze\Util\LocalTime;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use ImporterFactory;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

class ActivityMergeCommand extends ContainerAwareCommand
{
    protected $failedImports = array();

    protected function configure()
    {
        $this
            ->setName('runalyze:activity:merge')
            ->setDescription('Bulk import of activity files')
            ->addArgument('username', InputArgument::REQUIRED, 'Username')
            ->addArgument('file', InputArgument::REQUIRED, 'File to import')
            ->addArgument('id', InputArgument::OPTIONAL, 'Activity id')
            ->addOption('sport', 's', InputOption::VALUE_OPTIONAL, 'Sport id')
        ;
    }

    protected function setTokenFrom(Account $account)
    {
        $this->getContainer()->get('security.token_storage')->setToken(
            new UsernamePasswordToken($account, null, 'main', $account->getRoles())
        );
    }

    /**
     * @param OutputInterface $output
     * @param string $message
     * @return int
     */
    protected function fail(OutputInterface $output, $message)
    {
        $output->writeln(sprintf('<error>%s</error>', $message));

        return 1;
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     *
     * @return null|int null or 0 if everything went fine, or an error code
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $file = $input->getArgument('file');

        if (!is_file($file)) {
            return $this->fail($output, 'File cannot be found.');
        }

        $doctrine = $this->getContainer()->get('doctrine');
        /** @var Account|null $account */
        $account = $doctrine->getRepository('CoreBundle:Account')->findByUsername($input->getArgument('username'));

        if (null === $account) {
            return $this->fail($output, 'Unknown account');
        }

        $this->setTokenFrom($account);

        new \Frontend(true, $this->getContainer()->get('security.token_storage'));

        $fileName = (new \SplFileInfo($file))->getFilename();

        if (false === copy($file, DATA_DIRECTORY.'/import/'.$fileName)) {
            return $this->fail($output, sprintf('Can\'t copy "%s" to "%s".', $file, DATA_DIRECTORY.'/import/'.$fileName));
        }

        $factory = new ImporterFactory($fileName);

        if (!empty($factory->getErrors())) {
            return $this->fail($output, 'There were errors: '.implode(', ', $factory->getErrors()));
        }

        if (1 != count($factory->trainingObjects())) {
            return $this->fail($output, 'File must contain exactly one activity.');
        }

        /** @var \TrainingObject $newActivity */
        $newActivity = $factory->trainingObjects()[0];
        /** @var TrainingRepository $trainingRepository */
        $trainingRepository = $doctrine->getRepository('CoreBundle:Training');
        /** @var Training|null $activity */
        $activity = null;

        if (null !== $input->getArgument('id')) {
            $activity = $trainingRepository->find($input->getArgument('id'));
        } else {
            $queryBuilder = $trainingRepository->createQueryBuilder('t')
                ->where('t.account = :account')
                ->andWhere('t.time BETWEEN :timeStart and :timeEnd')
                ->setParameters([
                    ':account' => $account->getId(),
                    ':timeStart' => strtotime(date('Y-m-d', $newActivity->getTimestamp()).'T00:00:00Z'),
                    ':timeEnd' => strtotime(date('Y-m-d', $newActivity->getTimestamp()).'T23:59:59Z')
                ]);

            if (null !== $input->getOption('sport')) {
                $queryBuilder->andWhere('t.sport = :sport')->setParameter('sport', $input->getOption('sport'));
            }

            $activities = $queryBuilder->getQuery()->getResult();

            if (count($activities) === 0) {
                return $this->fail($output, sprintf('No activity found for %s.', date('d.m.Y', $newActivity->getTimestamp())));
            } elseif (count($activities) > 1) {
                return $this->fail($output, sprintf("Multiple activities found for %s:\n\t%s", date('d.m.Y', $newActivity->getTimestamp()),
                    implode("\n\t", array_map(function(Training $activity){
                        return sprintf('#%u (%s: %3.1f km, %s)',
                            $activity->getId(), LocalTime::date('H:i', $activity->getTime()), $activity->getDistance(), $activity->getSport()->getName()
                        );
                    }, $activities))
                ));
            }

            $activity = $activities[0];
        }

        if (null === $activity) {
            return $this->fail($output, 'Unknown activity.');
        }

        if (null !== $doctrine->getRepository('CoreBundle:Trackdata')->findByActivity($activity->getId())) {
            return $this->fail($output, 'Activity has already a trackdata object.');
        }

        if (null !== $activity->getRoute() && $activity->getRoute()->hasGeohashes()) {
            return $this->fail($output, 'Activity has already a route object with gps data.');
        }

        if (LocalTime::date('d.m.Y', $newActivity->getTimestamp()) != LocalTime::date('d.m.Y', $activity->getTime())) {
            return $this->fail($output, sprintf('Dates do not match. Activity with ID = %u was %s, given file was on %s.',
                $activity->getId(), LocalTime::date('d.m.Y', $activity->getTime()), LocalTime::date('d.m.Y', $newActivity->getTimestamp())
            ));
        }

        $newActivity->updateAfterParsing();
        $newActivity->mergeInto((new \Runalyze\Model\Factory($account->getId()))->activity($activity->getId()), $account->getId());

        $output->writeln('Done.');

        return null;
    }
}
