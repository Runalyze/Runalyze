<?php
/**
 * This file contains class::VDOTinfo
 * @package Runalyze\DataObjects\Training\View
 */

use Runalyze\Configuration;
use Runalyze\Calculation\JD;
use Runalyze\Calculation;
use Runalyze\Activity\Distance;
use Runalyze\Activity\Elevation;
use Runalyze\View\Activity\Context;

/**
 * Display VDOT info for a training
 * 
 * @author Hannes Christiansen
 * @package Runalyze\DataObjects\Training\View
 */
class VDOTinfo {
	/**
	 * Training object
	 * @var \Runalyze\View\Activity\Context
	 */
	protected $Context;

	/**
	 * @param \Runalyze\View\Activity\Context $context
	 */
	public function __construct(Context $context) {
		$this->Context = $context;
	}

	/**
	 * Display
	 */
	public function display() {
		echo '<div class="panel-heading">';
		$this->displayHeader();
		echo '</div>';

		echo '<div class="panel-content">';
		$this->displayAsCompetition();
		$this->displayWithHeartrate();
		$this->displayWithCorrector();
		$this->displayWithElevation();
		echo '</div>';
	}

	/**
	 * Display header
	 */
	protected function displayHeader() {
		echo HTML::h1( sprintf( __('VDOT calculation for: %s'), $this->Context->dataview()->titleWithComment() ) );
	}

	/**
	 * Display as competition
	 */
	protected function displayAsCompetition() {
		$VDOT = new JD\VDOT($this->Context->activity()->vdotByTime());

		$Fieldset = new FormularFieldset( __('Standard calculation: As competition'));
		$Fieldset->setHtmlCode('
			<p class="info small">
				'.__('All traditional formulas are being used to calculate a VDOT value for a given competition.').'
			</p>

			<div class="w50">
				<label>'.__('Distance').'</label>
				<span class="as-input">'.$this->Context->dataview()->distance().'</span>
			</div>
			<div class="w50 double-height-right">
				<label>&rArr; '.__('VDOT').'</label>
				<span class="as-input">'.$VDOT->uncorrectedValue().'</span>
			</div>
			<div class="w50">
				<label>'.__('Duration').'</label>
				<span class="as-input">'.$this->Context->dataview()->duration()->string().'</span>
			</div>
		');
		$Fieldset->display();
	}

	/**
	 * Display with heartrate
	 */
	protected function displayWithHeartrate() {
		$VDOT = new JD\VDOT($this->Context->activity()->vdotByHeartRate(), new JD\VDOTCorrector(Configuration::Data()->vdotFactor()));
		$vVDOTinPercent = JD\VDOT::percentageAt($this->Context->activity()->hrAvg() / Configuration::Data()->HRmax());

		$Fieldset = new FormularFieldset( __('Correction: based on heartrate') );
		$Fieldset->setHtmlCode('
			<p class="info small">
				'.__('Jack Daniels has tables to compare &#37;HRmax and &#37;vVDOT.').'<br>
				'.__('Because of a lot of influencing factors these computations are not always accurate.').'
			</p>

			<div class="w50">
				<label>'.__('Heartrate').'</label>
				<span class="as-input">'.$this->Context->dataview()->hrAvg()->inHRmax().' &#37;HRmax</span>
			</div>
			<div class="w50 double-height-right">
				<label>&rArr; '.__('VDOT').'</label>
				<span class="as-input">'.$VDOT->uncorrectedValue().'</span>
			</div>
			<div class="w50">
				<label>'.__('equals').'</label>
				<span class="as-input">'.round(100*$vVDOTinPercent).' &#37;vVDOT</span>
			</div>
		');
		$Fieldset->display();
	}

	/**
	 * Display with corrector
	 */
	protected function displayWithCorrector() {
		$VDOT = new JD\VDOT($this->Context->activity()->vdotByHeartRate(), new JD\VDOTCorrector(Configuration::Data()->vdotFactor()));

		$Fieldset = new FormularFieldset( __('Correction: based on correction factor') );
		$Fieldset->setHtmlCode('
			<p class="info small">
				'.__('To consider some individual factors, we use a correction factor.').'
				'.__('This factor is based on your <em>best</em> competition.').'
			</p>

			<div class="w50">
				<label>'.__('Correction factor').'</label>
				<span class="as-input">'.Configuration::Data()->vdotFactor().'</span>
			</div>
			<div class="w50 double-height-right">
				<label>&rArr; '.__('VDOT').'</label>
				<span class="as-input '.(!Configuration::Vdot()->useElevationCorrection() ? 'highlight' : '').'">'.$VDOT->value().'</span>
			</div>
			<div class="w50">
				<label>'.__('uncorrected').'</label>
				<span class="as-input">'.$VDOT->uncorrectedValue().'</span>
			</div>
		');
		$Fieldset->display();
	}

	/**
	 * Display with corrector
	 */
	protected function displayWithElevation() {
		if ($this->Context->hasRoute() && ($this->Context->route()->elevationUp() > 0 || $this->Context->route()->elevationDown())) {
			$up = $this->Context->route()->elevationUp();
			$down = $this->Context->route()->elevationDown();
		} else {
			$up = $this->Context->activity()->elevation();
			$down = $up;
		}

		$Modifier = new Calculation\Elevation\DistanceModifier(
			$this->Context->activity()->distance(),
			$up,
			$down,
			Configuration::Vdot()
		);

		$VDOT = new JD\VDOT(0, new JD\VDOTCorrector(Configuration::Data()->vdotFactor()));
		$VDOT->fromPaceAndHR(
			$Modifier->correctedDistance(),
			$this->Context->activity()->duration(),
			$this->Context->activity()->hrAvg() / Configuration::Data()->HRmax()
		);

		$Fieldset = new FormularFieldset( __('Correction: considering elevation') );
		$Fieldset->setHtmlCode('
			<p class="warning small '.(Configuration::Vdot()->useElevationCorrection() ? 'hide' : '').'">
				'.__('This correction method is currently unused.').'
			</p>

			<div class="w50">
				<label>'.__('Up/Down').'</label>
				<span class="as-input">+'.Elevation::format($up, false).'/-'.Elevation::format($down, true).'</span>
			</div>
			<div class="w50 double-height-right">
				<label>&rArr; '.__('VDOT').'</label>
				<span class="as-input '.(!Configuration::Vdot()->useElevationCorrection() ? '' : 'highlight').'">'.$VDOT->value().'</span>
			</div>
			<div class="w50">
				<label>'.__('Influence').'</label>
				<span class="as-input">'.Distance::format($Modifier->additionalDistance(), true, 3). ' =&gt; '.Distance::format($Modifier->correctedDistance(), true, 3).'</span>
			</div>
		');
		$Fieldset->display();
	}
}