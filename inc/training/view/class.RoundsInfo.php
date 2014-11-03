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

			$SpeedUnit                = SportFactory::getSpeedUnitFor($this->Training->Sport()->id());
			$DemandedPace             = SportSpeed::getSpeed(1, $this->DemandedPace, $SpeedUnit);
			$AchievedPace             = SportSpeed::getSpeed($Round['km'], $Round['s'], $SpeedUnit);
			$PaceDifferenceFullString = SportSpeed::difference($SpeedUnit, $DemandedPace, $AchievedPace);

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
		echo '<div class="panel-heading">';
		$this->displayHeader();
		echo '</div>';

		echo '<div class="panel-content">';
		$this->displayFormular();
		$this->displayRounds();
		$this->displayInformation();
		echo '</div>';
	}

	/**
	 * Display header
	 */
	protected function displayHeader() {
		echo HTML::h1( sprintf( __('Splits from: %s'), $this->Training->DataView()->getTitleWithCommentAndDate() ) );
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
		$Formular->addSubmitButton( __('Show splits') );
		$Formular->display();

		echo '<p>&nbsp;</p>';
	}

	/**
	 * Get configuration fieldset
	 * @return \FormularFieldset
	 */
	protected function getConfigurationFieldset() {
		$_POST['distance'] = $this->RoundDistance;

		$Fieldset = new FormularFieldset( __('Calculate splits') );

		$Distance = new FormularInput('distance', Ajax::tooltip(__('Lap every ...'), __('Distance, after which a new lap should start') ) );
		$Distance->setLayout( FormularFieldset::$LAYOUT_FIELD_W50 );
		$Distance->setUnit( FormularUnit::$KM );

		$DemandedTime = new FormularInput('demanded-time', __('Lap time goal'));
		$DemandedTime->setLayout( FormularFieldset::$LAYOUT_FIELD_W50 );
		$DemandedTime->setPlaceholder('h:mm:ss');
		$DemandedTime->addCSSclass('c');

		$ManualDistances = new FormularInput('manual-distances', Ajax::tooltip('<small>'.__('or').':</small> '.__('Manual laps'), __('List with all distances, comma seperated') ));
		$ManualDistances->setLayout( FormularFieldset::$LAYOUT_FIELD_W50 );
		$ManualDistances->setSize( FormularInput::$SIZE_FULL_INLINE );
		$ManualDistances->setPlaceholder('z.B.: 5, 10, 20, 21.1, 25, 30, 35, 40');

		$DemandedPace = new FormularInput('demanded-pace', '<small>'.__('or').':</small> '.__('Pace goal') );
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
		$Fieldset = new FormularFieldset( __('Laps') );
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
			'time'		=> __('Time'),
			'distance'	=> __('Distance'),
			'laptime'	=> __('Duration'),
			'diff'		=> __('Diff.'),
			'pace'		=> __('Pace'),
			'pacediff'	=> __('Diff'),
			'heartrate'	=> __('&oslash; bpm'),
			'elevation'	=> __('elev')
		);

		$Code  = '<table class="fullwidth zebra-style zebra-blue">';
		$Code .= '<thead><tr>';

		foreach ($Cells as $Cell)
			$Code .= '<th>'.$Cell.'</th>';

		$Code .= '</tr>';
		$Code .= '</thead>';
		$Code .= '<tbody class="top-and-bottom-border">';

		foreach ($this->Data as $Round) {
			$Code .= '<tr class="c">';

			foreach (array_keys($Cells) as $Cell)
				$Code .= '<td>'.$Round[$Cell].'</td>';

			$Code .= '</tr>';
		}

		$Code .= '</tbody>';
		$Code .= '<tbody>';
		$Code .= '<tr class="no-zebra"><td colspan="2" class="r">'.__('Average').':</td>';
		$Code .= '<td class="c">'.(count($this->ManualDistances) > 0 ? '' : Time::toString( $this->RoundDistance * Time::toSeconds($this->Training->getPace()) )).'</td>';
		$Code .= '<td></td>';
		$Code .= '<td class="c">'.$this->Training->DataView()->getSpeedString().'</td>';
		$Code .= '<td colspan="3"></td>';
		$Code .= '</tr>';
		$Code .= '</tbody>';
		$Code .= '</table>';

		return $Code;
	}

	/**
	 * Display elevation correction
	 */
	protected function displayInformation() {
		$Fieldset = new FormularFieldset( __('Note') );
		$Fieldset->setId('general-information');
		$Fieldset->setCollapsed();
		$Fieldset->addInfo(
			__('These laps are computed based on your gps data.').
			__('Laps stopped by hand are ignored in this evaluation.').
			__('Since there is not a data point for every single meter there may be some deviation from the distance.')
		);

		$Fieldset->display();
	}
}