<?php
/**
 * This file contains class::Plot
 * @package Runalyze\Draw
 */
/**
 * General plotting class
 * @author Hannes Christiansen
 * @package Runalyze\Draw
 */
class Plot {
	/**
	 * CSS-ID for displaying this plot
	 * @var unknown_type
	 */
	private $cssID = '';

	/**
	 * String for JS-flag of creationg
	 * @var string
	 */
	private $created = '';

	/**
	 * String for JS-object holding this plot
	 * @var string
	 */
	private $plot = '';

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
	 * Array containing titles for this plot
	 * @var array
	 */
	private $Titles = array();

	/**
	 * Array containing annotations for this plot
	 * @var array
	 */
	private $Annotations = array();

	/**
	 * Allow settings
	 * @var booleam
	 */
	protected $allowSettings = true;

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
	function __construct($cssID, $width = 480, $height = 190) {
		$this->width   = $width;
		$this->height  = $height;
		$this->cssID   = $cssID;
		$this->created = 'created_'.$this->cssID;
		$this->plot    = 'plot_'.$this->cssID;

		$this->setDefaultOptions();
	}

	/**
	 * Raise an error
	 * @param string $string
	 */
	public function raiseError($string) {
		$this->ErrorString = $string;
	}

	/**
	 * Set default options
	 */
	private function setDefaultOptions() {
		//$this->Options['colors'] = array("#C53001", "#C56D01", "#08527D");
		$this->Options['colors'] = array("#C61D17", "#E68617", "#8A1196", "#E6BE17", "#38219F");

		$this->Options['series']['stack'] = null;
		$this->Options['series']['points']['radius'] = 1;
		$this->Options['series']['points']['lineWidth'] = 3;
		$this->Options['series']['lines']['lineWidth'] = 1;
		$this->Options['series']['lines']['steps'] = false;
		$this->Options['series']['bars']['lineWidth'] = 1;
		$this->Options['series']['bars']['barWidth'] = 0.6;
		$this->Options['series']['bars']['align'] = "center";
		$this->Options['series']['bars']['fill'] = 0.9;

		$this->Options['legend']['backgroundColor'] = "#FFF";
		$this->Options['legend']['backgroundOpacity'] = 0.4;
		$this->Options['legend']['margin'] = 2;
		$this->Options['legend']['noColumns'] = 0;

		$this->Options['yaxis']['color'] = 'rgba(255,255,255,0.2)'; // "#FFF";
		$this->Options['xaxis']['color'] = 'rgba(255,255,255,0.2)'; // "#FFF";
		$this->Options['xaxis']['monthNames'] = array('Jan', 'Feb', 'Mrz', 'Apr', 'Mai', 'Jun', 'Jul', 'Aug', 'Sep', 'Okt', 'Nov', 'Dez');

		$this->Options['grid']['backgroundColor'] = "rgba(255,255,255,0.3)";
		$this->Options['grid']['borderWidth'] = 1;
		$this->Options['grid']['labelMargin'] = 2;
		$this->Options['grid']['axisMargin'] = 2;

		$this->Options['canvas'] = true;
		$this->Options['font'] = 'Verdana 8px';
		$this->Optoins['yaxis']['font'] = 'Verdana 8px';
		$this->Optoins['xaxis']['font'] = 'Verdana 8px';
		//$this->Options['grid']['canvasText'] = array('show' => false, 'font' => 'Verdana 8px');

		$this->setMarginForGrid(5);
	}

	/**
	 * Remove all (default) options, must be called before setting any own options
	 */
	public function removeDefaultOptions() {
		$this->Options = array();
	}

	/**
	 * Remove standard y-axis
	 */
	public function removeStandardYAxis() {
		unset($this->Options['yaxis']);
	}

	/**
	 * Get standard div for a special plot
	 * @param string $id
	 * @param int $width
	 * @param int $height
	 */
	static public function getDivFor($id, $width, $height) {
		return '<div style="position:relative;width:'.($width+2).'px;height:'.($height+2).'px;margin:2px auto;">'.self::getInnerDivFor($id, $width, $height).'</div>';
	}

