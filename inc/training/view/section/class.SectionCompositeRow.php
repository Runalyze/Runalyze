<?php
/**
 * This file contains class::SectionCompositeRow
 * @package Runalyze\DataObjects\Training\View\Section
 */

use Runalyze\Configuration;
use Runalyze\View\Activity;

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

		if ($this->Context->trackdata()->has(\Runalyze\Model\Trackdata\Object::PACE)) {
			$Table = new TableZonesPace($this->Context);
			$Code = $Table->getCode();
			$Code .= HTML::info( __('You\'ll be soon able to configure your own zones.') );

			$this->addRightContent('zones-pace', __('Pace zones'), $Code);
		}

		if ($this->Context->trackdata()->has(\Runalyze\Model\Trackdata\Object::HEARTRATE)) {
			$Table = new TableZonesHeartrate($this->Context);
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
			return new Activity\Plot\PaceAndHeartrateAndElevation($this->Context);
		}

		return new Activity\Plot\PaceAndHeartrate($this->Context);
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

		$this->addVdotInfoLink();

		if ($showElevation && $this->Context->hasRoute() && $this->Context->route()->hasElevations()) {
			$this->addElevationInfoLink();
		}
	}

	/**
	 * Add: average pace
	 */
	protected function addAveragePace() {
		if ($this->Context->activity()->distance() > 0 && $this->Context->activity()->duration() > 0) {
			$this->BoxedValues[] = new BoxedValue($this->Context->dataview()->pace()->asMinPerKm(), '/km', __('&oslash; Pace'));
			$this->BoxedValues[] = new BoxedValue($this->Context->dataview()->pace()->asKmPerHour(), 'km/h', __('&oslash; Speed'));
		}
	}

	/**
	 * Add: vdot/intensity
	 */
	protected function addCalculations() {
		if ($this->Context->dataview()->vdot()->value() > 0 || $this->Context->activity()->jdIntensity() > 0) {
			$this->BoxedValues[] = new BoxedValue(Helper::Unknown($this->Context->dataview()->vdot()->value(), '-'), '', __('VDOT'), $this->Context->dataview()->vdotIcon());
			$this->BoxedValues[] = new BoxedValue(Helper::Unknown($this->Context->activity()->jdIntensity(), '-'), '', __('Training points'));
		}
	}

	/**
	 * Add info link
	 */
	protected function addVdotInfoLink() {
		if ($this->Context->dataview()->vdot()->value() > 0) {
			$Linker = new Activity\Linker($this->Context->activity());

			$InfoLink = Ajax::window('<a href="'.$Linker->urlToVDOTInfo().'">'.__('More about VDOT calculation').'</a>', 'small');

			$this->Content .= HTML::info( $InfoLink );
		}
	}

	/**
	 * Add: average heartrate
	 */
	protected function addAverageHeartrate() {
		if ($this->Context->activity()->hrAvg() > 0) {
			$this->BoxedValues[] = new BoxedValue($this->Context->dataview()->hrAvg()->inBPM(), 'bpm', __('&oslash; Heartrate'));

			if ($this->Context->dataview()->hrMax()->canShowInHRmax()) {
				$this->BoxedValues[] = new BoxedValue($this->Context->dataview()->hrAvg()->inPercent(), '&#37;', __('&oslash; Heartrate'));
			}
		}
	}

	/**
	 * Add: average heartrate
	 */
	protected function addMaximalHeartrate() {
		if ($this->Context->activity()->hrMax() > 0) {
			$this->BoxedValues[] = new BoxedValue($this->Context->dataview()->hrMax()->inBPM(), 'bpm', __('max. Heartrate'));

			if ($this->Context->dataview()->hrMax()->canShowInHRmax()) {
				$this->BoxedValues[] = new BoxedValue($this->Context->dataview()->hrMax()->inPercent(), '&#37;', __('max. Heartrate'));
			}
		}
	}

	/**
	 * Add: calories/trimp
	 */
	protected function addCaloriesAndTrimp() {
		if ($this->Context->activity()->calories() > 0 || $this->Context->activity()->trimp() > 0) {
			$this->BoxedValues[] = new BoxedValue($this->Context->activity()->calories(), 'kcal', __('Calories'));
			$this->BoxedValues[] = new BoxedValue($this->Context->activity()->trimp(), '', __('TRIMP'));
		}
	}

	/**
	 * Add: elevation
	 */
	protected function addElevation() {
		if ($this->Context->activity()->distance() > 0) {
			$this->BoxedValues[] = new BoxedValue($this->Context->activity()->distance(), 'km', __('Distance'));
			$this->BoxedValues[] = new BoxedValue($this->Context->activity()->elevation(), 'm', __('Elevation'));

			// TODO: Calculated elevation?

			if ($this->Context->activity()->elevation() > 0) {
				$this->BoxedValues[] = new BoxedValue(substr($this->Context->dataview()->gradientInPercent(),0,-11), '&#37;', __('&oslash; Gradient'));
				$this->BoxedValues[] = new BoxedValue('+'.$this->Context->route()->elevationUp().'/-'.$this->Context->route()->elevationDown(), 'm', __('Elevation up/down'));
			}
		}
	}

	/**
	 * Add: course
	 */
	protected function addCourse() {
		if (strlen($this->Context->route()->name()) > 0) {
			$PathBox = new BoxedValue($this->Context->route()->name(), '', __('Course'));
			$PathBox->defineAsFloatingBlock('w100 flexible-height');

			$this->BoxedValues[] = $PathBox;
		}
	}

	/**
	 * Add info link
	 */
	protected function addElevationInfoLink() {
		$Linker = new Activity\Linker($this->Context->activity());

		$InfoLink = Ajax::window('<a href="'.$Linker->urlToElevationInfo().'">'.__('More about elevation').'</a>', 'small');

		$this->Content .= HTML::info( $InfoLink );

		if ($this->Context->route()->hasCorrectedElevations()) {
			$this->Content .= HTML::info( __('Elevation data were corrected.') );
		} elseif ($this->Context->route()->hasOriginalElevations() && Configuration::ActivityForm()->correctElevation()) {
			$this->Content .= HTML::warning( __('Elevation data are not corrected.') );
		}

		// TODO: Add link to correct them now!
	}
}