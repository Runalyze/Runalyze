<?php
/**
 * This file contains class::SectionPaceRow
 * @package Runalyze\DataObjects\Training\View\Section
 */
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
		$this->addRightContent('plot', __('Pace plot'), new TrainingPlotPace($this->Training));

		if ($this->Training->hasArrayPace()) {
			$Table = new TableZonesPace($this->Training);
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

		if ($this->Training->getCurrentlyUsedVdot() > 0) {
			$this->addInfoLink();
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
	protected function addInfoLink() {
		if ($this->Training->getVdotCorrected() > 0 || $this->Training->getJDintensity() > 0) {
			$InfoLink = Ajax::window('<a href="'.$this->Training->Linker()->urlToVDOTInfo().'">'.__('More about VDOT calculation').'</a>', 'small');

			$this->Content = HTML::info( $InfoLink );
		}
	}
}
