<?php
/**
 * Class: TrainingEditor
 * @author Hannes Christiansen <mail@laufhannes.de>
 */
class TrainingEditor {
	/**
	 * URL for editing trainings
	 * @var string
	 */
	static public $URL = 'call/call.Training.edit.php';

	/**
	 * ID of the current training
	 * @var int
	 */
	protected $Id = 0;

	/**
	 * Training data for editing
	 * @var array
	 */
	protected $TrainingData = array();

	/**
	 * All columns for update-query
	 * @var array
	 */
	protected $UpdateColumns = array();

	/**
	 * All values for update-query
	 * @var array
	 */
	protected $UpdateValues = array();

	/**
	 * Internal array with all errors to be displayed for every user (not only debug-mode)
	 * @var array
	 */
	protected $Errors = array();

	/**
	 * Constructor
	 */
	public function __construct($Id, $Data) {
		$this->Id = $Id;
		$this->TrainingData = $Data;
	}

	/**
	 * Parse data and update database
	 */
	public function performUpdate() {
		$this->parseDataForUpdate();
		$this->updateDatabase();
	}

	/**
	 * Parse data from array and prepare values for database
	 */
	private function parseDataForUpdate() {
		$this->addInternalValues();
		$this->addSimpleValues();
		$this->parseDate();
		$this->parseTrainingtime();
		$this->parseWeather();
		$this->parseClothes();
		$this->parseSplits();
	}

	/**
	 * Add internal values 
	 */
	private function addInternalValues() {
		$this->UpdateColumns[] = 'edited';
		$this->UpdateValues[]  = time();
	}

	/**
	 * Add simple values
	 */
	private function addSimpleValues() {
		$this->addValue('kcal');
		$this->addTextValue('comment');
		$this->addTextValue('partner');
		$this->addTextValue('route');

		$this->addValue('distance');
		$this->addValue('elevation');
		$this->addValue('pace');
		
		$this->addValue('sportid');
		$this->addValue('shoeid');
		$this->addValue('typeid');
		$this->addValue('pulse_avg');
		$this->addValue('pulse_max');

		$this->addBooleanValue('is_track');
		$this->addBooleanValue('is_public');
		$this->addBooleanValue('use_vdot');
		$this->addBooleanValue('abc');

		// These values are only set by filling the editor manually
		$this->addValue('arr_time');
		$this->addValue('arr_lat');
		$this->addValue('arr_lon');
		$this->addValue('arr_alt');
		$this->addValue('arr_dist');
		$this->addValue('arr_heart');
		$this->addValue('arr_pace');
	}

	/**
	 * Parse date
	 */
	private function parseDate() {
		if (!isset($this->TrainingData['datum']))
			return;
		
		$day     = explode('.', $this->TrainingData['datum']);
		$daytime = (isset($this->TrainingData['zeit'])) ? explode(':', $this->TrainingData['zeit']) : array(0,0);

		if (count($day) != 3 || count($daytime) != 2)
			$this->Errors[] = 'Das Datum konnte nicht gelesen werden.';
		else {
			$this->TrainingData['time'] = mktime($daytime[0], $daytime[1], 0, $day[1], $day[0], $day[2]);
			$this->addValue('time');
		}
	}

	/**
	 * Parse trainingtime
	 */
	private function parseTrainingtime() {
		if (!isset($this->TrainingData['s']))
			return;

		$ms   = explode(".", Helper::CommaToPoint($this->TrainingData['s']));
		$time = explode(":", $ms[0]);

		if (!isset($ms[1]))
			$ms[1] = 0;

		$this->TrainingData['s'] = round(3600 * $time[0] + 60 * $time[1] + $time[2] + ($ms[1]/100), 2);
		
		if ($this->TrainingData['s'] == 0)
			$this->Errors[] = 'Es muss eine Trainingszeit angegeben sein.';
		else
			$this->addValue('s');
	}

	/**
	 * Parse weather data
	 */
	private function parseWeather() {
		$this->addValue('weatherid');

		if (isset($this->TrainingData['temperature'])) {
			if (strlen($this->TrainingData['temperature']) > 0)
				$this->addValue('temperature');
			else {
				$this->UpdateColumns[] = 'temperature';
				$this->UpdateValues[] = 'NULL';
			}
		}
	}

	/**
	 * Parse clothes
	 */
	private function parseClothes() {
		if (!isset($this->TrainingData['clothes_sent']))
			return;

		$this->UpdateColumns[] = 'clothes';
		$this->UpdateValues[] = isset($this->TrainingData['clothes']) ? implode(',', array_keys($this->TrainingData['clothes'])) : '';

		$this->TrainingData['clothes'] = end($this->UpdateValues);
	}

