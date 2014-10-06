<?php
/**
 * This file contains class::SectionRouteRowElevation
 * @package Runalyze\DataObjects\Training\View\Section
 */
/**
 * Row: Route
 * 
 * @author Hannes Christiansen
 * @package Runalyze\DataObjects\Training\View\Section
 */
class SectionRouteRowElevation extends TrainingViewSectionRow {
	/**
	 * Set plot
	 */
	protected function setPlot() {
		$this->Plot = new TrainingPlotElevation($this->Training);
	}

	/**
	 * Set content
	 */
	protected function setContent() {
		$this->addElevation();

		foreach ($this->BoxedValues as &$Value) {
			$Value->defineAsFloatingBlock('w50');
		}

		$this->addCourse();

		if ($this->Training->hasArrayAltitude()) {
			$this->addInfoLink();
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
	protected function addInfoLink() {
		$InfoLink = Ajax::window('<a href="'.$this->Training->Linker()->urlToElevationInfo().'">'.__('More about elevation').'</a>', 'small');

		$this->Content = HTML::info( $InfoLink );

		if ($this->Training->elevationWasCorrected())
			$this->Content .= HTML::info( __('Elevation data were corrected.') );
		elseif ($this->Training->hasArrayAltitude() && Configuration::ActivityForm()->correctElevation())
			$this->Content .= HTML::warning( __('Elevation data are not corrected.') );

		// TODO: Add link to correct them now!
	}
}