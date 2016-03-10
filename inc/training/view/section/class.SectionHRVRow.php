<?php
/**
 * This file contains class::SectionHRVRow
 * @package Runalyze\DataObjects\Training\View\Section
 */

use Runalyze\View\Activity;
use Runalyze\Model\HRV;
use Runalyze\Calculation\HRV\Calculator;

/**
 * Row: HRV
 * 
 * @author Hannes Christiansen
 * @package Runalyze\DataObjects\Training\View\Section
 */
class SectionHRVRow extends TrainingViewSectionRowTabbedPlot {
	/**
	 * Set content
	 */
	protected function setContent() {
		$this->addBoxesForHRVstatistics();
	}

	/**
	 * Set content right
	 */
	protected function setRightContent() {
		if ($this->Context->hrv()->has(HRV\Entity::DATA)) {
			$this->addRightContent('hrv', __('R-R intervals'), new Activity\Plot\HRV($this->Context));
			$this->addRightContent('hrvpoincare', __('Poincaré plot'), new Activity\Plot\HRVPoincare($this->Context));
		}
	}

	/**
	 * Add cadence and power
	 */
	protected function addBoxesForHRVstatistics() {
		$Calculator = new Calculator($this->Context->hrv());
		$Calculator->calculate();

		$boxes = array(
			new BoxedValue(round(log($Calculator->RMSSD()), 1), '', 'lnRMSSD'),
			new BoxedValue(round($Calculator->mean()), 'ms', __('&Oslash; R-R interval')),
			new BoxedValue(round($Calculator->RMSSD()), 'ms', 'RMSSD'),
			new BoxedValue(round($Calculator->SDSD()), 'ms', 'SDSD'),
			new BoxedValue(round($Calculator->SDNN()), 'ms', 'SDNN'),
			new BoxedValue($Calculator->SDANN() > 0 ? round($Calculator->SDANN()) : '-', 'ms', '5 min-SDANN'),
			new BoxedValue(round($Calculator->pNN50()*100, 1), '%', 'pNN50'),
			new BoxedValue(round($Calculator->pNN20()*100, 1), '%', 'pNN20')
		);

		foreach ($boxes as $box) {
			$box->defineAsFloatingBlock('w50');
			$this->BoxedValues[] = $box;
		}
	}
}