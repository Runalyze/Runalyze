<?php
/**
 * This file contains class::Plot
 * @package Runalyze\Draw
 */

use Runalyze\Configuration;

/**
 * General plotting class
 * @author Hannes Christiansen
 * @package Runalyze\Draw
 */
class Plot {
	/**
	 * CSS-ID for displaying this plot
	 * @var string
	 */
	private $cssID = '';

	/**
	 * Width of the image
	 * @var mixed
	 */
	private $width = false;

	/**
	 * Height of the image
	 * @var int
	 */
	private $height = false;

	/**
	 * Internal Data, is public for direct access
	 * @var array
	 */
	public $Data = array();

	/**
	 * Array for all options for FLOT
	 * @var array
	 */
	public $Options = array();

	/**
	 * Options only used by Runalyze's plot handler
	 * @var array
	 */
	public $PlotOptions = array();

	/**
	 * Array containing annotations for this plot
	 * @var array
	 */
	private $Annotations = array();

	/**
	 * Error string
	 * @var string
	 */
	private $ErrorString = '';

	/**
	 * Constructor
	 * @param string $cssID
	 * @param mixed $width
	 * @param mixed $height
	 */
	public function __construct($cssID, $width = 480, $height = 190) {
		$this->width   = $width;
		$this->height  = $height;
		$this->cssID   = $cssID;
	}

	/**
	 * Raise an error
	 * @param string $string
	 */
	public function raiseError($string) {
		$this->ErrorString = $string;
	}

	/**
	 * Get standard div for a special plot
	 * @param string $id
	 * @param int $width
	 * @param int $height
	 * @return string
	 */
	public static function getDivFor($id, $width, $height) {
		return '<div style="position:relative;width:'.$width.'px;height:'.$height.'px;margin:0 auto;">'.self::getInnerDivFor($id, $width, $height).'</div>';
	}

	/**
	 * Get standard div for a special plot
	 * @param string $id
	 * @param int $width
	 * @param int $height
	 * @param bool $hidden
	 * @param string $class
	 * @return string
	 */
	public static function getInnerDivFor($id, $width, $height, $hidden = false, $class = '') {
		return '<div class="flot '.Ajax::$IMG_WAIT.' '.$class.($hidden ? ' flot-hide' : '').'" id="'.$id.'" style="width:'.$width.'px;height:'.$height.'px;position:absolute;"></div>';
	}

	/**
	 * Get a div for this plot
	 */
	public function getDiv() {
		return self::getDivFor($this->cssID, $this->width, $this->height);
	}

	/**
	 * Output div
	 */
	public function outputDiv() {
		echo $this->getDiv();
	}

	/**
	 * Output JavaScript
	 * @param boolean $removeOldPlot
	 */
	public function outputJavaScript($removeOldPlot = false) {
		if ($removeOldPlot)
			echo Ajax::wrapJS('RunalyzePlot.remove("'.$this->cssID.'");');

		$this->convertData();

		$bindedCode = (strlen($this->ErrorString) > 0) ? $this->getJSForError() : $this->getMainJS();

		echo Ajax::wrapJS('RunalyzePlot.preparePlot("'.$this->cssID.'","'.$this->width.'","'.$this->height.'",function(){'.$bindedCode.'});');
	}

	/**
	 * Get main functionalities for this plot
	 * @return string
	 */
	private function getMainJS() {
		return 'RunalyzePlot.addPlot("'.$this->cssID.'", '.
				json_encode($this->Data).', '.
				Ajax::json_encode_jsfunc($this->Options).', '.
				json_encode($this->PlotOptions).', '.
				json_encode($this->Annotations).');';
	}

	/**
	 * Get code for an error
	 * @return string
	 */
	private function getJSForError() {
		return '$("#'.$this->cssID.'").append(\'<div class="flot-error"><span>'.$this->ErrorString.'</span></div>\');';
	}

	/**
	 * Convert internal data to correct array for JSON
	 */
	private function convertData() {
		foreach ($this->Data as $i => $Data) {
			$Points = array();
			foreach ($Data['data'] as $x => $y) {
				$Points[] = array($x, $y);
			}
			$this->Data[$i]['data'] = $Points;
		}

		if (Configuration::ActivityView()->smoothCurves() && !isset($this->Options['series']['curvedLines']['apply']))
			$this->Options['series']['curvedLines']['apply'] = true;

		if (empty($this->Data) && strlen($this->ErrorString) == 0)
			$this->raiseError('Es sind keine Daten vorhanden.');
	}

	/**
	 * Set title to plot
	 * @param string $title
	 * @param string $position
	 */
	public function setTitle($title, $position = 'center') {
		// Titles currently not supported anymore
	}

