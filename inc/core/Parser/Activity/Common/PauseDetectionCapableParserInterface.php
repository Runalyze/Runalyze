<?php

namespace Runalyze\Parser\Activity\Common;

interface PauseDetectionCapableParserInterface
{
    /**
     * @param bool $flag
     */
    public function activatePauseDetection($flag = true);
}
