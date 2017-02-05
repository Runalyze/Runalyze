<?php

namespace Runalyze\Bundle\CoreBundle\Component\Tool\Poster;

use Symfony\Component\Process\ProcessBuilder;
use Symfony\Component\Process\Process;



class GeneratePoster
{

    protected $parameter= array();

    protected $kernelRootDir;

    /**
     * GenerateJsonData constructor
     * @param string $kernelRootDir
     */
    public function __construct($kernelRootDir)
    {
        $this->kernelRootDir = $kernelRootDir;
    }

    protected function pathToRepository() {
        return $this->kernelRootDir.'/../vendor/runalyze/GpxTrackPoster/';
    }

    public function createCommand() {

        var_dump($this->parameter);
        $builder = new Process('venv/bin/python create_poster.py '.implode(' ', $this->parameter));
        $builder->setWorkingDirectory($this->pathToRepository())
                ->run();
        var_dump($builder->getWorkingDirectory());
        var_dump($builder->getCommandLine());
var_dump($builder->getOutput());

        ///echo $process->getOutput();
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