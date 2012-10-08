<?php
/**
 * Class: Dataset
 * @author Hannes Christiansen <mail@laufhannes.de>
 */
class Dataset {
	/**
	 * Internal ID in database
	 * @var int
	 */
	private $trainingId = 0;

	/**
	 * Counter for displayed columns
	 * @var int
	 */
	public $column_count = 0;

	/**
	 * Internal Training-object
	 * @var Training
	 */
	private $Training = null;

	/**
	 * Data array from database
	 * @var array
	 */
	private $data;

	/**
	 * Boolean flag: compare km of datasets (preferred for group of trainings)
	 * @var boolean
	 */
	private $compare_km = false;

	/**
	 * Kilometer for last set
	 * @var double
	 */
	private $km_of_last_set = -1;

	/**
	 * Constructor
	 */
	public function __construct() {
		$dat = Mysql::getInstance()->fetch('SELECT * FROM `'.PREFIX.'dataset` WHERE `modus`>=2 AND `position`!=0 ORDER BY `position` ASC');
		if ($dat === false) {
			Error::getInstance()->addError('No dataset in database is active.');
			return false;
		}

		$this->data = $dat;
		$this->column_count = count($dat);

		if (CONF_DB_SHOW_DIRECT_EDIT_LINK)
			$this->column_count++;
	}

	/**
	 * Activate kilometer comparison 
	 */
	public function activateKilometerComparison() {
		$this->compare_km = true;
	}

	/**
	 * Set manually distance of last set (e.g. for different order)
	 * @param double $km 
	 */
	public function setKilometerToCompareTo($km) {
		$this->km_of_last_set = $km;
	}

	/**
	 * Load complete dataset where position != 0
	 */
	public function loadCompleteDataset() {
		$this->data = Mysql::getInstance()->fetch('SELECT * FROM `'.PREFIX.'dataset` WHERE `position`!=0 ORDER BY `position` ASC');
		$this->column_count = count($this->data);
	}

	/**
	 * Set training
	 * @param int $id
	 * @param array $data [optional]
	 */
	public function setTrainingId($id, $data = array()) {
		$this->trainingId = $id;
		$this->Training = new Training($id, $data);
	}

	/**
	 * Load a group of trainings for summary
	 * @param int $sportid
	 * @param int $timestamp_start
	 * @param int $timestamp_end
	 * @return boolean Are any trainings loaded?
	 */
	public function loadGroupOfTrainings($sportid, $timestamp_start, $timestamp_end) {
		$this->setTrainingId( Training::$CONSTRUCTOR_ID );

		$query_set = '';
		foreach ($this->data as $set)
			if ($set['summary'] == 1)
				if ($set['summary_mode'] != 'AVG')
					$query_set .= ', '.$set['summary_mode'].'(`'.$set['name'].'`) as `'.$set['name'].'`';
				else
					$query_set .= ', '.$set['summary_mode'].'(NULLIF(`'.$set['name'].'`,0)) as `'.$set['name'].'`';

		$WhereNotPrivate = (FrontendShared::$IS_SHOWN && !CONF_TRAINING_LIST_ALL) ? 'AND is_public=1' : '';

		$summary = Mysql::getInstance()->fetchSingle('SELECT sportid,time,is_track,SUM(1) as `num`'.$query_set.' FROM `'.PREFIX.'training` WHERE `sportid`='.$sportid.' AND `time` BETWEEN '.($timestamp_start-10).' AND '.($timestamp_end-10).' '.$WhereNotPrivate.' GROUP BY `sportid`');
		if ($summary === false || empty($summary))
			return false;

		foreach ($summary as $var => $value)
			$this->Training->set($var, $value);

		return true;
	}

	/**
	 * Is Dataset running in summary-mode?
	 * @return bool
	 */
	private function isSummaryMode() {
		return ($this->trainingId == -1);
	}

	/**
	 * Display short link for e.g. 'Gymnastik'
	 */
	public function displayShortLink() {
		$icon = Icon::getSportIcon($this->Training->get('sportid'), '',
			$this->Training->Sport()->name().': '.Time::toString( $this->Training->get('s') ));

		echo $this->Training->trainingLink($icon);
	}

