<?php

namespace Runalyze\Bundle\CoreBundle\Component\Tool\Poster\Converter;

use Symfony\Component\Process\Process;
use Symfony\Component\Filesystem\Filesystem;
use Runalyze\Bundle\CoreBundle\Component\Tool\Poster\Converter\AbstractSvgToPngConverter;

class InkscapeConverter extends AbstractSvgToPngConverter
{
    /** @var array */
    protected $Parameter = [];

    /** @var string rsvg path */
    protected $Command;

    /**
     * @param string $inkscapePath absolut path to inkscape
     */
    public function __construct($inkscapePath)
    {
        $this->Command = $inkscapePath;
    }

    /**
     * @param int|string $height [px]
     */
    public function setHeight($height)
    {
        $this->Parameter[] = '-h '.(int)$height;
    }

    /**
     * @param int|string $width [px]
     */
    public function setWidth($width)
    {
        $this->Parameter[] = '-w '.(int)$width;
    }

    /**
     * @param string $source absolute path to source file
     * @param string $target absolute path to target file
     * @return bool true on success
     */
    public function callConverter($source, $target)
    {
        if ((new Filesystem())->exists($source)) {
            $builder = new Process($this->Command.' -z -e  '.$target.' '.implode(' ', $this->Parameter).' '.$source);
            $builder->run();

            return true;
        }

        return false;
    }
}
