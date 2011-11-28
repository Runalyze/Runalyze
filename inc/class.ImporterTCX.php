<?php
/**
 * This file contains the class::ImporterTCX for importing a training from TCX
 */
/**
 * Class: ImporterTCX
 * 
 * @author Hannes Christiansen <mail@laufhannes.de>
 * @version 1.0
 */
class ImporterTCX extends Importer {
	/**
	 * Parsed XML as array
	 * @var array
	 */
	private $xml;

	/**
	 * Set values for training from file or post-data
	 */
	protected function setTrainingValues() {
		$FileContent = $this->getFileContentAsString();
		$Parser      = new XmlParser($FileContent);
		$this->xml   = $Parser->getContentAsArray();

		// TODO: CodeCleaning

		$xml = $this->xml;

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

		if (!$this->isGarminFile())
			return;

		$starttime = strtotime($xml['trainingcenterdatabase']['activities']['activity']['id']['value']);
		$start_tmp = $starttime;
		$laps = $xml['trainingcenterdatabase']['activities']['activity']['lap'];

		if (!is_array($laps))
			return array('error' => 'Es konnten keine gestoppten Runden gefunden werden.');

		if (Helper::isAssoc($laps))
			$laps = array($laps);

		foreach($laps as $lap) {
			if (isset($lap['calories']))
				$calories += $lap['calories']['value'];
			if (isset($lap['intensity']) && strtolower($lap['intensity']['value']) == 'active') {
				$splits[] = round($lap['distancemeters']['value']/1000, 2).'|'.Helper::Time(round($lap['totaltimeseconds']['value']), false, 2);
			}

			if (Helper::isAssoc($lap['track']))
				$lap['track'] = array($lap['track']);

			if (!isset($lap['track']) || !is_array($lap['track']) || empty($lap['track']))
				Error::getInstance()->addWarning('ImporterTCX: Keine Track-Daten vorhanden.');

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

		$this->set('sportid', CONF_RUNNINGSPORT);
		$this->set('datum', date("d.m.Y", $starttime));
		$this->set('zeit', date("H:i", $starttime));
		$this->set('distance', round(end($distance), 2));
		$this->set('kcal', $calories);
		$this->set('splits', implode('-', $splits));

		if (!empty($time))
			$this->set('s', end($time));

		if (!empty($heartrate)) {
			$this->set('pulse_avg', round(array_sum($heartrate)/count($heartrate)));
			$this->set('pulse_max', max($heartrate));
		}

		if (isset($xml['trainingcenterdatabase']['activities']['activity']['training']))
			$this->set('comment', $xml['trainingcenterdatabase']['activities']['activity']['training']['plan']['name']['value']);

		$this->setArrayForTime($time);
		$this->setArrayForLatitude($latitude);
		$this->setArrayForLongitude($longitude);
		$this->setArrayForElevation($altitude);
		$this->setArrayForDistance($distance);
		$this->setArrayForHeartrate($heartrate);
		$this->setArrayForPace($pace);
	}

	/**
	 * Is the given file an garmin-TCX-file?
	 * @return bool
	 */
	private function isGarminFile() {
		if (isset($this->xml['trainingcenterdatabase']['activities']['activity']) && is_array($this->xml['trainingcenterdatabase']['activities']['activity']))
			return true;

		$this->addError('Es scheint keine Garmin-Trainingsdatei zu sein.');
		return false;
	}
}
?>