<?php

namespace Runalyze\Parser\Activity\Common;

trait PauseDetectionCapableTrait
{
    /** @var bool */
    protected $DetectPauses = true;

    /**
     * @param bool $flag
     */
    public function activatePauseDetection($flag = true)
    {
        $this->DetectPauses = $flag;
    }
}