	/**
	 * Add an annotation to plot
	 * @param double $x
	 * @param double $y
	 * @param string $text
	 * @param double $toX relative repositioning in px
	 * @param double $toY relative repositioning in px
	 */
	public function addAnnotation($x, $y, $text, $toX = 0, $toY = 0) {
		$this->Annotations[] = array('x' => $x, 'y' => $y, 'text' => $text, 'toX' => $toX, 'toY' => $toY);
	}

	/**
	 * Clear all annotations
	 */
	public function clearAnnotations() {
		$this->Annotations = array();
	}

	/**
	 * Use chart for a line with steps
	 */
	public function lineWithSteps() {
		$this->Options['series']['lines']['steps'] = true;
	}

	/**
	 * Use chart for a line with steps
	 */
	public function lineWithPoints() {
		$this->Options['series']['lines']['show'] = true;
		$this->Options['series']['points']['show'] = true;
	}

	/**
	 * Use chart for points
	 * @param int $size
	 */
	public function showPoints($size = -1) {
		$this->Options['series']['points']['show'] = true;

		if ($size != -1)
			$this->Options['series']['points']['lineWidth'] = $size;
	}

    /**
     * Show series as points
     * @param int $series
     */
    public function showAsPoints($series) {
        $this->Data[$series]['points']['show'] = true;
    }


    /**
	* Set line width for series
	* @param int $series
	* @param int $width
	*/
	public function setLineWidth($series, $width) {
		$this->Data[$series]['lines']['lineWidth'] = $width;
	}

	/**
	 * Set shadow width for series
	 * @param int $series
	 * @param int $size
	 */
	public function setShadowSize($series, $size) {
		$this->Data[$series]['shadowSize'] = $size;
	}

	/**
	* Show specific series as bars
	* @param int $series
	*/
	public function showAsBars($series, $barWidth = 0, $lineWidth = 0) {
		$this->Data[$series]['bars']['show'] = true;

		if ($barWidth > 0)
			$this->Data[$series]['bars']['barWidth'] = $barWidth;

		if ($lineWidth > 0)
			$this->Data[$series]['bars']['lineWidth'] = $lineWidth;
	}

	/**
	 * Use chart for bar-plot
	 * @param bool $withPadding Only useful if ticks are set
	 */
	public function showBars($withPadding = false) {
		$this->Options['series']['bars']['show'] = true;

		if ($withPadding) {
			$maxLength = 0;
			foreach ($this->Data as $data)
				if (count($data['data']) > $maxLength)
					$maxLength = count($data['data']);

			$this->Options['xaxis']['min'] = -1;
			$this->Options['xaxis']['max'] = $maxLength;
			//$this->Options['xaxis']['autoscaleMargin'] = 0.02;
		}
	}

	/**
	 * Use stacked lines/bars
	 */
	public function stacked() {
		$this->Options['series']['stack'] = "stack";
		$this->Options['series']['lines']['fill'] = true;
	}

	/**
	 * Add a threshold
	 * @param string $axis
	 * @param double $from
	 * @param string $color
	 * @param int $lineWidth
	 */
	public function addThreshold($axis, $from, $color ='#000', $lineWidth = 1) {
		$this->Options['grid']['markings'][] = array(
			'color' => $color,
			'lineWidth' => $lineWidth,
			$axis.'axis' => array('from' => $from, 'to' => $from));
	}

	/**
	 * Add a marking area
	 * @param string $axis
	 * @param double $from
	 * @param double $to
	 * @param string $color
	 */
	public function addMarkingArea($axis, $from, $to, $color ='rgba(255,255,255,0.2)') {
		$this->Options['grid']['markings'][] = array(
			'color' => $color,
			$axis.'axis' => array('from' => $from, 'to' => $to));
	}

	/**
	 * Set the margin for the whole grid
	 * @param int $margin
	 */
	public function setMarginForGrid($margin) {
		$this->Options['grid']['minBorderMargin'] = $margin;
	}

	/**
     * Put grid above data
     */
    public function setGridAboveData() {
        $this->Options['grid']['aboveData'] = true;
    }


	/**
	 * Set legend as table (not in one line as default)
	 * @param string $position
	 */
	public function setLegendAsTable($position = 'nw') {
		$this->Options['legend']['noColumns'] = 1;
		$this->Options['legend']['position'] = $position;
	}

	/**
	 * Set specific lines to be filled
	 * @param array $keys
	 */
	public function setLinesFilled($keys = array(), $opacity=0.7) {
		if (empty($keys))
			$keys = array_keys($this->Data);

		foreach ($keys as $key)
			$this->Data[$key]['lines']['fill'] = $opacity;
	}

