<?php
/**
 * This file contains the class to handle every training.
 */
/**
 * Class: Stat
 * 
 * @author Hannes Christiansen <mail@laufhannes.de>
 * @version 1.0
 * @uses class::Mysql
 * @uses class:Error
 * @uses $global
 *
 * Last modified 2011/03/15 10:30 by Hannes Christiansen
 */

class Training {
	/**
	 * Minimal difference per step to be recognized for elevation data
	 * @var int
	 */
	public static $minElevationDiff = 3;

	/**
	 * Only every n-th point will be taken for the elevation
	 * @var int
	 */
	public static $everyNthElevationPoint = 5;

	/**
	 * Internal ID in database
	 * @var int
	 */
	private $id;

	/**
	 * Data array from database
	 * @var array
	 */
	private $data;

	/**
	 * Constructor (needs ID, can be -1 for set($var) on it's own
	 * @param int $id
	 */
	public function __construct($id) {
		global $global;

		if ($id == -1) {
			$this->id = -1;
			$this->data = array();
			return;
		}

		if (!is_numeric($id) || $id == NULL) {
			Error::getInstance()->addError('An object of class::Training must have an ID: <$id='.$id.'>');
			return false;
		}

		$dat = Mysql::getInstance()->fetch('ltb_training', $id);
		if ($dat === false) {
			Error::getInstance()->addError('This training (ID='.$id.') does not exist.');
			return false;
		}

		$this->id = $id;
		$this->data = $dat;

		if ($this->data['vdot'] != 0)
			$this->data['vdot'] = JD::correctVDOT($this->data['vdot']);
	}

	/**
	 * Set a column
	 * @param string $var
	 * @param string $value
	 */
	public function set($var, $value) {
		if ($this->id != -1) {
			Error::getInstance()->addWarning('Training::set - can\'t set value, Training already loaded');
			return;
		}

		$this->data[$var] = $value;
	}

	/**
	 * Get a column from DB-row
	 * @param string $var wanted column from database
	 * @return mixed
	 */
	public function get($var) {
		if (isset($this->data[$var]))
			return $this->data[$var];

		// 'temperatur' is set as NULL on default and will fail on above test
		if ($var != 'temperatur')
			Error::getInstance()->addWarning('Training::get - unknown column "'.$var.'"',__FILE__,__LINE__);
	}

	/**
	 * Get string for clothes
	 * @return string all clothes comma seperated
	 */
	public function getStringForClothes() {
		if ($this->get('kleidung') != '') {
			$kleidungen = array();
			$kleidungen_data = Mysql::getInstance()->fetch('SELECT `name` FROM `ltb_kleidung` WHERE `id` IN ('.$this->get('kleidung').') ORDER BY `order` ASC', false, true);
			foreach ($kleidungen_data as $data)
				$kleidungen[] = $data['name'];
			return implode(', ', $kleidungen);
		}

		return '';
	}

	/**
	 * Gives a HTML-link for using jTraining which is calling the training-tpl
	 * @param string $name displayed link name
	 * @return string HTML-link to this training
	 */
	public function trainingLink($name) {
		return Ajax::trainingLink($this->id, $name);
	}

	/**
	 * Display the whole training
	 */
	public function display() {
		$this->displayHeader();
		$this->displayPlotsAndMap();
		$this->displayTrainingData();
	}

	/**
	 * Display header
	 */
	public function displayHeader() {
		echo('<h1>'.NL);
		$this->displayEditLink();
		$this->displayTitle();
		echo('<small class="right">');
		$this->displayDate();
		echo('</small><br class="clear" />');
		echo('</h1>'.NL.NL.NL);
	}

