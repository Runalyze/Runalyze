<?php
/**
 * This file contains class::SectionCompositeRow
 * @package Runalyze\DataObjects\Training\View\Section
 */

use Runalyze\Configuration;
use Runalyze\View\Activity;
use Runalyze\View\Activity\Box;
use Runalyze\View\Leaflet;

/**
 * Row: Heartrate
 *
 * @author Hannes Christiansen
 * @package Runalyze\DataObjects\Training\View\Section
 */
class SectionCompositeRow extends TrainingViewSectionRowTabbedPlot {
	/**
	 * Add map
	 */
    protected function addMap() {
        if ($this->Context->hasRoute() && $this->Context->route()->hasPositionData() && !$this->Context->hideMap()) {
            $Map = new Leaflet\Map('map-'.$this->Context->activity()->id());
            $Map->addRoute(
                new Leaflet\Activity(
                    'route-'.$this->Context->activity()->id(),
                    $this->Context->route(),
                    $this->Context->trackdata()
                )
            );

            $this->TopContent = '<div id="training-map"">'.$Map->code().'</div>';
            $this->big = true;
        }
    }

	/**
	 * Set plot
	 */
	protected function setRightContent() {
        if (Configuration::ActivityView()->plotMode()->showCollection() && Configuration::ActivityView()->mapFirst())
            $this->addMap();

		$this->addRightContent('plot', __('Composite plot'), $this->getPlot());

		if ($this->Context->trackdata()->has(\Runalyze\Model\Trackdata\Entity::PACE)) {
			$Table = new TableZonesPace($this->Context);
			$Code = $Table->getCode();
			$Code .= HTML::info( __('You will be soon able to configure your own zones.') );

			$this->addRightContent('zones-pace', __('Pace zones'), $Code);
		}

		if ($this->Context->trackdata()->has(\Runalyze\Model\Trackdata\Entity::HEARTRATE)) {
			$Table = new TableZonesHeartrate($this->Context);
			$Code = $Table->getCode();
			$Code .= HTML::info( __('You will be soon able to configure your own zones.') );

			$this->addRightContent('zones-hr', __('Heart rate zones'), $Code);
		}
	}

	/**
	 * Get plot
	 * @return \Runalyze\View\Activity\Plot\ActivityPlot
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

        if (Configuration::ActivityView()->plotMode()->showCollection() && Configuration::ActivityView()->mapFirst()) {
            $this->BoxedValues[] = new Box\Distance($this->Context);
            $this->BoxedValues[] = new BoxedValue($this->Context->dataview()->duration()->string(), '', __('Time'));
        }

		$this->addAveragePace();
		$this->addCalculations();

		$this->addAverageHeartrate();
		$this->addMaximalHeartrate();
		$this->addCaloriesAndTrimp();

		if ($showElevation) {
			$this->addElevation();
		}

		if (Configuration::ActivityView()->plotMode()->showCollection() && Configuration::ActivityView()->mapFirst()) {
			$this->BoxedValues[] = new BoxedValue($this->Context->dataview()->elapsedTime(), '', __('Elapsed time'));
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
			$Pace = $this->Context->dataview()->pace();

			if ($Pace->unit()->isDecimalFormat()) {
				$this->BoxedValues[] = new Activity\Box\Pace($this->Context);
				$this->BoxedValues[] = new Activity\Box\PaceAlternative($this->Context);
			} else {
				$this->BoxedValues[] = new Activity\Box\Pace($this->Context);
				$this->BoxedValues[] = new Activity\Box\Speed($this->Context);
			}
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
		if (!Request::isOnSharedPage() && $this->Context->dataview()->vdot()->value() > 0) {
			$Linker = new Activity\Linker($this->Context->activity());
			$InfoLink = Ajax::window('<a href="'.$Linker->urlToVDOTInfo().'">'.__('More about VDOT calculation').'</a>', 'small');

			$this->Footer .= HTML::info( $InfoLink );
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
		if ($this->Context->activity()->distance() > 0 || $this->Context->activity()->elevation() > 0) {
			if ($this->Context->activity()->distance() > 0 && !(Configuration::ActivityView()->plotMode()->showCollection() && Configuration::ActivityView()->mapFirst())) {
				$this->BoxedValues[] = new Box\Distance($this->Context);
			}

			$this->BoxedValues[] = new Box\Elevation($this->Context);

			// TODO: Calculated elevation?

			if ($this->Context->activity()->elevation() > 0) {
				if ($this->Context->activity()->distance() > 0) {
					$this->BoxedValues[] = new Box\Gradient($this->Context);
				}

				$this->BoxedValues[] = new Box\ElevationUpDown($this->Context);
			}
		}
	}

	/**
	 * Add: course
	 */
	protected function addCourse() {
		if ($this->Context->hasRoute() && strlen($this->Context->route()->name()) > 0) {
			$PathBox = new BoxedValue($this->Context->route()->name(), '', __('Course'));
			$PathBox->defineAsFloatingBlock('w100 flexible-height');

			$this->BoxedValues[] = $PathBox;
		}
	}

	/**
	 * Add info link
	 */
	protected function addElevationInfoLink() {
		if (!Request::isOnSharedPage()) {
			$Linker = new Activity\Linker($this->Context->activity());
			$InfoLink = Ajax::window('<a href="'.$Linker->urlToElevationInfo().'">'.__('More about elevation').'</a>', 'normal');
			$this->Footer .= HTML::info( $InfoLink );
		}

		if ($this->Context->route()->hasCorrectedElevations()) {
			$this->Footer .= HTML::info( __('Elevation data were corrected.') );
		} elseif ($this->Context->route()->hasOriginalElevations() && Configuration::ActivityForm()->correctElevation()) {
			$this->Footer .= HTML::warning( __('Elevation data are not corrected.') );
		}

		// TODO: Add link to correct them now!
	}
}
