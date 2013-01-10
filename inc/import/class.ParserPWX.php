<?php
/**
 * Parser for pwx-files from Peaksware/Trainingpeaks
 * @see http://www.peaksware.com/PWX/1/0/pwx.xsd
 * @see http://support.trainingpeaks.com/api/easy-file-upload.aspx
 * @author Hannes Christiansen <mail@laufhannes.de> 
 */
class ParserPWX extends Parser {
	/**
	 * Complete XML
	 * @var SimpleXMLElement
	 */
	private $CompleteXML = null;

	/**
	 * XML
	 * @var SimpleXMLElement
	 */
	private $XML = null;

	/**
	 * Starttime, can be changed for pauses
	 * @var int
	 */
	private $starttime = 0;

	/**
	 * Calories
	 * @var int
	 */
	private $calories = 0;

	/**
	 * Boolean flag: loading xml failed
	 * @var boolean
	 */
	private $loadXmlFailed = false;

	/**
	 * Construct a new parser, needs XML
	 * @param string $XML
	 */
	public function __construct($XML) {
		$this->CompleteXML = simplexml_load_string_utf8($XML);

		if ($this->CompleteXML == false) {
			Filesystem::throwErrorForBadXml($XML);
			$this->loadXmlFailed = true;
			return false;
		}

		if ($this->checkXML())
			$this->initXML();
	}

	/**
	 * Parse current training 
	 */
	public function parseTraining() {
		$this->initEmptyValues();

		if ($this->loadXmlFailed) {
			$this->addError('Die PWX-Datei konnte nicht erfolgreich geladen werden.');
			return;
		}

		$this->parseStarttime();
		$this->parseLogEntries();
		$this->parseLaps();
		$this->setValues();
	}

	/**
	 * Check if XML is correct
	 * @return boolean 
	 */
	protected function checkXML() {
		if (!$this->CompleteXML instanceof SimpleXMLElement) {
			$this->addError('Keine XML-Datei gegeben.');
			return false;
		}

		if (is_null($this->CompleteXML->workout)) {
			$this->addError('Die XML-Datei enth&auml;lt keine Daten.');
			return false;
		}

		return true;
	}

	/**
	 * Init internal XML
	 */
	protected function initXML() {
		$this->XML = $this->CompleteXML->workout;
	}

	/**
	 * Init all empty values 
	 */
	protected function initEmptyValues() {
		$this->starttime = 0;
		$this->calories  = 0;
		$this->data      = array(
			'laps_distance' => 0,
			'laps_time'     => 0,
			'time_in_s'     => array(),
			'latitude'      => array(),
			'longitude'     => array(),
			'altitude'      => array(),
			'km'            => array(),
			'heartrate'     => array(),
			'pace'          => array(),
			'splits'        => array(),
			'splits_resting'=> array());
	}

	/**
	 * Set all parsed values
	 */
	protected function setValues() {
		$this->setAllArrays();
		$this->setGeneralValues();
		$this->setOptionalValue();
	}

	/**
	 * Set general values
	 */
	protected function setGeneralValues() {
		$this->setCreatorValues();
		$this->set('sportid', CONF_MAINSPORT);
		$this->set('kcal', $this->calories);

		if (empty($this->data['splits']))
			$this->data['splits'] = $this->data['splits_resting'];

		$this->set('splits', implode('-', $this->data['splits']));
		$this->set('use_vdot', 1);
	}

	/**
	 * Set values about creator 
	 */
	protected function setCreatorValues() {
		$this->set('creator_details', trim($this->getCreator()));
	}

	/**
	 * Get name of creator
	 * @return string
	 */
	protected function getCreator() {
		$String = '';

		if (isset($this->XML->device)) {
			$String = (string)$this->XML->device->make.', '.((string)$this->XML->device->model);
			$String .= ' ('.((string)$this->XML->device['id']).')';
		}

		return $String;
	}

