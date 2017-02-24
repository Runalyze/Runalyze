<?php

namespace Runalyze\Bundle\CoreBundle\Component\Tool\Poster;

use Runalyze\Bundle\CoreBundle\Entity\TrainingRepository;
use Symfony\Component\Process\Process;
use Symfony\Component\Filesystem\Filesystem;
use Runalyze\Bundle\CoreBundle\Entity\Account;
use Runalyze\Bundle\CoreBundle\Entity\Sport;
use Symfony\Component\Finder\Finder;
class GeneratePoster
{
    /** @var array */
    protected $Parameter = [];

    /** @var string */
    protected $KernelRootDir;

    /** @var string */
    protected $Python3path;

    /** @var TrainingRepository */
    protected $TrainingRepository;

    /** @var string */
    protected $Filename;

    /**
     * @param string $kernelRootDir
     * @param string $python3Path
     * @param TrainingRepository $trainingRepository
     */
    public function __construct($kernelRootDir, $python3Path, TrainingRepository $trainingRepository)
    {
        $this->KernelRootDir = $kernelRootDir;
        $this->Python3Path = $python3Path;
        $this->TrainingRepository = $trainingRepository;
    }

    /**
     * @return string
     */
    protected function pathToRepository()
    {
        return $this->KernelRootDir.'/../vendor/runalyze/GpxTrackPoster/';
    }

    /**
     * @return string
     */
    protected function pathToSvgDirectory()
    {
        return $this->KernelRootDir.'/../var/poster/';
    }

    /**
     * @param string $athlete
     * @param string $year
     */
    protected function generateRandomFileName($athlete, $year)
    {
        $this->Filename = md5($athlete.$year.strtotime("now")).'.svg';
    }

    /**
     * @return string path to generated file
     */
    public function generate()
    {
        $builder = new Process($this->Python3Path.' create_poster.py '.implode(' ', $this->Parameter));
        $builder->setWorkingDirectory($this->pathToRepository())->run();

        return $this->pathToSvgDirectory().$this->Filename;
    }

    /**
     * @param string $type
     * @param string $jsonDir
     * @param int $year
     * @param Account $account
     * @param Sport $sport
     * @param null|string $title
     */
    public function buildCommand($type, $jsonDir, $year, Account $account, Sport $sport, $title)
    {
        $this->generateRandomFileName($account->getUsername(), $year);

        $this->Parameter[] = '--json-dir '.$jsonDir;
        $this->Parameter[] = '--athlete '.$account->getUsername();
        $this->Parameter[] = '--year '.(int)$year;
        $this->Parameter[] = '--output '.$this->pathToSvgDirectory().$this->Filename;
        $this->Parameter[] = '--type '.$type;
        $this->Parameter[] = '--title '.escapeshellarg($title);

        $this->addStatsParameter($account, $sport, $year);

        if ((new Filesystem())->exists($jsonDir.'/special.params')) {
            $this->Parameter[] = file_get_contents($jsonDir.'/special.params');
        }

    }

    /**
     * @param Account $account
     * @param Sport $sport
     * @param int $year
     */
    private function addStatsParameter(Account $account, Sport $sport, $year)
    {
        $stats = $this->TrainingRepository->getStatsForPoster($account, $sport, $year)->getArrayResult();
        $data = $stats[0];

        $this->Parameter[] = '--stat-num '.(int)$data['num'];
        $this->Parameter[] = '--stat-total '.(float)$data['total_distance'];
        $this->Parameter[] = '--stat-min '.(float)$data['min_distance'];
        $this->Parameter[] = '--stat-max '.(float)$data['max_distance'];
    }

    /**
     * @return array
     */
    public function availablePosterTypes()
    {
        return ['grid', 'calendar', 'circular', 'heatmap'];
    }

    public function deleteSvg()
    {
        $filesystem = new Filesystem();
        $filesystem->remove($this->pathToSvgDirectory().$this->Filename);
    }
}
