<?php

namespace Runalyze\Bundle\CoreBundle\Component\Tool\Poster;

use Symfony\Component\Process\Process;

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

    protected function callConverter($source, $target) {
        $builder = new Process($this->rsvgPath.' '.implode(' ', $this->parameter). ' '.$source.' '.$target);
        $builder->run();
    }



}