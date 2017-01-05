<?php

namespace Runalyze\Bundle\CoreBundle\Command;

use Doctrine\ORM\Query;
use Runalyze\Bundle\CoreBundle\Entity\Account;
use Runalyze\Bundle\CoreBundle\Entity\AccountRepository;
use Runalyze\Bundle\CoreBundle\Entity\TrainingRepository;
use Runalyze\Model;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Filesystem\Filesystem;

class AthletePosterCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('runalyze:athlete:poster-json')
            ->setDescription('Generate json files for svg poster for a given athlete')
            ->addArgument('username', InputArgument::REQUIRED, 'Username of requested athlete')
            ->addArgument('dir', InputArgument::REQUIRED, 'Directory to store json files')
            ->addArgument('year', InputArgument::REQUIRED, 'Only data of this year will be fetched')
        ;
    }

    /**
     * @param string $username
     * @return null|Account
     */
    protected function getAthlete($username)
    {
        /** @var AccountRepository $repository */
        $repository = $this->getContainer()->get('doctrine')->getRepository('CoreBundle:Account');

        return $repository->findByUsername($username);
    }

    /**
     * @return null|int null or 0 if everything went fine, or an error code
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $athlete = $this->getAthlete($input->getArgument('username'));

        if (null !== $athlete) {
            $this->createJsonFilesFor($athlete, $input, $output);

            return null;
        }

        $output->writeln('<error>Unknown User</error>');

        return 1;
    }

    protected function createJsonFilesFor(Account $athlete, InputInterface $input, OutputInterface $output)
    {
        $config = $this->getContainer()->get('app.configuration_manager')->getList($athlete);
        $sportid = $config->getGeneral()->getRunningSport();
        $filesystem = new Filesystem();
        $counter = 0;

        /** @var TrainingRepository $trainingRepository */
        $trainingRepository = $this->getContainer()->get('doctrine')->getRepository('CoreBundle:Training');
        $query = $trainingRepository->getQueryForJsonPosterData($athlete, $sportid, $input->getArgument('year'));
        $result = $query->iterate(null, Query::HYDRATE_SCALAR);

        while ($data = $result->next()) {
            $data = $data[0];
            $json = [
                'start' => date('Y-m-d H:i:s', $data['time']),
                'end' => date('Y-m-d H:i:s', $data['time'] + $data['s']),
                'length' => 1000.0 * (float)$data['distance'],
                'segments' => $this->getSegmentsFor($data['geohashes'], $data['distance'])
            ];

            if (!empty($json['segments'])) {
                $filesystem->dumpFile($input->getArgument('dir').'/'.date('Y-m-d-His', $data['time']).'.json', json_encode($json));
                $counter++;
            }
        }

        $output->writeln(sprintf('%u json files have been saved.', $counter));
    }

    /**
     * @param string $geohashLine
     * @param float $distance
     * @return array
     */
    protected function getSegmentsFor($geohashLine, $distance)
    {
        $loop = new Model\Route\Loop(new Model\Route\Entity([Model\Route\Entity::GEOHASHES => $geohashLine]));
        $loop->setStepSize(5);
        $pauseLimit = 50 * 5 * $distance / $loop->num();
        $currentSegment = 0;
        $segments = [];
        $segments[] = [];

        while ($loop->nextStep()) {
            if ($loop->geohash() != '7zzzzzzzzzzz') {
                $coordinate = $loop->coordinate();
                $segments[$currentSegment][] = [
                    'lat' => (float)$coordinate->getLatitude(),
                    'lng' => (float)$coordinate->getLongitude()
                ];

                if ($loop->calculatedStepDistance() > $pauseLimit) {
                    $segments[] = [];
                    $currentSegment++;
                }
            }
        }

        return $segments;
    }
}