	/**
	 * Parse splits 
	 */
	private function parseSplits() {
		if (!isset($this->TrainingData['splits_sent']))
			return;

		if (!isset($this->TrainingData['splits']))
			$this->TrainingData['splits'] = array();

		$Splits = new Splits( $this->TrainingData['splits'] );

		$this->UpdateColumns[] = 'splits';
		$this->UpdateValues[] = $Splits->asString();
	}

	/**
	 * Add value with automatic transforming (umlaute/commas)
	 * @param string $key
	 */
	private function addBooleanValue($key) {
		if (isset($this->TrainingData[$key]) || isset($this->TrainingData[$key.'_sent'])) {
			$this->UpdateColumns[] = $key;
			$this->UpdateValues[] = isset($this->TrainingData[$key]);
		}
	}

	/**
	 * Add value with automatic transforming (commas)
	 * @param string $key
	 */
	private function addValue($key) {
		if (isset($this->TrainingData[$key])) {
			$this->UpdateColumns[] = $key;
			$this->UpdateValues[] = Helper::CommaToPoint($this->TrainingData[$key]);
		}
	}

	/**
	 * Add value with automatic transforming (umlaute)
	 * @param string $key
	 */
	private function addTextValue($key) {
		if (isset($this->TrainingData[$key])) {
			$this->UpdateColumns[] = $key;
			$this->UpdateValues[] = HTML::encodeTags($this->TrainingData[$key]);
		}
	}

	/**
	 * Update database-entry
	 */
	private function updateDatabase() {
		if (!empty($this->Errors))
			return;

		$Mysql = Mysql::getInstance();

		$Mysql->update(PREFIX.'training', $this->Id, $this->UpdateColumns, $this->UpdateValues);
		$Mysql->update(PREFIX.'training', $this->Id, 'trimp', Trimp::forTraining($this->TrainingData));
		$Mysql->update(PREFIX.'training', $this->Id, 'vdot', JD::Training2VDOT($this->Id, $this->TrainingData));

		$this->updateShoeValues();
		$this->updateMaxValues();
		$this->updateVdotCorrector();
	}

	/**
	 * Update shoe values 
	 */
	private function updateShoeValues() {
		if ((isset($this->TrainingData['shoeid_old']) || isset($this->TrainingData['shoeid']))
				&& isset($this->TrainingData['s_old'])
				&& isset($this->TrainingData['dist_old'])) {

			if (isset($this->TrainingData['shoeid_old']))
				Mysql::getInstance()->query('UPDATE `'.PREFIX.'shoe` SET `km`=`km`-"'.$this->TrainingData['dist_old'].'", `time`=`time`-'.$this->TrainingData['s_old'].' WHERE `id`='.$this->TrainingData['shoeid_old'].' LIMIT 1');
			if (isset($this->TrainingData['shoeid']))
				Mysql::getInstance()->query('UPDATE `'.PREFIX.'shoe` SET `km`=`km`+"'.$this->TrainingData['distance'].'", `time`=`time`+'.$this->TrainingData['s'].' WHERE `id`='.$this->TrainingData['shoeid'].' LIMIT 1');
		}
	}

	/**
	 * Update max values 
	 */
	private function updateMaxValues() {
		$TimeData = Mysql::getInstance()->fetchSingle('SELECT time FROM `'.PREFIX.'training` WHERE id='.$this->Id);

		Trimp::checkForMaxValuesAt($TimeData['time']);
	}

	/**
	 * Update VDOT-corrector 
	 */
	private function updateVdotCorrector() {
		if (isset($this->TrainingData['typeid']) && $this->TrainingData['typeid'] == CONF_WK_TYPID)
			JD::recalculateVDOTcorrector();
	}

	/**
	 * Get all errors for the user as array
	 */
	public function getErrorsAsArray() {
		return $this->Errors;
	}

	/**
	 * Link to editor for a given training
	 * @param int $id
	 * @param string $text [optional] by default: Icon::$EDIT
	 * @param string $linkId [optional]
	 * @return string 
	 */
	static public function linkTo($id, $text = '', $linkId = '') {
		if ($text == '')
			$text = Icon::$EDIT;

		if ($linkId != '')
			$linkId = ' id="'.$linkId.'"';

		return Ajax::window('<a'.$linkId.' href="'.self::$URL.'?id='.$id.'">'.$text.'</a>', 'small');
	}
}