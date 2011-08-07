<?php
/**
 * This file contains the class to handle the displaying for every training.
 */

Config::register('Training', 'TRAINING_MAP_COLOR', 'string', '#FF5500', 'Linienfarbe auf GoogleMaps-Karte (#RGB)');
Config::register('Training', 'TRAINING_MAP_MARKER', 'bool', true, 'Kilometer-Markierungen anzeigen');
Config::register('Training', 'TRAINING_MAPTYPE', 'select',
	array('G_NORMAL_MAP' => false, 'G_HYBRID_MAP' => true, 'G_SATELLITE_MAP' => false, 'G_PHYSICAL_MAP' => false), 'Typ der GoogleMaps-Karte',
	array('Normal', 'Hybrid', 'Sattelit', 'Physikalisch'));

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
			echo '<small class="right">'.NL;
			$this->displayPlotLinks('trainingGraph');
			echo '</small>'.NL;
			echo '<br /><br />'.NL;
			$this->displayPlot(key($Plots));
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
			$plots['pace'] = array('name' => 'Pace', 'src' => 'inc/draw/training.pace.php?id='.$this->Training->get('id'));
		if ($this->Training->hasSplitsData())
			$plots['splits'] = array('name' => 'Splits', 'src' => 'inc/draw/training.splits.php?id='.$this->Training->get('id'));
		if ($this->Training->hasPulseData())
			$plots['pulse'] = array('name' => 'Puls', 'src' => 'inc/draw/training.heartrate.php?id='.$this->Training->get('id'));
		if ($this->Training->hasElevationData())
			$plots['elevation'] = array('name' => 'H&ouml;henprofil', 'col' => 'arr_alt', 'src' => 'inc/draw/training.elevation.php?id='.$this->Training->get('id'));

		return $plots;
	}

	/**
	 * Display links for all plots
	 * @param string $rel related string (id of img)
	 */
	public function displayPlotLinks($rel = 'trainingGraph') {
		$links = array();
		$plots = $this->getPlotTypesAsArray();

		foreach ($plots as $key => $array)
			$links[] = Ajax::imgChange('<a href="'.$array['src'].'">'.$array['name'].'</a>', 'trainingGraph').NL;

		echo implode(' | ', $links);
	}

	/**
	 * Display a plot
	 * @param string $type name of the plot, should be in getPlotTypesAsArray
	 */
	public function displayPlot($type = 'undefined') {
		$plots = $this->getPlotTypesAsArray();
		if (isset($plots[$type])) {
			$img = '<img id="trainingGraph" src="'.$plots[$type]['src'].'" alt="'.$plots[$type]['name'].'" />';
			echo HTML::wrapImgForLoading($img, 480, 190);
		} else
			Error::getInstance()->addWarning('TrainingDisplay::displayPlot - Unknown plottype "'.$type.'"', __FILE__, __LINE__);
	}

	/**
	 * Display training data
	 */
	public function displayTrainingData() {
		$this->Training->displayTable();
		$this->displayRoundsContainer();
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

		echo '<div id="trainingRounds">' ;
			echo '<strong class="small">Rundenzeiten:&nbsp;</strong>'.NL;
			echo '<small class="right">'.NL;
				foreach ($RoundTypes as $i => $RoundType) {
					echo Ajax::change($RoundType['name'], 'trainingRounds', $RoundType['id']);
					if ($i < count($RoundTypes)-1)
						echo ' | ';
				}
			echo '&nbsp;</small>'.NL;

			if (empty($RoundTypes))
				echo '<small><em>Keine Daten vorhanden.</em></small>'.NL;

			foreach ($RoundTypes as $i => $RoundType) {
				echo '<div id="'.$RoundType['id'].'" class="change"'.($i==0?'':' style="display:none;"').'>';
					eval($RoundType['eval']);
				echo '</div>';
			}
		echo '</div>';
	}

	/**
	 * Display defined splits
	 */
	public function displaySplits() {
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
		$km 				= 1;
		$kmIndex	 		= array(0);
		$positiveElevation 	= 0;
		$negativeElevation 	= 0;
		$distancePoints 	= explode(Training::$ARR_SEP, $this->Training->get('arr_dist'));
		$timePoints 		= explode(Training::$ARR_SEP, $this->Training->get('arr_time'));
		$heartPoints 		= explode(Training::$ARR_SEP, $this->Training->get('arr_heart'));
		$elevationPoints 	= explode(Training::$ARR_SEP, $this->Training->get('arr_alt'));
		$numberOfPoints 	= sizeof($distancePoints);
		$rounds             = array();
		$showPulse          = count($heartPoints) > 1;
		$showElevation      = count($elevationPoints) > 1;

		foreach ($distancePoints as $i => $distance) {
			if (floor($distance) == $km || $i == $numberOfPoints-1) {
				$km++;
				$prevIndex = end($kmIndex);
				$kmIndex[] = $i;

				if ($showPulse) {
					$heartRateOfThisKm = array_slice($heartPoints, $prevIndex, ($i - $prevIndex));
					$bpm = round(array_sum($heartRateOfThisKm) / ($i - $prevIndex));
				} else
					$bpm = 0;

				$rounds[] = array(
					'time' => $timePoints[$i],
					'dist' => $distance,
					'km' => $distance - $distancePoints[$prevIndex],
					's' => $timePoints[$i] - $timePoints[$prevIndex],
					'bpm' => $bpm,
					'hm_up' => $positiveElevation,
					'hm_down' => $negativeElevation,
					);

				$positiveElevation = 0;
				$negativeElevation = 0;
			} elseif ($i != 0 && $showElevation && $elevationPoints[$i] != 0 && $elevationPoints[$i-1] != 0) {
				$elevationDifference = $elevationPoints[$i] - $elevationPoints[$i-1];
				$positiveElevation += ($elevationDifference > Training::$minElevationDiff) ? $elevationDifference : 0;
				$negativeElevation -= ($elevationDifference < -1*Training::$minElevationDiff) ? $elevationDifference : 0;
			}
		}

		$this->displayRoundsTable($rounds, $showPulse, $showElevation);
	}

	/**
	 * Display the table for all rounds
	 * @param array $rounds Array containing all rounds
	 * @param bool $showPulse Flag: Show heartfrequence?
	 * @param bool $showElevation Flag: Show elevation-data?
	 */
	private function displayRoundsTable($rounds, $showPulse, $showElevation) {
		echo '<table class="small" cellspacing="0">
			<tr class="c b">
				<td>Zeit</td>
				<td>Distanz</td>
				<td>Tempo</td>
				'.($showPulse ? '<td>bpm</td>' : '').'
				'.($showElevation ? '<td>hm</td>' : '').'
			</tr>'.NL;
		echo HTML::spaceTR(3 + (int)$showPulse + (int)$showElevation);

		foreach ($rounds as $i => $round) {
			if ($round['bpm'] == 0)
				$round['bpm'] = '?';
			if ($round['hm_up'] != 0)
				$round['hm_up'] = '+'.$round['hm_up'];
			if ($round['hm_down'] != 0)
				$round['hm_down'] = '-'.$round['hm_down'];

			echo '<tr class="a'.($i%2+2).' r">
				<td>'.Helper::Time($round['time']).'</td>
				<td>'.Helper::Km($round['dist'], 2).'</td>
				<td>'.Helper::Speed($round['km'], $round['s'], $this->Training->get('sportid')).'</td>
				'.($showPulse ? '<td>'.$round['bpm'].'</td>' : '').'
				'.($showElevation ? '<td>'.$round['hm_up'].'/'.$round['hm_down'].'</td>' : '').'
			</tr>'.NL;
		}

		echo '</table>'.NL;
	}

	/**
	 * Display route on GoogleMaps
	 */
	public function displayRoute() {
		echo '<iframe src="'.self::$mapURL.'?id='.$this->Training->get('id').'" style="border:1px solid #000;" width="478" height="300" frameborder="0"></iframe>';
	}

	/**
	 * Display link for edit window
	 */
	public function displayEditLink() {
		echo Ajax::window('<a href="call/call.Training.edit.php?id='.$this->Training->get('id').'" title="Training editieren">'.Icon::get(Icon::$EDIT, 'Training editieren').'</a> ','small');
	}
}
?>