	/**
	 * Get date string for given timestamp
	 * @param int $timestamp
	 * @return string
	 */
	static public function getDateString($timestamp) {
		$date    = date('d.m.', $timestamp);
		$addLink = '';
		$weekDay = Time::Weekday(date('w', $timestamp), true);

		if (CONF_DB_SHOW_CREATELINK_FOR_DAYS && !FrontendShared::$IS_SHOWN)
			$addLink = TrainingCreator::getWindowLinkForDate($timestamp);

		if (CONF_DB_HIGHLIGHT_TODAY && Time::isToday($timestamp))
			$weekDay = '<strong>'.$weekDay.'</strong>';

		return $date.' '.$addLink.' '.$weekDay;
	}

	/**
	 * Display this dataset as a table-row
	 */
	public function displayTableColumns() {
		$this->displayEditLink();

		foreach ($this->data as $set)
			$this->displayDataset($set);
	}

	/**
	 * Display a single dataset
	 * @param array $dataset
	 */
	private function displayDataset($set) {
		if ($this->isSummaryMode() && $set['summary'] == 0) {
			echo HTML::emptyTD();
			return;
		}

		$class = $set['class'] != '' ? ' class="'.$set['class'].'"' : '';
		$style = $set['style'] != '' ? ' style="'.$set['style'].'"' : '';

		echo '<td'.$class.$style.'>'.$this->getDataset($set['name']).'</td>'.NL;
	}

	/**
	 * Display edit link if used in DataBrowser 
	 */
	public function displayEditLink() {
		if (CONF_DB_SHOW_DIRECT_EDIT_LINK)
			if ($this->isSummaryMode() || FrontendShared::$IS_SHOWN)
				echo HTML::emptyTD ();
			else
				echo '<td>'.TrainingDisplay::getSmallEditLinkFor($this->Training->get('id')).'</td>'.NL;
	}

	/**
	 * Display public icon
	 */
	public function displayPublicIcon() {
		if (!is_object($this->Training))
			echo HTML::emptyTD ();
		elseif (!$this->Training->isPublic())
			echo '<td>'.Icon::$ADD_SMALL.'</td>'.NL;
		else
			echo '<td class="link">'.Icon::$ADD_SMALL_GREEN.'</td>'.NL;
	}

	/**
	 * Get content for a given dataset
	 * @param string $name
	 * @return string
	 */
	public function getDataset($name) {
		switch($name) {
			case 'sportid':
				return $this->datasetSport();
			case 'typeid':
				return $this->datasetType();
			case 'time':
				return $this->datasetDate();
			case 'distance':
				return $this->datasetDistance();
			case 's':
				return $this->datasetTime();
			case 'pace':
				return $this->datasetPace();
			case 'elevation':
				return $this->datasetElevation();
			case 'kcal':
				return $this->datasetCalories();
			case 'pulse_avg':
				return $this->datasetPulse();
			case 'pulse_max':
				return $this->datasetPulseMax();
			case 'trimp':
				return $this->datasetTRIMP();
			case 'temperature':
				return $this->datasetTemperature();
			case 'weatherid':
				return $this->datasetWeather();
			case 'route':
				return $this->datasetPath();
			case 'clothes':
				return $this->datasetClothes();
			case 'splits':
				return $this->datasetSplits();
			case 'comment':
				return $this->datasetDescription();
			case 'partner':
				return $this->datasetPartner();
			case 'abc':
				return $this->datasetABC();
			case 'shoeid':
				return $this->datasetShoe();
			case 'vdot':
				return $this->datasetVDOT();
		}

		return '&nbsp;';
	}

	/**
	 * Dataset for: `sportid`
	 * @return string
	 */
	private function datasetSport() {
		return $this->Training->Sport()->Icon();
	}

	/**
	 * Dataset for: `typeid`
	 * @return string
	 */
	private function datasetType() {
		$Type = '';

		if ($this->Training->hasType())
			$Type = $this->Training->Type()->formattedAbbr();

		return $Type;
	}

	/**
	 * Dataset for: `time`
	 * @return string
	 */
	private function datasetDate() {
		return $this->Training->getDaytimeString();
	}

