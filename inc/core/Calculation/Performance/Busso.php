<?php
/**
 * This file contains class::Busso
 * @package Runalyze\Calculation\Performance
 */

namespace Runalyze\Calculation\Performance;

/**
 * Model for human performance: Busso model
 * 
 * For a short presentation of this model, see [1].
 * 
 * @see [1] http://fellrnr.com/wiki/Modeling_Human_Performance#The_Busso_Model
 * @see [2] http://journals.lww.com/acsm-msse/pages/articleviewer.aspx?year=2003&issue=07000&article=00018&type=abstract
 * 
 * @author Hannes Christiansen
 * @package Runalyze\Calculation\Performance
 */
class Busso extends Model {
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
	 * r3 (for k3)
	 * @var int
	 */
	protected $r3;

	/**
	 * k1 (adaptive)
	 * @var int
	 */
	protected $k1;

	/**
	 * k3 (fatigue)
	 * @var int
	 */
	protected $k3;

	/**
	 * Construct
	 * @param array $trimpData array('days back' => 'trimp value')
	 * @param int $r1
	 * @param int $r2
	 * @param int $r3
	 * @param int $k1
	 * @param int $k3
	 */
	public function __construct(array $trimpData, $r1 = 42, $r2 = 7, $r3 = 4, $k1 = 1, $k3 = 2) {
		$this->r1 = $r1;
		$this->r2 = $r2;
		$this->r3 = $r3;
		$this->k1 = $k1;
		$this->k3 = $k3;

		parent::__construct($trimpData);
	}

	/**
	 * Calculate
	 */
	protected function calculateArrays() {
		$k2 = 0;

		for ($i = $this->Range['from']; $i <= $this->Range['to']; ++$i) {
			$T = isset($this->TRIMP[$i]) ? $this->TRIMP[$i] : 0;

			$k2 = $T + exp(-1 / $this->r3) * $k2;

			$this->Fitness[$i] = $T + exp(-1 / $this->r1) * $this->Fitness[$i-1];
			$this->Fatigue[$i] = $T + exp(-1 / $this->r2) * $this->Fatigue[$i-1];
			$this->Performance[$i] = $this->Fitness[$i] * $this->k1 - $this->Fatigue[$i] * $this->k3 * $k2;
		}
	}
}