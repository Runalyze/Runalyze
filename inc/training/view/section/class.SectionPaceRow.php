<?php
/**
 * This file contains class::SectionPaceRow
 * @package Runalyze\DataObjects\Training\View\Section
 */

use Runalyze\View\Activity;

/**
 * Row: Heartrate
 *
 * @author Hannes Christiansen
 * @package Runalyze\DataObjects\Training\View\Section
 */
class SectionPaceRow extends TrainingViewSectionRowTabbedPlot {
	/**
	 * Set plot
	 */
	protected function setRightContent() {
		$this->addRightContent('plot', __('Pace plot'), new Activity\Plot\Pace($this->Context));

		if (
			$this->Context->trackdata()->has(\Runalyze\Model\Trackdata\Entity::PACE) &&
			$this->Context->trackdata()->has(\Runalyze\Model\Trackdata\Entity::TIME)
		) {
			$Table = new TableZonesPace($this->Context);
			$Code = $Table->getCode();
			$Code .= HTML::info( __('You\'ll be soon able to configure your own zones.') );

			$this->addRightContent('zones', __('Pace zones'), $Code);
		}
	}

	/**
	 * Set content
	 */
	protected function setContent() {
		$this->addAveragePace();
		$this->addCalculations();

		foreach ($this->BoxedValues as &$Value) {
			$Value->defineAsFloatingBlock('w50');
		}

		if ($this->Context->dataview()->vdot()->value() > 0) {
			$this->addInfoLink();
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
	protected function addInfoLink() {
		if ($this->Context->dataview()->vdot()->value() > 0 || $this->Context->activity()->jdIntensity() > 0) {
			if (!Request::isOnSharedPage()) {
				$Linker = new Activity\Linker($this->Context->activity());
				$InfoLink = Ajax::window('<a href="'.$Linker->urlToVDOTInfo().'">'.__('More about VDOT calculation').'</a>', 'small');

				$this->Footer = HTML::info( $InfoLink );
			}
		}
	}
}
