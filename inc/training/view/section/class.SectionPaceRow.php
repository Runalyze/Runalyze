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
class SectionPaceRow extends TrainingViewSectionRow {
	/**
	 * Set plot
	 */
	protected function setPlot() {
		$this->Plot = new TrainingPlotPace($this->Training);
	}

	/**
	 * Set content
	 */
	protected function setContent() {
		$this->addAveragePace();
		$this->addCalculations();

		foreach ($this->BoxedValues as &$Value)
			$Value->defineAsFloatingBlock('w50');

		$this->addInfoLink();

		// TODO: Remove this and use tabbed view as soon as zones have a plot
		$this->withShadow = true;
		if ($this->Training->hasArrayPace()) {
			$this->Code .= '<p>&nbsp;</p>';

			$Table = new TableZonesPace($this->Training);
			$this->Code .= $Table->getCode();
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
			$this->BoxedValues[] = new BoxedValue($this->Training->getCurrentlyUsedVdot(), '', __('VDOT'), $this->Training->DataView()->getVDOTicon());
			$this->BoxedValues[] = new BoxedValue($this->Training->getJDintensity(), '', __('Training points'));
		}
	}

	/**
	 * Add info link
	 */
	protected function addInfoLink() {
		$InfoLink = Ajax::window('<a href="'.$this->Training->Linker()->urlToVDOTInfo().'">'.__('More about VDOT calculation').'</a>', 'small');

		$this->Content = HTML::info( $InfoLink );
	}
}