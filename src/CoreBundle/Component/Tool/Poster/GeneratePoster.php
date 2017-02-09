<?php

namespace Runalyze\Bundle\CoreBundle\Component\Tool\Poster;

use Symfony\Component\Process\ProcessBuilder;
use Symfony\Component\Process\Process;

/**
 * Class GeneratePoster
 * @package Runalyze\Bundle\CoreBundle\Component\Tool\Poster
 */
class GeneratePoster
{

    protected $parameter= array();

    protected $kernelRootDir;

    protected $python3path;

    protected $filename;

    /**
     * GenerateJsonData constructor
     * @param string $kernelRootDir
     * @param string $python3path
     */
    public function __construct($kernelRootDir, $python3path)
    {
        $this->kernelRootDir = $kernelRootDir;
        $this->python3path = $python3path;
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
        $builder->setWorkingDirectory($this->pathToRepository())
                ->run();
        return $this->pathToSvgDirectory().$this->filename;

    }

    public function buildCommand($type, $jsondir, $year, $athlete) {
        $this->randomFileName($athlete, $year);
        $this->parameter[] = '--json-dir '.$jsondir;
        $this->parameter[] = '--athlete '.$athlete;
        $this->parameter[] = '--year '.$year;
        $this->parameter[] = '--output '.$this->pathToSvgDirectory().$this->filename;
        $this->parameter[] = '--type '.$type;
    }

    public function availablePosterTypes() {
        return ['grid', 'calendar', 'circular', 'heatmap'];
    }
}