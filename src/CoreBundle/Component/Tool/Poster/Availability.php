<?php

namespace Runalyze\Bundle\CoreBundle\Component\Tool\Poster;

use Symfony\Component\Filesystem\Filesystem;

class Availability
{

    protected $rsvgPath;

    protected $python3path;

    public function __construct($rsvgPath, $python3path)
    {
        $this->rsvgPath = $rsvgPath;
        $this->python3path = $python3path;
    }

    public function isAvailable() {
        if ($this->isPythonAvailable() && $this->isRsvgConverterAvailable()) {
            return true;
        } else {
            return false;
        }
    }

    private function isRsvgConverterAvailable() {
        $rsvg = new Filesystem();
        return $rsvg->exists($this->rsvgPath);

    }

    private function isPythonAvailable() {
        $python = new Filesystem();
        return $python->exists($this->python3path);
    }
}