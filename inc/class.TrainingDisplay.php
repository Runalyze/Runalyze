<?php
/**
 * This file contains the class to handle the displaying for every training.
 */

Config::register('Training', 'TRAINING_PLOTS_BELOW', 'bool', false, 'Diagramme untereinander anstatt im Wechsel anzeigen');

/**
* Class: TrainingDisplay
*
* @author Hannes Christiansen <mail@laufhannes.de>
* @version 1.0
* @uses class::Mysql
* @uses class::Error
* @uses class::HTML
* @uses class::Helper
*/
class TrainingDisplay {
	/**
	 * Path to file for displaying the map (used for iframe)
	 * @var string
	 */
	public static $mapURL = 'inc/tcx/window.map.php';

	/**
	 * Minimum distance to be shown as a zone
	 * @var double
	 */
	private static $MINIMUM_DISTANCE_FOR_ZONE = 0.1;

	/**
	 * Object for training
	 * @var Training
	 */
	private $Training;

	/**
	 * Constructor
	 * @param Training $Training
	 */
	public function __construct($Training) {
		$this->Training = $Training;
	}

	/**
	 * Display the whole training
	 */
	public function display() {
		$this->displayHeader();
		$this->displayPlotsAndMap();
		$this->displayTrainingData();

		echo HTML::clearBreak();
	}

	/**
	 * Display header
	 */
	public function displayHeader() {
		echo '<h1>'.NL;
		$this->displayEditLink();
		$this->Training->displayTitle();

		echo '<small class="right">';
		$this->Training->displayDate();
		echo '</small>';
		echo HTML::clearBreak();

		echo '</h1>'.NL;
	}

	/**
	 * Display plot links, first plot and map
	 */
	public function displayPlotsAndMap() {
		$Plots = $this->getPlotTypesAsArray();

		echo '<div class="right">'.NL;
		if (!empty($Plots)) {
			if (CONF_TRAINING_PLOTS_BELOW) {
				foreach ($Plots as $Key => $Plot) {
					$this->displayPlot($Key, false);
					echo '<br />'.NL;
				}
			} else {
				reset($Plots);
				$firstKey = key($Plots);

				echo '<small class="right margin-5">'.NL;
					$this->displayPlotLinks();
				echo '</small>'.NL;
				echo '<br /><br />'.NL;
				echo '<div id="trainingPlots" class="flotChangeable" style="position:relative;width:482px;height:192px;margin:2px auto;">';
				foreach ($Plots as $Key => $Plot)
					$this->displayPlot($Key, $Key != $firstKey);
				echo '</div>';
			}
			echo '<br /><br />'.NL;
		}

		if ($this->Training->hasPositionData())
			$this->displayRoute();

		echo '</div>'.NL;
	}

	/**
	 * Get array for all plot types
	 * @return array
	 */
	private function getPlotTypesAsArray() {
		$plots = array();
		if ($this->Training->hasPaceData())
			$plots['pace'] = array('name' => 'Pace', 'key' => 'pace', 'src' => 'inc/draw/training.pace.php?id='.$this->Training->get('id'));
		if ($this->Training->hasSplitsData())
			$plots['splits'] = array('name' => 'Splits', 'key' => 'splits', 'src' => 'inc/draw/training.splits.php?id='.$this->Training->get('id'));
		if ($this->Training->hasPulseData())
			$plots['pulse'] = array('name' => 'Puls', 'key' => 'pulse', 'src' => 'inc/draw/training.heartrate.php?id='.$this->Training->get('id'));
		if ($this->Training->hasElevationData())
			$plots['elevation'] = array('name' => 'H&ouml;henprofil', 'key' => 'elevation', 'col' => 'arr_alt', 'src' => 'inc/draw/training.elevation.php?id='.$this->Training->get('id'));

		return $plots;
	}

	/**
	 * Display links for all plots
	 */
	public function displayPlotLinks() {
		$links = array();
		$plots = $this->getPlotTypesAsArray();

		foreach ($plots as $key => $array)
			$links[] = Ajax::flotChange($array['name'], 'trainingPlots', $array['key'].'_'.$this->Training->get('id'));

		echo implode(' | ', $links);
	}

