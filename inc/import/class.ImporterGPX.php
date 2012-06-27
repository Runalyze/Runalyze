<?php
/**
 * Class: ImporterGPX
 */
class ImporterGPX extends Importer {
	/**
	 * Parsed XML as array
	 * @var array
	 */
	private $XML;

	/**
	 * Set values for training from file or post-data
	 */
	protected function setTrainingValues() {
		$XML = simplexml_load_string_utf8($this->getFileContentAsString());

		if (empty($XML->trk) || empty($XML->trk->trkseg)) {
			$this->addError('Es scheint keine GPX-Trainingsdatei zu sein.');
			return;
		}

		$this->XML   = $XML;
		$this->parseXML();
	}

	/**
	 * Parse xml
	 */
	private function parseXML() {
		if (isset($this->XML->trk->desc))
			$this->set('comment', (string)$this->XML->trk->desc);

		if (isset($this->XML->time))
			$time = strtotime((string)$this->XML->time);
		elseif (isset($this->XML->metadata) && isset($this->XML->metadata->time))
			$time = strtotime((string)$this->XML->metadata->time);
		else
			$time = $this->XML->trk->trkseg->trkpt[0]->time;

		$this->XML = $this->XML->trk->trkseg;

		$this->set('sportid', CONF_RUNNINGSPORT);
		$this->set('datum', date("d.m.Y", $time));
		$this->set('zeit', date("H:i", $time));
		$this->set('time', $time);

		$this->parseTrack();
	}

	/**
	 * Parse track
	 */
	private function parseTrack() {
		$time      = array();
		$latitude  = array();
		$longitude = array();
		$distance  = array();
		$elevation = array();
		$heartrate = array();
		$pace      = array();
		$i         = 0;

		foreach ($this->XML->trkpt as $Point) {
			if (!empty($Point['lat'])) {
				$lat  = round((double)$Point['lat'], 7);
				$lon  = round((double)$Point['lon'], 7);
				$dist = $i==0 ? 0 : round(GpsData::distance($lat, $lon, $latitude[$i-1], $longitude[$i-1]), 3);
			} elseif (isset($latitude[$i-1])) {
				$lat  = $latitude[$i-1];
				$lon  = $longitude[$i-1];
				$dist = 0;
			} else
				continue;

			$time[]      = (int)strtotime((string)$Point->time) - $this->get('time');
			$elevation[] = 0;
			$heartrate[] = 0;
			$latitude[]  = $lat;
			$longitude[] = $lon;
			$distance[]  = ($i==0) ? $dist : end($distance) + $dist;
			$pace[]      = ((end($distance) - prev($distance)) != 0)
				? round((end($time) - prev($time)) / (end($distance) - prev($distance)))
				: 0;

			$i++;
		}

		$this->setArrayForTime($time);
		$this->setArrayForLatitude($latitude);
		$this->setArrayForLongitude($longitude);
		$this->setArrayForElevation($elevation);
		$this->setArrayForDistance($distance);
		$this->setArrayForHeartrate($heartrate);
		$this->setArrayForPace($pace);

		$this->set('s', end($time));
		$this->set('distance', round(end($distance), 2));
	}
}