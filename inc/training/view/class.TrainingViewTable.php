<?php
/**
 * This file contains class::TrainingViewTable
 * @package Runalyze\DataObjects\Training\View
 */
/**
 * Display table for a training
 * 
 * @author Hannes Christiansen
 * @package Runalyze\DataObjects\Training\View
 */
class TrainingViewTable {
	/**
	 * Training object
	 * @var TrainingObject
	 */
	protected $Training = null;

	/**
	 * Standard lines
	 * @var array
	 */
	protected $Lines = array();

	/**
	 * Outside lines
	 * @var array
	 */
	protected $OutsideLines = array();

	/**
	 * Additional lines
	 * @var array
	 */
	protected $ExtraLines = array();

	/**
	 * Constructor
	 * @param TrainingObject $Training
	 */
	public function __construct(TrainingObject &$Training) {
		$this->Training = $Training;

		$this->initLines();
		$this->initOutsideLines();
		$this->mergeStandardAndOutsideLines();
		$this->initExtraLines();
	}

	/**
	 * Add line
	 * @param string $Label
	 * @param string $Data
	 */
	protected function addLine($Label, $Data) {
		$this->Lines[] = array($Label, $Data);
	}

	/**
	 * Add outside line
	 * @param string $Label
	 * @param string $Data
	 */
	protected function addOutsideLine($Label, $Data) {
		$this->OutsideLines[] = array($Label, $Data);
	}

	/**
	 * Add extra line
	 * @param string $Label
	 * @param string $Data
	 */
	protected function addExtraLine($Label, $Data) {
		$this->ExtraLines[] = array($Label, $Data);
	}

	/**
	 * Merge standard and outside lines
	 */
	private function mergeStandardAndOutsideLines() {
		if (!empty($this->OutsideLines))
			$this->Lines = array_merge($this->Lines, array(array('&nbsp;', '')), $this->OutsideLines);
	}

	/**
	 * Init lines
	 */
	protected function initLines() {
		if ($this->Training->hasDistance())
			$this->addLine('Distanz', $this->Training->DataView()->getDistanceStringWithFullDecimals());

			$this->addLine('Zeit', $this->Training->DataView()->getTimeString());

		if ($this->Training->hasDistance())
			$this->addLine('Tempo', $this->Training->DataView()->getSpeedString());

		if ($this->Training->getPulseAvg() > 0)
			$this->addLine('&oslash;&nbsp;Puls', $this->Training->DataView()->getPulseAvgAsBpmAndPercent());

		if ($this->Training->getPulseMax() > 0)
			$this->addLine('max.&nbsp;Puls', $this->Training->DataView()->getPulseMaxAsBpmAndPercent());

		if ($this->Training->getCalories() > 0)
			$this->addLine('Kalorien', $this->Training->DataView()->getCalories());

		if (CONF_RECHENSPIELE)
			$this->addLine('Trimp', $this->Training->DataView()->getTrimpString());

		if (CONF_RECHENSPIELE && $this->Training->Sport()->isRunning() && $this->Training->getVdotCorrected() > 0)
			$this->addLine('Vdot', $this->Training->DataView()->getVDOTAndIcon());
	}

	/**
	 * Init outside lines
	 */
	private function initOutsideLines() {
		if (!$this->Training->Weather()->isEmpty())
			$this->addOutsideLine('Wetter', $this->Training->Weather()->fullString());

		if ($this->Training->getRoute() != '')
			$this->addOutsideLine('Strecke', HTML::encodeTags($this->Training->getRoute()));

		$this->addElevationLines();

		if (!$this->Training->Shoe()->isDefaultId())
			$this->addOutsideLine('Schuh', Request::isOnSharedPage() ? $this->Training->Shoe()->getName() : $this->Training->Shoe()->getSearchLink());

		if (!$this->Training->Clothes()->areEmpty())
			$this->addOutsideLine('Kleidung', Request::isOnSharedPage() ? $this->Training->Clothes()->asString() : $this->Training->Clothes()->asLinks());

		if ($this->Training->getPartner() != '')
			$this->addOutsideLine('Partner', Request::isOnSharedPage() ? $this->Training->DataView()->getPartner() : $this->Training->DataView()->getPartnerAsLinks());

		if ($this->Training->getNotes() != '')
			$this->addOutsideLine('Notizen', $this->Training->DataView()->getNotes());
	}