	/**
	 * Set optional values
	 */
	protected function setOptionalValue() {
		if (!empty($this->data['km']))
			$this->set('distance', round(end($this->data['km']), 2));
		elseif ($this->data['laps_distance'] > 0)
			$this->set('distance', round($this->data['laps_distance'], 2));

		if (!empty($this->data['time_in_s']))
			$this->set('s', end($this->data['time_in_s']));
		elseif ($this->data['laps_time'] > 0)
			$this->set('s', $this->data['laps_time']);

		$this->set('comment', (string)$this->XML->cmt);
	}

	/**
	 * Set all arrays
	 */
	protected function setAllArrays() {
		$this->setArrayForTime($this->data['time_in_s']);
		$this->setArrayForLatitude($this->data['latitude']);
		$this->setArrayForLongitude($this->data['longitude']);
		$this->setArrayForElevation($this->data['altitude']);
		$this->setArrayForDistance($this->data['km']);
		$this->setArrayForHeartrate($this->data['heartrate']);
		$this->setArrayForPace($this->data['pace']);
	}

	/**
	 * Parse starttime
	 */
	protected function parseStarttime() {
		$this->starttime = strtotime((string)$this->XML->time);

		$this->set('time', $this->starttime);
		$this->set('datum', date("d.m.Y", $this->starttime));
		$this->set('zeit', date("H:i", $this->starttime));
	}

	/**
	 * Parse log entries 
	 */
	protected function parseLogEntries() {
		foreach ($this->XML->sample as $Log)
			$this->parseLogEntry($Log);
	}

	/**
	 * Parse log entry
	 * @param SimpleXMLElement $Log 
	 */
	protected function parseLogEntry($Log) {
		if ((int)$Log->timeoffset == 0)
			return;

		if (empty($Log->dist) && (empty($Log->lat) || empty($Log->lon)))
			return;

		if (!empty($Log->dist))
			$dist = round((int)$Log->dist)/1000;
		elseif (empty($this->data['latitude']))
			$dist = 0;
		else
			$dist = end($this->data['km']) + GpsData::distance(end($this->data['latitude']), end($this->data['longitude']),
															(double)$Log->lat, (double)$Log->lon);

		$this->data['time_in_s'][] = (int)$Log->timeoffset;
		$this->data['latitude'][]  = (!empty($Log->lat)) ? (double)$Log->lat : 0;
		$this->data['longitude'][] = (!empty($Log->lon)) ? (double)$Log->lon : 0;
		$this->data['altitude'][]  = (!empty($Log->alt)) ? round((int)$Log->alt) : 0;
		$this->data['heartrate'][] = (!empty($Log->hr)) ? round((int)$Log->hr) : 0;
		$this->data['km'][]        = $dist;
		$this->data['pace'][]      = ((end($this->data['km']) - prev($this->data['km'])) != 0)
									? round((end($this->data['time_in_s']) - prev($this->data['time_in_s'])) / (end($this->data['km']) - prev($this->data['km'])))
									: 0;
	}

	/**
	 * Parse all laps
	 */
	protected function parseLaps() {
		if (isset($this->XML->segment))
			foreach ($this->XML->segment->summarydata as $Lap)
				$this->parseLap($Lap);
	}

	/**
	 * Parse one single lap
	 * @param SimpleXMLElement $Lap
	 */
	protected function parseLap($Lap) {
		if (!empty($Lap->Calories))
			$this->calories += (int)$Lap->Calories;

		$this->data['laps_time']     += round((int)$Lap->duration);
		$this->data['laps_distance'] += round((int)$Lap->dist)/1000;

		// TODO: save pause-laps too with special identification
		$SplitString = round((int)$Lap->dist/1000, 2).'|'.Time::toString(round((int)$Lap->duration), false, 2);
		$SplitKey    = (!is_null($Lap->work) || (int)$Lap->work == 1) ? 'splits' : 'splits_resting';
		$this->data[$SplitKey][] = $SplitString;
	}
}