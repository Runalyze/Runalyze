<?php

namespace Runalyze\Parser\Activity\Common\Data;

class FitDetails
{
    /** @var float|null [ml/kg/min] */
    public $VO2maxEstimate = null;

    /** @var int|null [min] */
    public $RecoveryTime = null;

    /** @var int|null */
    public $HrvAnalysis = null;

    /** @var float|null [1.0 .. 5.0] */
    public $TrainingEffect = null;

    /** @var int|null */
    public $PerformanceCondition = null;

    /** @var int|null */
    public $PerformanceConditionEnd = null;
}
