<?php
/**
 * This file contains class::VDOTinfo
 * @package Runalyze\DataObjects\Training\View
 */
/**
 * Display VDOT info for a training
 * 
 * @author Hannes Christiansen
 * @package Runalyze\DataObjects\Training\View
 */
class VDOTinfo {
	/**
	 * Training object
	 * @var \TrainingObject
	 */
	protected $Training = null;

	/**
	 * Constructor
	 * @param TrainingObject $Training Training object
	 */
	public function __construct(TrainingObject &$Training) {
		$this->Training = $Training;
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
		echo HTML::h1( sprintf( __('VDOT calculation for: %s'), $this->Training->DataView()->getTitleWithCommentAndDate() ) );
	}

	/**
	 * Display as competition
	 */
	protected function displayAsCompetition() {
		$Fieldset = new FormularFieldset( __('Standard calculation: As competition'));
		$Fieldset->setHtmlCode('
			<p class="info small">
				'.__('All traditional formulas are used to calculate a VDOT value for a given competition.').'
			</p>

			<div class="w50">
				<label>'.__('Distance').'</label>
				<span class="as-input">'.$this->Training->DataView()->getDistanceString().'</span>
			</div>
			<div class="w50 double-height-right">
				<label>&rArr; '.__('VDOT').'</label>
				<span class="as-input">'.$this->Training->getVdotByTime().'</span>
			</div>
			<div class="w50">
				<label>'.__('Duration').'</label>
				<span class="as-input">'.$this->Training->DataView()->getTimeString().'</span>
			</div>
		');
		$Fieldset->display();
	}

	/**
	 * Display with heartrate
	 */
	protected function displayWithHeartrate() {
		$Fieldset = new FormularFieldset( __('Correction: based on heartrate') );
		$Fieldset->setHtmlCode('
			<p class="info small">
				'.__('Jack Daniels has tables to compare &#37;HRmax and &#37;VDOT.').'<br>
				'.__('Because of a lot of influencing factors these computations are not always accurate.').'
			</p>

			<div class="w50">
				<label>'.__('Heartrate').'</label>
				<span class="as-input">'.$this->Training->DataView()->getPulseAvgInPercent().'HFmax</span>
			</div>
			<div class="w50 double-height-right">
				<label>&rArr; '.__('VDOT').'</label>
				<span class="as-input">'.$this->Training->getVdotUncorrected().'</span>
			</div>
			<div class="w50">
				<label>'.__('equals').'</label>
				<span class="as-input">'.round(100*JD::pHF2pVDOT($this->Training->getPulseAvg()/HF_MAX)).' &#37;VDOT</span>
			</div>
		');
		$Fieldset->display();
	}

	/**
	 * Display with corrector
	 */
	protected function displayWithCorrector() {
		$Fieldset = new FormularFieldset( __('Correction: based on correction factor') );
		$Fieldset->setHtmlCode('
			<p class="info small">
				'.__('To consider some individual factors, we use a correction factor.').'
				'.__('This factor is based on your <em>best</em> competition.').'
			</p>

			<div class="w50">
				<label>'.__('Correction factor').'</label>
				<span class="as-input">'.JD::correctionFactor().'</span>
			</div>
			<div class="w50 double-height-right">
				<label>&rArr; '.__('VDOT').'</label>
				<span class="as-input '.(!Configuration::Vdot()->useElevationCorrection() ? 'highlight' : '').'">'.$this->Training->getVdotCorrected().'</span>
			</div>
			<div class="w50">
				<label>'.__('uncorrected').'</label>
				<span class="as-input">'.$this->Training->getVdotUncorrected().'</span>
			</div>
		');
		$Fieldset->display();
	}

	/**
	 * Display with corrector
	 */
	protected function displayWithElevation() {
		$up   = $this->Training->hasArrayAltitude() ? $this->Training->getElevationUp() : $this->Training->getElevation();
		$down = $this->Training->hasArrayAltitude() ? $this->Training->getElevationDown() : $this->Training->getElevation();

		$additionalDistance = (int)Configuration::Vdot()->correctionForPositiveElevation()*$up + (int)Configuration::Vdot()->correctionForNegativeElevation()*$down;
		$newVDOT =  JD::Training2VDOTwithElevation(0, array(
				'sportid'	=> Configuration::General()->runningSport(),
				'distance'	=> $this->Training->getDistance(),
				's'			=> $this->Training->getTimeInSeconds(),
				'pulse_avg'	=> $this->Training->getPulseAvg()
			),
			$up,
			$down
		);

		if (Configuration::Vdot()->useCorrectionFactor())
			$newVDOT = JD::correctionFactor() * $newVDOT;

		$Fieldset = new FormularFieldset( __('Correction: considering elevation') );
		$Fieldset->setHtmlCode('
			<p class="warning small '.(Configuration::Vdot()->useElevationCorrection() ? 'hide' : '').'">
				'.__('This correction method is currently unused.').'
			</p>

			<div class="w50">
				<label>'.__('Up/Down').'</label>
				<span class="as-input">+'.$up.'/-'.$down.'&nbsp;m</span>
			</div>
			<div class="w50 double-height-right">
				<label>&rArr; '.__('VDOT').'</label>
				<span class="as-input '.(!Configuration::Vdot()->useElevationCorrection() ? '' : 'highlight').'">'.round($newVDOT, 2).'</span>
			</div>
			<div class="w50">
				<label>'.__('Influence').'</label>
				<span class="as-input">'.Math::WithSign($additionalDistance).'m = '.Running::Km($this->Training->getDistance() + $additionalDistance/1000, 3).'</span>
			</div>
		');
		$Fieldset->display();
	}
}