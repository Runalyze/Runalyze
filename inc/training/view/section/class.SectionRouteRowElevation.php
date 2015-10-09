<?php
/**
 * This file contains class::SectionRouteRowElevation
 * @package Runalyze\DataObjects\Training\View\Section
 */

use Runalyze\Configuration;
use Runalyze\View\Activity;
use Runalyze\View\Activity\Linker;
use Runalyze\Activity\Distance;

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
			$this->BoxedValues[] = new BoxedValue(Distance::format($this->Context->activity()->distance(), false, false, false), Configuration::General()->distanceUnit()->value(), __('Distance'));
			$Elevation = new Distance($this->Context->activity()->elevation()/1000);
                        $this->BoxedValues[] = new BoxedValue($Elevation->stringForDistanceFeet(false, false), $Elevation->unitForDistancesFeet(), __('Elevation'));

			// TODO: Calculated elevation?

			if ($this->Context->activity()->elevation() > 0) {
				$this->BoxedValues[] = new BoxedValue(substr($this->Context->dataview()->gradientInPercent(),0,-11), '&#37;', __('&oslash; Gradient'));

				if ($this->Context->hasRoute()) {
					$upDown = '+'.  Distance::formatFeet($this->Context->route()->elevationUp()/1000, false, false).'/-'.Distance::formatFeet($this->Context->route()->elevationDown()/1000, false, false);
				} else {
					$upDown = '+'.Distance::formatFeet($this->Context->activity()->elevation()/1000, false, false).'/-'.Distance::formatFeet($this->Context->activity()->elevation()/1000, false, false);
				}

				$this->BoxedValues[] = new BoxedValue($upDown, Configuration::General()->distanceUnitAsFeet(), __('Elevation up/down'));
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
