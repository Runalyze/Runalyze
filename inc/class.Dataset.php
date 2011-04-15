<?php
/**
 * This file contains the class to handle and display the datasat for any training
 */
/**
 * Class: Dataset
 * 
 * @author Hannes Christiansen <mail@laufhannes.de>
 * @version 1.0
 * @uses class::Mysql
 * @uses class:Error
 *
 * Last modified 2011/04/15 10:30 by Hannes Christiansen
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
	 * Constructor
	 */
	public function __construct() {
		$dat = Mysql::getInstance()->fetch('SELECT * FROM `ltb_dataset` WHERE `modus`>=2 AND `position`!=0 ORDER BY `position` ASC');
		if ($dat === false) {
			Error::getInstance()->addError('No dataset in database is active.');
			return false;
		}

		$this->data = $dat;
		$this->column_count = count($dat);
	}

	/**
	 * Set training
	 * @param int $id
	 */
	public function setTrainingId($id) {
		$this->trainingId = $id;
		$this->Training = new Training($id);
	}

	/**
	 * Load a group of trainings for summary
	 * @param int $sportid
	 * @param int $timestamp_start
	 * @param int $timestamp_end
	 */
	public function loadGroupOfTrainings($sportid, $timestamp_start, $timestamp_end) {
		$this->setTrainingId(-1);

		$query_set = '';
		foreach ($this->data as $set)
			if ($set['zusammenfassung'] == 1)
				if ($set['zf_mode'] != 'AVG')
					$query_set .= ', '.$set['zf_mode'].'(`'.$set['name'].'`) as `'.$set['name'].'`';

		$summary = Mysql::getInstance()->fetch('SELECT *, SUM(1) as `num`'.$query_set.' FROM `ltb_training` WHERE `sportid`='.$sportid.' AND `time` BETWEEN '.($timestamp_start-10).' AND '.($timestamp_end-10).' GROUP BY `sportid`');
		foreach ($summary as $var => $value)
			$this->Training->set($var, $value);

		foreach ($this->data as $set)
			if ($set['zusammenfassung'] == 1 && $set['zf_mode'] == 'AVG') {
				$avg_data = Mysql::getInstance()->fetch('SELECT AVG(`'.$set['name'].'`) as `'.$set['name'].'` FROM `ltb_training` WHERE `time` BETWEEN '.($timestamp_start-10).' AND '.($timestamp_end-10).' AND `'.$set['name'].'`!=0 AND `'.$set['name'].'`!="" AND `sportid`="'.$sportid.'" GROUP BY `sportid`');
				if ($avg_data === false)
					$avg_data[$set['name']] = '';
				$this->Training->set($set['name'], $avg_data[$set['name']]);
			}
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
		$name = Helper::Time( $this->Training->get('dauer') ); 
		$icon = Icon::getSportIcon($this->Training->get('sportid'), $name);
		echo $this->Training->trainingLink($icon);
	}

	/**
	 * Display this dataset as a table-row
	 */
	public function displayTableColumns() {
		foreach ($this->data as $set) {
			$this->displayDataset($set);
		}
	}

	/**
	 * Display a single dataset
	 * @param array $dataset
	 */
	private function displayDataset($set) {
		if ($this->isSummaryMode() && $set['zusammenfassung'] == 0) {
			echo Helper::emptyTD();
			return;
		}

		$class = $set['class'] != '' ? ' class="'.$set['class'].'"' : '';
		$style = $set['style'] != '' ? ' style="'.$set['style'].'"' : '';

		echo '<td'.$class.$style.'>'.$this->getDataset($set['name']).'</td>';
	}

	/**
	 * Get content for a given dataset
	 * @param string $name
	 * @return string
	 */
	private function getDataset($name) {
		switch($name) {
			case 'sportid':
				return $this->datasetSport();
			case 'typid':
				return $this->datasetType();
			case 'time':
				return $this->datasetDate();
			case 'distanz':
				return $this->datasetDistance();
			case 'dauer':
				return $this->datasetTime();
			case 'pace':
				return $this->datasetPace();
			case 'hm':
				return $this->datasetElevation();
			case 'kalorien':
				return $this->datasetCalories();
			case 'puls':
				return $this->datasetPulse();
			case 'puls_max':
				return $this->datasetPulseMax();
			case 'trimp':
				return $this->datasetTRIMP();
			case 'temperatur':
				return $this->datasetTemperature();
			case 'wetterid':
				return $this->datasetWeather();
			case 'strecke':
				return $this->datasetPath();
			case 'kleidung':
				return $this->datasetClothes();
			case 'splits':
				return $this->datasetSplits();
			case 'bemerkung':
				return $this->datasetDescription();
			case 'trainingspartner':
				return $this->datasetPartner();
			case 'laufabc':
				return $this->datasetABC();
			case 'schuhid':
				return $this->datasetShoe();
			case 'vdot':
				return $this->datasetVDOT();
			default:
				return '&nbsp;';
		}
	}

	/**
	 * Dataset for: `sportid`
	 */
	private function datasetSport() {
		return Icon::getSportIcon($this->Training->get('sportid'), Helper::Sport($this->Training->get('sportid')));
	}

	/**
	 * Dataset for: `typid`
	 */
	private function datasetType() {
		$type_id = $this->Training->get('typid');

		$text = Helper::TypeShort($type_id);
		if (Helper::TypeHasHighRPE($type_id))
			return '<strong>'.$text.'</strong>';

		return $text;
	}

	/**
	 * Dataset for: `time`
	 */
	private function datasetDate() {
		return date("H:i", $this->Training->get('time')) != "00:00" ? date("H:i", $this->Training->get('time')).' Uhr' : '';
	}

	/**
	 * Dataset for: `distanz`
	 */
	private function datasetDistance() {
		return ($this->Training->get('distanz') != 0) ? Helper::Km($this->Training->get('distanz'), 1, $this->Training->get('bahn')) : '';
	}

	/**
	 * Dataset for: `dauer`
	 */
	private function datasetTime() {
		return Helper::Time($this->Training->get('dauer'));
	}

	/**
	 * Dataset for: `pace`
	 */
	private function datasetPace() {
		return Helper::Speed($this->Training->get('distanz'), $this->Training->get('dauer'), $this->Training->get('sportid'));
	}

	/**
	 * Dataset for: `hm`
	 */
	private function datasetElevation() {
		return ($this->Training->get('hm') != 0)
			? '<span title="&oslash; '.round($this->Training->get('hm')/$this->Training->get('distanz')/10, 2).' &#37;">'.$this->Training->get('hm').' hm</span>'
			: '';
	}

	/**
	 * Dataset for: `kalorien`
	 */
	private function datasetCalories() {
		return Helper::Unknown($this->Training->get('kalorien')).' kcal';
	}

	/**
	 * Dataset for: `puls`
	 */
	private function datasetPulse() {
		return Helper::PulseString($this->Training->get('puls'));
	}

	/**
	 * Dataset for: `puls_max`
	 */
	private function datasetPulseMax() {
		return Helper::PulseString($this->Training->get('puls_max'));
	}

	/**
	 * Dataset for: `trimp`
	 */
	private function datasetTRIMP() {
		return '<span style="color:#'.Helper::Stresscolor($this->Training->get('trimp')).';">'.$this->Training->get('trimp').'</span>';
	}

	/**
	 * Dataset for: `temperatur`
	 */
	private function datasetTemperature() {
		return ($this->Training->get('temperatur') != 0) ? $this->Training->get('temperatur').' &#176;C' : '';
	}

	/**
	 * Dataset for: `wetterid`
	 */
	private function datasetWeather() {
		return ($this->Training->get('wetterid') != 1) ? Helper::WeatherImage($this->Training->get('wetterid')) : '';
	}

	/**
	 * Dataset for: `strecke`
	 */
	private function datasetPath() {
		return ($this->Training->get('strecke') != '') ? 'Strecke: '.$this->Training->get('strecke') : '';
	}

	/**
	 * Dataset for: `kleidung`
	 */
	private function datasetClothes() {
		return $this->Training->getStringForClothes();
	}

	/**
	 * Dataset for: `splits`
	 */
	private function datasetSplits() {
		if ($this->Training->get('splits') == '')
			return;

		return Icon::get( Icon::$CLOCK, 'Zwischenzeiten gespeichert');
	}

	/**
	 * Dataset for: `bemerkung`
	 */
	private function datasetDescription() {
		return '<span title="'.$this->Training->get('bemerkung').'">'.Helper::Cut($this->Training->get('bemerkung'), 20).'</span>';
	}

	/**
	 * Dataset for: `trainingspartner`
	 */
	private function datasetPartner() {
		return ($this->Training->get('trainingspartner') != '') ? 'mit '.$this->Training->get('trainingspartner') : '';
	}

	/**
	 * Dataset for: `laufabc`
	 */
	private function datasetABC() {
		if ($this->Training->get('laufabc') == 0)
			return;

		return Icon::get( Icon::$ABC, 'Lauf-ABC');
	}

	/**
	 * Dataset for: `schuhid`
	 */
	private function datasetShoe() {
		return Helper::Shoe($this->Training->get('schuhid'));
	}

	/**
	 * Dataset for: `vdot`
	 */
	private function datasetVDOT() {
		$VDOT = $this->Training->get('vdot');
		if ($VDOT == 0)
			return '';
		if ( $VDOT > (VDOT_FORM+3) )
			$icon = Icon::$FORM_UP;
		elseif ( $VDOT > (VDOT_FORM+1) )
			$icon = Icon::$FORM_UP_HALF;
		elseif ( $VDOT < (VDOT_FORM-3) )
			$icon = Icon::$FORM_DOWN;
		elseif ( $VDOT < (VDOT_FORM-1) )
			$icon = Icon::$FORM_DOWN_HALF;
		else
			$icon = Icon::$FORM_NORMAL;

		$title = $VDOT.': 3.000m in '.Helper::Prognosis(3, 0, $VDOT).',
			5 km in '.Helper::Prognosis(5, 0, $VDOT).',
			10 km in '.Helper::Prognosis(10, 0, $VDOT).',
			HM in '.Helper::Prognosis(21.1, 0, $VDOT);

		return Icon::get($icon, $title);
	}
}
?>