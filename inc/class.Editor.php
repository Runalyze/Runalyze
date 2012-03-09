<?php
/**
 * This file contains the class::Editor for editing trainings
 */
/**
 * Class: Editor
 * 
 * @author Hannes Christiansen <mail@laufhannes.de>
 * @version 1.0
 */
class Editor {
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
		$this->addSimpleValues();
		$this->parseDate();
		$this->parseTrainingtime();
		$this->parseWeather();
		$this->parseClothes();
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
		$this->addValue('splits');

		$this->addBooleanValue('is_track');
		$this->addBooleanValue('abc');
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
			$this->UpdateValues[] = $this->TrainingData[$key];
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
		
		if (isset($this->TrainingData['shoeid_old'])
				&& isset($this->TrainingData['s_old'])
				&& isset($this->TrainingData['dist_old'])
				&& isset($this->TrainingData['shoeid'])
				&& $this->TrainingData['shoeid'] != 0) {
			$Mysql->query('UPDATE `'.PREFIX.'shoe` SET `km`=`km`-"'.$_POST['dist_old'].'", `time`=`time`-'.$_POST['s_old'].' WHERE `id`='.$_POST['shoeid_old'].' LIMIT 1');
			$Mysql->query('UPDATE `'.PREFIX.'shoe` SET `km`=`km`+"'.$this->TrainingData['distance'].'", `time`=`time`+'.$this->TrainingData['s'].' WHERE `id`='.$_POST['shoeid'].' LIMIT 1');
		}

		$Mysql->update(PREFIX.'training', $this->Id, 'trimp', Helper::TRIMP($this->Id));
		$Mysql->update(PREFIX.'training', $this->Id, 'vdot', JD::Training2VDOT($this->Id));

		$TimeData = $Mysql->fetchSingle('SELECT time FROM `'.PREFIX.'training` WHERE id='.$this->Id);

		$ATL = Helper::ATL($TimeData['time']);
		$CTL = Helper::CTL($TimeData['time']);
		$TRIMP = Helper::TRIMP($this->Id);
		
		if ($ATL > MAX_ATL)
			Config::update('MAX_ATL', $ATL);
		if ($CTL > MAX_CTL)
			Config::update('MAX_ATL', $CTL);
		if ($TRIMP > MAX_TRIMP)
			Config::update('MAX_ATL', $TRIMP);
	}

	/**
	 * Get all errors for the user as array
	 */
	public function getErrorsAsArray() {
		return $this->Errors;
	}
}
?>