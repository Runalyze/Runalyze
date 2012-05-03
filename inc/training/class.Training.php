<?php
/**
 * Class: Training
 * 
 * @author Hannes Christiansen <mail@laufhannes.de>
 */
class Training {
	/**
	 * ID to use Training as object without data from database
	 * @var int
	 */
	public static $CONSTRUCTOR_ID = -1;

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
	 * Object for given GPS-data
	 * @var GpsData
	 */
	private $GpsData = null;

	/**
	 * Constructor (needs ID, can be -1 for set($var) on it's own
	 * @param int $id
	 * @param array $data [optional]
	 */
	public function __construct($id, $data = array()) {
		if (!empty($data)) {
			$this->id   = $id;
			$this->data = $data;
		} elseif (!$this->canSetDataFromId($id))
			return false;

		$this->fillUpDataWithDefaultValues();
		$this->createObjects();
		$this->correctVDOT();
	}

	/**
	 * Get ID
	 * @return int
	 */
	public function id() {
		return $this->id;
	}

	/**
	 * Is this a valid training?
	 * @return boolean
	 */
	public function isValid() {
		return !empty($this->data);
	}

	/**
	 * Is the ID of this training just for construction? (not in database)
	 * @return bool
	 */
	private function hasConstructorId() {
		return ($this->id == self::$CONSTRUCTOR_ID);
	}

