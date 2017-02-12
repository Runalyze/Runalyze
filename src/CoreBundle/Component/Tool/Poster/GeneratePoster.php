<?php

namespace Runalyze\Bundle\CoreBundle\Component\Tool\Poster;

use Runalyze\Bundle\CoreBundle\Entity\TrainingRepository;
use Symfony\Component\Process\Process;
use Symfony\Component\Filesystem\Filesystem;
use Runalyze\Bundle\CoreBundle\Entity\Account;
use Runalyze\Bundle\CoreBundle\Entity\Sport;


/**
 * Class GeneratePoster
 * @package Runalyze\Bundle\CoreBundle\Component\Tool\Poster
 */
class GeneratePoster
{

    protected $parameter= array();

    protected $kernelRootDir;

    protected $python3path;

    /** @var TrainingRepository */
    protected $trainingRepository;

    protected $filename;

    /**
     * GenerateJsonData constructor
     * @param string $kernelRootDir
     * @param string $python3path
     * @param TrainingRepository $trainingRepository
     */
    public function __construct($kernelRootDir, $python3path, TrainingRepository $trainingRepository)
    {
        $this->kernelRootDir = $kernelRootDir;
        $this->python3path = $python3path;
        $this->trainingRepository = $trainingRepository;
    }

    protected function pathToRepository() {
        return $this->kernelRootDir.'/../vendor/runalyze/GpxTrackPoster/';
    }

    protected function pathToSvgDirectory() {
        return $this->kernelRootDir.'/../var/poster/';
    }

    protected function randomFileName($athlete, $year) {
        $this->filename = md5($athlete.$year.strtotime("now")).'.svg';
    }

    public function generate() {
        $builder = new Process($this->python3path.' create_poster.py '.implode(' ', $this->parameter));
        $builder->setWorkingDirectory($this->pathToRepository())->run();
        return $this->pathToSvgDirectory().$this->filename;
    }

    /**
     * @param string $type
     * @param string $jsondir
     * @param int $year
     * @param Account $account
     * @param Sport $sport
     * @param null|string $title
     */
    public function buildCommand($type, $jsondir, $year, Account $account, Sport $sport, $title) {
        $fs = new Filesystem();
        $this->randomFileName($account->getUsername(), $year);
        $this->parameter[] = '--json-dir '.$jsondir;
        $this->parameter[] = '--athlete '.$account->getUsername();
        $this->parameter[] = '--year '.$year;
        $this->parameter[] = '--output '.$this->pathToSvgDirectory().$this->filename;
        $this->parameter[] = '--type '.$type;
        $this->addStatsParameter($account, $sport, $year);
        if ($fs->exists($jsondir.'/special.params')) {
            $this->parameter[] = file_get_contents($jsondir.'/special.params');
        }
        if (!empty($title)) {
            $this->parameter[] = '--title "'.$title.'"';
        }
    }

    /**
     * @param Account $account
     * @param Sport $sport
     * @param int $year
     */
    private function addStatsParameter(Account $account, Sport $sport, $year) {
        $stats = $this->trainingRepository->getStatsForPoster($account, $sport, $year)->getArrayResult();
        $data = $stats[0];
        $this->parameter[] = '--stat-num '.$data['num'];
        $this->parameter[] = '--stat-total '.$data['total_distance'];
        $this->parameter[] = '--stat-min  '.$data['min_distance'];
        $this->parameter[] = '--stat-max  '.$data['max_distance'];
    }

    public function availablePosterTypes() {
        return ['grid', 'calendar', 'circular', 'heatmap'];
    }

    public function deleteSvg() {
        $filesystem = new Filesystem();
        $filesystem->remove($this->pathToSvgDirectory().$this->filename);
    }
}