<?php
/**
 * This file contains the class to handle every training.
 */

Config::register('Training', 'MAINSPORT', 'selectdb', 1, 'Haupt-Sportart', array('sport', 'name'));
Config::register('Training', 'RUNNINGSPORT', 'selectdb', 1, 'Lauf-Sportart', array('sport', 'name'));
Config::register('Training', 'WK_TYPID', 'selectdb', 5, 'Trainingstyp: Wettkampf', array('type', 'name'));
Config::register('Training', 'LL_TYPID', 'selectdb', 7, 'Trainingstyp: Langer Lauf', array('type', 'name'));
Config::register('Training', 'TRAINING_DECIMALS', 'select',
	array('0' => false, '1' => true, '2' => false), 'Anzahl angezeigter Nachkommastellen',
	array('0', '1', '2'));

Config::register('Eingabeformular', 'COMPUTE_KCAL', 'bool', true, 'Kalorienverbrauch automatisch berechnen');
Config::register('Eingabeformular', 'TRAINING_CREATE_MODE', 'select',
	array('tcx' => false, 'garmin' => true, 'form' => false), 'Standard-Eingabemodus',
	array('tcx-Datei hochladen', 'GarminCommunicator', 'Standard-Formular'));
Config::register('Eingabeformular', 'TRAINING_ELEVATION_SERVER', 'select',
	array('google' => true, 'geonames' => false), 'Server f&uuml;r H&ouml;henkorrektur',
	array('maps.googleapis.com', 'ws.geonames.org'));
Config::register('Eingabeformular', 'TRAINING_DO_ELEVATION', 'bool', true, 'H&ouml;henkorrektur verwenden');

