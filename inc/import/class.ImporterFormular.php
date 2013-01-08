<?php
/**
 * Class: ImporterFormular
 * @author Hannes Christiansen <mail@laufhannes.de>
 */
class ImporterFormular extends Importer {
	/**
	 * Internal array with all columns for insert command
	 * @var array
	 */
	private $columns = array();
	
	/**
	* Internal array with all values for insert command
	* @var array
	*/
	private $values = array();

	/**
	 * Error string
	 * @var string
	 */
	private $errorString = '';

	/**
	 * Timestamp of training
	 * @var int
	 */
	private $time = 0;

	/**
	 * ID of the new training, If a new training has been inserted
	 * @var int
	 */
	public $insertedID = -1;

	/**
	 * Set values for training from file or post-data
	 */
	protected function setTrainingValues() {
		$this->tryToSetFromPostData('sportid');

		$this->set('abc', isset($_POST['abc']) ? 1 : 0);
		$this->set('is_track', isset($_POST['is_track']) ? 1 : 0);

		if ($this->postDataHasBeenSent())
			$this->parsePostDataAndTryToInsert();
	}

	/**
	 * Try to set value from post array
	 * @param string $key
	 */
	private function tryToSetFromPostData($key) {
		if (isset($_POST[$key]))
			$this->set($key, $_POST[$key]);
	}

	/**
	 * Is post data available from standard formular?
	 * @return bool
	 */
	private function postDataHasBeenSent() {
		return (isset($_POST['type']) && $_POST['type'] == "newtraining");
	}

	/**
	 * Parse post data and try to insert training to database
	 */
	private function parsePostDataAndTryToInsert() {
		$this->parsePostData();
		$this->insertTraining();

		if ($this->insertFailed) {
			echo HTML::em('Es ist ein Fehler aufgetreten.').BR;
			if (!empty($this->errorString))
				echo HTML::error($this->errorString).BR;
			echo BR;
		} else {
			echo HTML::em('Das Training wurde erfolgreich eingetragen.');
			echo Ajax::closeOverlay();
		}
	}
	
	/**
	* Parse post data and try to insert training to database
	*/
	public function parsePostData() {
		$AutoParseKeys   = array();
		$AutoParseKeys[] = 'kcal';
		$AutoParseKeys[] = 'sportid';
		$AutoParseKeys[] = 'activity_id';
		$StringKeys      = array();
		$StringKeys[]    = 'partner';
		$StringKeys[]    = 'comment';

		if (!isset($_POST['sportid'])) {
			$this->errorString = 'Es muss eine Sportart ausgew&auml;hlt werden.';
			return;
		}

		$Sport     = new Sport($_POST['sportid']);
		$distance  = ($Sport->usesDistance() && isset($_POST['distance'])) ? Helper::CommaToPoint($_POST['distance']) : 0;
		$time      = $this->getTimeFromPost();
		$time_in_s = $this->getTrainingTimeFromPost();

		if ($time === false || $time_in_s === false)
			return;

		$this->columns[]         = 'created';
		$this->values[]          = time();
		$this->columns[]         = 'time';
		$this->values[]          = $time;
		$this->columns[]         = 's';
		$this->values[]          = $time_in_s;
		$this->columns[]         = 'is_public';
		$this->values[]          = isset($_POST['is_public']) ? 1 : 0;

		// Prepare values for distances
		if ($Sport->usesDistance()) {
			$AutoParseKeys[]     = 'distance';

			$this->columns[]     = 'is_track';
			$this->values[]      = isset($_POST['is_track']) ? 1 : 0;
			$this->columns[]     = 'pace';
			$this->values[]      = Running::Pace($distance, $time_in_s);
		}

		// Prepare values for outside-sport
		if ($Sport->isOutside()) {
			$this->columns[]     = 'elevation';
			$this->values[]      = isset($_POST['elevation']) ? $_POST['elevation'] : 0;
			$this->columns[]     = 'clothes';
			$this->values[]      = isset($_POST['clothes']) && is_array($_POST['clothes']) ? implode(',', array_keys($_POST['clothes'])) : '';
			$this->columns[]     = 'temperature';
			$this->values[]      = isset($_POST['temperature']) && is_numeric($_POST['temperature']) ? $_POST['temperature'] : NULL;
			
			$StringKeys[]        = 'route';
			$AutoParseKeys[]     = 'weatherid';
			$AutoParseKeys[]     = 'arr_time';
			$AutoParseKeys[]     = 'arr_lat';
			$AutoParseKeys[]     = 'arr_lon';
			$AutoParseKeys[]     = 'arr_alt';
			$AutoParseKeys[]     = 'arr_dist';
			$AutoParseKeys[]     = 'arr_heart';
			$AutoParseKeys[]     = 'arr_pace';
		} else {
			$this->columns[]     = 'temperature';
			$this->values[]      = NULL;
		}

		if ($Sport->usesPulse()) {
			$AutoParseKeys[]     = 'pulse_avg';
			$AutoParseKeys[]     = 'pulse_max';
		}

		if ($Sport->hasTypes() && isset($_POST['typeid']) && $_POST['typeid'] > 0) {
			$AutoParseKeys[]     = 'typeid';
		}

		$Splits = new Splits( Splits::$FROM_POST );
		$this->columns[]     = 'splits';
		$this->values[]      = $Splits->asString();

		if ($Sport->isRunning()) {
			$AutoParseKeys[]     = 'shoeid';
			$this->columns[]     = 'abc';
			$this->values[]      = isset($_POST['abc']) ? 1 : 0;
			$this->columns[]     = 'use_vdot';
			$this->values[]      = isset($_POST['use_vdot']) ? 1 : 0;
		}

		$this->columns[] = 'creator';
		$this->values[]  = isset($_POST['creator']) && !empty($_POST['creator']) ? $_POST['creator'] : Importer::$CREATOR_FORM;
		$AutoParseKeys[] = 'creator_details';
		
		foreach ($StringKeys as $var) {
			$this->columns[] = $var;
			$this->values[]  = isset($_POST[$var]) ? HTML::encodeTags($_POST[$var]) : '';
		}
		
		foreach ($AutoParseKeys as $var) {
			$this->columns[] = $var;
			$this->values[]  = isset($_POST[$var]) ? Helper::CommaToPoint($_POST[$var]) : 0;
		}
	}

