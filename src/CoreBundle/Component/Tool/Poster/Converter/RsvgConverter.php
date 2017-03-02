<?php

namespace Runalyze\Bundle\CoreBundle\Component\Tool\Poster\Converter;

use Symfony\Component\Process\Process;
use Symfony\Component\Filesystem\Filesystem;

class RsvgConverter extends AbstractSvgToPngConverter
{
    /**
     * @param string $rsvgPath absolut path to rsvg[-convert]
     */
    public function __construct($rsvgPath)
    {
        $this->Command = $rsvgPath;
    }

    public function setHeight($height)
    {
        $this->Parameter[] = '-h '.(int)$height;
    }

    public function setWidth($width)
    {
        $this->Parameter[] = '-w '.(int)$width;
    }

    public function callConverter($source, $target)
    {
        if ((new Filesystem())->exists($source)) {
            $builder = new Process($this->Command.' -f png '.implode(' ', $this->Parameter).' '.$source.' -o '.$target);
            $builder->run();

            return true;
        }

        return false;
    }
}
