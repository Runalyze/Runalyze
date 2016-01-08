<?php
/**
 * This file contains class::ElevationInfo
 * @package Runalyze\DataObjects\Training\View
 */

use Runalyze\Configuration;
use Runalyze\Parameter\Application\ElevationMethod;
use Runalyze\Data;
use Runalyze\View\Activity\Context;
use Runalyze\Model;
use Runalyze\Activity\Elevation;

/**
 * Display elevation info for a training
 * 
 * @author Hannes Christiansen
 * @package Runalyze\DataObjects\Training\View
 */
class ElevationInfo {
	/**
	 * Training object
	 * @var \Runalyze\View\Activity\Context
	 */
	protected $Context;

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
	 * @param \Runalyze\View\Activity\Context $context
	 */
	public function __construct(Context $context) {
		$this->Context = $context;

		$this->handleRequest();
	}

	/**
	 * Handle request
	 */
	protected function handleRequest() {
		if (Request::param('use-calculated-value') == 'true') {
			$oldObject = clone $this->Context->activity();
			$this->Context->activity()->set(Model\Activity\Entity::ELEVATION, $this->Context->route()->elevation());

			$Updater = new Model\Activity\Updater(
				DB::getInstance(),
				$this->Context->activity(),
				$oldObject
			);
			$Updater->setAccountID(SessionAccountHandler::getId());
			$Updater->update();
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
		$this->lowestPoint = min( $this->Context->route()->elevations() );
		$this->highestPoint = max( $this->Context->route()->elevations() );

		$this->manualElevation = $this->Context->activity()->elevation();
		$this->calculatedElevation = $this->Context->route()->elevation();
	}

	/**
	 * Display header
	 */
	protected function displayHeader() {
		echo HTML::h1( sprintf( __('Elevation calculation for: %s'), $this->Context->dataview()->titleWithComment() ) );
	}

	/**
	 * Display standard values
	 */
	protected function displayStandardValues() {
		if ($this->manualElevation != $this->calculatedElevation) {
			$Linker = new Runalyze\View\Activity\Linker($this->Context->activity());
			$useCalculatedValueLink = Ajax::window('<a class="small as-input" href="'.$Linker->urlToElevationInfo('use-calculated-value=true').'">&raquo; '.__('apply data').'</a>', 'normal');
		} else {
			$useCalculatedValueLink = '';
		}

		$Fieldset = new FormularFieldset( __('General data') );
		$Fieldset->setHtmlCode('
			<div class="w50">
				<label>'.Ajax::tooltip(__('manual value'), __('If you did not insert a value by hand, this value has been calculated.')).'</label>
				<span class="as-input">'.Elevation::format($this->manualElevation).'</span>
			</div>
			<div class="w50">
				<label>'.__('Lowest point').'</label>
				<span class="as-input">'.Elevation::format($this->lowestPoint).'</span>
			</div>
			<div class="w50">
				<label>'.Ajax::tooltip(__('calculated value'), __('This value is calculated with your current configuration. The saved value may be outdated.') ).'</label>
				<span class="as-input">'.Elevation::format($this->calculatedElevation).'</span> '.$useCalculatedValueLink.'
			</div>
			<div class="w50">
				<label>'.__('Highest point').'</label>
				<span class="as-input">'.Elevation::format($this->highestPoint).'</span>
			</div>
			<div class="w50">
				<label>&oslash; '.__('Gradient').'</label>
				<span class="as-input">'.$this->Context->dataview()->gradientInPercent().'</span>
			</div>
			<div class="w50">
				<label>'.__('Up/Down').'</label>
				<span class="as-input">+'.Elevation::format($this->Context->route()->elevationUp()).' / -'.Elevation::format($this->Context->route()->elevationDown()).'</span>
			</div>
		');
		$Fieldset->display();
	}

	/**
	 * Display different algorithms
	 */
	protected function displayDifferentAlgorithms() {
		if (!$this->Context->route()->hasElevations()) {
			return;
		}

		$Code  = $this->getDifferentAlgorithmsFor($this->Context->route()->elevations());
		$Code .= '<p class="small info">'.__('You can choose the algorithm and threshold in the configuration window.').'</p>';

		$Fieldset = new FormularFieldset( __('Elevation data for different algorithms/thresholds') );
		$Fieldset->setHtmlCode($Code);
		$Fieldset->display();
	}

	/**
	 * Display different algorithms with original data
	 */
	protected function displayDifferentAlgorithmsWithOriginalData() {
		if (!$this->Context->route()->hasOriginalElevations() || !$this->Context->route()->elevationsCorrected()) {
			return;
		}

		$Fieldset = new FormularFieldset( __('Elevation data for different algorithms/thresholds (based on original data)') );
		$Fieldset->setId('table-with-original-data');
		$Fieldset->setCollapsed();
		$Fieldset->setHtmlCode( $this->getDifferentAlgorithmsFor($this->Context->route()->elevationsOriginal()) );
		$Fieldset->display();
	}

	/**
	 * Get different algorithms for
	 * @param array $array
	 * @return string
	 */
	protected function getDifferentAlgorithmsFor($array) {
		$Method        = new ElevationMethod();
		$Calculator    = new Data\Elevation\Calculation\Calculator($array);
		$TresholdRange = range(1, 10);
		$Algorithms    = array(
			array(ElevationMethod::NONE, false),
			array(ElevationMethod::THRESHOLD, true),
			array(ElevationMethod::DOUGLAS_PEUCKER, true),
			//array(ElevationMethod::REUMANN_WITKAM, false)
		);

		$Code  = '<table class="fullwidth zebra-style small">';
		$Code .= '<thead>';
		$Code .= '<tr><th class="r">'.__('Threshold').':</th>';
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
					$Code .= '<td class="r'.$highlight.'">'. Elevation::format($Calculator->totalElevation()).'</td>';
				}
			} else {
				$Calculator->calculate();
				$Code .= '<td class="c'.(Configuration::ActivityView()->elevationMethod()->value() == $Algorithm[0] ? ' highlight' : '').'" colspan="'.count($TresholdRange).'">'.Elevation::format($Calculator->totalElevation()).'</td>';
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
		$Url = (new Runalyze\View\Activity\Linker($this->Context->activity()))->urlToElevationCorrection();
		$Fieldset = new FormularFieldset( __('Elevation correction') );
		$Links = array();

		if ($this->Context->route()->elevationsCorrected()) {
			$textInfo = __('Elevation data has been corrected.').($this->Context->route()->elevationsSource() != '' ? ' ('.$this->Context->route()->elevationsSource().')': '');
			$rawLinks = $this->getLinksForCorrectionStrategies();
		} else {
			$textInfo = __('Elevation data has not been corrected.');
			$rawLinks = array('' => __('correct now'));
		}

		foreach ($rawLinks as $urlAppendix => $text) {
			$Links[] = '<a class="ajax" target="gps-results" href="'.$Url.$urlAppendix.'"><strong>&raquo; '.$text.'</strong></a>';
		}

		$Fieldset->setHtmlCode(
			'<p class="info block" id="gps-results">'.$textInfo.'</p>
			<p class="info block">'.implode('<br>', $Links).'</p>'
		);

		$Fieldset->display();
	}

	/**
	 * @return array
	 */
	protected function getLinksForCorrectionStrategies() {
		return array(
			'&strategy=GeoTIFF' => sprintf(__('correct again using %s'), __('srtm files')),
			'&strategy=Geonames' => sprintf(__('correct again using %s'), 'geonames.org'),
			'&strategy=GoogleMaps' => sprintf(__('correct again using %s'), 'maps.google.com'),
			'&strategy=none' => __('remove corrected elevation data')
		);
	}

	/**
	 * Display plot
	 */
	protected function displayPlot() {
		$Plot = new Runalyze\View\Activity\Plot\ElevationAlgorithms($this->Context);

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
		$Fieldset->addInfo(
				__('The calculation of elevation data is very difficult - there is not one single solution. '.
					'Bad gps data can be corrected via srtm-data but these are only available in a 90x90m grid and not always perfectly accurate. '.
					'In addition, every platform uses another algorithm to determine the elevation value (for up-/downwards). '.
					'We give you therefore the possibility to choose algorithm and threshold such that the values fit your experience.')
		);

		$Fieldset->display();
	}
}
