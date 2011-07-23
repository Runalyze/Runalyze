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

		$dat = Mysql::getInstance()->fetch(PREFIX.'training', $id);
		if ($dat === false) {
			Error::getInstance()->addError('This training (ID='.$id.') does not exist.');
			return false;
		}

		$this->id = $id;
		$this->data = $dat;
		$this->fillUpDataWithDefaultValues();

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
		$distancePoints 	= explode(self::$ARR_SEP, $this->get('arr_dist'));
		$timePoints 		= explode(self::$ARR_SEP, $this->get('arr_time'));
		$heartPoints 		= explode(self::$ARR_SEP, $this->get('arr_heart'));
		$elevationPoints 	= explode(self::$ARR_SEP, $this->get('arr_alt'));
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
		echo '<iframe src="'.self::$mapURL.'?id='.$this->id.'" style="border:0;" width="480" height="300" frameborder="0"></iframe>';
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
		$columns[] = 'time';
		$values[]  = mktime($post_time[0], $post_time[1], 0, $post_day[1], $post_day[0], $post_day[2]);
		// Prepare "Dauer"
		$ms        = explode(".", Helper::CommaToPoint($_POST['dauer']));
		$dauer     = explode(":", $ms[0]);
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
			$vars[]    = 'hm';
			$vars[]    = 'wetterid';
			$vars[]    = 'strecke';
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
			$values[]  = $_POST['laufabc'] ? 1 : 0;
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
			if ($sport['typen'] == 1)
				$Mysql->query('UPDATE `'.PREFIX.'schuhe` SET `km`=`km`+'.$distance.', `dauer`=`dauer`+'.$time_in_s.' WHERE `id`='.$_POST['schuhid'].' LIMIT 1');
	
			$Mysql->query('UPDATE `'.PREFIX.'sports` SET `distanz`=`distanz`+'.$distance.', `dauer`=`dauer`+'.$time_in_s.' WHERE `id`='.$_POST['sportid'].' LIMIT 1');	
		}

		// TODO ElevationCorrection

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
				$splits[] = round($lap['distancemeters']['value']/1000, 2).'|'.Helper::Time(round($lap['totaltimeseconds']['value']));
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
		$array['sportid']   = RUNNINGSPORT;
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

		Mysql::getInstance()->update(PREFIX.'training', $this->id, 'arr_alt', implode(self::$ARR_SEP, $altitude));
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