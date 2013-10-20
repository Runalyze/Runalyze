<?php
/**
 * This file contains class::RoundsInfo
 * @package Runalyze\DataObjects\Training\View
 */
/**
 * Display rounds info for a training
 * 
 * @author Hannes Christiansen
 * @package Runalyze\DataObjects\Training\View
 */
class RoundsInfo {
	/**
	 * Training object
	 * @var \TrainingObject
	 */
	protected $Training = null;

	/**
	 * Rounds data
	 * @var array
	 */
	protected $Data = array();

	/**
	 * Round distance
	 * @var mixed
	 */
	protected $RoundDistance = 1;

	/**
	 * Demanded time
	 * @var int
	 */
	protected $DemandedTime = 0;

	/**
	 * Demanded pace
	 * @var int
	 */
	protected $DemandedPace = 0;

	/**
	 * Manual distances
	 * @var array
	 */
	protected $ManualDistances = array();

	/**
	 * Constructor
	 * @param TrainingObject $Training Training object
	 */
	public function __construct(TrainingObject &$Training) {
		$this->Training = $Training;

		$this->handleRequest();
	}

	/**
	 * Handle request
	 */
	protected function handleRequest() {
		if ((float)Request::param('distance') > 0)
			$this->RoundDistance = (float)Request::param('distance');

		if (strlen(Request::param('demanded-time')) > 0) {
			$this->DemandedTime = Time::toSeconds(Request::param('demanded-time'));
			$this->DemandedPace = $this->DemandedTime / $this->RoundDistance;
		} else {
			$this->DemandedTime = Time::toSeconds( $this->Training->getPace() ) * $this->RoundDistance;
		}

		if (strlen(Request::param('demanded-pace')) > 0) {
			$this->DemandedPace = Time::toSeconds(Request::param('demanded-pace'));
			$this->DemandedTime = $this->RoundDistance * $this->DemandedPace;
		} else {
			$this->DemandedPace = $this->DemandedTime/$this->RoundDistance;
		}

		if (strlen(Request::param('manual-distances')) > 0)
			$this->ManualDistances = explode(',', Request::param('manual-distances'));

		$this->calculateValues();
	}

	/**
	 * Calculate values
	 */
	protected function calculateValues() {
		$Rounds = $this->Training->GpsData()->getRoundsAsFilledArray( empty($this->ManualDistances) ? $this->RoundDistance : $this->ManualDistances );

		$showCellForHeartrate = $this->Training->GpsData()->hasHeartrateData();
		$showCellForElevation = $this->Training->GpsData()->hasElevationData();

		foreach ($Rounds as $Round) {
			$TimeDifferenceInSeconds  = $this->DemandedTime - $Round['s'];
			$TimeDifferenceClass      = $TimeDifferenceInSeconds >= 0 ? 'plus' : 'minus';
			$TimeDifferenceSign       = $TimeDifferenceInSeconds >= 0 ? '+' : '-';
			$TimeDifferenceString     = '<span class="'.$TimeDifferenceClass.'">'.$TimeDifferenceSign.Time::toString(abs($TimeDifferenceInSeconds), false, 2).'</span>';

			$PaceDifferenceInSeconds  = $this->DemandedPace - $Round['s']/$Round['km'];
			$PaceDifferenceClass      = $PaceDifferenceInSeconds >= 0 ? 'plus' : 'minus';
			$PaceDifferenceSign       = $PaceDifferenceInSeconds >= 0 ? '+' : '-';
			$PaceDifferenceString     = SportFactory::getSpeedWithAppendixAndTooltip(1, abs($PaceDifferenceInSeconds), $this->Training->Sport()->id());
			$PaceDifferenceFullString = '<span class="'.$PaceDifferenceClass.'">'.$PaceDifferenceSign.$PaceDifferenceString.'</span>';

			$this->Data[] = array(
				'time'      => Time::toString($Round['time']),
				'distance'  => Running::Km($Round['distance'], 2),
				'lapdist'	=> Running::Km($Round['km'], 2),
				'laptime'	=> Time::toString($Round['s']),
				'diff'		=> empty($this->ManualDistances) && abs($Round['km'] - $this->RoundDistance) < 0.1 ? $TimeDifferenceString : '-',
				'pace'      => SportFactory::getSpeedWithAppendixAndTooltip($Round['km'], $Round['s'], $this->Training->Sport()->id()),
				'pacediff'	=> $PaceDifferenceFullString,
				'heartrate' => $showCellForHeartrate ? Helper::Unknown($Round['heartrate']) : '-',
				'elevation' => $showCellForElevation ? Math::WithSign($Round['hm-up']).'/'.Math::WithSign(-$Round['hm-down']) : '-'
			);
		}
	}

	/**
	 * Display
	 */
	public function display() {
		$this->displayHeader();
		$this->displayFormular();
		$this->displayRounds();
		$this->displayInformation();
	}

	/**
	 * Display header
	 */
	protected function displayHeader() {
		echo HTML::h1('Zwischenzeiten vom '.$this->Training->DataView()->getTitleWithCommentAndDate());
	}