	/**
	 * Display plot links, first plot and map
	 */
	public function displayPlotsAndMap() {
		$plots = $this->getPlotTypesAsArray();

		echo('<div class="right">'.NL);
		if (count($plots) > 0) {
			echo('<small class="right">'.NL);
			$this->displayPlotLinks('trainingGraph');
			echo('</small>'.NL);
			echo('<br /><br />'.NL);
			$this->displayPlot(key($plots));
			echo('<br />'.NL);
			echo('<br />'.NL.NL);
		}

		if ($this->hasPositionData()) {
			$this->displayRoute();
		}
		echo('</div>'.NL.NL);
	}

	/**
	 * Display training data
	 */
	public function displayTrainingData() {
		$this->displayTable();

		if ($this->get('distanz') > 0)
			$this->displayRoundsContainer();
	}

	/**
	 * Display the title for this training
	 * @param bool $short short version without description, default: false
	 */
	public function displayTitle($short = false) {
		echo $this->getTitle();
		if (!$short) {
			if ($this->get('laufabc') == 1)
				echo(' <img src="img/abc.png" alt="Lauf-ABC" />');
			if ($this->get('bemerkung') != '')
				echo (': '.$this->get('bemerkung'));
		}
	}

	/**
	 * Get the title for this training
	 * @return string
	 */
	public function getTitle() {
		return ($this->get('sportid') == RUNNINGSPORT)
			? Helper::TypeName($this->get('typid'))
			: Helper::Sport($this->get('sportid'));
	}

	/**
	 * Display the formatted date
	 */
	public function displayDate() {
		echo (Helper::Weekday( date('w', $this->get('time')) ).', '.$this->getDate());
	}

	/**
	 * Get the date for this training
	 */
	public function getDate() {
		$time = $this->get('time');
		return date('H:i', $time) != '00:00'
			? date('d.m.Y, H:i', $time).' Uhr'
			: date('d.m.Y', $time);
	}

	/**
	 * Get array for all plot types
	 * @return array
	 */
	private function getPlotTypesAsArray() {
		$plots = array();
		if ($this->hasPaceData())
			$plots['pace'] = array('name' => 'Pace', 'src' => 'inc/draw/training.pace.php?id='.$this->id);
		if ($this->hasSplitsData())
			$plots['splits'] = array('name' => 'Splits', 'src' => 'inc/draw/training.splits.php?id='.$this->id);
		if ($this->hasPulseData())
			$plots['pulse'] = array('name' => 'Puls', 'src' => 'inc/draw/training.heartrate.php?id='.$this->id);
		if ($this->hasElevationData())
			$plots['elevation'] = array('name' => 'H&ouml;henprofil', 'col' => 'arr_alt', 'src' => 'inc/draw/training.elevation.php?id='.$this->id);

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
			$links[] = '<a class="jImg" rel="trainingGraph" href="'.$array['src'].'">'.$array['name'].'</a>'.NL;
		echo implode(' | ', $links);
	}

	/**
	 * Display a plot
	 * @param string $type name of the plot, should be in getPlotTypesAsArray
	 */
	public function displayPlot($type = 'undefined') {
		$plots = $this->getPlotTypesAsArray();
		if (isset($plots[$type]))
			echo '<div class="bigImg" style="height:190px; width:480px;"><img id="trainingGraph" src="'.$plots[$type]['src'].'" alt="'.$plots[$type]['name'].'" /></div>'.NL;
		else
			Error::getInstance()->addWarning('Training::displayPlot - Unknown plottype "'.$type.'"', __FILE__, __LINE__);
	}

	/**
	 * Display table with all training data
	 */
	public function displayTable() {
		include('tpl/tpl.Training.table.php');
	}

