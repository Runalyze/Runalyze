<?php
/**
 * This file contains the class to handle every training.
 */

Config::register('Training', 'MAINSPORT', 'selectdb', 1, 'Haupt-Sportart', array('sports', 'name'));
Config::register('Training', 'RUNNINGSPORT', 'selectdb', 1, 'Lauf-Sportart', array('sports', 'name'));
Config::register('Training', 'WK_TYPID', 'selectdb', 5, 'Trainingstyp: Wettkampf', array('typ', 'name'));
Config::register('Training', 'LL_TYPID', 'selectdb', 7, 'Trainingstyp: Langer Lauf', array('typ', 'name'));

Config::register('Training', 'TRAINING_MAP_COLOR', 'string', '#FF5500', 'Linienfarbe auf GoogleMaps-Karte (#RGB)');
Config::register('Training', 'TRAINING_MAP_MARKER', 'bool', true, 'Kilometer-Markierungen anzeigen');
Config::register('Training', 'TRAINING_MAPTYPE', 'select',
	array('G_NORMAL_MAP' => false, 'G_HYBRID_MAP' => true, 'G_SATELLITE_MAP' => false, 'G_PHYSICAL_MAP' => false), 'Typ der GoogleMaps-Karte',
	array('Normal', 'Hybrid', 'Sattelit', 'Physikalisch'));

Config::register('Eingabeformular', 'TRAINING_DO_ELEVATION', 'bool', true, 'H&ouml;henkorrektur verwenden');
Config::register('Eingabeformular', 'TRAINING_ELEVATION_SERVER', 'select',
	array('google' => true, 'geonames' => false), 'Server f&uuml;r H&ouml;henkorrektur',
	array('maps.googleapis.com', 'ws.geonames.org'));