	/**
	 * Get standard div for a special plot
	 * @param string $id
	 * @param int $width
	 * @param int $height
	 * @param bool $hidden
	 * @param string $class
	 */
	static public function getInnerDivFor($id, $width, $height, $hidden = false, $class = '') {
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

		echo $this->getJavaScript();
	}

	/**
	 * Get JavaScript-Code for this plot
	 * @return string
	 */
	private function getJavaScript() {
		$this->convertData();
		$bindedCode  = '$("#'.$this->cssID.'").width('.$this->width.'-2);'.NL;
		$bindedCode .= '$("#'.$this->cssID.'").height('.$this->height.'-2-'.(empty($this->Titles)?0:15).');'.NL;
		$padding     = '1px';

		if (strlen($this->ErrorString) > 0) {
			$bindedCode .= $this->getJSForError();
		} else {
			$bindedCode .= $this->getMainJS();

			if (isset($this->Options['pan']) && $this->Options['pan']['interactive'])
				$bindedCode .= $this->getJSForPanning();

			if (isset($this->Options['zoom']) && $this->Options['zoom']['interactive'])
				$bindedCode .= $this->getJSForZooming();

			//if (isset($this->Options['crosshair']))
				$bindedCode .= $this->getJSForTracking();

			if (isset($this->Options['selection']))
				$bindedCode .= $this->getJSForSelection();

			if (!empty($this->Annotations))
				$bindedCode .= $this->getJSForAnnotations();
		}

		if (!empty($this->Titles)) {
			$bindedCode .= $this->getJSForTitles();
			$padding     = '1px 1px 16px 1px';
		}

		if ($this->allowSettings)
			$bindedCode .= 'RunalyzePlot.initSettingsLink("'.$this->cssID.'");';

		$bindedCode .= '$("#'.$this->cssID.'").removeClass("'.Ajax::$IMG_WAIT.'");';
		$bindedCode .= '$("#'.$this->cssID.'").css(\'padding\',\''.$padding.'\');';

		return Ajax::wrapJS('
			var '.$this->created.'=false,
				func_'.$this->created.'=function(){
					if(!'.$this->created.' && $("#'.$this->cssID.'").width() > 0 && $("#'.$this->cssID.'").is(":visible") && !$("#'.$this->cssID.'").hasClass("flot-hide")) {
						'.$this->created.'=true;'.$bindedCode.'RunalyzePlot.finishInit("'.$this->cssID.'");
					}
				};
			$(document).off("createFlot.'.$this->cssID.'").on("createFlot.'.$this->cssID.'",func_'.$this->created.');
		');
	}

	/**
	 * Get main functionalities for this plot
	 * @return string
	 */
	private function getMainJS() {
		return 'RunalyzePlot.addPlot(
					"'.$this->cssID.'",
					'.json_encode($this->Data).',
					'.Ajax::json_encode_jsfunc($this->Options).');'.NL;
	}

