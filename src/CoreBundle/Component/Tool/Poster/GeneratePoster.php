<?php

namespace Runalyze\Bundle\CoreBundle\Component\Tool\Poster;

use Symfony\Component\Process\ProcessBuilder;
use Symfony\Component\Process\Process;



class GeneratePoster
{

    protected $parameter= array();

    protected $kernelRootDir;

    protected $python3path;

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

    public function createCommand() {

        $builder = new Process($this->python3path.' create_poster.py '.implode(' ', $this->parameter));
        $builder->setWorkingDirectory($this->pathToRepository())
                ->run();

    }

    public function buildCommand($type, $jsondir, $year, $athlete) {
        $this->parameter[] = '--json-dir '.$jsondir;
        $this->parameter[] = '--athlete '.$athlete;
        $this->parameter[] = '--year '.$year;


        if (in_array($type, $this->availablePosterTypes())) {
            $this->parameter[] = '--type '.$type;
        }
    }

    public function availablePosterTypes() {
        return ['grid', 'calendar', 'circular', 'heatmap'];
    }
}