<?php
/**
 * This file contains the class to handle every training.
 */
/**
 * Class: Stat
 * 
 * @author Hannes Christiansen <mail@laufhannes.de>
 * @version 1.0
 * @uses class::Mysql ($mysql)
 * @uses class:Error ($error)
 * @uses $global
 *
 * Last modified 2011/01/15 18:21 by Hannes Christiansen
 */

// TODO: Dataset über diese Klasse oder eine eigene? DataBrowser?

class Training {
	static $minElevationDiff = 3; // minimal difference per step to be recognized
	static $everyNthElevationPoint = 5; // only every n-th point will be taken for the elevation

	private $id,
		$data;

	function __construct($id) {
		global $error, $mysql, $global;
		if (!is_numeric($id) || $id == NULL) {
			$error->add('ERROR', 'An object of class::Training must have an ID: <$id='.$id.'>');
			return false;
		}
		$dat = $mysql->fetch('ltb_training', $id);
		if ($dat === false) {
			$error->add('ERROR', 'This training (ID='.$id.') does not exist.');
			return false;
		}
		$this->id = $id;
		$this->data = $dat;
	}

	/**
	 * Get a column from DB-row
	 * @param string $var wanted column from database
	 * @return mixed
	 */
	function get($var) {
		global $error, $mysql;

		if (isset($this->data[$var]))
			return $this->data[$var];

		$error->add('WARNING','Training::get - unknown column "'.$var.'"',__FILE__,__LINE__);
	}

	/**
	 * Get string for clothes
	 * @return string all clothes comma seperated
	 */
	function getStringForClothes() {
		global $error, $mysql;

		if ($this->get('kleidung') != '') {
			$kleidungen = array();
			$kleidungen_data = $mysql->fetch('SELECT `name` FROM `ltb_kleidung` WHERE `id` IN ('.$this->get('kleidung').') ORDER BY `order` ASC', false, true);
			foreach ($kleidungen_data as $data)
				$kleidungen[] = $data['name'];
			return implode(', ', $kleidungen);
		}

		return '';
	}

	/**
	 * Gives a HTML-link for using jTraining which is calling the training-tpl
	 * @param string $name displayed link name
	 * @return string HTML-link to this training
	 */
	function trainingLink($name) {
		return Ajax::trainingLink($this->id, $name);
	}

	/**
	 * Display the whole training
	 */
	function display() {
		$this->displayHeader();
		$this->displayPlotsAndMap();
		$this->displayTrainingData();
	}

	/**
	 * Display header
	 */
	function displayHeader() {
		echo('<h1>'.NL);
		$this->displayEditLink();
		$this->displayTitle();
		echo('<small class="right">');
		$this->displayDate();
		echo('</small>');
		echo('</h1>'.NL.NL.NL);
	}

	/**
	 * Display plot links, first plot and map
	 */
	function displayPlotsAndMap() {
		$plots = $this->getPlotTypesAsArray();

		echo('<div class="right">'.NL);
		if (sizeof($plots) > 0) {
			echo('<small class="right">'.NL);
			$this->displayPlotLinks('trainingGraph');
			echo('</small>'.NL);
			echo('<br /><br />'.NL);
			$this->displayPlot(key($plots));
			echo('<br />'.NL);
			echo('<br />'.NL.NL);
		}

		if ($this->hasPositionData()) {
			$this->displayRoute();
		}
		echo('</div>'.NL.NL);
	}

	/**
	 * Display training data
	 */
	function displayTrainingData() {
		$this->displayTable();
		$this->displayRounds();
		$this->displaySplits();
	}

	/**
	 * Display the title for this training
	 * @param bool $short short version without description, default: false
	 */
	function displayTitle($short = false) {
		echo ($this->get('sportid') == RUNNINGSPORT)
			? Helper::Typ($this->get('typid'))
			: Helper::Sport($this->get('sportid'));
		if (!$short) {
			if ($this->get('laufabc') == 1)
				echo(' <img src="img/abc.png" alt="Lauf-ABC" />');
			if ($this->get('bemerkung') != '')
				echo (': '.$this->get('bemerkung'));
		}
	}

	/**
	 * Display the formatted date
	 */
	function displayDate() {
		$time = $this->get('time');
		$date = date('H:i', $time) != '00:00'
			? date('d.m.Y, H:i', $time).' Uhr'
			: date('d.m.Y', $time);
		echo (Helper::Wochentag( date('w', $time) ).', '.$date);
	}