	/**
	 * Get time or 'false' if not readable
	 * @return mixed
	 */
	private function getTimeFromPost() {
		if (isset($_POST['time'])) {
			$this->time = $_POST['time'];
			return $this->time;
		}

		if (!isset($_POST['zeit']) || strlen($_POST['zeit']) < 3)
			$_POST['zeit'] = '00:00';
		if (isset($_POST['datum'])) {
			$post_day  = explode(".", $_POST['datum']);
			$post_time = explode(":", $_POST['zeit']);
		} else {
			$this->errorString = 'Es muss ein Datum eingetragen werden.';
			return false;
		}
		
		if (count($post_day) != 3 || count($post_time) != 2) {
			$this->errorString = 'Das Datum konnte nicht gelesen werden.';
			return false;
		}

		$this->time = mktime($post_time[0], $post_time[1], 0, $post_day[1], $post_day[0], $post_day[2]);

		return $this->time;
	}

	/**
	 * Get training time from post
	 * @return mixed
	 */
	private function getTrainingTimeFromPost() {
		if (!isset($_POST['s']))
			$time_in_s = 0;
		else
			$time_in_s = is_numeric($_POST['s']) ? $_POST['s'] : self::timeStringToSeconds($_POST['s']);

		if ($time_in_s == 0) {
			$this->errorString = 'Es muss eine Trainingsdauer angegeben sein.';
			return false;
		}

		return $time_in_s;
	}

	/**
	 * Parse string and get executet time in seconds
	 * @param string $string
	 * @return int
	 */
	static public function timeStringToSeconds($string) {
		$ms        = explode(".", Helper::CommaToPoint($string));
		$dauer     = explode(":", $ms[0]);

		if (!isset($ms[1]))
			$ms[1] = 0;

		if (strlen($ms[1]) < 2)
			$ms[1] = $ms[1] * 10;

		if (!isset($dauer[1])) {
			$dauer[1] = $dauer[0];
			$dauer[0] = 0;
		}

		if (!isset($dauer[2])) {
			$dauer[2] = $dauer[1];
			$dauer[1] = $dauer[0];
			$dauer[0] = 0;
		}

		return round(3600 * $dauer[0] + 60 * $dauer[1] + $dauer[2] + ($ms[1]/100), 2);
	}

	/**
	* Insert training to database
	*/
	public function insertTraining() {
		if (!empty($this->errorString))
			$this->insertFailed = true;

		if ($this->insertFailed === true)
			return;

		$Mysql = Mysql::getInstance();
		$id    = $Mysql->insert(PREFIX.'training', $this->columns, $this->values);

		if ($id === false) {
			$this->insertFailed = true;
			$this->errorString  = 'ImporterFormular: Unbekannter Fehler mit der Datenbank.';
			return;
		}

		$TrainingData = array_combine($this->columns, $this->values);
		$TrainingData['id'] = $id;

		$Training  = new Training($id, $TrainingData);
		
		$Mysql->query('UPDATE `'.PREFIX.'training` SET `trimp`="'.Trimp::forTraining($TrainingData).'" WHERE `id`='.$id.' LIMIT 1');
		$Mysql->query('UPDATE `'.PREFIX.'training` SET `vdot`="'.JD::Training2VDOT($id).'" WHERE `id`='.$id.' LIMIT 1');

		Trimp::checkForMaxValuesAt($this->time);

		if ($Training->Sport()->isRunning()) {
			if ($Training->hasType() && in_array('typeid', $this->columns) && $Training->get('typeid') == CONF_WK_TYPID)
				JD::recalculateVDOTcorrector();
		
			if ($Training->get('shoeid') > 0)
				$Mysql->query('UPDATE `'.PREFIX.'shoe` SET `km`=`km`+'.$Training->get('distance').', `time`=`time`+'.$Training->get('s').' WHERE `id`='.$Training->get('shoeid').' LIMIT 1');
		}

		if (CONF_TRAINING_DO_ELEVATION && $Training->hasElevationData()) {
			$Training->elevationCorrection();
		
			$Mysql->update(PREFIX.'training', $id, 'elevation', $Training->GpsData()->calculateElevation());
		} elseif ($Training->hasElevationData() && (!in_array('elevation', $this->columns) || $Training->get('elevation') == 0)) {
				$Mysql->update(PREFIX.'training', $id, 'elevation', $Training->GpsData()->calculateElevation());
		}

		$this->insertedID   = $id;
		$this->insertFailed = false;
	}
}