	/**
	 * Set smoothing
	 * @var bool $flag
	 * @var bool $fit [optional]
	 */
	public function smoothing($flag = true, $fit = null) {
		$this->Options['series']['curvedLines']['apply'] = $flag;

		if (!is_null($fit))
			$this->Options['series']['curvedLines']['fit'] = $fit;
	}

	/**
	 * Hide labels on xaxis
	 */
	public function hideXLabels() {
		$this->Options['xaxis']['show'] = false;
	}

	/**
	 * Set labels on xaxis
	 * @param array $array
	 */
	public function setXLabels($array) {
		$this->Options['xaxis']['ticks'] = $array;
	}

	/**
	 * Set axis-mode to time
	 */
	public function setXAxisAsTime() {
		$this->Options['xaxis']['mode'] = "time";
        $this->Options['xaxis']['monthNames']=	array(
            __('Jan'),
            __('Feb'),
            __('Mar'),
            __('Apr'),
            __('May'),
            __('Jun'),
            __('Jul'),
            __('Aug'),
            __('Sep'),
            __('Oct'),
            __('Nov'),
            __('Dec'),
        );

	}

	/**
	 * Set axis-mode to time with specific format
	 * @param string $format
	 */
	public function setXAxisTimeFormat($format) {
		$this->setXAxisAsTime();
		$this->Options['xaxis']['timeformat'] = $format;
	}

	/**
	 * Set x-axis limits to a specific year
	 * @param int $Year
	 */
	public function setXAxisLimitedTo($Year) {
		$this->Options['xaxis']['min'] = mktime(1,0,0,1,1,$Year).'000';
		$this->Options['xaxis']['max'] = mktime(1,0,0,1,0,$Year+1).'000';
	}

	/**
	 * Set maximum of x-axis as today
	 */
	public function setXAxisMaxToToday() {
		$this->Options['xaxis']['max'] = time().'000';
	}

	/**
	 * Set axis-mode to time
	 * @param int $axis [optional]
	 */
	public function setYAxisAsTime($axis = false) {
		if ($axis === false)
			$this->Options['yaxis']['mode'] = "time";
		else
			$this->Options['yaxes'][$axis-1]['mode'] = "time";
	}

	/**
	 * Set axis-mode to time with specific format
	 * @param string $format
	 * @param int $axis [optional]
	 */
	public function setYAxisTimeFormat($format, $axis = false) {
		$this->setYAxisAsTime($axis);

		if ($axis === false)
			$this->Options['yaxis']['timeformat'] = $format;
		else
			$this->Options['yaxes'][$axis-1]['timeformat'] = $format;
	}

	/**
	 * Add y axis
	 * @param int $i
	 * @param string $position
	 * @param bool $align
	 * @param int $alignTo
	 * @return int
	 */
	public function addYAxis($i, $position, $align = true, $alignTo = 1) {
		$this->Options['yaxes'][$i-1]['position'] = $position;

		if ($position == 'right' && $align)
			$this->Options['yaxes'][$i-1]['alignTicksWithAxis'] = $alignTo;
	}

	/**
	 * Hide y axis
	 * @param int $i
	 */
	public function hideYAxis($i) {
		$this->Options['yaxes'][$i-1]['show'] = false;
	}

	/**
	 * Add unit to y axis
	 * @param int $i
	 * @param string $unit
	 * @param int $roundTo
	 * @param float $factor
	 */
	public function addYUnit($i, $unit, $roundTo = 2, $factor = 1) {
		if ($factor != 1) {
			$this->Options['yaxes'][$i-1]['tickFormatter'] = 'function (v) { v = v * '.$factor.'; return '.$this->jsRoundUnit($roundTo).' + \' '.$unit.'\'; }';
		} else {
			$this->Options['yaxes'][$i-1]['tickFormatter'] = 'function (v) { return '.$this->jsRoundUnit($roundTo).' + \' '.$unit.'\'; }';
		}
	}

	/**
	 * Add unit to x axis
	 * @param string $unit
	 * @param int $roundTo
	 */
	public function setXUnit($unit, $roundTo = 2) {
		$this->Options['xaxis']['tickFormatter'] = 'function (v) { return '.$this->jsRoundUnit($roundTo).' + \' '.$unit.'\'; }';
	}

	/**
	 * Add unit to x axis
	 * @param float $factor
	 * @param string $unit
	 * @param int $roundTo
	 */
	public function setXUnitFactor($factor, $unit, $roundTo = 2) {
		$this->Options['xaxis']['tickFormatter'] = 'function (v) { v = v * '.$factor.'; return '.$this->jsRoundUnit($roundTo).' + \' '.$unit.'\'; }';
	}

	/**
	 * JS function to round
	 * @param int $roundTo
	 * @return string
	 */
	private function jsRoundUnit($roundTo) {
		if ($roundTo == 0)
			return 'Math.round(v)';

		return 'Math.round(v * Math.pow(10,'.$roundTo.')) / Math.pow(10,'.$roundTo.')';
	}

