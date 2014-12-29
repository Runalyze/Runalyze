<?php
/**
 * This file contains class::ElevationInfo
 * @package Runalyze\DataObjects\Training\View
 */

use Runalyze\Configuration;
use Runalyze\Parameter\Application\ElevationMethod;
use Runalyze\Data\Elevation;

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

		echo '<div class="panel-heading">';
		$this->displayHeader();
		echo '</div>';

		echo '<div class="panel-content">';
		$this->displayStandardValues();
		$this->displayDifferentAlgorithms();
		$this->displayDifferentAlgorithmsWithOriginalData();
		$this->displayPlot();
		$this->displayElevationCorrection();
		$this->displayInformation();
		echo '</div>';
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
		echo HTML::h1( sprintf( __('Elevation calculation for: %s'), $this->Training->DataView()->getTitleWithCommentAndDate() ) );
	}

	/**
	 * Display standard values
	 */
	protected function displayStandardValues() {
		if ($this->manualElevation != $this->calculatedElevation)
			$useCalculatedValueLink = Ajax::window('<a class="small as-input" href="'.$this->Training->Linker()->urlToElevationInfo('use-calculated-value=true').'">&raquo; '.__('apply data').'</a>', 'small');
		else
			$useCalculatedValueLink = '';

		$Fieldset = new FormularFieldset( __('General data') );
		$Fieldset->setHtmlCode('
			<div class="w50">
				<label>'.Ajax::tooltip(__('manual value'), __('If you did not insert a value by hand, this value has been calculated.')).'</label>
				<span class="as-input">'.$this->manualElevation.'&nbsp;m</span>
			</div>
			<div class="w50">
				<label>'.__('Lowest point').'</label>
				<span class="as-input">'.$this->lowestPoint.'&nbsp;m</span>
			</div>
			<div class="w50">
				<label>'.Ajax::tooltip(__('calculated value'), __('This value is calculated with your current configuration. The saved value may be outdated.') ).'</label>
				<span class="as-input">'.$this->calculatedElevation.'&nbsp;m</span> '.$useCalculatedValueLink.'
			</div>
			<div class="w50">
				<label>'.__('Highest point').'</label>
				<span class="as-input">'.$this->highestPoint.'&nbsp;m</span>
			</div>
			<div class="w50">
				<label>&oslash; '.__('Gradient').'</label>
				<span class="as-input">'.$this->Training->DataView()->getGradientInPercent().'</span>
			</div>
			<div class="w50">
				<label>'.__('Up/Down').'</label>
				<span class="as-input">'.$this->Training->DataView()->getElevationUpAndDown().'</span>
			</div>
		');
		$Fieldset->display();
	}

	/**
	 * Display different algorithms
	 */
	protected function displayDifferentAlgorithms() {
		if (!$this->Training->hasArrayAltitude())
			return;

		$Code  = $this->getDifferentAlgorithmsFor($this->Training->getArrayAltitude());
		$Code .= '<p class="small info">'.__('You can choose the algorithm and treshold in the configuration window.').'</p>';

		$Fieldset = new FormularFieldset( __('Elevation data for different algorithms/tresholds') );
		$Fieldset->setHtmlCode($Code);
		$Fieldset->display();
	}

	/**
	 * Display different algorithms with original data
	 */
	protected function displayDifferentAlgorithmsWithOriginalData() {
		if (!$this->Training->hasArrayAltitudeOriginal() || !$this->Training->elevationWasCorrected())
			return;

		$Fieldset = new FormularFieldset( __('Elevation data for different algorithms/tresholds (based on original data)') );
		$Fieldset->setId('table-with-original-data');
		$Fieldset->setCollapsed();
		$Fieldset->setHtmlCode( $this->getDifferentAlgorithmsFor($this->Training->getArrayAltitudeOriginal()) );
		$Fieldset->display();
	}

	/**
	 * Get different algorithms for
	 * @param array $array
	 * @return string
	 */
	protected function getDifferentAlgorithmsFor($array) {
		$Method        = new ElevationMethod();
		$Calculator    = new Elevation\Calculation\Calculator($array);
		$TresholdRange = range(1, 10);
		$Algorithms    = array(
			array(ElevationMethod::NONE, false),
			array(ElevationMethod::THRESHOLD, true),
			array(ElevationMethod::DOUGLAS_PEUCKER, true),
			//array(ElevationMethod::REUMANN_WITKAM, false)
		);

		$Code  = '<table class="fullwidth zebra-style small">';
		$Code .= '<thead>';
		$Code .= '<tr><th class="r">'.__('Treshold').':</th>';
		foreach ($TresholdRange as $t)
			$Code .= '<th>'.$t.'</th>';
		$Code .= '</tr>';
		$Code .= '</thead>';
		$Code .= '<tbody>';

		foreach ($Algorithms as $Algorithm) {
			$Method->set($Algorithm[0]);
			$Calculator->setMethod($Method);
			$Code .= '<tr><td class="b">'.$Method->valueAsLongString().'</td>';

			if ($Algorithm[1]) {
				foreach ($TresholdRange as $t) {
					$highlight = (Configuration::ActivityView()->elevationMinDiff() == $t) && (Configuration::ActivityView()->elevationMethod()->value() == $Algorithm[0]) ? ' highlight' : '';
					$Calculator->setThreshold($t);
					$Calculator->calculate();
					$Code .= '<td class="r'.$highlight.'">'.$Calculator->totalElevation().'&nbsp;m</td>';
				}
			} else {
				$Calculator->calculate();
				$Code .= '<td class="c'.(Configuration::ActivityView()->elevationMethod()->value() == $Algorithm[0] ? ' highlight' : '').'" colspan="'.count($TresholdRange).'">'.$Calculator->totalElevation().'&nbsp;m</td>';
			}

			$Code .= '</tr>';
		}

		$Code .= '</tbody>';
		$Code .= '</table>';

		return $Code;
	}

	/**
	 * Display elevation correction
	 */
	protected function displayElevationCorrection() {
		$Fieldset = new FormularFieldset( __('Elevation correction') );

		if ($this->Training->elevationWasCorrected()) {
			$Fieldset->addSmallInfo( __('Elevation data have been corrected.') );
		} else {
			$Fieldset->setHtmlCode(
				'<p class="warning small block" id="gps-results">
					'.__('Elevation data has not been corrected.').'<br>
					<br>
					<a class="ajax" target="gps-results" href="'.$this->Training->Linker()->urlToElevationCorrection().'"><strong>&raquo; '.__('correct now').'</strong></a>
				</p>'
			);
		}

		$Fieldset->display();
	}

	/**
	 * Display plot
	 */
	protected function displayPlot() {
		$Context = new Runalyze\View\Activity\Context(
			$this->Training->id(),
			SessionAccountHandler::getId()
		);

		$Plot = new Runalyze\View\Activity\Plot\ElevationAlgorithms($Context);

		echo '<fieldset>';
		echo '<legend>'.__('Compare algorithms').'</legend>';
		echo '<div id="plot-'.$Plot->getKey().'" class="plot-container">';
		$Plot->display();
		echo '</div>';
		echo '</fieldset>';
	}

	/**
	 * Display elevation correction
	 */
	protected function displayInformation() {
		$Fieldset = new FormularFieldset( __('Note for elevation data') );
		$Fieldset->setId('general-information');
		$Fieldset->setCollapsed();
		$Fieldset->addSmallInfo(
				__('The calculation of elevation data is very difficult - there is not one single solution. '.
					'Bad gps data can be corrected via srtm-data but these are only available in a 90x90m grid and not always perfectly accurate. '.
					'In addition, every platform uses another algorithm to determine the elevation value (for up-/downwards). '.
					'We give you therefore the possibility to choose algorithm and treshold such that the values fit your experiences.')
		);

		$Fieldset->display();
	}
}