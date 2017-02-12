<?php

namespace Runalyze\Sports\Performance\Model;

/**
 * Model for human performance: TSB model
 *
 * @see http://fellrnr.com/wiki/Modeling_Human_Performance#The_TSB_Model
 */
class TsbModel extends AbstractModel
{
    /** @var int */
    protected $DaysForCtl;

    /** @var int */
    protected $DaysForAtl;

    /** @var float */
    protected $LambdaCTL;

    /** @var float */
    protected $LambdaATL;

    /**
     * @param array $trimpData array('days back' => 'trimp value')
     * @param int $daysForCtl
     * @param int $daysForAtl
     */
    public function __construct(array $trimpData, $daysForCtl = 42, $daysForAtl = 7)
    {
        $this->DaysForCtl = $daysForCtl;
        $this->DaysForAtl = $daysForAtl;

        $this->LambdaCTL = 2 / ($this->DaysForCtl + 1);
        $this->LambdaATL = 2 / ($this->DaysForAtl + 1);

        parent::__construct($trimpData);
    }

    protected function calculateArrays()
    {
        for ($i = $this->Range['from']; $i <= $this->Range['to']; ++$i) {
            $T = isset($this->Trimp[$i]) ? $this->Trimp[$i] : 0;

            $this->Fitness[$i] = $T * $this->LambdaCTL + (1 - $this->LambdaCTL) * $this->Fitness[$i - 1];
            $this->Fatigue[$i] = $T * $this->LambdaATL + (1 - $this->LambdaATL) * $this->Fatigue[$i - 1];
            $this->Performance[$i] = $this->Fitness[$i] - $this->Fatigue[$i];
        }
    }

    /**
     * @param int|float $ctl
     * @param int|float $atl
     * @return int|float
     */
    public function restDays($ctl, $atl)
    {
        if ($atl == 0 || $this->LambdaATL == $this->LambdaCTL || $this->LambdaCTL == 1) {
            return 0;
        }

        if ($ctl == 0) {
            $ctl = 1;
        }

        $restDays = log($ctl / $atl) / (log((1 - $this->LambdaATL) / (1 - $this->LambdaCTL)));

        if ($ctl < 15) {
            // for very low CTLs we need some compensation as we get very large number of rest days
            $restDays = 4 + $restDays / -5;
        }

        return max(0, $restDays);
    }

    /**
     * @param int|float $ctl
     * @param int|float $atl
     * @return float|int
     */
    public function maxTrimpToBalanced($ctl, $atl)
    {
        if ($this->LambdaCTL == $this->LambdaATL) {
            return 0;
        }

        return ($atl - $ctl) / ($this->LambdaCTL - $this->LambdaATL);
    }
}
