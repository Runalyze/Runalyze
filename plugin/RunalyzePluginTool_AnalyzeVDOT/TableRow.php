<?php
/**
 * This file contains class::TableRow
 * @package Runalyze\Plugin\Tool\AnalyzeVDOT
 */

namespace Runalyze\Plugin\Tool\AnalyzeVDOT;

use Runalyze\Model\Activity;
use Runalyze\Calculation\JD\VDOT;
use Runalyze\Calculation\JD\Shape;
use Runalyze\Calculation\JD\VDOTCorrector;
use Runalyze\Configuration;
use Runalyze\Activity\Distance;
use Runalyze\Activity\Duration;

use RunningPrognosisDaniels;
use HTML;
use DB;
use SessionAccountHandler;

/**
 * Job
 * 
 * @author Hannes Christiansen
 * @package Runalyze\Plugin\Tool\AnalyzeVDOT
 */
class TableRow {
	/**
	 * Data
	 * @var array
	 */
	protected $Data;

	/**
	 * Activity
	 * @var \Runalyze\Model\Activity\Object
	 */
	protected $Activity;

	/**
	 * VDOT shape at that time
	 * @var float
	 */
	protected $Shape;

	/**
	 * Constructor
	 * @param array $data
	 */
	public function __construct(array $data) {
		$this->Data = $data;
		$this->Activity = new Activity\Object($data);
		$this->Shape = $this->loadShape($data['time']);

		VDOT::setPrecision(2);
		VDOTCorrector::setGlobalFactor( Configuration::Data()->vdotFactor() );
	}

	/**
	 * Load shape
	 * @param int $time
	 * @return float
	 */
	private function loadShape($time) {
		$Shape = new Shape(
			DB::getInstance(),
			SessionAccountHandler::getId(),
			Configuration::General()->runningSport(),
			Configuration::Vdot()
		);
		$Shape->calculateAt($time);

		return $Shape->value();
	}

	/**
	 * Date
	 * @return string
	 */
	public function date() {
		return date('d.m.Y', $this->Activity->timestamp());
	}

	/**
	 * Name
	 * @return string
	 */
	public function name() {
		$comment = $this->Activity->comment();

		if ($comment) {
			return $comment;
		}

		return __('noname');
	}

	/**
	 * Distance
	 * @return string
	 */
	public function distance() {
		return Distance::format($this->Activity->distance());
	}

	/**
	 * Duration
	 * @return string
	 */
	public function duration() {
		return $this->formatTime( $this->Activity->duration() );
	}

	/**
	 * VDOT by time
	 * @return string
	 */
	public function vdotByTime() {
		return $this->formatVDOT( $this->Activity->vdotByTime() );
	}

	/**
	 * Heart rate in bpm
	 * @return int
	 */
	public function bpm() {
		return $this->Activity->hrAvg();
	}

	/**
	 * VDOT by heart rate
	 * @return string
	 */
	public function vdotByHR() {
		return $this->formatVDOT( $this->Activity->vdotByHeartRate() );
	}

	/**
	 * Prognosis time by heart rate
	 * @return string
	 */
	public function prognosisByHR() {
		$Prognosis = new RunningPrognosisDaniels();
		$Prognosis->adjustVDOT(false);
		$Prognosis->setVDOT($this->Activity->vdotByHeartRate());

		return $this->formatTime( $Prognosis->inSeconds($this->Activity->distance()) );
	}

	/**
	 * VDOT by heart rate and correction
	 * @return string
	 */
	public function vdotByHRafterCorrection() {
		if (Configuration::Vdot()->useCorrectionFactor()) {
			return $this->formatVDOT( Configuration::Data()->vdotFactor() * $this->Activity->vdotByHeartRate() );
		}

		return '-';
	}

	/**
	 * Prognosis time by heart rate after correction
	 * @return string
	 */
	public function prognosisByHRafterCorrection() {
		if (Configuration::Vdot()->useCorrectionFactor()) {
			$Prognosis = new RunningPrognosisDaniels();
			$Prognosis->adjustVDOT(false);
			$Prognosis->setVDOT(Configuration::Data()->vdotFactor() * $this->Activity->vdotByHeartRate());

			return $this->formatTime( $Prognosis->inSeconds($this->Activity->distance()) );
		}

		return '-';
	}

	/**
	 * VDOT by shape
	 * @return string
	 */
	public function vdotByShape() {
		return $this->formatVDOT( $this->Shape );
	}

	/**
	 * Prognosis time by shape in seconds
	 * @return int
	 */
	public function prognosisByShapeInSeconds() {
		$Prognosis = new RunningPrognosisDaniels();
		$Prognosis->adjustVDOT(false);
		$Prognosis->setVDOT($this->Shape);

		return $Prognosis->inSeconds($this->Activity->distance());
	}

	/**
	 * Prognosis time by shape
	 * @return string
	 */
	public function prognosisByShape() {
		return $this->formatTime( $this->prognosisByShapeInSeconds() );
	}

	/**
	 * Shape deviation in percent
	 * @return string
	 */
	public function shapeDeviation() {
		$Difference = 100 * ($this->prognosisByShapeInSeconds() - $this->Activity->duration()) / $this->Activity->duration();

		return HTML::plusMinus(sprintf("%01.2f", $Difference), 2).' &#37;';
	}

	/**
	 * Correction factor
	 * @return string
	 */
	public function correctionFactor() {
		$Corrector = new VDOTCorrector();
		$Corrector->fromActivity($this->Activity);

		return sprintf("%1.4f", $Corrector->factor());
	}

	/**
	 * Format VDOT value for output
	 * @param float $vdot
	 * @return float
	 */
	protected function formatVDOT($vdot) {
		return number_format($vdot, 2);
	}

	/**
	 * Format time for output
	 * @param int $seconds
	 * @return string
	 */
	protected function formatTime($seconds) {
		$Duration = new Duration($seconds);

		return $Duration->string(Duration::FORMAT_WITH_HOURS);
	}
}