	/**
	 * Dataset for: `distance`
	 * @return string
	 */
	private function datasetDistance() {
		if ($this->compare_km) {
			$CurrentDistance  = $this->Training->get('distance');
			$ColorFactor      = 0;
			$ComparisonString = '-';

			if ($this->km_of_last_set > 0) {
				$Percent          = round(100*($CurrentDistance - $this->km_of_last_set ) / $this->km_of_last_set, 1);
				$ColorFactor      = 100*$Percent / 20;
				$ComparisonString = Math::WithSign($Percent).' %';
			}

			$this->km_of_last_set = $CurrentDistance;

			return $this->Training->getDistanceString().' <small style="display:inline-block;width:45px;color:#'.Running::Stresscolor($ColorFactor).'">'.$ComparisonString.'</small>';
		}

		return $this->Training->getDistanceString();
	}

	/**
	 * Dataset for: `s`
	 * @return string
	 */
	private function datasetTime() {
		return $this->Training->getTimeString();
	}

	/**
	 * Dataset for: `pace`
	 * @return string
	 */
	private function datasetPace() {
		return $this->Training->getSpeedString();
	}

	/**
	 * Dataset for: `elevation`
	 * @return string
	 */
	private function datasetElevation() {
		if (!$this->Training->hasElevation())
			return '';

		$displayString = $this->Training->get('elevation').'&nbsp;hm</span>';
		$tooltipString = '&oslash; Steigung: '.round($this->Training->get('elevation')/$this->Training->get('distance')/10, 2).' &#37;';

		return Ajax::tooltip($displayString, $tooltipString);
	}

	/**
	 * Dataset for: `kcal`
	 * @return string
	 */
	private function datasetCalories() {
		return Helper::Unknown($this->Training->get('kcal')).'&nbsp;kcal';
	}

	/**
	 * Dataset for: `pulse_avg`
	 * @return string
	 */
	private function datasetPulse() {
		return Running::PulseString($this->Training->get('pulse_avg'), $this->Training->get('time'));
	}

	/**
	 * Dataset for: `pulse_max`
	 * @return string
	 */
	private function datasetPulseMax() {
		return Running::PulseString($this->Training->get('pulse_max'), $this->Training->get('time'));
	}

	/**
	 * Dataset for: `trimp`
	 * @return string
	 */
	private function datasetTRIMP() {
		return $this->Training->getTrimpString();
	}

	/**
	 * Dataset for: `temperature`
	 * @return string
	 */
	private function datasetTemperature() {
		if (is_null($this->Training->Weather()) || $this->Training->Weather()->isEmpty())
			return '';

		return $this->Training->Weather()->temperatureString();
	}

	/**
	 * Dataset for: `weatherid`
	 * @return string
	 */
	private function datasetWeather() {
		if (is_null($this->Training->Weather()) || $this->Training->Weather()->isUnknown())
			return '';

		return $this->Training->Weather()->icon();
	}

	/**
	 * Dataset for: `route`
	 * @return string
	 */
	private function datasetPath() {
		return ($this->Training->hasRoute()) ? Helper::Cut($this->Training->get('route'), 20) : '';
	}

	/**
	 * Dataset for: `kleidung`
	 * @return string
	 */
	private function datasetClothes() {
		return Helper::Cut($this->Training->Clothes()->asString(), 20);
	}

	/**
	 * Dataset for: `splits`
	 * @return string
	 */
	private function datasetSplits() {
		if (is_null($this->Training->Type()) || !$this->Training->Type()->hasSplits() || $this->Training->get('splits') == '')
			return;

		return Ajax::tooltip(Icon::$CLOCK, $this->Training->Splits()->asReadableString());
	}

	/**
	 * Dataset for: `comment`
	 * @return string
	 */
	private function datasetDescription() {
		return Helper::Cut($this->Training->get('comment'), 20);
	}

	/**
	 * Dataset for: `partner`
	 * @return string
	 */
	private function datasetPartner() {
		return ($this->Training->get('partner') != '') ? 'mit '.Helper::Cut($this->Training->get('partner'), 15) : '';
	}

	/**
	 * Dataset for: `abc`
	 * @return string
	 */
	private function datasetABC() {
		if ($this->Training->get('abc') == 0)
			return;

		return Ajax::tooltip(Icon::$ABC, 'Lauf-ABC');
	}

	/**
	 * Dataset for: `shoeid`
	 * @return string
	 */
	private function datasetShoe() {
		if (!$this->Training->Sport()->isRunning())
			return '';

		return Shoe::getNameOf($this->Training->get('shoeid'));
	}

	/**
	 * Dataset for: `vdot`
	 * @return string
	 */
	private function datasetVDOT() {
		return $this->Training->getVDOTicon();
	}
}