	/**
	 * Get array for all plot types
	 * @return array
	 */
	private function getPlotTypesAsArray() {
		$plots = array();
		if ($this->hasPaceData())
			$plots['pace'] = array('name' => 'Pace', 'src' => 'lib/draw/training_pace.php?id='.$this->id);
		if ($this->hasSplitsData())
			$plots['splits'] = array('name' => 'Splits', 'src' => 'lib/draw/splits.php?id='.$this->id);
		if ($this->hasPulseData())
			$plots['pulse'] = array('name' => 'Puls', 'src' => 'lib/draw/training_puls.php?id='.$this->id);
		if ($this->hasElevationData())
			$plots['elevation'] = array('name' => 'Höhenprofil', 'col' => 'arr_alt', 'src' => 'lib/draw/training_hm.php?id='.$this->id);

		return $plots;
	}

	/**
	 * Display links for all plots
	 * @param string $rel related string (id of img)
	 */
	function displayPlotLinks($rel = 'trainingGraph') {
		$links = array();
		$plots = $this->getPlotTypesAsArray();
		foreach ($plots as $key => $array)
			$links[] = '<a class="jImg" rel="trainingGraph" href="'.$array['src'].'">'.$array['name'].'</a>'.NL;
		echo implode(' | ', $links);
	}

	/**
	 * Display a plot
	 * @param string $type name of the plot, should be in getPlotTypesAsArray
	 */
	function displayPlot($type = 'undefined') {
		global $error, $mysql;

		// TODO Use class::Draw as soon as possible
		$plots = $this->getPlotTypesAsArray();
		if (isset($plots[$type]))
			echo '<img id="trainingGraph" src="'.$plots[$type]['src'].'" alt="'.$plots[$type]['name'].'" />'.NL;
		else
			$error->add('WARNING','Training::displayPlot - Unknown plottype "'.$type.'"',__FILE__,__LINE__);
	}

	/**
	 * Display table with all training data
	 */
	function displayTable() {
		include('tpl/tpl.Training.table.php');
	}

