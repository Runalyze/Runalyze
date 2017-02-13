<?php

namespace Runalyze\Bundle\CoreBundle\Component\Tool\Poster;

use Symfony\Component\Process\Process;
use Symfony\Component\Filesystem\Filesystem;

class SvgToPngConverter
{
    /** @var array */
    protected $Parameter = [];

    /** @var string rsvg path */
    protected $RsvgPath;

    /**
     * @param string $rsvgPath absolut path to rsvg[-convert]
     */
    public function __construct($rsvgPath)
    {
        $this->RsvgPath = $rsvgPath;
    }

    /**
     * @param string $source absolute path to source file
     * @param string $target absolute path to target file
     * @return bool true on success
     */
    public function convert($source, $target)
    {
        return $this->callConverter($source, $target);
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
    protected function callConverter($source, $target)
    {
        if ((new Filesystem())->exists($source)) {
            $builder = new Process($this->RsvgPath.' -f png '.implode(' ', $this->Parameter).' '.$source.' -o '.$target);
            $builder->run();

            return true;
        }

        return false;
    }
}