	/**
	 * Add lines for elevation
	 */
	private function addElevationLines() {
		$current    = $this->Training->getElevation();
		$calculated = $this->Training->GpsData()->calculateElevation();
		$difference = $this->Training->GpsData()->getElevationDifference();
		$updown     = $this->Training->GpsData()->getElevationUpDownOfStep(true);

		if ($current > 0 || $calculated > 0) {
			$Text = $current.'&nbsp;m';

			if ($calculated != $current)
				$Text .= ' <small>('.$calculated.'&nbsp;m berechnet)</small>';

			if (CONF_TRAINING_DO_ELEVATION && !$this->Training->elevationWasCorrected())
				$Text .= '<br />
					<em id="gps-results" class="block">
						Die H&ouml;hendaten sind noch nicht korrigiert.
						<a class="ajax" target="gps-results" href="call/call.Training.elevationCorrection.php?id='.$this->Training->id().'" title="H&ouml;hendaten korrigieren"><strong>&raquo; jetzt korrigieren</strong></a>
					</em>';

			$this->addOutsideLine('H&ouml;henmeter', $Text);
		}

		if (abs($difference) > 20)
			$this->addOutsideLine('H&ouml;henunterschied', Math::WithSign($difference).'m');

		if ($calculated > 0)
			$this->addOutsideLine(Ajax::tooltip('Auf-/Abstieg', 'Durch die Gl&auml;ttung im Algorithmus m&uuml;ssen diese Werte nicht zu den anderen passen.', 'atRight'), '+'.$updown[0].'/-'.$updown[1].'&nbsp;m');

		if ($current > 0)
			$this->addOutsideLine('Steigung', number_format($current/10/$this->Training->getDistance(), 2, ',', '.').'&nbsp;&#37;');
	}

	/**
	 * Init extra lines
	 */
	protected function initExtraLines() {
		if ($this->Training->getCreatedTimestamp() > 0)
			$this->addExtraLine('Erstellt', 'am '.date('d.m.Y', $this->Training->getCreatedTimestamp()));

		if ($this->Training->getEditedTimestamp() > 0)
			$this->addExtraLine('Bearbeitet', 'zuletzt am '.date('d.m.Y', $this->Training->getEditedTimestamp()));

		// TODO
		if ($this->Training->getCreator() == ImporterFactory::$CREATOR_FILE)
			$this->addExtraLine('Importer', 'Datei-Upload');
		elseif ($this->Training->getCreator() == ImporterFactory::$CREATOR_GARMIN_COMMUNICATOR)
			$this->addExtraLine('Importer', 'Garmin-Communicator');

		if ($this->Training->hasArrayAltitude())
			$this->addExtraLine('H&ouml;hendaten', ($this->Training->elevationWasCorrected() ? '' : 'noch nicht ').'korrigiert');
	}

	/**
	 * Display
	 */
	public function display() {
		echo '<table class="small">';

		$this->displayLines();
		$this->displayExtraLines();

		echo '</table>';
	}

	/**
	 * Display lines
	 */
	private function displayLines() {
		echo '<tbody>';

		foreach ($this->Lines as $Line)
			echo '<tr><td class="inlineHead">'.$Line[0].'</td><td>'.$Line[1].'</td></tr>';

		echo '</tbody>';
	}

	/**
	 * Display extra lines
	 */
	private function displayExtraLines() {
		if (empty($this->ExtraLines)) {
			echo Ajax::wrapJSasFunction('$("#training-view-toggler-details").remove();');
			return;
		}

		echo '<tbody id="training-table-extra">';
		echo '<tr><td colspan="2">&nbsp;</td></tr>';
		echo '<tr class="space"><td colspan="2"></td></tr>';
		echo '<tr><td colspan="2">&nbsp;</td></tr>';

		foreach ($this->ExtraLines as $Line)
			echo '<tr><td class="inlineHead">'.$Line[0].'</td><td>'.$Line[1].'</td></tr>';

		echo '</tbody>';
	}
}