/**
 * Class: Training
 * 
 * @author Hannes Christiansen <mail@laufhannes.de>
 * @version 1.0
 * @uses class::Mysql
 * @uses class::Error
 * @uses class::Config
 * @uses class::JD
 * @uses class::TrainingDisplay
 * @uses class::Clothes
 * @uses class::Sport
 * @uses class::Type
 * @uses class::Weather
 * @uses class::Shoe
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
	private $data = array();

	/**
	 * Object for given clothes
	 * @var Clothes
	 */
	private $Clothes = null;

	/**
	 * Object for given sport
	 * @var Sport
	 */
	private $Sport = null;

	/**
	 * Object for given type
	 * @var Type
	 */
	private $Type = null;

	/**
	 * Object for given weather
	 * @var Weather
	 */
	private $Weather = null;

	/**
	 * Constructor (needs ID, can be -1 for set($var) on it's own
	 * @param int $id
	 */
	public function __construct($id) {
		if (!$this->isValidId($id))
			return false;

		$this->fillUpDataWithDefaultValues();
		$this->createObjects();
		$this->correctVDOT();
	}

	/**
	 * Check id and set internal data if id is valid
	 * @param int $id
	 * @return bool
	 */
	private function isValidId($id) {
		if (!is_numeric($id) || $id == NULL) {
			Error::getInstance()->addError('An object of class::Training must have an ID: <$id='.$id.'>');
			return false;
		}

		if ($id == -1) {
			$dat = array();
		} else {
			$dat = Mysql::getInstance()->fetch(PREFIX.'training', $id);
			if ($dat === false) {
				Error::getInstance()->addError('This training (ID='.$id.') does not exist.');
				return false;
			}
		}

		$this->id   = $id;
		$this->data = $dat;

		return true;
	}

	/**
	 * Create all needed objects
	 */
	private function createObjects() {
		if ($this->id == -1)
			return;

		$this->Clothes = new Clothes($this->get('clothes'));
		$this->Sport = new Sport($this->get('sportid'));

		if ($this->hasType())
			$this->Type = new Type($this->get('typeid'));

		if ($this->isOutside())
			$this->Weather = new Weather($this->get('weatherid'), $this->get('temperature'));
	}

	/**
	 * Get object for clothes
	 * @return Clothes
	 */
	public function Clothes() {
		return $this->Clothes;
	}

	/**
	 * Get object for sport
	 * @return Sport
	 */
	public function Sport() {
		return $this->Sport;
	}

	/**
	 * Get object for type
	 * @return Type
	 */
	public function Type() {
		return $this->Type;
	}

	/**
	 * Get object for weather
	 * @return Weather
	 */
	public function Weather() {
		return $this->Weather;
	}

	/**
	 * Has this training a trainingtype?
	 * @return bool
	 */
	public function hasType() {
		return $this->Sport()->hasTypes();
	}

	/**
	 * Has this training data for outside-trainings?
	 * @return bool
	 */
	public function isOutside() {
		return $this->Sport()->isOutside();
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

		if ($var != 'temperature')
			Error::getInstance()->addWarning('Training::get - unknown column "'.$var.'"');
	}

	/**
	 * Override global post-array for edit-window
	 * @return array
	 */
	public function overridePostArray() {
		$_POST = array_merge($_POST, $this->data);
		$_POST['sport'] = $this->Sport()->name();
		$_POST['datum'] = date("d.m.Y", $this->get('time'));
		$_POST['zeit'] = date("H:i", $this->get('time'));
		$_POST['s'] = Helper::Time($this->get('s'), false, true);

		$_POST['s_old'] = $_POST['s'];
		$_POST['dist_old'] = $this->get('distance');
		$_POST['shoeid_old'] = $this->get('shoeid');

		$_POST['clothes'] = $this->Clothes()->arrayForPostdata();
		$_POST['kcalPerHour'] = $this->Sport()->kcalPerHour();
		$_POST['pace'] = $this->getPace();
		$_POST['kmh'] = $this->getKmh();
	}

	/**
	 * Fill internal data with default values for NULL-columns
	 */
	private function fillUpDataWithDefaultValues() {
		$vars = array(
			'route',
			'splits',
			'comment',
			'partner',
			'arr_time',
			'arr_lat',
			'arr_lon',
			'arr_alt',
			'arr_dist',
			'arr_heart',
			'arr_pace');

		foreach ($vars as $var) {
			if (!isset($this->data[$var]) || is_null($this->data[$var]))
				$this->data[$var] = '';
		}
	}

	/**
	 * Uses JD::correctVDOT to correct own VDOT-value if specified
	 */
	private function correctVDOT() {
		if (!isset($this->data['vdot']))
			$this->data['vdot'] = 0;
		elseif ($this->data['vdot'] != 0)
			$this->data['vdot'] = JD::correctVDOT($this->data['vdot']);
	}

	/**
	 * Get string for clothes
	 * @return string all clothes comma seperated
	 */
	public function getStringForClothes() {
		return Clothes::getStringForClothes($this->get('clothes'));
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
	 * Gives a HTML-link for using jTraining which is calling the training-tpl
	 * @return string HTML-link to this training
	 */
	public function trainingLinkWithComment() {
		return $this->trainingLink($this->get('comment'));
	}

	/**
	 * Gives a HTML-link for using jTraining which is calling the training-tpl
	 * @return string HTML-link to this training
	 */
	public function trainingLinkWithSportIcon() {
		return $this->trainingLink($this->Sport()->Icon());
	}

	/**
	 * Get date as link to that week in DataBrowser
	 * @return string
	 */
	public function getDateAsWeeklink() {
		return DataBrowser::getLink(date("d.m.Y", $this->data['time']), Helper::Weekstart($this->data['time']), Helper::Weekend($this->data['time']));
	}

	/**
	 * Display the whole training
	 */
	public function display() {
		$Display = new TrainingDisplay($this);
		$Display->display();
	}

	/**
	 * Display table with all training data
	 */
	public function displayTable() {
		include('tpl/tpl.Training.table.php');
	}

	/**
	 * Display the title for this training
	 * @param bool $short short version without description, default: false
	 */
	public function displayTitle($short = false) {
		echo $this->getTitle();
		if (!$short) {
			if ($this->get('abc') == 1)
				echo(' '.Icon::get(Icon::$ABC, 'Lauf-ABC'));
			if ($this->get('comment') != '')
				echo (': '.$this->get('comment'));
		}
	}

	/**
	 * Get the title for this training
	 * @return string
	 */
	public function getTitle() {
		return ($this->hasType())
			? $this->Type()->name()
			: $this->Sport()->name();
	}

	/**
	 * Display the formatted date
	 */
	public function displayDate() {
		echo (Helper::Weekday( date('w', $this->get('time')) ).', '.$this->getDate());
	}

	/**
	 * Get the date for this training
	 * @param bool $withTime [optional] adding daytime to string
	 * @return string
	 */
	public function getDate($withTime = true) {
		$day = date('d.m.Y', $this->get('time'));

		if ($withTime && strlen($this->getDaytimeString()) > 0)
			return $day.' '.$this->getDaytimeString();

		return $day;
	}

	/**
	 * Get string for datetime
	 * @return string
	 */
	public function getDaytimeString() {
		$time = $this->get('time');

		return date('H:i', $time) != '00:00' ? date('H:i', $time).' Uhr' : '';
	}

	/**
	 * Get trainingtime as string
	 * @return string
	 */
	public function getTimeString() {
		return Helper::Time($this->get('s'));
	}

	/**
	 * Get distance as string
	 * @return string
	 */
	public function getDistanceString() {
		if ($this->hasDistance())
			return Helper::Km($this->get('distance'), CONF_TRAINING_DECIMALS, $this->get('is_track'));

		return '';
	}

	/**
	 * Get distance as string
	 * @return string
	 */
	public function getDistanceStringWithoutEmptyDecimals() {
		if ($this->hasDistance())
			return Helper::Km($this->get('distance'), (round($this->get('distance')) != $this->get('distance') ? 1 : 0), $this->get('is_track'));

		return '';
	}

	/**
	 * Get distance as string
	 * @return string
	 */
	public function getDistanceStringWithFullDecimals() {
		if ($this->hasDistance())
			return Helper::Km($this->get('distance'), 2, $this->get('is_track'));

		return '';
	}

	/**
	 * Get distance or time if distance is zero
	 * @return string
	 */
	public function getKmOrTime() {
		if ($this->hasDistance())
			return $this->getTimeString();

		return $this->getDistanceString();
	}

	/**
	 * Get a string for the speed depending on sportid
	 * @return string
	 */
	public function getSpeedString() {
		return Helper::Speed($this->get('distance'), $this->get('s'), $this->get('sportid'));
	}
	
	/**
	* Get pace as string without unit
	* @return string
	*/
	public function getPace() {
		return Helper::Pace($this->get('distance'), $this->get('s'));
	}
	
	/**
	* Get km/h as string without unit
	* @return string
	*/
	public function getKmh() {
		return Helper::Kmh($this->get('distance'), $this->get('s'));
	}

	/**
	 * Is a positive distance set?
	 * @return bool
	 */
	public function hasDistance() {
		return ($this->get('distance') > 0);
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
	 * Get link for create window
	 */
	static public function getCreateWindowLink() {
		$icon = Icon::get(Icon::$ADD, 'Training hinzuf&uuml;gen');
		return Ajax::window('<a href="call/call.Training.create.php" title="Training hinzuf&uuml;gen">'.$icon.'</a>', 'normal');
	}

	/**
	 * Display the window/formular for creation
	 */
	static public function displayCreateWindow() {
		if (isset($_POST['type']) && $_POST['type'] == "newtraining") {
			$returnCode = self::parsePostdataForCreation();

			if ($returnCode === true) {
				echo '<em>Das Training wurde erfolgreich eingetragen.</em>';
				echo '<script type="text/javascript">closeOverlay();</script>';
				return;
			} else {
				echo '<em>Es ist ein Fehler aufgetreten.</em><br />';
				if (is_string($returnCode))
					echo $returnCode.'<br />';
				echo '<br />';
			}
		}

		include 'tpl/tpl.Training.create.php';
	}

	/**
	 * Parse posted data to create a new training
	 */
	static private function parsePostdataForCreation() {
		// TODO: CleanCode!
		$Mysql   = Mysql::getInstance();
		$vars    = array(); // Values beeing parsed with Helper::Umlaute/CommaToPoint() for each $_POST[$vars[]]
		$columns = array(); // Columns inserted directly
		$values  = array(); // Values inserted directly
		$vars[]  = 'kcal';
		$vars[]  = 'comment';
		$vars[]  = 'partner';

		if (!isset($_POST['sportid']))
			return 'Es muss eine Sportart ausgew&auml;hlt werden.';
		$Sport = new Sport($_POST['sportid']);

		$distance = ($Sport->usesDistance() && isset($_POST['distance'])) ? Helper::CommaToPoint($_POST['distance']) : 0;
		$columns[] = 'sportid';
		$values[]  = $_POST['sportid'];
	
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

		if (!isset($_POST['s']))
			return 'Es muss eine Trainingszeit angegeben sein.';
		$time = mktime($post_time[0], $post_time[1], 0, $post_day[1], $post_day[0], $post_day[2]);
		$columns[] = 'time';
		$values[]  = $time;
		// Prepare "Dauer"
		$ms        = explode(".", Helper::CommaToPoint($_POST['s']));
		$dauer     = explode(":", $ms[0]);
		if (!isset($ms[1]))
			$ms[1] = 0;
		$time_in_s = round(3600 * $dauer[0] + 60 * $dauer[1] + $dauer[2] + ($ms[1]/100), 2);
		if ($time_in_s == 0)
			return 'Es muss eine Trainingszeit angegeben sein.';

		$columns[] = 's';
		$values[]  = $time_in_s;
		// Prepare values for distances
		if ($Sport->usesDistance()) {
			$vars[]    = 'distance';
			$columns[] = 'is_track';
			$values[]  = isset($_POST['is_track']) ? 1 : 0;
			$columns[] = 'pace';
			$values[]  = Helper::Pace($distance, $time_in_s);
		}
		// Prepare values for outside-sport
		if ($Sport->isOutside()) {
			$vars[]    = 'weatherid';
			$vars[]    = 'route';
			$columns[] = 'elevation';
			$values[]  = isset($_POST['elevation']) ? $_POST['elevation'] : 0;
			$columns[] = 'clothes';
			$values[]  = isset($_POST['clothes']) ? implode(',', array_keys($_POST['clothes'])) : '';
			$columns[] = 'temperature';
			$values[]  = isset($_POST['temperature']) && is_numeric($_POST['temperature']) ? $_POST['temperature'] : NULL;

			$vars[]    = 'arr_time';
			$vars[]    = 'arr_lat';
			$vars[]    = 'arr_lon';
			$vars[]    = 'arr_alt';
			$vars[]    = 'arr_dist';
			$vars[]    = 'arr_heart';
			$vars[]    = 'arr_pace';
		} else {
			// Set NULL to temperatur otherwise
			$columns[] = 'temperature';
			$values[]  = NULL;
		}
		// Prepare values if using heartfrequence
		if ($Sport->usesPulse()) {
			$vars[]    = 'pulse_avg';
			$vars[]    = 'pulse_max';
		}
		// Prepare values for running (checked via "type")
		if ($Sport->hasTypes()) {
			$Type = new Type($_POST['typeid']);
			$vars[]    = 'typeid';
			if ($Type->hasSplits())
				$vars[] = 'splits';
		}
		if ($Sport->isRunning()) {
			$vars[]    = 'shoeid';
			$columns[] = 'abc';
			$values[]  = isset($_POST['abc']) ? 1 : 0;
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
		
		if ($ATL > MAX_ATL)
			Config::update('MAX_ATL', $ATL);
		if ($CTL > MAX_CTL)
			Config::update('MAX_ATL', $CTL);
		if ($TRIMP > MAX_TRIMP)
			Config::update('MAX_ATL', $TRIMP);

		if (isset($_POST['shoeid']))
			$Mysql->query('UPDATE `'.PREFIX.'shoe` SET `km`=`km`+'.$distance.', `time`=`time`+'.$time_in_s.' WHERE `id`='.$_POST['shoeid'].' LIMIT 1');

		if (CONF_TRAINING_DO_ELEVATION) {
			$Training = new Training($id);
			$Training->elevationCorrection();

			$Mysql->update(PREFIX.'training', $id, 'elevation', Training::calculateElevation($Training->get('arr_alt')));
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
		$laps = $xml['trainingcenterdatabase']['activities']['activity']['lap'];

		if (!is_array($laps))
			return array('error' => 'Es konnten keine gestoppten Runden gefunden werden.');

		if (Helper::isAssoc($laps))
			$laps = array($laps);

		foreach($laps as $lap) {
			$i++;

			if (isset($lap['calories']))
				$calories += $lap['calories']['value'];
			if (isset($lap['intensity']) && strtolower($lap['intensity']['value']) == 'active') {
				$splits[] = round($lap['distancemeters']['value']/1000, 2).'|'.Helper::Time(round($lap['totaltimeseconds']['value']), false, 2);
			}

			if (Helper::isAssoc($lap['track']))
				$lap['track'] = array($lap['track']);

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
		$array['distance']   = round(end($distance), 2);
		if (!empty($time)) {
			$array['s']     = Helper::Time(end($time), false, true);
			$array['pace']      = Helper::Pace($array['distance'], end($time));
			$array['kmh']       = Helper::Kmh($array['distance'], end($time));
		}
		if (!empty($heartrate)) {
			$array['pulse_avg']      = round(array_sum($heartrate)/count($heartrate));
			$array['pulse_max']  = max($heartrate);
		}
		$array['kcal']  = $calories;
		if (isset($xml['trainingcenterdatabase']['activities']['activity']['training']))
			$array['comment'] = $xml['trainingcenterdatabase']['activities']['activity']['training']['plan']['name']['value'];
		$array['splits']    = implode('-', $splits);

		$array['arr_time']  = implode(self::$ARR_SEP, $time);
		$array['arr_lat']   = implode(self::$ARR_SEP, $latitude);
		$array['arr_lon']   = implode(self::$ARR_SEP, $longitude);
		$array['arr_alt']   = implode(self::$ARR_SEP, $altitude);
		$array['arr_dist']  = implode(self::$ARR_SEP, $distance);
		$array['arr_heart'] = implode(self::$ARR_SEP, $heartrate);
		$array['arr_pace']  = implode(self::$ARR_SEP, $pace);
		//$array['elevation'] - Will be calculated later on

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

					//$enc    = new xmlgooglemaps_googleMapAPIPolylineEnc(32,4);
					//$encArr = $enc->dpEncode($points);
					//$path   = $encArr[2];
					// Maybe problems with coding? Use numbers instead
					//$url    = 'http://maps.googleapis.com/maps/api/elevation/xml?path=enc:'.$path.'&samples='.count($points).'&sensor=false';
					$url    = 'http://maps.googleapis.com/maps/api/elevation/xml?locations='.implode('|',$string).'&sensor=false';
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

					if (count($string) == 1)
						for ($j = 0; $j < self::$everyNthElevationPoint; $j++)
							$altitude[] = round($Result['elevationresponse']['result']['elevation']['value']);
					else
						foreach ($Result['elevationresponse']['result'] as $point) {
							if (!isset($point['elevation']) || !isset($point['elevation']['value']))
								Error::getInstance()->addWarning('Probably malformed response from Google: '.print_r($point, true));
							else 
								for ($j = 0; $j < self::$everyNthElevationPoint; $j++)
									$altitude[] = round($point['elevation']['value']);
					}
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