	/**
	 * Set size for ticks on y-axis
	 * @param int $axis
	 * @param int $tickSize
	 * @param int $decimals
	 */
	public function setYTicks($axis, $tickSize, $decimals = false) {
		$this->Options['yaxes'][$axis-1]['minTickSize'] = $tickSize;

		if ($tickSize == null)
			unset($this->Options['yaxes'][$axis-1]['minTickSize']);

		if ($decimals !== false)
			$this->Options['yaxes'][$axis-1]['tickDecimals'] = $decimals;
	}

	/**
	 * Set limits to y-axis
	 * @param int $axis
	 * @param mixed $min
	 * @param mixed $max
	 * @param bool $autoscale
	 * @param mixed $factor
	 */
	public function setYLimits($axis, $min, $max, $autoscale = false, $factor = 'auto') {
		if ($autoscale) {
			$diff = $max - $min;
			if ($factor == 'auto') {
				$factor = pow(10, round(log10($diff))-1);

				if ($factor > 10)
					$factor = 10;
			}

			$minScaled = $min > 0 ? max(0, $min/$factor - 0.02*$diff) : $min/$factor - 0.02*$diff;
			$min = floor($minScaled)*$factor;
			$max = ceil($max/$factor+0.02*$diff)*$factor;

			$this->setYTicks($axis, $factor);
		}

		$this->Options['yaxes'][$axis-1]['min'] = $min;
		$this->Options['yaxes'][$axis-1]['max'] = $max;
	}

	/**
	 * Set y axis labels
	 * @param int $axis
	 * @param array $ticks
	 */
	public function setYAxisLabels($axis, $ticks) {
		$this->Options['yaxes'][$axis-1]['ticks'] = $ticks;
	}

	/**
	 * Set y axis to reverse
	 * @param int $axis
	 */
	public function setYAxisReverse($axis) {
		$this->Options['yaxes'][$axis-1]['transform'] = 'function(v){return -v;}';
		$this->Options['yaxes'][$axis-1]['inverseTransform'] = 'function(v){return -v;}';
	}

	/**
	 * Set y axis to reverse pace scale
	 * @param int $axis
	 */
	public function setYAxisPaceReverse($axis) {
		$this->Options['yaxes'][$axis-1]['transform'] = 'function(v){return 1/(v);}';
		$this->Options['yaxes'][$axis-1]['inverseTransform'] = 'function(v){return 1/v;}';
	}

	/**
	 * Set all zero points to null
	 */
	public function setZeroPointsToNull() {
		foreach ($this->Data as $series => $data) {
			$this->Data[$series]['data'] = array_map("PLOT__setZeroToNullMapper", $data['data']);
		}
	}

	/**
	 * Correct special characters like umlaute to unicode-HTML
	 * @param string $string
	 * @return string
	 */
	public static function correctSpecialChars($string) {
		$string    = utf8_encode($string);
		$encrypted = array("ß",      "Ä",      "Ö",      "Ü",      "ä",      "ö",      "ü");
		$correct   = array("&#223;", "&#196;", "&#214;", "&#220;", "&#228;", "&#246;", "&#252;");

		return str_replace($encrypted, $correct, $string);
	}

	/**
	 * Get JavaScript-timestamp for a day of a year
	 * @param int $year
	 * @param int $day
	 * @return int
	 */
	public static function dayOfYearToJStime($year, $day) {
		return mktime(12,0,0,1,$day,$year).'000';
	}

	/**
	 * Correct all values as JS-timestamps
	 * @param array $array
	 * @return array
	 */
	public static function correctValuesForTime($array) {
		return array_map("PLOT__correctValuesMapperForTime", $array);
	}

	/**
	 * Correct all pace-values to km/h
	 * @param array $array
	 * @return array
	 */
	public static function correctValuesFromPaceToKmh($array) {
		return array_map("PLOT__correctValuesMapperFromPaceToKmh", $array);
	}
}

/**
 * Mapper for Plot::correctValuesForTime
 *
 * Correct php-timestamps to JS-timestamps
 * @param mixed $v
 * @return mixed
 */
function PLOT__correctValuesMapperForTime($v) {
	return round($v*1000);
}

/**
 * Mapper for Plot::correctValuesFromPaceToKmh
 * @param mixed $v
 * @return mixed
 */
function PLOT__correctValuesMapperFromPaceToKmh($v) {
	if ($v == 0)
		return 0;

	return round(3600/$v, 2);
}

/**
 * Mapper for Plot::setZeroPointsToNull
 * @param mixed $v
 * @return mixed
 */
function PLOT__setZeroToNullMapper($v) {
	if ($v == 0)
		return null;

	return $v;
}
