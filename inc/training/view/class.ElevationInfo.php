<?php
/**
 * This file contains class::ElevationInfo
 * @package Runalyze\DataObjects\Training\View
 */
/**
 * Display elevation info for a training
 * 
 * @author Hannes Christiansen
 * @package Runalyze\DataObjects\Training\View
 */
class ElevationInfo {
	/**
	 * Training object
	 * @var \TrainingObject
	 */
	protected $Training = null;

	/**
	 * Lowest point
	 * @var int
	 */
	protected $lowestPoint = 0;

	/**
	 * Highest point
	 * @var int
	 */
	protected $highestPoint = 0;

	/**
	 * Manual elevation
	 * @var int
	 */
	protected $manualElevation = 0;

	/**
	 * Calculated elevation
	 * @var int
	 */
	protected $calculatedElevation = 0;

	/**
	 * Constructor
	 * @param TrainingObject $Training Training object
	 */
	public function __construct(TrainingObject &$Training) {
		$this->Training = $Training;

		$this->handleRequest();
	}

	/**
	 * Handle request
	 */
	protected function handleRequest() {
		if (Request::param('use-calculated-value') == 'true') {
			$this->Training->setCalculatedValueAsElevation();
		}
	}

	/**
	 * Display
	 */
	public function display() {
		$this->calculateValues();

		$this->displayHeader();
		$this->displayStandardValues();
		$this->displayDifferentAlgorithms();
		$this->displayPlot();
		$this->displayElevationCorrection();
		$this->displayInformation();
	}

	/**
	 * Calculate values
	 */
	protected function calculateValues() {
		$this->lowestPoint = min( $this->Training->getArrayAltitude() );
		$this->highestPoint = max( $this->Training->getArrayAltitude() );

		$this->manualElevation = $this->Training->getElevation();
		$this->calculatedElevation = $this->Training->GpsData()->calculateElevation();
	}

	/**
	 * Display header
	 */
	protected function displayHeader() {
		echo HTML::h1('H&ouml;henmeter-Berechnung zum '.$this->Training->DataView()->getTitleWithCommentAndDate());
	}

