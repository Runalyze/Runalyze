<?php
/**
 * This file contains class::TSB
 * @package Runalyze\Calculation\Performance
 */

namespace Runalyze\Calculation\Performance;

/**
 * Model for human performance: TSB model
 *
 * @see http://fellrnr.com/wiki/Modeling_Human_Performance#The_TSB_Model
 *
 * @author Hannes Christiansen
 * @package Runalyze\Calculation\Performance
 */
class TSB extends Model
{
	/**
	 * Days for CTL
	 * @var int
	 */
	protected $CTLn;

	/**
	 * Days for ATL
	 * @var int
	 */
	protected $ATLn;

	private $lambdaCTL;
	private $lambdaATL;


	/**
	 * Construct
	 * @param array $trimpData array('days back' => 'trimp value')
	 * @param int $CTLn [optional] days for CTL, default 42
	 * @param int $ATLn [optional] days for ATL, default 7
	 */
	public function __construct(array $trimpData, $CTLn = 42, $ATLn = 7)
	{
		$this->CTLn = $CTLn;
		$this->ATLn = $ATLn;

		$this->lambdaCTL = 2 / ($this->CTLn + 1);
		$this->lambdaATL = 2 / ($this->ATLn + 1);

		parent::__construct($trimpData);
	}

	/**
	 * Calculate
	 */
	protected function calculateArrays()
	{
		for ($i = $this->Range['from']; $i <= $this->Range['to']; ++$i) {
			$T = isset($this->TRIMP[$i]) ? $this->TRIMP[$i] : 0;

			$this->Fitness[$i] = $T * $this->lambdaCTL + (1 - $this->lambdaCTL) * $this->Fitness[$i - 1];
			$this->Fatigue[$i] = $T * $this->lambdaATL + (1 - $this->lambdaATL) * $this->Fatigue[$i - 1];
			$this->Performance[$i] = $this->Fitness[$i] - $this->Fatigue[$i];
		}
	}

	public function restDays($ctl, $atl)
	{
		if ($atl == 0) return 0;
		if ($ctl == 0) $ctl = 1;
		$restDays = log($ctl / $atl) / (log((1 - $this->lambdaATL) / (1 - $this->lambdaCTL)));
		if ($ctl < 15) $restDays = 4 + $restDays / -5; //for very low CTLs we need some compensation as we get very large number of rest days
		return max(0, $restDays);
	}

	public function maxTrimpToBalanced($ctl, $atl)
	{
		return ($atl - $ctl) / ($this->lambdaCTL - $this->lambdaATL);
	}
}