	/**
	 * Display surrounding container for rounds-data
	 */
	public function displayRoundsContainer() {
		$RoundTypes = array();
		if ($this->hasPaceData())
			$RoundTypes[] = array('name' => 'berechnete', 'id' => 'computedRounds', 'eval' => '$this->displayRounds();');
		if ($this->hasSplitsData())
			$RoundTypes[] = array('name' => 'gestoppte', 'id' => 'stoppedRounds', 'eval' => '$this->displaySplits();');

		echo('<div id="trainingRounds">');
			echo('<strong>Rundenzeiten:</strong>');
			echo('<small class="right">');
			foreach ($RoundTypes as $i => $RoundType) {
				echo Ajax::change($RoundType['name'], 'trainingRounds', $RoundType['id']);
				if ($i < count($RoundTypes)-1)
					echo(' | ');
			}
			echo('&nbsp;</small>');

			if (empty($RoundTypes))
				echo('<small><em>Keine Daten vorhanden.</em></small>');
			foreach ($RoundTypes as $i => $RoundType) {
				echo('<div id="'.$RoundType['id'].'" class="change"'.($i==0?'':' style="display:none;"').'>');
				eval($RoundType['eval']);
				echo('</div>');
			}
		echo('</div>');
	}

	/**
	 * Get an array with all times (in seconds) of the splits
	 * @return array
	 */
	public function getSplitsTimeArray() {
		$array = array();
		$splits = explode('-', str_replace('\r\n', '-', $this->get('splits')));

		for ($i = 0, $num = count($splits); $i < $num; $i++) {
			$split = explode('|', $splits[$i]);
			$timedata = explode(':', $split[1]);
			$array[] = $timedata[0]*60 + $timedata[1];
		}

		return $array;
	}

	/**
	 * Get an array with all paces (in min/km) of the splits
	 * @return array
	 */
	public function getSplitsPacesArray() {
		$paces = array();
		$times = $this->getSplitsTimeArray();
		$distances = $this->getSplitsDistancesArray();

		for ($i = 0, $n = count($times); $i < $n; $i++)
			$paces[] = round($times[$i]/$distances[$i]);

		return $paces;
	}

	/**
	 * Get an array with all distances (in kilometer) of the splits
	 * @return array
	 */
	public function getSplitsDistancesArray() {
		$array = array();
		$splits = explode('-', str_replace('\r\n', '-', $this->get('splits')));

		for ($i = 0, $num = count($splits); $i < $num; $i++) {
			$split = explode('|', $splits[$i]);
			$array[] = $split[0];
		}

		return $array;
	}

	/**
	 * Get all splits as a string: '1 km in 3:20, ...'
	 * @return string
	 */
	public function getSplitsAsString() {
		$splits = explode('-', str_replace('\r\n', '-', $this->get('splits')));
		foreach ($splits as $i => $split) {
			$splits[$i] = str_replace('|', ' km in ', $split);
		}

		return implode(', ', $splits);
	}

