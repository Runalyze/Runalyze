<?php

namespace Runalyze\Bundle\CoreBundle\Component\Tool\Poster;

use Symfony\Component\Filesystem\Filesystem;

class Availability
{
    /** @var string */
    protected $RsvgPath;

    /** @var string */
    protected $InkscapePath;

    /** @var string */
    protected $Python3Path;

    /**
     * @param string $rsvgPath absolute path of rsvg[-convert]
     * @param string $inkscapePath absolute path of inkscape
     * @param string $python3Path absolute path of Python3
     */
    public function __construct($rsvgPath, $inkscapePath, $python3Path)
    {
        $this->RsvgPath = $rsvgPath;
        $this->InkscapePath = $inkscapePath;
        $this->Python3Path = $python3Path;
    }

    /**
     * @return bool
     */
    public function isAvailable()
    {
        return ($this->isPythonAvailable() && $this->isRsvgConverterAvailable());
    }

    /**
     * @return bool
     */
    protected function isInkscapeAvailable()
    {
        return (new Filesystem())->exists($this->InkscapePath);
    }


    /**
     * @return bool
     */
    protected function isRsvgConverterAvailable()
    {
        return (new Filesystem())->exists($this->RsvgPath);
    }

    /**
     * @return bool
     */
    protected function isPythonAvailable()
    {
        return (new Filesystem())->exists($this->Python3Path);
    }
}
