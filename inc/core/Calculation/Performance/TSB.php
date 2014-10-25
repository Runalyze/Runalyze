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
 * @package Runalyze\Calculation\TrainingLoad
 */
class TSB extends Model {
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

	/**
	 * Construct
	 * @param array $trimpData array('days back' => 'trimp value')
	 * @param int $CTLn [optional] days for CTL, default 42
	 * @param int $ATLn [optional] days for ATL, default 7
	 */
	public function __construct(array $trimpData, $CTLn = 42, $ATLn = 7) {
		$this->CTLn = $CTLn;
		$this->ATLn = $ATLn;

		parent::__construct($trimpData);
	}

	/**
	 * Calculate
	 */
	protected function calculateArrays() {
		$lambdaCTL = 2 / ($this->CTLn + 1);
		$lambdaATL = 2 / ($this->ATLn + 1);

		for ($i = $this->Range['from']; $i <= $this->Range['to']; ++$i) {
			$T = isset($this->TRIMP[$i]) ? $this->TRIMP[$i] : 0;

			$this->Fitness[$i] = $T * $lambdaCTL + (1 - $lambdaCTL) * $this->Fitness[$i-1];
			$this->Fatigue[$i] = $T * $lambdaATL + (1 - $lambdaATL) * $this->Fatigue[$i-1];
			$this->Performance[$i] = $this->Fitness[$i] - $this->Fatigue[$i];
		}
	}
}