	/**
	 * Get code for displaying titles
	 * @return string
	 */
	private function getJSForTitles() {
		$title  = '<div class="flot-title">';
		if ($this->allowSettings)
			$title .= '<span class="left link flot-settings-link">'.Icon::$CONF.'</span>';
		if (isset($this->Titles['left']))
			$title .= '<span class="left">'.$this->Titles['left'].'</span>';
		if (isset($this->Titles['right']))
			$title .= '<span class="right">'.$this->Titles['right'].'</span>';
		if (isset($this->Titles['center']))
			$title .= $this->Titles['center'];
		$title .= '</div>';

		if ($this->allowSettings) {
			$title .= '<div class="toolbar-line flot-settings-line hide">';
			$title .= '<span class="link labeledLink flot-settings-save" onclick="RunalyzePlot.save(\''.$this->cssID.'\')">'.Icon::$SAVE.' Speichern</span>';
			$title .= '<span class="link labeledLink flot-settings-fullscreen" onclick="RunalyzePlot.toggleFullscreen(\''.$this->cssID.'\')">'.Icon::$ZOOM_IN_SMALL.' Vollbild</span>';
			$title .= '<span class="link labeledLink flot-settings-fullscreen-hide hide" onclick="RunalyzePlot.toggleFullscreen(\''.$this->cssID.'\')">'.Icon::$ZOOM_OUT_SMALL.' Vollbild verlassen</span>';

			$title .= '<span class="right show-only-fullscreen" style="margin-top:3px;">';
			$title .= '<label class="checkable" onclick="$(this).children(\'i\').toggleClass(\'checked\');RunalyzePlot.toggleCrosshairMode(\''.$this->cssID.'\');"><i class="fa fa-fw checkbox-icon'.(isset($this->Options['crosshair'])?' checked':'').'"></i> Crosshair</label>';
			if (isset($this->Options['selection'])) // Does only work if handler are bound
				$title .= '<label class="checkable" onclick="$(this).children(\'i\').toggleClass(\'checked\');RunalyzePlot.toggleSelectionMode(\''.$this->cssID.'\');"><i class="fa fa-fw checkbox-icon'.(isset($this->Options['selection'])?' checked':'').'"></i> Auswahl</label>';
			$title .= '</span>';

			$title .= '</div>';
		}

		return '$("#'.$this->cssID.'").append(\''.addslashes($title).'\');'.NL;
	}
	
	/**
	 * Get code for an error
	 * @return string
	 */
	private function getJSForError() {
		return'$("#'.$this->cssID.'").append(\'<div class="flot-error"><span>'.$this->ErrorString.'</span></div>\');'.NL;
	}

	/**
	 * Get code for adding annotations
	 * @return string
	 */
	private function getJSForAnnotations() {
		$code = '';
		foreach ($this->Annotations as $Array)
			$code .= 'RunalyzePlot.addAnnotationTo("'.$this->cssID.'", '.$Array['x'].', '.$Array['y'].', "'.$Array['text'].'", '.$Array['toX'].', '.$Array['toY'].');';

		return $code;
	}

	/**
	 * Get code for enable zooming
	 * @return string
	 */
	private function getJSForZooming() {
		return 'RunalyzePlot.enableZoomingFor("'.$this->cssID.'");';
	}

	/**
	 * Get code for enable panning
	 * @return string
	 */
	private function getJSForPanning() {
		return 'RunalyzePlot.togglePanning("'.$this->cssID.'")'.NL;
	}

	/**
	 * Get code for enable tracking
	 * @return string
	 */
	private function getJSForTracking() {
		if ($this->usesPoints())
			$Type = 'Points';
		elseif ($this->usesBars())
			$Type = 'Bars';
		else
			$Type = '';

		return 'bindFlotForQTip'.$Type.'($("#'.$this->cssID.'"), RunalyzePlot.getPlot("'.$this->cssID.'") );'.NL;
	}

	/**
	 * Get code for enable selection
	 * @return string
	 */
	private function getJSForSelection() {
		return 'bindFlotForSelection($("#'.$this->cssID.'"), RunalyzePlot.getPlot("'.$this->cssID.'"), '.($this->Options['selection']['rangeCalculation']?'true':'false').' );'.NL;
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

		if (empty($this->Data) && strlen($this->ErrorString) == 0)
			$this->raiseError('Es sind keine Daten vorhanden.');
	}

