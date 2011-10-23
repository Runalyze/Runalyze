<?php
/**
 * This file contains the class::ImporterFormular for creating a training from formular
 */
/**
 * Class: ImporterFormular
 * 
 * @author Hannes Christiansen <mail@laufhannes.de>
 * @version 1.0
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
	 * Set values for training from file or post-data
	 */
	protected function setTrainingValues() {
		$this->tryToSetFromPostData('sportid');

		$this->set('abc', isset($_POST['abc']) ? 1 : 0);
		$this->set('is_track', isset($_POST['is_track']) ? 1 : 0);

		$this->decodeIfSet('route');
		$this->decodeIfSet('comment');
		$this->decodeIfSet('partner');

		if ($this->postDataHasBeenSent())
			$this->parsePostDataAndTryToInsert();
	}

	/**
	 * UTF8-decode if set
	 * @param unknown_type $key
	 */
	private function decodeIfSet($key) {
		if (isset($_POST[$key]))
			$_POST[$key] = utf8_decode($_POST[$key]);
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
			echo Ajax::wrapJS('closeOverlay();');
		}
	}
	
	/**
	* Parse post data and try to insert training to database
	*/
	private function parsePostData() {
		$Mysql = Mysql::getInstance();

		$AutoParseKeys   = array();
		$AutoParseKeys[] = 'kcal';
		$AutoParseKeys[] = 'comment';
		$AutoParseKeys[] = 'partner';
		$AutoParseKeys[] = 'sportid';

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

		$this->columns[]         = 'time';
		$this->values[]          = $time;
		$this->columns[]         = 's';
		$this->values[]          = $time_in_s;

		// Prepare values for distances
		if ($Sport->usesDistance()) {
			$AutoParseKeys[]     = 'distance';
			$this->columns[]     = 'is_track';
			$this->values[]      = isset($_POST['is_track']) ? 1 : 0;
			$this->columns[]     = 'pace';
			$this->values[]      = Helper::Pace($distance, $time_in_s);
		}

		// Prepare values for outside-sport
		if ($Sport->isOutside()) {
			$AutoParseKeys[]     = 'weatherid';
			$AutoParseKeys[]     = 'route';
			$this->columns[]     = 'elevation';
			$this->values[]      = isset($_POST['elevation']) ? $_POST['elevation'] : 0;
			$this->columns[]     = 'clothes';
			$this->values[]      = isset($_POST['clothes']) ? implode(',', array_keys($_POST['clothes'])) : '';
			$this->columns[]     = 'temperature';
			$this->values[]      = isset($_POST['temperature']) && is_numeric($_POST['temperature']) ? $_POST['temperature'] : NULL;
		
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

		if ($Sport->hasTypes() && $_POST['typeid'] > 0) {
			$Type = new Type($_POST['typeid']);

			$AutoParseKeys[]     = 'typeid';

			if ($Type->hasSplits())
				$AutoParseKeys[] = 'splits';
		}
		if ($Sport->isRunning()) {
			$AutoParseKeys[]     = 'shoeid';
			$this->columns[]     = 'abc';
			$this->values[]      = isset($_POST['abc']) ? 1 : 0;
		}
		
		foreach ($AutoParseKeys as $var) {
			$this->columns[] = $var;
			$this->values[]  = isset($_POST[$var]) ? Helper::Umlaute(Helper::CommaToPoint($_POST[$var])) : NULL;
		}
	}

	/**
	 * Get time or 'false' if not readable
	 * @return mixed
	 */
	private function getTimeFromPost() {
		if (!isset($_POST['zeit']))
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
		
		if (!isset($_POST['s'])) {
			$this->errorString = 'Es muss eine Trainingszeit angegeben sein.';
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
		$ms        = explode(".", Helper::CommaToPoint($_POST['s']));
		$dauer     = explode(":", $ms[0]);

		if (!isset($ms[1]))
			$ms[1] = 0;

		$time_in_s = round(3600 * $dauer[0] + 60 * $dauer[1] + $dauer[2] + ($ms[1]/100), 2);

		if ($time_in_s == 0) {
			$this->errorString = 'Es muss eine Trainingszeit angegeben sein.';
			return false;
		}

		return $time_in_s;
	}
	
	/**
	* Insert training to database
	*/
	private function insertTraining() {
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

		$ATL       = Helper::ATL($this->time);
		$CTL       = Helper::CTL($this->time);
		$TRIMP     = Helper::TRIMP($id);
		$Training  = new Training($id);
		
		$Mysql->query('UPDATE `'.PREFIX.'training` SET `trimp`="'.$TRIMP.'" WHERE `id`='.$id.' LIMIT 1');
		$Mysql->query('UPDATE `'.PREFIX.'training` SET `vdot`="'.JD::Training2VDOT($id).'" WHERE `id`='.$id.' LIMIT 1');
		
		if ($ATL > MAX_ATL)
			Config::update('MAX_ATL', $ATL);
		if ($CTL > MAX_CTL)
			Config::update('MAX_ATL', $CTL);
		if ($TRIMP > MAX_TRIMP)
			Config::update('MAX_ATL', $TRIMP);
		
		if ($Training->get('shoeid') > 0)
			$Mysql->query('UPDATE `'.PREFIX.'shoe` SET `km`=`km`+'.$Training->get('distance').', `time`=`time`+'.$Training->get('s').' WHERE `id`='.$Training->get('shoeid').' LIMIT 1');
		
		if (CONF_TRAINING_DO_ELEVATION) {
			$Training->elevationCorrection();
		
			$Mysql->update(PREFIX.'training', $id, 'elevation', $Training->GpsData()->calculateElevation());
		}

		$this->insertFailed = false;
	}
}
?>