	/**
	 * Display standard values
	 */
	protected function displayStandardValues() {
		if ($this->manualElevation != $this->calculatedElevation)
			$useCalculatedValueLink = Ajax::window('<a class="small asInput" href="'.$this->Training->Linker()->urlToElevationInfo('use-calculated-value=true').'">&raquo; &uuml;bernehmen</a>', 'small');
		else
			$useCalculatedValueLink = '';

		$Fieldset = new FormularFieldset('Allgemeine Daten');
		$Fieldset->setHtmlCode('
			<div class="w50">
				<label>'.Ajax::tooltip('manueller Wert', 'Wenn beim Erstellen keine H&ouml;henmeter angegeben wurden, wurde der berechnete Wert &uuml;bernommen.').'</label>
				<span class="asInput">'.$this->manualElevation.'&nbsp;m</span>
			</div>
			<div class="w50">
				<label>niedrigster Punkt</label>
				<span class="asInput">'.$this->lowestPoint.'&nbsp;m</span>
			</div>
			<div class="w50">
				<label>berechneter Wert</label>
				<span class="asInput">'.$this->calculatedElevation.'&nbsp;m</span> '.$useCalculatedValueLink.'
			</div>
			<div class="w50">
				<label>h&ouml;chster Punkt</label>
				<span class="asInput">'.$this->highestPoint.'&nbsp;m</span>
			</div>
			<div class="w50">
				<label>&oslash; Steigung</label>
				<span class="asInput">'.$this->Training->DataView()->getGradientInPercent().'</span>
			</div>
			<div class="w50">
				<label>Auf-/Abstieg</label>
				<span class="asInput">'.$this->Training->DataView()->getElevationUpAndDown().'</span>
			</div>
		');
		$Fieldset->display();
	}

	/**
	 * Display elevation correction
	 */
	protected function displayDifferentAlgorithms() {
		$Code = '';

		if ($this->Training->hasArrayAltitudeOriginal()) {
			$Code .= '<strong class="small">Anhand der Originaldaten:</strong>';
			$Code .= $this->getDifferentAlgorithmsFor($this->Training->getArrayAltitudeOriginal());
			$Code .= '<br />';
		}

		if ($this->Training->hasArrayAltitude()) {
			$Code .= '<strong class="small">Anhand der korrigierten Daten:</strong>';
			$Code .= $this->getDifferentAlgorithmsFor($this->Training->getArrayAltitude());
		}

		$Code .= '<p class="small info">Den zu verwendenden Algorithmus und Schwellenwert kannst du in der Konfiguration selbst festlegen.</p>';

		$Fieldset = new FormularFieldset('Verschiedene Algorithmen zur H&ouml;hengl&auml;ttung');
		$Fieldset->setHtmlCode($Code);
		$Fieldset->display();
	}

	/**
	 * Get different algorithms for
	 * @param array $array
	 * @return string
	 */
	protected function getDifferentAlgorithmsFor($array) {
		$Calculator    = new ElevationCalculator($array);
		$TresholdRange = range(1, 10);
		$Algorithms    = array(
			array(ElevationCalculator::$ALGORITHM_NONE, false),
			array(ElevationCalculator::$ALGORITHM_TRESHOLD, true),
			array(ElevationCalculator::$ALGORITHM_DOUGLAS_PEUCKER, true),
			//array(ElevationCalculator::$ALGORITHM_REUMANN_WITKAMM, false)
		);

		$Code  = '<table class="small fullWidth">';
		$Code .= '<thead>';
		$Code .= '<tr><td></td><td class="c" colspan="'.count($TresholdRange).'">Schwellenwert</td></tr>';
		$Code .= '<tr><th></th>';
		foreach ($TresholdRange as $t)
			$Code .= '<th>'.$t.'</th>';
		$Code .= '</tr>';
		$Code .= '</thead>';
		$Code .= '<tbody>';

		foreach ($Algorithms as $i => $Algorithm) {
			$Calculator->setAlgorithm($Algorithm[0]);
			$Code .= '<tr class="'.HTML::trClass($i).'"><td class="b">'.ElevationCalculator::nameOfCurrentAlgorithm().'</td>';

			if ($Algorithm[1]) {
				foreach ($TresholdRange as $t) {
					$highlight = CONF_ELEVATION_MIN_DIFF == $t && CONF_ELEVATION_METHOD == $Algorithm[0] ? ' highlight' : '';
					$Calculator->setTreshold($t);
					$Calculator->calculateElevation();
					$Code .= '<td class="r'.$highlight.'">'.$Calculator->getElevation().'&nbsp;m</td>';
				}
			} else {
				$Calculator->calculateElevation();
				$Code .= '<td class="c'.(CONF_ELEVATION_METHOD == $Algorithm[0] ? ' highlight' : '').'" colspan="'.count($TresholdRange).'">'.$Calculator->getElevation().'&nbsp;m</td>';
			}

			$Code .= '</tr>';
		}

		$Code .= '</tbody>';
		$Code .= '</table>';

		return $Code;;
	}

	/**
	 * Display elevation correction
	 */
	protected function displayElevationCorrection() {
		$Fieldset = new FormularFieldset('H&ouml;henkorrektur');

		if ($this->Training->elevationWasCorrected()) {
			$Fieldset->addSmallInfo('Die H&ouml;hendaten wurden bereits korrigiert.');
		} else {
			$Fieldset->setHtmlCode(
				'<p class="warning small block" id="gps-results">
					Die H&ouml;hendaten wurden noch nicht korrigiert.<br />
					<br />
					<a class="ajax" target="gps-results" href="'.$this->Training->Linker()->urlToElevationCorrection().'" title="H&ouml;hendaten korrigieren"><strong>&raquo; jetzt korrigieren</strong></a>
				</p>'
			);
		}

		$Fieldset->display();
	}

	/**
	 * Display plot
	 */
	protected function displayPlot() {
		$Plot = new TrainingPlotElevationCompareAlgorithms($this->Training);

		echo '<fieldset>';
		echo '<legend>Algorithmen im Diagramm</legend>';
		echo '<div id="plot-'.$Plot->getKey().'" class="plot-container">';
		$Plot->displayAsSinglePlot();
		echo '</div>';
		echo '</fieldset>';
	}

	/**
	 * Display elevation correction
	 */
	protected function displayInformation() {
		$Fieldset = new FormularFieldset('Hinweis zu H&ouml;henmetern');
		$Fieldset->setId('general-information');
		$Fieldset->setCollapsed();
		$Fieldset->addSmallInfo('
			Die Berechnung von H&ouml;henmetern erweist sich immer als schwierig, weil es kein &quot;richtig&quot; gibt.
			Fehlerhafte GPS-Daten k&ouml;nnen zwar durch SRTM-Daten korrigiert werden,
			aber auch diese liegen nur in einem Raster von 90x90m vor und sind teilweise stark verrauscht.
			Um dieses Rauschen zu entfernen, kann ein Gl&auml;ttungsalgorithmus verwendet werden.
			Da der verwendete Algorithmus (und seine Einstellungen) von Programm zu Programm unterscheiden,
			kommt man niemals bei jeder Software (wie Garmin Connect, SportTracks, etc.) auf die gleichen Werte.
		');

		$Fieldset->display();
	}
}