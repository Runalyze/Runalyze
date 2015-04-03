<?php
/**
 * This file contains class::SectionRouteRowElevation
 * @package Runalyze\DataObjects\Training\View\Section
 */

use Runalyze\Configuration;
use Runalyze\View\Activity;
use Runalyze\View\Activity\Linker;

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
		$this->Plot = new Activity\Plot\Elevation($this->Context);
	}

	/**
	 * Set content
	 */
	protected function setContent() {
		$this->addElevation();

		foreach ($this->BoxedValues as &$Value) {
			$Value->defineAsFloatingBlock('w50');
		}

		if ($this->Context->hasRoute()) {
			$this->addCourse();

			if ($this->Context->route()->hasElevations()) {
				$this->addInfoLink();
			}
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

				if ($this->Context->hasRoute()) {
					$upDown = '+'.$this->Context->route()->elevationUp().'/-'.$this->Context->route()->elevationDown();
				} else {
					$upDown = '+'.$this->Context->activity()->elevation().'/-'.$this->Context->activity()->elevation();
				}

				$this->BoxedValues[] = new BoxedValue($upDown, 'm', __('Elevation up/down'));
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
	protected function addInfoLink() {
		if (!Request::isOnSharedPage()) {
			$Linker = new Linker($this->Context->activity());
			$InfoLink = Ajax::window('<a href="'.$Linker->urlToElevationInfo().'">'.__('More about elevation').'</a>', 'small');
			$this->Footer = HTML::info( $InfoLink );
		} else {
			$this->Footer = '';
		}

		if ($this->Context->route()->hasCorrectedElevations()) {
			$this->Footer .= HTML::info( __('Elevation data were corrected.') );
		} elseif ($this->Context->route()->hasOriginalElevations() && Configuration::ActivityForm()->correctElevation()) {
			$this->Footer .= HTML::warning( __('Elevation data are not corrected.') );
		}

		// TODO: Add link to correct them now!
	}
}
