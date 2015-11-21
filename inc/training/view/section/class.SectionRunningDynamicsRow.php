<?php
/**
 * This file contains class::SectionRunningDynamicsRow
 * @package Runalyze\DataObjects\Training\View\Section
 */

use Runalyze\Data\Cadence;
use Runalyze\View\Activity;
use Runalyze\Model\Trackdata;
use Runalyze\Activity\Distance;
use Runalyze\View\Activity\Box;

/**
 * Row: Running dynamics
 * 
 * @author Hannes Christiansen
 * @package Runalyze\DataObjects\Training\View\Section
 */
class SectionRunningDynamicsRow extends TrainingViewSectionRowTabbedPlot {
	/**
	 * Set content
	 */
	protected function setContent() {
		$this->addCadenceAndStrideLength();
		$this->addRunningDynamics();
	}

	/**
	 * Set content right
	 */
	protected function setRightContent() {
		if ($this->Context->trackdata()->has(Trackdata\Object::CADENCE)) {
			if ($this->Context->activity()->sportid() == Runalyze\Configuration::General()->runningSport()) {
				$Cadence = new Cadence\Running();
			} else {
				$Cadence = new Cadence\General();
			}

			$Plot = new Activity\Plot\Cadence($this->Context);
			$this->addRightContent('cadence', $Cadence->label(), $Plot);
		}

		if (
			$this->Context->activity()->sportid() == Runalyze\Configuration::General()->runningSport() &&
			$this->Context->trackdata()->has(Trackdata\Object::TIME) &&
			$this->Context->trackdata()->has(Trackdata\Object::DISTANCE) &&
			$this->Context->trackdata()->has(Trackdata\Object::CADENCE)
		) {
			$Plot = new Activity\Plot\StrideLength($this->Context);
			$this->addRightContent('stridelength', __('Stride length plot'), $Plot);
		}

		if ($this->Context->trackdata()->has(Trackdata\Object::VERTICAL_OSCILLATION)) {
			$Plot = new Activity\Plot\VerticalOscillation($this->Context);
			$this->addRightContent('verticaloscillation', __('Oscillation plot'), $Plot);
		}
		
		if ($this->Context->trackdata()->has(Trackdata\Object::VERTICAL_RATIO)) {
			$Plot = new Activity\Plot\VerticalRatio($this->Context);
			$this->addRightContent('verticalratio', __('Vertical ratio'), $Plot);
		}

		if ($this->Context->trackdata()->has(Trackdata\Object::GROUNDCONTACT)) {
			$Plot = new Activity\Plot\GroundContact($this->Context);
			$this->addRightContent('groundcontact', __('Ground contact plot'), $Plot);
		}
	}

	/**
	 * Set boxed values
	 */
	protected function setBoxedValues() {
	}

	/**
	 * Add cadence and power
	 */
	protected function addCadenceAndStrideLength() {
		if ($this->Context->activity()->cadence() > 0 || $this->Context->activity()->strideLength() > 0) {
			$Cadence = new BoxedValue(Helper::Unknown($this->Context->dataview()->cadence()->value(), '-'), $this->Context->dataview()->cadence()->unitAsString(), $this->Context->dataview()->cadence()->label());
			$Cadence->defineAsFloatingBlock('w50');

			$StrideLength = new Activity\Box\StrideLength($this->Context);
			$StrideLength->defineAsFloatingBlock('w50');

			$this->BoxedValues[] = $Cadence;
			$this->BoxedValues[] = $StrideLength;
		}
	}

	/**
	 * Add running dynamics
	 */
	protected function addRunningDynamics() {
		if ($this->Context->activity()->groundcontact() > 0 || $this->Context->activity()->verticalOscillation() > 0) {
			$Contact = new BoxedValue(Helper::Unknown($this->Context->activity()->groundcontact(), '-'), 'ms', __('Ground contact'));
			$Contact->defineAsFloatingBlock('w50');

			$Oscillation = new BoxedValue(Helper::Unknown(round($this->Context->activity()->verticalOscillation()/10, 1), '-'), 'cm', __('Vertical oscillation'));
			$Oscillation->defineAsFloatingBlock('w50');
			
			$VerticalRatio = new BoxedValue(Helper::Unknown(round($this->Context->activity()->verticalRatio()/100, 1), '-'), '%', __('Vertical Ratio'));
			$VerticalRatio->defineAsFloatingBlock('w50');
			
			$GroundContactBalance = new Box\GroundContactBalance($this->Context);
			$GroundContactBalance->defineAsFloatingBlock('w50');
			
			$this->BoxedValues[] = $Contact;
			$this->BoxedValues[] = $Oscillation;
			$this->BoxedValues[] = $VerticalRatio;
			$this->BoxedValues[] = $GroundContactBalance;
		}
	}
}