	/**
	 * Display defined splits
	 */
	public function displaySplits() {
		echo('<table class="small" cellspacing="0">'.NL);
		echo('
			<tr class="c b">
				<td>Distanz</td>
				<td>Zeit</td>
				<td>Pace</td>
				<td>Diff.</td>
			</tr>
			<tr class="space"><td colspan="4" /></tr>');

		$splits = explode('-', str_replace('\r\n', '-', $this->get('splits')));
		Error::getInstance()->addTodo('Training::splits Bitte testen: Ist die Pace-Berechnung korrekt?', __FILE__, __LINE__);
		Error::getInstance()->addTodo('Training::splits Gesamtschnitt/Vorgabe/etc.', __FILE__, __LINE__);

		$Distances = $this->getSplitsDistancesArray();
		$Times = $this->getSplitsTimeArray();
		$Paces = $this->getSplitsPacesArray();
		$demandedPace = Helper::DescriptionToDemandedPace($this->get('bemerkung'));
		$achievedPace = array_sum($Paces) / count($Paces);
		$TimeSum = array_sum($Times);
		$DistSum = array_sum($Distances);

		for ($i = 0, $num = count($Distances); $i < $num; $i++) {
			$PaceDiff = ($demandedPace != 0) ? ($demandedPace - $Paces[$i]) : ($achievedPace - $Paces[$i]);
			$PaceClass = ($PaceDiff >= 0) ? 'plus' : 'minus';
			$PaceDiffString = ($PaceDiff >= 0) ? '+'.Helper::Time($PaceDiff, false, 2) : '-'.Helper::Time(-$PaceDiff, false, 2);

			echo('
			<tr class="a'.($i%2+1).' r">
				<td>'.Helper::Km($Distances[$i], 2).'</td>
				<td>'.Helper::Time($Times[$i]).'</td>
				<td>'.Helper::Pace($Distances[$i], $Times[$i]).'/km</td>
				<td class="'.$PaceClass.'">'.$PaceDiffString.'/km</td>
			</tr>');
		}

		if ($demandedPace > 0) {
			$AvgDiff = $demandedPace - $achievedPace;
			$AvgClass = ($AvgDiff >= 0) ? 'plus' : 'minus';
			$AvgDiffString = ($AvgDiff >= 0) ? '+'.Helper::Time($AvgDiff, false, 2) : '-'.Helper::Time(-$AvgDiff, false, 2);
	
			echo('
				<tr class="space"><td colspan="4" /></tr>
				<tr class="r">
					<td colspan="2">Vorgabe: </td>
					<td >'.Helper::Time($demandedPace).'/km</td>
					<td class="'.$AvgClass.'">'.$AvgDiffString.'/km</td>
				</tr>');
		} else {
			echo('
				<tr class="r">
					<td colspan="2">Schnitt: </td>
					<td>'.Helper::Time($achievedPace).'/km</td>
					<td></td>
				</tr>');
		}

		echo('</table>'.NL);
	}

	/**
	 * Display (computed) rounds
	 */
	public function displayRounds() {
		$km 				= 1;
		$kmIndex	 		= array(0);
		$positiveElevation 	= 0;
		$negativeElevation 	= 0;
		$distancePoints 	= explode('|', $this->get('arr_dist'));
		$timePoints 		= explode('|', $this->get('arr_time'));
		$heartPoints 		= explode('|', $this->get('arr_heart'));
		$elevationPoints 	= explode('|', $this->get('arr_alt'));
		$numberOfPoints 	= sizeof($distancePoints);

		echo('<table class="small" cellspacing="0">'.NL);
		echo('<tr class="c b">
				<td>Zeitpunkt</td>
				<td>Distanz</td>
				<td>Pace</td>');
		if (count($heartPoints) > 1)
			echo(NL.'<td>bpm</td>');
		if (count($elevationPoints) > 1)
			echo(NL.'<td>hm</td>');
		echo(NL.'</tr>'.NL);
		echo('<tr class="space"><td colspan="5" /></tr>'.NL);

		foreach ($distancePoints as $i => $distance) {
			if (floor($distance) == $km || $i == $numberOfPoints-1) {
				$km++;
				$kmIndex[] = $i;
				$previousIndex = $kmIndex[count($kmIndex)-2];
				$pace = Helper::Pace(($distancePoints[$i] - $distancePoints[$previousIndex]), ($timePoints[$i] - $timePoints[$previousIndex]));
				echo('<tr class="a'.($i%2+1).' r">
						<td>'.Helper::Time($timePoints[$i]).'</td>
						<td>'.Helper::Km($distance, 2).'</td>
						<td>'.$pace.'</td>');
				if (count($heartPoints) > 1) {
					$heartRateOfThisKm = array_slice($heartPoints, $previousIndex, ($i - $previousIndex));
					if (array_sum($heartRateOfThisKm) > 0)
						echo('<td>'.round(array_sum($heartRateOfThisKm)/count($heartRateOfThisKm)).'</td>');
					else
						echo('<td>?</td>');
				}
				if (count($elevationPoints) > 1)
					echo('<td>'.($positiveElevation != 0 ? '+'.$positiveElevation : '0').'/'.($negativeElevation != 0 ? '-'.$negativeElevation : '0').'</td>
						</tr>');
				$positiveElevation = 0;
				$negativeElevation = 0;
			} elseif ($i != 0 && count($elevationPoints) > 1 && $elevationPoints[$i] != 0 && $elevationPoints[$i-1] != 0) {
				$elevationDifference = $elevationPoints[$i] - $elevationPoints[$i-1];
				$positiveElevation += ($elevationDifference > self::$minElevationDiff) ? $elevationDifference : 0;
				$negativeElevation -= ($elevationDifference < -1*self::$minElevationDiff) ? $elevationDifference : 0;
			}
		}
		echo('</table>'.NL.NL);
	}

	/**
	 * Display route on GoogleMaps
	 */
	public function displayRoute() {
		echo '<iframe src="lib/gpx/karte.php?id='.$this->id.'" style="border:0;" width="480" height="300" frameborder="0"></iframe>';
	}

	/**
	 * Has the training information about splits?
	 */
	public function hasSplitsData() {
		return $this->get('splits') != '';
	}

	/**
	 * Has the training information about pace?
	 */
	public function hasPaceData() {
		return $this->get('arr_pace') != '';
	}

	/**
	 * Has the training information about elevation?
	 */
	public function hasElevationData() {
		return $this->get('arr_alt') != '';
	}

	/**
	 * Has the training information about pulse?
	 */
	public function hasPulseData() {
		return $this->get('arr_heart') != '' && max(explode('|',$this->get('arr_heart'))) > 60;
	}

	/**
	 * Has the training information about position?
	 */
	public function hasPositionData() {
		return $this->get('arr_lat') != '' && $this->get('arr_lon') != '';
	}

	/**
	 * Display create window
	 */
	public function displayCreateWindow() {
		// TODO Set up class.Training.createWindow.php ?
		Error::getInstance()->addTodo('Set up class::Training::createWindow()');
	}

	/**
	 * Display link for edit window
	 */
	public function displayEditLink() {
		echo Ajax::window('<a href="inc/class.Training.edit.php?id='.$this->id.'" title="Training editieren"><img src="img/edit.png" alt="Training editieren" /></a> ','small');
	}

	/**
	 * Parse a tcx-file
	 */
	public function parseTcx() {
		// TODO
		Error::getInstance()->addTodo('Set up class::Training::parseTcx()');
	}

	/**
	 * Correct the elevation data
	 */
	public function elevationCorrection() {
		if (!$this->hasPositionData())
			return;

		$latitude = explode('|', $this->get('arr_lat'));
		$longitude = explode('|', $this->get('arr_lon'));
		$altitude = array();

		$num = count($latitude);
		for ($i = 0; $i < $num; $i++) {
			if ($i%self::$everyNthElevationPoint == 0) {
				$lats[] = $latitude[$i];
				$longs[] = $longitude[$i];
			}
			if (($i+1)%(20*self::$everyNthElevationPoint) == 0 || $i == $num-1) {
				$html = false;
				while ($html === false) {
					$html = @file_get_contents('http://ws.geonames.org/srtm3?lats='.implode(',', $lats).'&lngs='.implode(',', $longs));
					if (substr($html,0,1) == '<')
						$html = false;
				}
				$data = explode("\r\n", $html);

				foreach ($data as $k => $v)
					$data[$k] = trim($v);
				$data_num = count($data) - 1; // There is always one empty element

				for ($d = 0; $d < $data_num; $d++)
					for ($j = 0; $j < self::$everyNthElevationPoint; $j++)
						$altitude[] = trim($data[$d]);

				$lats = array();
				$longs = array();
			}
		}

		Mysql::getInstance()->update('ltb_training', $this->id, 'arr_alt', implode('|', $altitude));
		echo('Success.');
	}

	/**
	 * Compress data for lower database-traffic
	 */
	private function compressData() {
		// TODO
		Error::getInstance()->addTodo('Set up class::Training::compressData()');
	}
}
?>