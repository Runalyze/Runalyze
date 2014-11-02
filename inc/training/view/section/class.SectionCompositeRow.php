<?php
/**
 * This file contains class::SectionCompositeRow
 * @package Runalyze\DataObjects\Training\View\Section
 */

use Runalyze\Configuration;

/**
 * Row: Heartrate
 * 
 * @author Hannes Christiansen
 * @package Runalyze\DataObjects\Training\View\Section
 */
class SectionCompositeRow extends TrainingViewSectionRowTabbedPlot {
	/**
	 * Set plot
	 */
	protected function setRightContent() {
		$this->addRightContent('plot', __('Composite plot'), $this->getPlot());

		if ($this->Training->hasArrayPace()) {
			$Table = new TableZonesPace($this->Training);
			$Code = $Table->getCode();
			$Code .= HTML::info( __('You\'ll be soon able to configure your own zones.') );

			$this->addRightContent('zones-pace', __('Pace zones'), $Code);
		}

		if ($this->Training->hasArrayHeartrate()) {
			$Table = new TableZonesHeartrate($this->Training);
			$Code = $Table->getCode();
			$Code .= HTML::info( __('You\'ll be soon able to configure your own zones.') );

			$this->addRightContent('zones-hr', __('Heartrate zones'), $Code);
		}
	}

	/**
	 * Get plot
	 * @return \TrainingPlotCollection|\TrainingPlotPacePulse
	 */
	protected function getPlot() {
		if (Configuration::ActivityView()->plotMode()->showCollection()) {
			return new TrainingPlotCollection($this->Training);
		}

		return new TrainingPlotPacePulse($this->Training);
	}

	/**
	 * Set content
	 */
	protected function setContent() {
		$showElevation = Configuration::ActivityView()->plotMode()->showCollection();

		$this->addAveragePace();
		$this->addCalculations();

		$this->addAverageHeartrate();
		$this->addMaximalHeartrate();
		$this->addCaloriesAndTrimp();

		if ($showElevation) {
			$this->addElevation();
		}

		foreach ($this->BoxedValues as &$Value) {
			$Value->defineAsFloatingBlock('w50');
		}

		if ($showElevation) {
			$this->addCourse();
		}

		if ($this->Training->getCurrentlyUsedVdot() > 0) {
			$this->addVdotInfoLink();
		}

		if ($showElevation && $this->Training->hasArrayAltitude()) {
			$this->addElevationInfoLink();
		}
	}

	/**
	 * Add: average pace
	 */
	protected function addAveragePace() {
		if ($this->Training->getDistance() > 0 && $this->Training->getTimeInSeconds() > 0) {
			$this->BoxedValues[] = new BoxedValue($this->Training->getPace(), '/km', __('&oslash; Pace'));
			$this->BoxedValues[] = new BoxedValue($this->Training->DataView()->getKmh(), 'km/h', __('&oslash; Speed'));
		}
	}

	/**
	 * Add: vdot/intensity
	 */
	protected function addCalculations() {
		if ($this->Training->getVdotCorrected() > 0 || $this->Training->getJDintensity() > 0) {
			$this->BoxedValues[] = new BoxedValue(Helper::Unknown($this->Training->getCurrentlyUsedVdot(), '-'), '', __('VDOT'), $this->Training->DataView()->getVDOTicon());
			$this->BoxedValues[] = new BoxedValue(Helper::Unknown($this->Training->getJDintensity(), '-'), '', __('Training points'));
		}
	}

	/**
	 * Add info link
	 */
	protected function addVdotInfoLink() {
		$InfoLink = Ajax::window('<a href="'.$this->Training->Linker()->urlToVDOTInfo().'">'.__('More about VDOT calculation').'</a>', 'small');

		$this->Content = HTML::info( $InfoLink );
	}

	/**
	 * Add: average heartrate
	 */
	protected function addAverageHeartrate() {
		if ($this->Training->getPulseAvg() > 0) {
			$this->BoxedValues[] = new BoxedValue($this->Training->getPulseAvg(), 'bpm', __('&oslash; Heartrate'));
			$this->BoxedValues[] = new BoxedValue(Running::PulseInPercent($this->Training->getPulseAvg()), '&#37;', __('&oslash; Heartrate'));
		}
	}

	/**
	 * Add: average heartrate
	 */
	protected function addMaximalHeartrate() {
		if ($this->Training->getPulseMax() > 0) {
			$this->BoxedValues[] = new BoxedValue($this->Training->getPulseMax(), 'bpm', __('max. Heartrate'));
			$this->BoxedValues[] = new BoxedValue(Running::PulseInPercent($this->Training->getPulseMax()), '&#37;', __('max. Heartrate'));
		}
	}

	/**
	 * Add: calories/trimp
	 */
	protected function addCaloriesAndTrimp() {
		if ($this->Training->getCalories() > 0 || $this->Training->getTrimp() > 0) {
			$this->BoxedValues[] = new BoxedValue($this->Training->getCalories(), 'kcal', __('Calories'));
			$this->BoxedValues[] = new BoxedValue($this->Training->getTrimp(), '', __('TRIMP'));
		}
	}

	/**
	 * Add: elevation
	 */
	protected function addElevation() {
		if ($this->Training->getDistance() > 0) {
			$this->BoxedValues[] = new BoxedValue($this->Training->getDistance(), 'km', __('Distance'));
			$this->BoxedValues[] = new BoxedValue($this->Training->getElevation(), 'm', __('Elevation'));

			// TODO: Calculated elevation?

			if ($this->Training->getElevation() > 0) {
				$this->BoxedValues[] = new BoxedValue(substr($this->Training->DataView()->getGradientInPercent(),0,-6), '&#37;', __('&oslash; Gradient'));
				$this->BoxedValues[] = new BoxedValue(substr($this->Training->DataView()->getElevationUpAndDown(),0,-7), 'm', __('Elevation up/down'));
			}
		}
	}

	/**
	 * Add: course
	 */
	protected function addCourse() {
		if (strlen($this->Training->getRoute()) > 0) {
			$PathBox = new BoxedValue($this->Training->getRoute(), '', __('Course'));
			$PathBox->defineAsFloatingBlock('w100 flexible-height');

			$this->BoxedValues[] = $PathBox;
		}
	}

	/**
	 * Add info link
	 */
	protected function addElevationInfoLink() {
		$InfoLink = Ajax::window('<a href="'.$this->Training->Linker()->urlToElevationInfo().'">'.__('More about elevation').'</a>', 'small');

		$this->Content = HTML::info( $InfoLink );

		if ($this->Training->elevationWasCorrected())
			$this->Content .= HTML::info( __('Elevation data were corrected.') );
		elseif ($this->Training->hasArrayAltitude() && Configuration::ActivityForm()->correctElevation())
			$this->Content .= HTML::warning( __('Elevation data are not corrected.') );

		// TODO: Add link to correct them now!
	}
}