Config::register('Eingabeformular', 'TRAINING_CREATE_MODE', 'select',
	array('tcx' => false, 'garmin' => true, 'form' => false), 'Standard-Eingabemodus',
	array('tcx-Datei hochladen', 'GarminCommunicator', 'Standard-Formular'));

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
	public static $minElevationDiff = 2;

	/**
	 * Only every n-th point will be taken for the elevation
	 * @var int
	 */
	public static $everyNthElevationPoint = 5;

	/**
	 * Path to file for displaying the map (used for iframe)
	 * @var string
	 */
	public static $mapURL = 'inc/tcx/window.map.php';

	/**
	 * Array seperator for gps-data in database
	 * @var char
	 */
	public static $ARR_SEP = '|';

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
		if ($id == -1) {
			$this->id = -1;
			$this->data = array();
			return;
		}

		if (!is_numeric($id) || $id == NULL) {
			Error::getInstance()->addError('An object of class::Training must have an ID: <$id='.$id.'>');
			return false;
		}

		$dat = Mysql::getInstance()->fetch(PREFIX.'training', $id);
		if ($dat === false) {
			Error::getInstance()->addError('This training (ID='.$id.') does not exist.');
			return false;
		}

		$this->id = $id;
		$this->data = $dat;
		$this->fillUpDataWithDefaultValues();
		$this->correctVDOT();
	}

	/**
	 * Set a column
	 * @param string $var
	 * @param string $value
	 */
	public function set($var, $value) {
		if ($this->id != -1) {
			Error::getInstance()->addWarning('Training::set - can\'t set value, Training already loaded.');
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

		if ($var != 'temperatur')
			Error::getInstance()->addWarning('Training::get - unknown column "'.$var.'"');
	}

	/**
	 * Fill internal data with default values for NULL-columns
	 */
	private function fillUpDataWithDefaultValues() {
		if (is_null($this->data['strecke']))
			$this->data['strecke'] = '';
		if (is_null($this->data['splits']))
			$this->data['splits'] = '';
		if (is_null($this->data['bemerkung']))
			$this->data['bemerkung'] = '';
		if (is_null($this->data['trainingspartner']))
			$this->data['trainingspartner'] = '';
		if (is_null($this->data['arr_time']))
			$this->data['arr_time'] = '';
		if (is_null($this->data['arr_lat']))
			$this->data['arr_lat'] = '';
		if (is_null($this->data['arr_lon']))
			$this->data['arr_lon'] = '';
		if (is_null($this->data['arr_alt']))
			$this->data['arr_alt'] = '';
		if (is_null($this->data['arr_dist']))
			$this->data['arr_dist'] = '';
		if (is_null($this->data['arr_heart']))
			$this->data['arr_heart'] = '';
		if (is_null($this->data['arr_pace']))
			$this->data['arr_pace'] = '';
	}

	/**
	 * Uses JD::correctVDOT to correct own VDOT-value if specified
	 */
	private function correctVDOT() {
		if ($this->data['vdot'] != 0)
			$this->data['vdot'] = JD::correctVDOT($this->data['vdot']);
	}

	/**
	 * Get string for clothes
	 * @return string all clothes comma seperated
	 */
	public function getStringForClothes() {
		if ($this->get('kleidung') != '') {
			$kleidungen = array();
			$kleidungen_data = Mysql::getInstance()->fetchAsArray('SELECT `name` FROM `'.PREFIX.'kleidung` WHERE `id` IN ('.$this->get('kleidung').') ORDER BY `order` ASC');
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
		echo Helper::clearBreak();
	}

	/**
	 * Display header
	 */
	public function displayHeader() {
		echo '<h1>'.NL;
		$this->displayEditLink();
		$this->displayTitle();
		echo '<small class="right">';
		$this->displayDate();
		echo '</small><br class="clear" />';
		echo '</h1>'.NL;
	}

	/**
	 * Display plot links, first plot and map
	 */
	public function displayPlotsAndMap() {
		$plots = $this->getPlotTypesAsArray();

		echo '<div class="right">'.NL;
		if (!empty($plots)) {
			echo '<small class="right">'.NL;
			$this->displayPlotLinks('trainingGraph');
			echo '</small>'.NL;
			echo '<br /><br />'.NL;
			$this->displayPlot(key($plots));
			echo '<br /><br />'.NL;
		}

		if ($this->hasPositionData())
			$this->displayRoute();

		echo '</div>'.NL;
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
				echo(' '.Icon::get(Icon::$ABC, 'Lauf-ABC'));
			if ($this->get('bemerkung') != '')
				echo (': '.$this->get('bemerkung'));
		}
	}

	/**
	 * Get the title for this training
	 * @return string
	 */
	public function getTitle() {
		return ($this->get('sportid') == CONF_RUNNINGSPORT)
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
			$links[] = Ajax::imgChange('<a href="'.$array['src'].'">'.$array['name'].'</a>', 'trainingGraph').NL;
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

		$splits       = explode('-', str_replace('\r\n', '-', $this->get('splits')));
		$Distances    = $this->getSplitsDistancesArray();
		$Times        = $this->getSplitsTimeArray();
		$Paces        = $this->getSplitsPacesArray();
		$demandedPace = Helper::DescriptionToDemandedPace($this->get('bemerkung'));
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

		echo Helper::spaceTR(4);

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
		} else {
			echo '
				<tr class="r">
					<td colspan="2">Schnitt: </td>
					<td>'.Helper::Time($achievedPace).'/km</td>
					<td></td>
				</tr>'.NL;
		}

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
		$distancePoints 	= explode(self::$ARR_SEP, $this->get('arr_dist'));
		$timePoints 		= explode(self::$ARR_SEP, $this->get('arr_time'));
		$heartPoints 		= explode(self::$ARR_SEP, $this->get('arr_heart'));
		$elevationPoints 	= explode(self::$ARR_SEP, $this->get('arr_alt'));
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
				$positiveElevation += ($elevationDifference > self::$minElevationDiff) ? $elevationDifference : 0;
				$negativeElevation -= ($elevationDifference < -1*self::$minElevationDiff) ? $elevationDifference : 0;
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
		echo Helper::spaceTR(3 + (int)$showPulse + (int)$showElevation);

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
				<td>'.Helper::Speed($round['km'], $round['s'], $this->get('sportid')).'</td>
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
		echo '<iframe src="'.self::$mapURL.'?id='.$this->id.'" style="border:1px solid #000;" width="478" height="300" frameborder="0"></iframe>';
	}

	/**
	 * Calculate absolute number for elevation
	 * @param array $alternateData [optional] Array for arr_alt
	 * @return int
	 */
	static public function calculateElevation($data) {
		if (empty($data))
			return 0;

		$elevationPoints 	= explode(self::$ARR_SEP, $data);
		$minimumElevation   = (min($elevationPoints) > 0) ? max($elevationPoints) - min($elevationPoints) : 0;
		$positiveElevation 	= 0;  $up   = false;
		$negativeElevation 	= 0;  $down = false;
		$currentElevation   = 0;

		// Algorithm: must be at least 5m up/down without down/up
		foreach ($elevationPoints as $i => $p) {
			if ($i != 0 && $elevationPoints[$i] != 0 && $elevationPoints[$i-1] != 0) {
				$diff = $p - $elevationPoints[$i-1];
				if ( ($diff > 0 && !$down) || ($diff < 0 && !$up) )
					$currentElevation += $diff;
				else {
					if (abs($currentElevation) >= 5) {
						if ($up)
							$positiveElevation += $currentElevation;
						if ($down)
							$negativeElevation -= $currentElevation;
					}
					$currentElevation = $diff;
				}
				$up   = ($diff > 0);
				$down = ($diff < 0);
			}
		}

		return max($minimumElevation, $positiveElevation, $negativeElevation);
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
	 * Display link for edit window
	 */
	public function displayEditLink() {
		echo Ajax::window('<a href="inc/class.Training.edit.php?id='.$this->id.'" title="Training editieren">'.Icon::get(Icon::$EDIT, 'Training editieren').'</a> ','small');
	}

	/**
	 * Get link for create window
	 */
	static public function getCreateWindowLink() {
		$icon = Icon::get(Icon::$ADD, 'Training hinzuf&uuml;gen');
		return Ajax::window('<a href="inc/class.Training.create.php" title="Training hinzuf&uuml;gen">'.$icon.'</a>', 'normal');
	}

	/**
	 * Display the window/formular for creation
	 */
	static public function displayCreateWindow() {
		if (isset($_POST['type']) && $_POST['type'] == "newtraining") {
			$returnCode = self::parsePostdataForCreation();

			if ($returnCode === true) {
				echo('<em>Das Training wurde erfolgreich eingetragen.</em>');
				echo('<script type="text/javascript">closeOverlay();</script>');
				return;
			} else {
				echo('<em>Es ist ein Fehler aufgetreten.</em><br />');
				if (is_string($returnCode))
					echo($returnCode.'<br />');
				echo('<br />');
			}
		}

		include('tpl/window.create.php');
	}

	/**
	 * Parse posted data to create a new training
	 */
	static private function parsePostdataForCreation() {
		$Mysql   = Mysql::getInstance();
		$vars    = array(); // Values beeing parsed with Helper::Umlaute/CommaToPoint() for each $_POST[$vars[]]
		$columns = array(); // Columns inserted directly
		$values  = array(); // Values inserted directly
		$vars[]  = 'kalorien';
		$vars[]  = 'bemerkung';
		$vars[]  = 'trainingspartner';

		if (!isset($_POST['sportid']))
			return 'Es muss eine Sportart ausgew&auml;hlt werden.';
		$sport = $Mysql->fetch(PREFIX.'sports', $_POST['sportid']);
		if ($sport === false)
			return 'Es wurde keine Sportart ausgew&auml;hlt.';

		$distance = ($sport['distanztyp'] == 1 && isset($_POST['distanz'])) ? Helper::CommaToPoint($_POST['distanz']) : 0;
		$columns[] = 'sportid';
		$values[]  = $sport['id'];
	
		// Prepare "Time"
		if (!isset($_POST['zeit']))
			$_POST['zeit'] = '00:00';
		if (isset($_POST['datum'])) {
			$post_day  = explode(".", $_POST['datum']);
			$post_time = explode(":", $_POST['zeit']);
		} else
			return 'Es muss ein Datum eingetragen werden.';
		if (count($post_day) != 3 || count($post_time) != 2)
			return 'Das Datum konnte nicht gelesen werden.';

		if (!isset($_POST['dauer']))
			return 'Es muss eine Trainingszeit angegeben sein.';
		$time = mktime($post_time[0], $post_time[1], 0, $post_day[1], $post_day[0], $post_day[2]);
		$columns[] = 'time';
		$values[]  = $time;
		// Prepare "Dauer"
		$ms        = explode(".", Helper::CommaToPoint($_POST['dauer']));
		$dauer     = explode(":", $ms[0]);
		if (!isset($ms[1]))
			$ms[1] = 0;
		$time_in_s = round(3600 * $dauer[0] + 60 * $dauer[1] + $dauer[2] + ($ms[1]/100), 2);
		if ($time_in_s == 0)
			return 'Es muss eine Trainingszeit angegeben sein.';

		$columns[] = 'dauer';
		$values[]  = $time_in_s;
		// Prepare values for distances
		if ($sport['distanztyp'] == 1) {
			$vars[]    = 'distanz';
			$columns[] = 'bahn';
			$values[]  = isset($_POST['bahn']) ? 1 : 0;
			$columns[] = 'pace';
			$values[]  = Helper::Pace($distance, $time_in_s);
		}
		// Prepare values for outside-sport
		if ($sport['outside'] == 1) {
			$vars[]    = 'wetterid';
			$vars[]    = 'strecke';
			$columns[] = 'hm';
			$values[]  = isset($_POST['hm']) ? $_POST['hm'] : 0;
			$columns[] = 'kleidung';
			$values[]  = isset($_POST['kleidung']) ? substr($_POST['kleidung'], 0, -1) : '';
			$columns[] = 'temperatur';
			$values[]  = isset($_POST['temperatur']) && is_numeric($_POST['temperatur']) ? $_POST['temperatur'] : NULL;

			$vars[]    = 'arr_time';
			$vars[]    = 'arr_lat';
			$vars[]    = 'arr_lon';
			$vars[]    = 'arr_alt';
			$vars[]    = 'arr_dist';
			$vars[]    = 'arr_heart';
			$vars[]    = 'arr_pace';
		} else {
			// Set NULL to temperatur otherwise
			$columns[] = 'temperatur';
			$values[]  = NULL;
		}
		// Prepare values if using heartfrequence
		if ($sport['pulstyp'] == 1) {
			$vars[]    = 'puls';
			$vars[]    = 'puls_max';
		}
		// Prepare values for running (checked via "type")
		if ($sport['typen'] == 1) {
			$vars[]    = 'typid';
			$vars[]    = 'schuhid';
			$columns[] = 'laufabc';
			$values[]  = isset($_POST['laufabc']) ? 1 : 0;
			if (Helper::TypeHasSplits($_POST['typid']))
				$vars[] = 'splits';
		}
	
		foreach($vars as $var) {
			$columns[] = $var;
			$values[]  = isset($_POST[$var]) ? Helper::Umlaute(Helper::CommaToPoint($_POST[$var])) : NULL;
		}

		$id = $Mysql->insert(PREFIX.'training', $columns, $values);
		if ($id === false)
			return 'Unbekannter Fehler mit der Datenbank.';
	
		$ATL = Helper::ATL($time);
		$CTL = Helper::CTL($time);
		$TRIMP = Helper::TRIMP($id);

		$Mysql->query('UPDATE `'.PREFIX.'training` SET `trimp`="'.$TRIMP.'" WHERE `id`='.$id.' LIMIT 1');
		$Mysql->query('UPDATE `'.PREFIX.'training` SET `vdot`="'.JD::Training2VDOT($id).'" WHERE `id`='.$id.' LIMIT 1');

		if ($ATL > CONFIG_MAX_ATL)
			$Mysql->query('UPDATE `'.PREFIX.'config` SET `max_atl`="'.$ATL.'"');
		if ($CTL > CONFIG_MAX_CTL)
			$Mysql->query('UPDATE `'.PREFIX.'config` SET `max_ctl`="'.$CTL.'"');
		if ($TRIMP > CONFIG_MAX_TRIMP)
			$Mysql->query('UPDATE `'.PREFIX.'config` SET `max_trimp`="'.$TRIMP.'"');

		if (isset($_POST['schuhid'])) {
			if ($sport['typen'] == 1) // Why the hell this if?
				$Mysql->query('UPDATE `'.PREFIX.'schuhe` SET `km`=`km`+'.$distance.', `dauer`=`dauer`+'.$time_in_s.' WHERE `id`='.$_POST['schuhid'].' LIMIT 1');

			// TODO Is this distance used anymore?
			$Mysql->query('UPDATE `'.PREFIX.'sports` SET `distanz`=`distanz`+'.$distance.', `dauer`=`dauer`+'.$time_in_s.' WHERE `id`='.$_POST['sportid'].' LIMIT 1');	
		}

		if (CONF_TRAINING_DO_ELEVATION) {
			$Training = new Training($id);
			$Training->elevationCorrection();

			$Mysql->update(PREFIX.'training', $id, 'hm', Training::calculateElevation($Training->get('arr_alt')));
		}

		return true;
	}

	/**
	 * Parse a tcx-file
	 * @param string $xml XML-Data
	 * @return array Used as $_POST
	 */
	static public function parseTcx($xml) {
		require_once('tcx/class.ParserTcx.php');

		if (!is_array($xml)) {
			$Parser = new ParserTcx($xml);
			$xml = $Parser->getContentAsArray();
		} else
			Error::getInstance()->addNotice('Training::parseTcx() got an array instead of a xml-string - nothing parsed.');

		$i = 0;
		$starttime = 0;
		$calories  = 0;
		$time      = array();
		$latitude  = array();
		$longitude = array();
		$altitude  = array();
		$distance  = array();
		$heartrate = array();
		$pace      = array();
		$splits    = array();

		if (!is_array($xml['trainingcenterdatabase']['activities']['activity']))
			return array('error' => 'Es scheint keine Garmin-Trainingsdatei zu sein.');

		$starttime = strtotime($xml['trainingcenterdatabase']['activities']['activity']['id']['value']);
		$start_tmp = $starttime;

		if (!is_array($xml['trainingcenterdatabase']['activities']['activity']['lap']))
			return array('error' => 'Es konnten keine gestoppten Runden gefunden werden.');

		foreach($xml['trainingcenterdatabase']['activities']['activity']['lap'] as $lap) {
			$i++;

			if (isset($lap['calories']))
				$calories += $lap['calories']['value'];
			if (isset($lap['intensity']) && strtolower($lap['intensity']['value']) == 'active') {
				$splits[] = round($lap['distancemeters']['value']/1000, 2).'|'.Helper::Time(round($lap['totaltimeseconds']['value']), false, 2);
			}

			if (!isset($lap['track']) || !is_array($lap['track']) || empty($lap['track']))
				Error::getInstance()->addWarning('Training::parseTcx(): Keine Track-Daten vorhanden.');

			foreach ($lap['track'] as $track) {
				$last_point = 0;

				if (isset($track['trackpoint']))
					$trackpointArray = $track['trackpoint'];
				else
					$trackpointArray = $track;

				foreach($trackpointArray as $trackpoint) {
					if (isset($trackpoint['distancemeters']) && $trackpoint['distancemeters']['value'] > $last_point) {
						$last_point = $trackpoint['distancemeters']['value'];
						$time[]     = strtotime($trackpoint['time']['value']) - $start_tmp;
						$distance[] = round($trackpoint['distancemeters']['value'])/1000;
						$pace[]     = ((end($distance) - prev($distance)) != 0)
							? round((end($time) - prev($time)) / (end($distance) - prev($distance)))
							: 0;
						if (isset($trackpoint['position'])) {
							$latitude[]  = $trackpoint['position']['latitudedegrees']['value'];
							$longitude[] = $trackpoint['position']['longitudedegrees']['value'];
						} else {
							$latitude[]  = 0;
							$longitude[] = 0;
						}
						$altitude[] = (isset($trackpoint['altitudemeters']))
							? round($trackpoint['altitudemeters']['value'])
							: 0;
						$heartrate[] = (isset($trackpoint['heartratebpm']))
							? $trackpoint['heartratebpm']['value']['value']
							: 0;
					} else { // Delete pause from timeline
						//Error::getInstance()->addDebug('Training::parseTcx(): '.Helper::Time(strtotime($trackpoint['time']['value'])-$start_tmp-end($time)).' pause after '.Helper::Km(end($distance),2).'.');
						$start_tmp += (strtotime($trackpoint['time']['value'])-$start_tmp) - end($time);
					}
				}
			}
		}

		$array = array();
		$array['sportid']   = CONF_RUNNINGSPORT;
		$array['datum']     = date("d.m.Y", $starttime);
		$array['zeit']      = date("H:i", $starttime);
		$array['distanz']   = round(end($distance), 2);
		if (!empty($time)) {
			$array['dauer']     = Helper::Time(end($time), false, true);
			$array['pace']      = Helper::Pace($array['distanz'], end($time));
			$array['kmh']       = Helper::Kmh($array['distanz'], end($time));
		}
		if (!empty($heartrate)) {
			$array['puls']      = round(array_sum($heartrate)/count($heartrate));
			$array['puls_max']  = max($heartrate);
		}
		$array['kalorien']  = $calories;
		if (isset($xml['trainingcenterdatabase']['activities']['activity']['training']))
			$array['bemerkung'] = $xml['trainingcenterdatabase']['activities']['activity']['training']['plan']['name']['value'];
		$array['splits']    = implode('-', $splits);
		//$array['hm']
		
		//$array['strecke']
		//$array['wetterid']
		//$array['temperatur']
		//$array['trainingspartner']
		//$array['typid']
		//$array['schuhid']
		//$array['laufabc']
		//$array['bahn']

		$array['arr_time']  = implode(self::$ARR_SEP, $time);
		$array['arr_lat']   = implode(self::$ARR_SEP, $latitude);
		$array['arr_lon']   = implode(self::$ARR_SEP, $longitude);
		$array['arr_alt']   = implode(self::$ARR_SEP, $altitude);
		$array['arr_dist']  = implode(self::$ARR_SEP, $distance);
		$array['arr_heart'] = implode(self::$ARR_SEP, $heartrate);
		$array['arr_pace']  = implode(self::$ARR_SEP, $pace);

		return $array;
	}

	/**
	 * Correct the elevation data
	 */
	public function elevationCorrection() {
		if (!$this->hasPositionData())
			return;

		$latitude  = explode(self::$ARR_SEP, $this->get('arr_lat'));
		$longitude = explode(self::$ARR_SEP, $this->get('arr_lon'));
		$altitude  = array();

		$num = count($latitude);
		$numForEachCall = (CONF_TRAINING_ELEVATION_SERVER == 'google') ? 20 : 20; // 400 for google if coding would be okay

		for ($i = 0; $i < $num; $i++) {
			if ($i%self::$everyNthElevationPoint == 0) {
				$lats[] = $latitude[$i];
				$longs[] = $longitude[$i];
				$points[] = array($latitude[$i], $longitude[$i]);
				$string[] = $latitude[$i].','.$longitude[$i];
			}
			if (($i+1)%($numForEachCall*self::$everyNthElevationPoint) == 0 || $i == $num-1) {
				if (CONF_TRAINING_ELEVATION_SERVER == 'google') {
					// maps.googleapis.com
					require_once('tcx/class.googleMapsAPI.php');
					require_once('tcx/class.ParserTcx.php');

					$enc    = new xmlgooglemaps_googleMapAPIPolylineEnc(32,4);
					$encArr = $enc->dpEncode($points);
					$path   = $encArr[2];
					// Maybe problems with coding? Use numbers instead
					//$url    = 'http://maps.googleapis.com/maps/api/elevation/xml?path=enc:'.$path.'&samples='.count($points).'&sensor=false';
					$url    = 'http://maps.googleapis.com/maps/api/elevation/xml?path='.implode('|',$string).'&samples='.count($points).'&sensor=false';
					$xml    = @file_get_contents($url);

					$Parser = new ParserTcx($xml);
					$Result = $Parser->getContentAsArray();
					if (!isset($Result['elevationresponse'])) {
						Error::getInstance()->addError('GoogleMapsAPI returned bad xml.');
						Error::getInstance()->addError('Request was: '.$url);
						return false;
					} elseif ($Result['elevationresponse']['status']['value'] != 'OK') {
						Error::getInstance()->addError('GoogleMapsAPI returned bad status: '.$Result['elevationresponse']['status']['value']);
						Error::getInstance()->addError('Request was: '.$url);
						return false;
					}
					foreach ($Result['elevationresponse']['result'] as $point)
						for ($j = 0; $j < self::$everyNthElevationPoint; $j++)
							$altitude[] = round($point['elevation']['value']);
				} else {
					// ws.geonames.org
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
				}

				$lats = array();
				$longs = array();
				$points = array();
				$string = array();
			}
		}
		
		$this->data['arr_alt'] = implode(self::$ARR_SEP, $altitude);
		Mysql::getInstance()->update(PREFIX.'training', $this->id, 'arr_alt', $this->data['arr_alt']);
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