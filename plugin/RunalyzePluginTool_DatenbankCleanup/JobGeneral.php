<?php
/**
 * This file contains class::JobGeneral
 * @package Runalyze\Plugin\Tool\DatabaseCleanup
 */

namespace Runalyze\Plugin\Tool\DatabaseCleanup;

use Runalyze\Calculation\BasicEndurance;
use Runalyze\Configuration;
use Runalyze\Model\Equipment;

/**
 * JobGeneral
 * 
 * @author Hannes Christiansen
 * @package Runalyze\Plugin\Tool\DatabaseCleanup
 */
class JobGeneral extends Job {
	/**
	 * Task key: internal constants
	 * @var string
	 */
	const INTERNALS = 'internals';

	/**
	 * Task key: equipment statistics
	 * @var string
	 */
	const EQUIPMENT = 'equipment';

	/**
	 * Task key: vdot shape
	 * @var string
	 */
	const VDOT = 'vdot';

	/**
	 * Task key: vdot corrector
	 * @var string
	 */
	const VDOT_CORRECTOR = 'vdot-corrector';

	/**
	 * Task key: basic endurance
	 * @var string
	 */
	const ENDURANCE = 'endurance';

	/**
	 * Task key: maximal trimp values
	 * @var string
	 */
	const MAX_TRIMP = 'trimp';

	/**
	 * Run job
	 */
	public function run() {
		if ($this->isRequested(self::INTERNALS)) {
			$this->recalculateInternalConstants();
		}

		if ($this->isRequested(self::EQUIPMENT)) {
			$this->recalculateEquipmentStatistics();
		}

		if ($this->isRequested(self::VDOT_CORRECTOR)) {
			$this->recalculateVDOTcorrector();
		}

		if ($this->isRequested(self::VDOT)) {
			$this->recalculateVDOTshape();
		}

		if ($this->isRequested(self::ENDURANCE)) {
			$this->recalculateBasicEndurance();
		}

		if ($this->isRequested(self::MAX_TRIMP)) {
			$this->recalculateMaximalPerformanceValues();
		}
	}

	/**
	 * Recalculate internal constants
	 */
	protected function recalculateInternalConstants() {
		\Helper::recalculateStartTime();
		\Helper::recalculateHFmaxAndHFrest();

		$this->addMessage( __('Internal constants have been refreshed.') );
	}

	/**
	 * Recalculate equipment statistics
	 */
	protected function recalculateEquipmentStatistics() {
		$Updater = new Equipment\StatisticsUpdater(\DB::getInstance(), \SessionAccountHandler::getId());
		$num = $Updater->run();

		if ($num === false) {
			$this->addMessage( __('There was a problem while recalculating your equipment statistics') );
		} else {
			$this->addMessage( sprintf( __('Statistics have been recalculated for all <strong>%s</strong> pieces of equipment.'), $num ) );
		}
	}

	/**
	 * Recalculate vdot shape
	 */
	protected function recalculateVDOTshape() {
		$oldValue = Configuration::Data()->vdotShape();
		$newValue = Configuration::Data()->recalculateVDOTshape();

		$this->addSuccessMessage(__('VDOT shape'), number_format($oldValue, 1), number_format($newValue, 1));
	}

	/**
	 * Recalculate vdot corrector
	 */
	protected function recalculateVDOTcorrector() {
		$oldValue = Configuration::Data()->vdotCorrector();
		$newValue = Configuration::Data()->recalculateVDOTcorrector();

		$this->addSuccessMessage(__('Vdot corrector'), number_format($oldValue, 4), number_format($newValue, 4));
	}

	/**
	 * Recalculate basic endurance
	 */
	protected function recalculateBasicEndurance() {
		$oldValue = Configuration::Data()->basicEndurance();
		BasicEndurance::recalculateValue();
		$newValue = Configuration::Data()->basicEndurance();

		$this->addSuccessMessage(__('Basic endurance'), $oldValue, $newValue);
	}

	/**
	 * Recalculate maximal performance values
	 */
	protected function recalculateMaximalPerformanceValues() {
		$Data = Configuration::Data();

		$oldCTL = $Data->maxCTL();
		$oldATL = $Data->maxATL();
		$oldTRIMP = $Data->maxTrimp();

		$Data->recalculateMaxValues();

		$newCTL = $Data->maxCTL();
		$newATL = $Data->maxATL();
		$newTRIMP = $Data->maxTrimp();

		$this->addSuccessMessage(__('Maximal CTL'), $oldCTL, $newCTL);
		$this->addSuccessMessage(__('Maximal ATL'), $oldATL, $newATL);
		$this->addSuccessMessage(__('Maximal TRIMP'), $oldTRIMP, $newTRIMP);
	}
}