<?php
/**
 * This file contains class::Banister
 * @package Runalyze\Calculation\Performance
 */

namespace Runalyze\Calculation\Performance;

/**
 * Model for human performance: Banister model
 * 
 * For a short presentation of this model, see [1].
 * The factors k1 and k2 are initially proposed as 1 and 2 respectively
 * for running by Morton et al. [2] However, they may vary.
 * For swimming e.g. they are 0.062 and 0.128 respectively [3].
 * A table with more factors can be found at [4].
 * 
 * @see [1] http://fellrnr.com/wiki/Modeling_Human_Performance#The_Banister_Model
 * @see [2] http://www.researchgate.net/publication/20910238_Modeling_human_performance_in_running
 * @see [3] http://www.ncbi.nlm.nih.gov/pmc/articles/PMC1974899/
 * @see [4] http://home.trainingpeaks.com/blog/article/the-science-of-the-performance-manager
 * 
 * @author Hannes Christiansen
 * @package Runalyze\Calculation\Performance
 */
class Banister extends Model {
	/**
	 * r1 (adaptive)
	 * @var int
	 */
	protected $r1;

	/**
	 * r2 (fatigue)
	 * @var int
	 */
	protected $r2;

	/**
	 * k1 (adaptive)
	 * @var int
	 */
	protected $k1;

	/**
	 * k2 (fatigue)
	 * @var int
	 */
	protected $k2;

	/**
	 * Construct
	 * @param array $trimpData array('days back' => 'trimp value')
	 * @param int $r1
	 * @param int $r2
	 * @param int $k1
	 * @param int $k2
	 */
	public function __construct(array $trimpData, $r1 = 42, $r2 = 7, $k1 = 1, $k2 = 2) {
		$this->r1 = $r1;
		$this->r2 = $r2;
		$this->k1 = $k1;
		$this->k2 = $k2;

		parent::__construct($trimpData);
	}

	/**
	 * Calculate
	 */
	protected function calculateArrays() {
		for ($i = $this->Range['from']; $i <= $this->Range['to']; ++$i) {
			$T = isset($this->TRIMP[$i]) ? $this->TRIMP[$i] : 0;

			$this->Fitness[$i] = $T + exp(-1 / $this->r1) * $this->Fitness[$i-1];
			$this->Fatigue[$i] = $T + exp(-1 / $this->r2) * $this->Fatigue[$i-1];
			$this->Performance[$i] = $this->Fitness[$i] * $this->k1 - $this->Fatigue[$i] * $this->k2;
		}
	}
}