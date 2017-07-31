<?php
/**
 * This file contains class::SectionRouteRowElevation
 * @package Runalyze\DataObjects\Training\View\Section
 */

use Runalyze\Configuration;
use Runalyze\View\Activity;
use Runalyze\View\Activity\Linker;
use Runalyze\View\Activity\Box;

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
        $this->addClimbScore();

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
		if ($this->Context->activity()->distance() > 0 || $this->Context->activity()->elevation() > 0) {
			if ($this->Context->activity()->distance() > 0) {
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

    protected function addClimbScore() {
        if (null !== $this->Context->activity()->climbScore()) {
            $this->BoxedValues[] = new BoxedValue(
                number_format($this->Context->activity()->climbScore(), 1),
                '',
                __('Climb Score')
            );

            $this->BoxedValues[] = new BoxedValue(
                round(100 * $this->Context->activity()->percentageHilly()),
                '&#37;',
                __('Percentage hilly')
            );
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
        $this->Footer = '';

        $Linker = new Linker($this->Context->activity());

        if (null !== $this->Context->activity()->climbScore()) {
            $this->Footer .= HTML::info(Ajax::window('<a href="'.$Linker->urlToClimbScore().'">'.__('Climb Score view').'</a>', 'normal'));
        }

		if (!Request::isOnSharedPage()) {
            $this->Footer .= HTML::info(Ajax::window('<a href="'.$Linker->urlToElevationInfo().'">'.__('More about elevation').'</a>', 'normal'));
		}

		if ($this->Context->route()->hasCorrectedElevations()) {
			$this->Footer .= HTML::info( __('Elevation data were corrected.') );
		} elseif ($this->Context->route()->hasOriginalElevations() && Configuration::ActivityForm()->correctElevation()) {
			$this->Footer .= HTML::warning( __('Elevation data are not corrected.') );
		}

		// TODO: Add link to correct them now!
	}
}