	/**
	 * Display a plot
	 * @param string $type name of the plot, should be in getPlotTypesAsArray
	 * @param bool $hidden
	 */
	public function displayPlot($type = 'undefined', $hidden = false) {
		$plots = $this->getPlotTypesAsArray();
		if (isset($plots[$type])) {
			echo Plot::getInnerDivFor($plots[$type]['key'].'_'.$this->Training->get('id'), 480, 190, $hidden);
			include FRONTEND_PATH.'draw/Plot.Training.'.$plots[$type]['key'].'.php';
		} else
			Error::getInstance()->addWarning('TrainingDisplay::displayPlot - Unknown plottype "'.$type.'"', __FILE__, __LINE__);
	}

	/**
	 * Display training data
	 */
	public function displayTrainingData() {
		$this->Training->displayTable();
		$this->displayRoundsContainer();
		$this->displayPaceZones();
		$this->displayPulseZones();
	}

	/**
	 * Display pace-zones
	 */
	public function displayPaceZones() {
		$Data = array();
		$Zones = $this->Training->GpsData()->getPaceZonesAsFilledArrays();

		foreach ($Zones as $min => $Info) {
			if ($Info['distance'] > self::$MINIMUM_DISTANCE_FOR_ZONE)
				$Data[] = array(
					'zone'     => ($min == 0 ? 'schneller' : 'bis '.Helper::Pace(1, $min*60).'/km'),
					'time'     => $Info['time'],
					'distance' => $Info['distance'],
					'average'  => round(100*$Info['hf-sum']/Helper::getHFmax()/$Info['num']).'&nbsp;&#37;');
		}

		$this->displayZone('Tempozonen', $Data, '&oslash; Puls');
	}

	/**
	 * Display pace-zones
	 */
	public function displayPulseZones() {
		$Data = array();
		$Zones = $this->Training->GpsData()->getPulseZonesAsFilledArrays();

		foreach ($Zones as $hf => $Info) {
			if ($Info['distance'] > self::$MINIMUM_DISTANCE_FOR_ZONE)
				$Data[] = array(
					'zone'     => 'bis '.(10*$hf).'&nbsp;&#37;',
					'time'     => $Info['time'],
					'distance' => $Info['distance'],
					'average'  => Helper::Pace($Info['num'], $Info['pace-sum']).'/km');
		}

		$this->displayZone('Pulszonen', $Data, 'Pace');
	}

	/**
	 * Display pace-zones
	 */
	public function displayZone($title, $Data, $titleForAverage = '') {
		$showCellForAverageData = ($titleForAverage != '');
		$totalTime = 0;

		if (empty($Data))
			return;

		foreach ($Data as $i => $Info)
			$totalTime += $Info['time'];

		foreach ($Data as $i => $Info) {
			$Data[$i]['percentage'] = round(100 * $Info['time'] / $totalTime, 1);
			$Data[$i]['time']       = Helper::Time($Info['time'], false, $Info['time'] < 60 ? 2 : false);
			$Data[$i]['distance']   = Helper::Km($Info['distance'], 2);
		}

		include 'tpl/tpl.Training.zone.php';
	}

	/**
	 * Display surrounding container for rounds-data
	 */
	public function displayRoundsContainer() {
		$RoundTypes = array();
		if ($this->Training->hasPaceData())
			$RoundTypes[] = array('name' => 'berechnete', 'id' => 'computedRounds', 'eval' => '$this->displayRounds();');
		if ($this->Training->hasSplitsData())
			$RoundTypes[] = array('name' => 'gestoppte', 'id' => 'stoppedRounds', 'eval' => '$this->displaySplits();');

		if (empty($RoundTypes))
			return;

		$RoundLinksArray = array();
		foreach ($RoundTypes as $i => $RoundType)
			$RoundLinksArray[] = Ajax::change($RoundType['name'], 'trainingRounds', $RoundType['id']);
		$RoundLinks = implode(' | ', $RoundLinksArray);

		include 'tpl/tpl.Training.roundContainer.php';
	}