	/**
	 * Set title to plot
	 * @param string $title
	 * @param string $position
	 */
	public function setTitle($title, $position = 'center') {
		$this->Titles[$position] = $title;
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
	 * Enable selection for this plot
	 * @param mixed $mode can be false
	 * @param string $color
	 * @param boolean $rangeCalculation
	 */
	public function enableSelection($mode = 'x', $color = '', $rangeCalculation = true) {
		if ($mode === false)
			unset($this->Options['selection']);
		else {
			$this->Options['selection']['mode'] = $mode;
			$this->Options['selection']['color'] = $color=='' ? 'rgba(170, 0, 0, 0.5)' : $color;
			$this->Options['selection']['rangeCalculation'] = $rangeCalculation;
		}
	}

	/**
	 * Enable zooming for this plot
	 * @param bool $mode
	 */
	public function enableZooming($mode = true) {
		$this->Options['zoom']['interactive'] = $mode;
		$this->Options['pan']['interactive'] = $mode;
	}

	/**
	 * Enable tracking with crosshair
	 */
	public function enableTracking() {
		$this->Options['crosshair']['mode'] = "x";
		$this->Options['grid']['hoverable'] = true;
		$this->Options['grid']['autoHighlight'] = false;

		if ($this->usesPoints())
			$this->Options['crosshair']['color'] = 'rgba(170, 0, 0, 0.2)';
		if ($this->usesBars())
			$this->Options['crosshair']['color'] = 'rgba(170, 0, 0, 0)';
	}

	/**
	 * Enable hiding graphs
	 */
	public function enableHiddengraphs() {
		$this->Options['legend']['hideable'] = true;
	}

	/**
	 * Allow settings
	 * @param boolean $flag true or false
	 */
	public function allowSettings($flag = true) {
		$this->allowSettings = $flag;
	}

	/**
	 * Does this plot uses points?
	 * @return bool
	 */
	private function usesPoints() {
		return (isset($this->Options['series'])
				&& isset($this->Options['series']['points'])
				&& isset($this->Options['series']['points']['show'])
				&& $this->Options['series']['points']['show']);
	}

	/**
	 * Does this plot uses bars?
	 * @return bool
	 */
	private function usesBars() {
		return (isset($this->Options['series'])
				&& isset($this->Options['series']['bars'])
				&& isset($this->Options['series']['bars']['show'])
				&& $this->Options['series']['bars']['show']);
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
	 * @param string $color
	 * @param int $lineWidth
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
	 * Hide the legend
	 */
	public function hideLegend() {
		$this->Options['legend']['show'] = false;
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
	public function setLinesFilled($keys = array()) {
		if (empty($keys))
			$keys = array_keys($this->Data);

		foreach ($keys as $key)
			$this->Data[$key]['lines']['fill'] = 0.7;
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
	 * Add unit to y axis
	 * @param int $i
	 * @param string $unit
	 */
	public function addYUnit($i, $unit, $roundTo = 2) {
		$this->Options['yaxes'][$i-1]['tickFormatter'] = 'function (v) { return Math.round(v * Math.pow(10,'.$roundTo.')) / Math.pow(10,'.$roundTo.') + \' '.$unit.'\'; }';
	}

	/**
	 * Add unit to x axis
	 * @param string $unit
	 */
	public function setXUnit($unit, $roundTo = 2) {
		$this->Options['xaxis']['tickFormatter'] = 'function (v) { return Math.round(v * Math.pow(10,'.$roundTo.')) / Math.pow(10,'.$roundTo.') + \' '.$unit.'\'; }';
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

			$min = floor($min/$factor-0.02*$diff)*$factor;
			$max = ceil($max/$factor+0.02*$diff)*$factor;

			if ($min < 0)
				$min = 0;

			$this->setYTicks($axis, $factor);
		}

		$this->Options['yaxes'][$axis-1]['min'] = $min;
		$this->Options['yaxes'][$axis-1]['max'] = $max;
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
	 */
	static public function dayOfYearToJStime($year, $day) {
		return mktime(1,0,0,1,$day,$year).'000';
	}

	/**
	 * Correct all values as JS-timestamps
	 * @param array $array
	 * @return array
	 */
	static public function correctValuesForTime($array) {
		return array_map("PLOT__correctValuesMapperForTime", $array);
	}

	/**
	 * Correct all pace-values to km/h
	 * @param array $array
	 * @return array
	 */
	static public function correctValuesFromPaceToKmh($array) {
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
	return $v*1000;
}

/**
 * Mapper for Plot::correctValuesFromPaceToKmh
 * @param mixed $v
 * @return mixed
 */
function PLOT__correctValuesMapperFromPaceToKmh($v) {
	if ($v == 0)
		return 0;

	return 3600/$v;
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