	/**
	 * Display defined splits
	 */
	function displaySplits() {
		if ($this->hasSplitsData()) {
			echo('<strong>Zwischenzeiten:</strong><br />'.NL);
			echo('<table cellspacing="0" style="width:480px;">'.NL);
			echo('<tr>'.NL);

			$splits = explode('-', str_replace('\r\n', '-', $this->get('splits')));
			$error->add('TODO','Training::splits Bitte testen: Ist die Pace-Berechnung korrekt?',__FILE__,__LINE__);
			$error->add('TODO','Training::splits Gesamtschnitt/Vorgabe/etc.',__FILE__,__LINE__);
			
			for ($i = 0, $num = count($splits); $i < $num; $i++) {
				$split = explode('|', $splits[$i]);
				$timedata = explode(':', $split[1]);
				$distance[] = $split[0];
				$time[] = round(($timedata[0]*60 + $timedata[1])/$split[0]);
			
				$border = ($i+1)%3 != 0 ? ' style="border-right:1px solid #CCC;"' : '';
			
				echo('
					<td class="a'.($i%2+1).' b">'.Helper::Km($split[0]).'</td>
					<td class="a'.($i%2+1).'">'.Helper::Time($timedata[0]*60 + $timedata[1]).'</td>
					<td class="a'.($i%2+1).'"'.$border.'><small>'.Helper::Time($time[$i]).'/km</small></td>');
																// Or use Helper::Pace here?
				if (($i+1)%3 == 0)
					echo('
				</tr>
				<tr>');
				if ($i == $num-1)
					echo('
					<td class="a'.($i%2+1).'" colspan="'.(9 - 3*($i+1)%3).'" />');
			}

			echo('</tr>'.NL);
			echo('</table>'.NL);
		}
	}

	/**
	 * Display (computed) rounds
	 */
	function displayRounds() {
		if ($this->hasPaceData()) {
			echo('<strong>Berechnete Rundenzeiten:</strong><br />'.NL);
			echo('<table cellspacing="0">'.NL);
			$km 				= 1;
			$kmIndex	 		= array(0);
			$positiveElevation 	= 0;
			$negativeElevation 	= 0;
			$distancePoints 	= explode('|', $this->get('arr_dist'));
			$timePoints 		= explode('|', $this->get('arr_time'));
			$heartPoints 		= explode('|', $this->get('arr_heart'));
			$elevationPoints 	= explode('|', $this->get('arr_alt'));
			$numberOfPoints 	= sizeof($distancePoints);

			foreach ($distancePoints as $i => $distance) {
				if (floor($distance) == $km || $i == $numberOfPoints-1) {
					$km++;
					$kmIndex[] = $i;
					$previousIndex = $kmIndex[count($kmIndex)-2];
					$pace = Helper::Pace(($distancePoints[$i] - $distancePoints[$previousIndex]), ($timePoints[$i] - $timePoints[$previousIndex]));
					echo('<tr class="a'.($i%2+1).' r">
							<td>'.Helper::Time($timePoints[$i]).'</td>
							<td>'.Helper::Km($distance, 2).'</td>
							<td class="small">'.$pace.'</td>');
					if (count($heartPoints) > 1) {
						$heartRateOfThisKm = array_slice($heartPoints, $previousIndex, ($i - $previousIndex));
						echo('<td class="small">'.round(array_sum($heartRateOfThisKm)/count($heartRateOfThisKm)).'</td>');
					}
					if (count($elevationPoints) > 1)
						echo('<td class="small">'.($positiveElevation != 0 ? '+'.$positiveElevation : '0').'/'.($negativeElevation != 0 ? '-'.$negativeElevation : '0').'</td>
							</tr>');
					$positiveElevation = 0;
					$negativeElevation = 0;
				} elseif ($i != 0 && count($elevationPoints) > 1 && $elevationPoints[$i] != 0 && $elevationPoints[$i-1] != 0) {
					$elevationDifference = $elevationPoints[$i] - $elevationPoints[$i-1];
					$positiveElevation += ($elevationDifference > self::$minElevationDiff) ? $elevationDifference : 0;
					$negativeElevation -= ($elevationDifference < -1*self::$minElevationDiff) ? $elevationDifference : 0;
				}
			}
			echo('</table>'.NL.NL);
		}
	}

	/**
	 * Display route on GoogleMaps
	 */
	function displayRoute() {
		echo '<iframe src="lib/gpx/karte.php?id='.$this->id.'" style="border:0;" width="482" height="300" frameborder="0"></iframe>';
	}

	/**
	 * Has the training information about splits?
	 */
	private function hasSplitsData() {
		return $this->get('splits') != '';
	}

	/**
	 * Has the training information about pace?
	 */
	private function hasPaceData() {
		return $this->get('arr_pace') != '';
	}

	/**
	 * Has the training information about elevation?
	 */
	private function hasElevationData() {
		return $this->get('arr_alt') != '';
	}

	/**
	 * Has the training information about pulse?
	 */
	private function hasPulseData() {
		return $this->get('arr_heart') != '';
	}

	/**
	 * Has the training information about position?
	 */
	function hasPositionData() {
		return $this->get('arr_lat') != '' && $this->get('arr_lon') != '';
	}

	/**
	 * Display create window
	 */
	function displayCreateWindow() {
		// TODO Set up class.Training.createWindow.php ?
	}

	/**
	 * Display link for edit window
	 */
	function displayEditLink() {
		echo Ajax::window('<a href="inc/class.Training.edit.php?id='.$this->id.'" title="Training editieren"><img src="img/edit.png" alt="Training editieren" /></a> ','small');
	}

	/**
	 * Parse a tcx-file
	 */
	function parseTcx() {
		// TODO
	}

	/**
	 * Correct the elevation data
	 */
	function elevationCorrection() {
		global $mysql;

		if (!$this->hasPositionData())
			return;

		$latitude = explode('|', $this->get('arr_lat'));
		$longitude = explode('|', $this->get('arr_lon'));
		$altitude = array();

		$num = count($latitude);
		for ($i = 0; $i < $num; $i++) {
			if ($i%self::$everyNthElevationPoint == 0) {
				$lats[] = $latitude[$i];
				$longs[] = $longitude[$i];
			}
			if (($i+1)%(20*self::$everyNthElevationPoint) == 0 || $i == $num-1) {
				$html = @file_get_contents('http://ws.geonames.org/srtm3?lats='.implode(',', $lats).'&lngs='.implode(',', $longs));
				if (substr($html,0,1) == '<')
					die('Something went wrong connecting to geonames.org - i: '.$i);
				$data = explode("\r\n", $html);

				foreach ($data as $k => $v)
					$data[$k] = trim($v);
				$data_num = count($data) - 1; // There is always one empty element

				for ($d = 0; $d < $data_num; $d++)
					for ($j = 0; $j < self::$everyNthElevationPoint; $j++)
						$altitude[] = trim($data[$d]);

				$lats = array();
				$longs = array();
			}
		}

		$mysql->update('ltb_training', $this->id, 'arr_alt', implode('|', $altitude));
		echo('Success.');
	}

	/**
	 * Compress data for lower database-traffic
	 */
	function compressData() {
		// TODO
	}
}
?>