	/**
	 * Display defined splits
	 */
	public function displaySplits() {
		// TODO: Clean Code - will be done with new splits-system
		echo '<table class="small" cellspacing="0">
			<tr class="c b">
				<td>Distanz</td>
				<td>Zeit</td>
				<td>Pace</td>
				<td>Diff.</td>
			</tr>
			<tr class="space"><td colspan="4" /></tr>'.NL;

		$splits       = explode('-', str_replace('\r\n', '-', $this->Training->get('splits')));
		$Distances    = $this->Training->getSplitsDistancesArray();
		$Times        = $this->Training->getSplitsTimeArray();
		$Paces        = $this->Training->getSplitsPacesArray();
		$demandedPace = Helper::DescriptionToDemandedPace($this->Training->get('comment'));
		$achievedPace = array_sum($Paces) / count($Paces);
		$TimeSum      = array_sum($Times);
		$DistSum      = array_sum($Distances);

		for ($i = 0, $num = count($Distances); $i < $num; $i++) {
			$PaceDiff = ($demandedPace != 0) ? ($demandedPace - $Paces[$i]) : ($achievedPace - $Paces[$i]);
			$PaceClass = ($PaceDiff >= 0) ? 'plus' : 'minus';
			$PaceDiffString = ($PaceDiff >= 0) ? '+'.Helper::Time($PaceDiff, false, 2) : '-'.Helper::Time(-$PaceDiff, false, 2);

			echo '
			<tr class="a'.($i%2+2).' r">
				<td>'.Helper::Km($Distances[$i], 2).'</td>
				<td>'.Helper::Time($Times[$i]).'</td>
				<td>'.Helper::Pace($Distances[$i], $Times[$i]).'/km</td>
				<td class="'.$PaceClass.'">'.$PaceDiffString.'/km</td>
			</tr>'.NL;
		}

		echo HTML::spaceTR(4);

		if ($demandedPace > 0) {
			$AvgDiff = $demandedPace - $achievedPace;
			$AvgClass = ($AvgDiff >= 0) ? 'plus' : 'minus';
			$AvgDiffString = ($AvgDiff >= 0) ? '+'.Helper::Time($AvgDiff, false, 2) : '-'.Helper::Time(-$AvgDiff, false, 2);
	
			echo '
				<tr class="r">
					<td colspan="2">Vorgabe: </td>
					<td>'.Helper::Time($demandedPace).'/km</td>
					<td class="'.$AvgClass.'">'.$AvgDiffString.'/km</td>
				</tr>'.NL;
		}
	
		echo '
			<tr class="r">
				<td colspan="2">Schnitt: </td>
				<td>'.Helper::Time($achievedPace).'/km</td>
				<td></td>
			</tr>'.NL;

		echo '</table>'.NL;
	}

	/**
	 * Display (computed) rounds
	 */
	public function displayRounds() {
		$Data   = array();
		$Rounds = $this->Training->GpsData()->getRoundsAsFilledArray();
		$showCellForHeartrate = $this->Training->GpsData()->hasHeartrateData();
		$showCellForElevation = $this->Training->GpsData()->hasElevationData();

		foreach ($Rounds as $i => $Round) {
			$Data[] = array(
				'time'      => Helper::Time($Round['time']),
				'distance'  => Helper::Km($Round['distance'], 2),
				'pace'      => Helper::Speed($Round['km'], $Round['s'], $this->Training->get('sportid')),
				'heartrate' => Helper::Unknown($Round['heartrate']),
				'elevation' => Helper::WithSign($Round['hm-up']).'/'.Helper::WithSign(-$Round['hm-down']));
		}
		
		include 'tpl/tpl.Training.round.php';
	}

	/**
	 * Display route on GoogleMaps
	 */
	public function displayRoute() {
		$Map = new Gmap($this->Training->get('id'), $this->Training->GpsData());
		$Map->displayMap();
	}

	/**
	 * Display link for edit window
	 */
	public function displayEditLink() {
		echo Ajax::window('<a href="call/call.Training.edit.php?id='.$this->Training->get('id').'">'.Icon::get(Icon::$EDIT, '', '', 'Training bearbeiten').'</a> ','small');
	}

	/**
	 * Display link for edit window
	 * @param int $id
	 * @return string
	 */
	static public function getSmallEditLinkFor($id) {
		return Ajax::window('<a href="call/call.Training.edit.php?id='.$id.'">'.Icon::get(Icon::$EDIT_SMALL, '').'</a> ','small');
	}
}
?>