	/**
	 * Check id and set internal data if id is valid
	 * @param int $id
	 * @return bool
	 */
	private function canSetDataFromId($id) {
		if (!is_numeric($id) || $id == NULL) {
			Error::getInstance()->addError('An object of class::Training must have an ID: <$id='.$id.'>');
			return false;
		}

		if ($id == self::$CONSTRUCTOR_ID) {
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
		if ($this->hasConstructorId())
			return;

		$this->Clothes = new Clothes($this->get('clothes'));
		$this->Sport   = new Sport($this->get('sportid'));
		$this->GpsData = new GpsData($this->data);

		if ($this->hasType())
			$this->Type = new Type($this->get('typeid'));

		$this->Weather = new Weather($this->get('weatherid'), $this->get('temperature'));
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
	 * Set a column
	 * @param string $var
	 * @param string $value
	 */
	public function set($var, $value) {
		if (!$this->hasConstructorId()) {
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
	 * Get RPE
	 * @return int 
	 */
	public function RPE() {
		if (!is_null($this->Type))
			return $this->Type()->RPE();

		return $this->Sport()->RPE();
	}

	/**
	 * Get average heartfrequence
	 * @return int
	 */
	public function avgHF() {
		if ($this->get('pulse_avg') > 0)
			return $this->get('pulse_avg');

		return $this->Sport()->avgHF();
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
		if (is_null($this->Sport))
			$this->Sport = new Sport(CONF_MAINSPORT);

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
	 * Get object for GPS-data
	 * @return GpsData
	 */
	public function GpsData() {
		return $this->GpsData;
	}

	/**
	 * Overwrite global post-array for edit-window
	 * @return array
	 */
	public function overwritePostArray() {
		$_POST = array_merge($_POST, $this->data);

		if ($this->id == self::$CONSTRUCTOR_ID)
			return;

		$_POST['sport']       = $this->Sport()->name();
		$_POST['datum']       = date("d.m.Y", $this->get('time'));
		$_POST['zeit']        = date("H:i", $this->get('time'));
		$_POST['s']           = Helper::Time($this->get('s'), false, true);

		$_POST['s_old']       = $this->get('s');
		$_POST['dist_old']    = $this->get('distance');
		$_POST['shoeid_old']  = $this->get('shoeid');

		$_POST['clothes']     = $this->Clothes()->arrayForPostdata();
		$_POST['kcalPerHour'] = $this->Sport()->kcalPerHour();
		$_POST['pace']        = $this->getPace();
		$_POST['kmh']         = $this->getKmh();
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
		if (strlen($this->get('comment')) > 0)
			return $this->trainingLink($this->get('comment'));

		return $this->trainingLink('<em>unbekannt</em>');
	}

	/**
	 * Gives a HTML-link for using jTraining which is calling the training-tpl
	 * @return string HTML-link to this training
	 */
	public function trainingLinkWithSportIcon() {
		return $this->trainingLink($this->Sport()->Icon());
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
		include 'tpl/tpl.Training.table.php';
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
	 * Display title and date
	 * @param bool $short short version without description, default: false
	 */
	public function displayTitleWithDate($short = false) {
		$this->displayTitle($short);
		echo ', ';
		$this->displayDate();
	}

	/**
	 * Display title with date and navigation links for prev/next training 
	 */
	public function displayTitleWithNavigation() {
		echo TrainingDisplay::getEditPrevLinkFor($this->id(), $this->get('time'));

		$this->displayTitleWithDate(true);
		echo NL;

		echo TrainingDisplay::getEditNextLinkFor($this->id(), $this->get('time'));
	}

	/**
	 * Get title for a training-plot
	 * @return string
	 */
	public function getPlotTitle() {
		$text  = $this->getDate(false).', ';
		$text .= $this->getTitle();

		if ($this->get('comment') != '')
			$text .= ': '.$this->get('comment');

		return $text;
	}

	/**
	 * Display the formatted date
	 */
	public function displayDate() {
		echo (Time::Weekday( date('w', $this->get('time')) ).', '.$this->getDateWithWeekLink());
	}

	/**
	 * Get date as link to that week in DataBrowser
	 * @return string
	 */
	public function getDateAsWeeklink() {
		return DataBrowser::getLink(date("d.m.Y", $this->data['time']), Time::Weekstart($this->data['time']), Time::Weekend($this->data['time']));
	}

	/**
	 * Get the title for this training
	 * @return string
	 */
	public function getTitle() {
		return ($this->hasType() && !$this->Type()->isUnknown())
			? $this->Type()->name()
			: $this->Sport()->name();
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
	 * Get the date for this training, linked to DataBrowser
	 * @param bool $withTime [optional] adding daytime to string
	 * @return string
	 */
	public function getDateWithWeekLink($withTime = true) {
		$string = explode(' ', $this->getDate($withTime));
		$string[0] = DataBrowser::getWeekLink($string[0], $this->get('time'));

		return implode(' ', $string);
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
		if (!$this->hasDistance())
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
	 * Get string for displaying colored trimp
	 * @return string
	 */
	public function getTrimpString() {
		return '<span style="color:#'.Helper::Stresscolor($this->get('trimp')).';">'.$this->get('trimp').'</span>';
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
	 * Get trainingspartner
	 * @return string
	 */
	public function getPartner() {
		return $this->get('partner');
	}

	/**
	 * Get trainingspartner as links
	 * @return string
	 */
	public function getPartnerAsLinks() {
		if (!$this->hasPartner())
			return '';

		$links = array();
		$partners = explode(', ', $this->getPartner());
		foreach ($partners as $partner)
			$links[] = DataBrowser::getSearchLink($partner, 'opt[partner]=is&val[partner]='.$partner);

		return implode(', ', $links);
	}

	/**
	 * Get string for clothes
	 * @return string all clothes comma seperated
	 */
	public function getStringForClothes() {
		return Clothes::getStringForClothes($this->get('clothes'));
	}

	/**
	 * Has this training data for outside-trainings?
	 * @return bool
	 */
	public function isOutside() {
		return $this->Sport()->isOutside();
	}

	/**
	 * Has this training a trainingtype?
	 * @return bool
	 */
	public function hasType() {
		return $this->Sport()->hasTypes();
	}

	/**
	 * Is a positive distance set?
	 * @return bool
	 */
	public function hasDistance() {
		return ($this->get('distance') > 0);
	}

	/**
	 * Is an heartfrequence set?
	 * @return bool
	 */
	public function hasPulse() {
		return ($this->get('pulse_avg') > 0 || $this->get('pulse_max') > 0);
	}

	/**
	 * Is a positive elevation set?
	 * @return bool
	 */
	public function hasElevation() {
		return ($this->get('elevation') > 0);
	}

	/**
	 * Is a route set?
	 * @return bool
	 */
	public function hasRoute() {
		return ($this->get('route') != '');
	}

	/**
	 * Has the training information about splits?
	 */
	public function hasSplitsData() {
		return $this->get('splits') != '';
	}

	/**
	 * Has the training information about trainingspartner?
	 */
	public function hasPartner() {
		return $this->get('partner') != '';
	}

	/**
	 * Has the training information about pace?
	 */
	public function hasPaceData() {
		return $this->GpsData->hasPaceData();
	}

	/**
	 * Has the training information about elevation?
	 */
	public function hasElevationData() {
		return $this->GpsData->hasElevationData();
	}

	/**
	 * Has the training information about pulse?
	 */
	public function hasPulseData() {
		return $this->GpsData->hasHeartrateData();
	}

	/**
	 * Has the training information about position?
	 */
	public function hasPositionData() {
		return $this->GpsData->hasPositionData();
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

			if (isset($split[1]))
				$array[] = Helper::TimeToSeconds($split[1]);
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

		$n = count($times);
		if ($n != count($distances))
			Error::getInstance()->addWarning('Training-Splits: Menge von Distanzen/Zeiten stimmen nicht &uuml;berein.');

		for ($i = 0, $n = count($times); $i < $n; $i++)
			if (isset($times[$i]) && isset($distances[$i]))
				$paces[] = $distances[$i] > 0 ? round($times[$i]/$distances[$i]) : 0;

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

			if (!is_numeric($split[0]))
				Error::getInstance()->addNotice('Training-Splits: Keine korrekte Form (&quot;'.$splits[$i].'&quot;)');
			else
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
			$splits[$i] = str_replace('|', '&nbsp;km&nbsp;in&nbsp;', $split);
		}

		return implode(', ', $splits);
	}

	/**
	 * Get link for create window
	 */
	static public function getCreateWindowLink() {
		$icon = Icon::get(Icon::$ADD, '', '', 'Training hinzuf&uuml;gen');
		return Ajax::window('<a href="call/call.Training.create.php">'.$icon.'</a>', 'normal');
	}

	/**
	 * Get link for create window for a given date
	 * @param string $date
	 * @return string
	 */
	static public function getCreateWindowLinkForDate($date) {
		if (is_int($date))
			$date = date('d.m.Y', $date);

		$icon = Icon::get(Icon::$ADD_GRAY, '');
		return Ajax::window('<a href="call/call.Training.create.php?date='.$date.'">'.$icon.'</a>', 'normal');
	}

	/**
	 * Display the window/formular for creation
	 */
	static public function displayCreateWindow() {
		if (isset($_POST['forceAsFileName']))
			$_GET['file'] = $_POST['forceAsFileName'];

		$fileName     = isset($_GET['file']) ? $_GET['file'] : '';
		$showUploader = empty($_POST) && !isset($_GET['file']);
		$Importer     = Importer::getInstance($fileName);

		if (!isset($_POST['datum']) && isset($_GET['date'])) {
			$_POST['datum'] = $_GET['date'];
			$showUploader = false;
		}

		if ($Importer->tryToUploadFileHasSuccess())
			return;

		if ($Importer->tryToCreateTrainingHasSuccess())
			return;

		include 'tpl/tpl.Training.create.php';
	}

	/**
	 * Correct the elevation data
	 */
	public function elevationCorrection() {
		if ($this->GpsData()->hasElevationData())
			Mysql::getInstance()->update(PREFIX.'training', $this->id,
				'arr_alt',
				implode(self::$ARR_SEP, $this->GpsData()->getElevationCorrection()));
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
	 * Get rounded and corrected VDOT-value
	 * @return number
	 */
	public function getVDOT() {
		return round($this->get('vdot'), 2);
	}

	/**
	 * Get icon with prognosis as title for VDOT-value
	 * @return string
	 */
	public function getVDOTicon() {
		$VDOT = $this->getVDOT();
		if ($VDOT == 0)
			return '';

		if ($this->id == -1)
			$VDOT = round(JD::correctVDOT($VDOT), 2);

		return Icon::getVDOTicon($VDOT);
	}
}
?>