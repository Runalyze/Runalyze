<?php
/**
 * This file contains class::TrainingViewIFrameTable
 * @package Runalyze\DataObjects\Training\View
 */
/**
 * Display table for a training
 * 
 * @author Hannes Christiansen
 * @package Runalyze\DataObjects\Training\View
 */
class TrainingViewIFrameTable {
	/**
	 * Training object
	 * @var TrainingObject
	 */
	protected $Training = null;

	/**
	 * Left infos
	 * @var array
	 */
	protected $LeftInfos = array();

	/**
	 * Right infos
	 * @var array
	 */
	protected $RightInfos = array();

	/**
	 * Full infos
	 * @var array
	 */
	protected $FullInfos = array();

	/**
	 * Constructor
	 * @param TrainingObject $Training
	 */
	public function __construct(TrainingObject &$Training) {
		$this->Training = $Training;

		$this->initLines();
	}

	/**
	 * Add left info
	 * @param string $Label
	 * @param string $Data
	 */
	protected function addLeft($Label, $Data) {
		$this->LeftInfos[] = array($Label, $Data);
	}

	/**
	 * Add right info
	 * @param string $Label
	 * @param string $Data
	 */
	protected function addRight($Label, $Data) {
		$this->RightInfos[] = array($Label, $Data);
	}

	/**
	 * Add full info
	 * @param string $Label
	 * @param string $Data
	 */
	protected function addFull($Label, $Data) {
		$this->FullInfos[] = array($Label, $Data);
	}

	/**
	 * Init lines
	 */
	private function initLines() {
		if ($this->Training->hasDistance()) {
			$this->addLeft('Distanz', $this->Training->DataView()->getDistanceStringWithFullDecimals());
			$this->addLeft('Tempo', $this->Training->DataView()->getSpeedString());
		}

		if ($this->Training->getPulseAvg() > 0)
			$this->addRight('&oslash;&nbsp;Puls', Running::PulseStringInBpm($this->Training->getPulseAvg()));
		if ($this->Training->getPulseMax() > 0)
			$this->addRight('max.&nbsp;Puls', Running::PulseStringInBpm($this->Training->getPulseMax()));

		$this->addLeft('Zeit', $this->Training->DataView()->getTimeString());
		$this->addLeft('Kalorien', $this->Training->DataView()->getCalories());

		if (CONF_RECHENSPIELE) {
			$this->addRight('Trimp', $this->Training->DataView()->getTrimpString());

			if ($this->Training->Sport()->isRunning() && $this->Training->getVdotCorrected() > 0)
				$this->addRight('Vdot', $this->Training->DataView()->getVDOTAndIcon());
		}

		if ($this->Training->getPartner() != '')
			$this->addLeft('Partner', $this->Training->getPartner());

		if (!$this->Training->Shoe()->isDefaultId())
			$this->addLeft('Schuh', $this->Training->Shoe()->getName());

		if (!$this->Training->Weather()->isEmpty())
			$this->addRight('Wetter', $this->Training->Weather()->fullString());

		if (!$this->Training->Clothes()->areEmpty())
			$this->addRight('Kleidung', $this->Training->Clothes()->asString());

		$elevation = $this->Training->getElevation();
		if ($this->Training->getRoute() != '' || $elevation > 0) {
			$berechnet = $this->Training->GpsData()->calculateElevation();
			$routeInfo = Helper::Unknown($this->Training->getRoute());

			if ($elevation > 0 || $berechnet > 0) {
				$routeInfo .= ', '.$elevation.' H&ouml;henmeter';

				if ($berechnet != $elevation)
					$routeInfo .= ', '.$berechnet.' berechnet';

				$routeInfo .= ', '.number_format($elevation/10/$this->Training->getDistance(), 2, ',', '.').' &#37; Steigung';
			}

			$this->addFull('Strecke', $routeInfo);
		}
	}

	/**
	 * Display
	 */
	public function display() {
		echo '<table class="small fullwidth">';

		$num = max(array(count($this->LeftInfos), count($this->RightInfos)));

		for ($i = 0; $i < $num; $i++) {
			echo '<tr>';

			if (isset($this->LeftInfos[$i])) {
				echo '<td class="inline-head">'.$this->LeftInfos[$i][0].'</td>';
				echo '<td>'.$this->LeftInfos[$i][1].'</td>';
			} else {
				echo '<td colspan="2">&nbsp;</td>';
			}

			if (isset($this->RightInfos[$i])) {
				echo '<td class="inline-head">'.$this->RightInfos[$i][0].'</td>';
				echo '<td>'.$this->RightInfos[$i][1].'</td>';
			} else {
				echo '<td colspan="2">&nbsp;</td>';
			}

			echo '</tr>';
		}

		foreach ($this->FullInfos as $INFO) {
			echo '<tr>';
			echo '<td class="inline-head">'.$INFO[0].'</td>';
			echo '<td colspan="3">'.$INFO[1].'</td>';
			echo '</tr>';
		}

		echo '</table>';
	}
}