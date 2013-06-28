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
		$this->displayHeader();
		$this->displayAsCompetition();
		$this->displayWithHeartrate();
		$this->displayWithCorrector();
		$this->displayWithElevation();
	}

	/**
	 * Display header
	 */
	protected function displayHeader() {
		echo HTML::h1('VDOT-Berechnung zum '.$this->Training->DataView()->getTitleWithCommentAndDate());
	}

	/**
	 * Display as competition
	 */
	protected function displayAsCompetition() {
		$Fieldset = new FormularFieldset('Standardberechnung: als Wettkampf');
		$Fieldset->setHtmlCode('
			<p class="info small">
				Die eigentlichen Formeln dienen dazu, einer Wettkampfzeit einen VDOT-Wert zuzuordnen.
			</p>

			<div class="w50">
				<label>Distanz</label>
				<span class="asInput">'.$this->Training->DataView()->getDistanceString().'</span>
			</div>
			<div class="w50 double-height-right">
				<label>&rArr; VDOT</label>
				<span class="asInput">'.$this->Training->getVdotByTime().'</span>
			</div>
			<div class="w50">
				<label>Dauer</label>
				<span class="asInput">'.$this->Training->DataView()->getTimeString().'</span>
			</div>
		');
		$Fieldset->display();
	}

	/**
	 * Display with heartrate
	 */
	protected function displayWithHeartrate() {
		$Fieldset = new FormularFieldset('Korrektur: mit Herzfrequenz');
		$Fieldset->setHtmlCode('
			<p class="info small">
				Jack Daniels hat eine Tabelle f&uuml;r den Zusammenhang von &#37;HFmax und &#37;VDOT.<br />
				Aufgrund der vielen Einflussfaktoren auf den Puls sind diese Werte nicht immer richtig.
			</p>

			<div class="w50">
				<label>Puls</label>
				<span class="asInput">'.$this->Training->DataView()->getPulseAvgInPercent().'HFmax</span>
			</div>
			<div class="w50 double-height-right">
				<label>&rArr; VDOT</label>
				<span class="asInput">'.$this->Training->getVdotUncorrected().'</span>
			</div>
			<div class="w50">
				<label>entspricht</label>
				<span class="asInput">'.round(100*JD::pHF2pVDOT($this->Training->getPulseAvg()/HF_MAX)).' &#37;VDOT</span>
			</div>
		');
		$Fieldset->display();
	}

	/**
	 * Display with corrector
	 */
	protected function displayWithCorrector() {
		$Fieldset = new FormularFieldset('Korrektur: mit Korrekturfaktor');
		$Fieldset->setHtmlCode('
			<p class="info small">
				100&#37;VDOT entsprechen laut Jack Daniels 100&#37;HFmax, aber wer schafft 11 Minuten bei Maximalpuls?
				Der Korrekturfaktor wird aus dem Puls des <em>besten</em> Wettkampfs berechnet.
			</p>

			<div class="w50">
				<label>Korrekturfaktor</label>
				<span class="asInput">'.VDOT_CORRECTOR.'</span>
			</div>
			<div class="w50 double-height-right">
				<label>&rArr; VDOT</label>
				<span class="asInput highlight">'.$this->Training->getVdotCorrected().'</span>
			</div>
			<div class="w50">
				<label>unkorrigiert</label>
				<span class="asInput">'.$this->Training->getVdotUncorrected().'</span>
			</div>
		');
		$Fieldset->display();
	}

	/**
	 * Display with corrector
	 */
	protected function displayWithElevation() {
		$additionalDistance = 2*$this->Training->getElevationUp() - $this->Training->getElevationDown();
		$newVDOT = VDOT_CORRECTOR * JD::Training2VDOT(0, array(
			'sportid'	=> CONF_RUNNINGSPORT,
			'distance'	=> $this->Training->getDistance() + $additionalDistance/1000,
			's'			=> $this->Training->getTimeInSeconds(),
			'pulse_avg'	=> $this->Training->getPulseAvg()
		));

		$Fieldset = new FormularFieldset('Korrektur: mit Beachtung der H&ouml;henmeter');
		$Fieldset->setHtmlCode('
			<p class="warning small">
				Diese Korrektur wird noch nicht verwendet.
			</p>

			<div class="w50">
				<label>Auf-/Abstieg</label>
				<span class="asInput">'.$this->Training->DataView()->getElevationUpAndDown().'</span>
			</div>
			<div class="w50 double-height-right">
				<label>&rArr; VDOT</label>
				<span class="asInput">'.round($newVDOT, 2).'</span>
			</div>
			<div class="w50">
				<label>Distanzeinfluss</label>
				<span class="asInput">'.Math::WithSign($additionalDistance).'m = '.Running::Km($this->Training->getDistance() + $additionalDistance/1000, 3).'</span>
			</div>
		');
		$Fieldset->display();
	}
}