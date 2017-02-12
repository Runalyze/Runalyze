<?php

namespace Runalyze\Bundle\CoreBundle\Component\Tool\Poster;

use Symfony\Component\Process\Process;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Class SvgToPngConverter
 * @package Runalyze\Bundle\CoreBundle\Component\Tool\Poster
 */
class SvgToPngConverter
{

    protected $parameter = array();

    /** @var rsvg Path */
    protected $rsvgPath;

    /**
     * SvgToPngConverter constructor.
     * @param $rsvgPath
     */
    public function __construct($rsvgPath)
    {
        $this->rsvgPath = $rsvgPath;
    }

    public function convert($source, $target) {
        $this->callConverter($source, $target);
    }

    /**
     * @param $height
     */
    public function setHeight($height) {
        $this->parameter[] = '-h '.$height;
    }

    /**
     * @param $width
     */
    public function setWidth($width) {
        $this->parameter[] = '-w' .$width;
    }

    /**
     * @param $source
     * @param $target
     * @return bool
     */
    protected function callConverter($source, $target) {

        $fs = new Filesystem();
        if ($fs->exists($source)) {
            $builder = new Process($this->rsvgPath . ' ' . implode(' ', $this->parameter) . ' ' . $source . ' ' . $target);
            $builder->run();
            return true;
        } else {
            return false;
        }
    }



}