	/**
	 * Display formular
	 */
	protected function displayFormular() {
		$Formular = new Formular();
		$Formular->setId('rounds-configurator');
		$Formular->addCSSclass('ajax');
		$Formular->addCSSclass('no-automatic-reload');
		$Formular->addFieldset( $this->getConfigurationFieldset() );
		$Formular->addHiddenValue('id', $this->Training->id());
		$Formular->addSubmitButton('Zwischenzeiten anzeigen');
		$Formular->display();

		echo '<p>&nbsp;</p>';
	}

	/**
	 * Get configuration fieldset
	 * @return \FormularFieldset
	 */
	protected function getConfigurationFieldset() {
		$_POST['distance'] = $this->RoundDistance;

		$Fieldset = new FormularFieldset('Einstellungen f&uuml;r die Zwischenzeiten');

		$Distance = new FormularInput('distance', Ajax::tooltip('Zwischenzeit alle ...', 'Rundendistanz, nach der eine Zwischenzeit angezeigt werden soll.'));
		$Distance->setLayout( FormularFieldset::$LAYOUT_FIELD_W50 );
		$Distance->setUnit( FormularUnit::$KM );

		$DemandedTime = new FormularInput('demanded-time', 'Rundenzeit-Vorgabe');
		$DemandedTime->setLayout( FormularFieldset::$LAYOUT_FIELD_W50 );
		$DemandedTime->setPlaceholder('h:mm:ss');
		$DemandedTime->addCSSclass('c');

		$ManualDistances = new FormularInput('manual-distances', Ajax::tooltip('<small>oder:</small> Manuelle Abschnitte', 'Kommagetrennte Liste mit allen Distanzen, f&uuml;r die eine Zwischenzeit erstellt werden soll.'));
		$ManualDistances->setLayout( FormularFieldset::$LAYOUT_FIELD_W50 );
		$ManualDistances->setSize( FormularInput::$SIZE_FULL_INLINE );
		$ManualDistances->setPlaceholder('z.B.: 5, 10, 20, 21.1, 25, 30, 35, 40');

		$DemandedPace = new FormularInput('demanded-pace', '<small>oder:</small> Pace-Vorgabe');
		$DemandedPace->setLayout( FormularFieldset::$LAYOUT_FIELD_W50 );
		$DemandedPace->setUnit( FormularUnit::$PACE );

		$Fieldset->addField($Distance);
		$Fieldset->addField($ManualDistances);
		$Fieldset->addField($DemandedTime);
		$Fieldset->addField($DemandedPace);

		return $Fieldset;
	}

	/**
	 * Display rounds
	 */
	protected function displayRounds() {
		$Fieldset = new FormularFieldset('Zwischenzeiten');
		$Fieldset->setId('rounds');
		$Fieldset->setHtmlCode( $this->getRoundsTable() );
		$Fieldset->display();
	}

	/**
	 * Get rounds table
	 * @return string
	 */
	protected function getRoundsTable() {
		$Cells = array(
			'time'		=> 'Zeit',
			'distance'	=> 'Distanz',
			'laptime'	=> 'Dauer',
			'diff'		=> 'Diff.',
			'pace'		=> 'Tempo',
			'pacediff'	=> 'Diff.',
			'heartrate'	=> '&oslash; bpm',
			'elevation'	=> 'hm'
		);

		$Code  = '<table class="w100">';
		$Code .= '<thead><tr>';

		foreach ($Cells as $Cell)
			$Code .= '<th>'.$Cell.'</th>';

		$Code .= '</tr>';
		$Code .= '</thead>';
		$Code .= '<tbody>';

		foreach ($this->Data as $i => $Round) {
			$Code .= '<tr class="c '.HTML::trClass2($i).'">';

			foreach (array_keys($Cells) as $Cell)
				$Code .= '<td>'.$Round[$Cell].'</td>';

			$Code .= '</tr>';
		}

		$Code .= '</tbody>';
		$Code .= '<tfoot>';
		$Code .= HTML::spaceTR( count($Cells) );
		$Code .= '<tr><td colspan="2" class="r">Schnitt:</td>';
		$Code .= '<td class="c">'.(count($this->ManualDistances) > 0 ? '' : Time::toString( $this->RoundDistance * Time::toSeconds($this->Training->getPace()) )).'</td>';
		$Code .= '<td></td>';
		$Code .= '<td class="c">'.$this->Training->DataView()->getSpeedString().'</td>';
		$Code .= '<td colspan="3"></td>';
		$Code .= '</tr>';
		$Code .= '</tfoot>';
		$Code .= '</table>';

		return $Code;
	}

	/**
	 * Display elevation correction
	 */
	protected function displayInformation() {
		$Fieldset = new FormularFieldset('Hinweis zu den Zwischenzeiten');
		$Fieldset->setId('general-information');
		$Fieldset->setCollapsed();
		$Fieldset->addInfo('
			Die hier angezeigten Zwischenzeiten werden anhand der GPS-Daten berechnet.
			Die manuell abgestoppten Rundenzeiten haben auf diese Auswertung keinen Einfluss.
			Da nicht f&uuml;r jeden Meter ein Datenpunkt existiert, k&ouml;nnen die ausgewerteten Kilometer Rundungsfehler enthalten.
		');

		$Fieldset->display();
	}
}