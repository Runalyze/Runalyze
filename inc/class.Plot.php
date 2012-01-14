<?php
/**
 * This file contains the class to draw a plot
 */
/**
 * Class: Plot
 * 
 * @author Hannes Christiansen <mail@laufhannes.de>
 * @version 1.0
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
	 * Error string
	 * @var string
	 */
	private $ErrorString = '';

	/**
	 * Get all needed JavaScript-files for this class as array
	 */
	public static function getNeededJSFilesAsArray() {
		$Files = array();
		$Files[] = "lib/flot/jquery.plot.js";
		$Files[] = "lib/flot/jquery.qtip.min.js";
		$Files[] = "lib/flot/jquery.flot.min.js";
		$Files[] = "lib/flot/jquery.flot.selection.min.js";
		$Files[] = "lib/flot/jquery.flot.crosshair.min.js";
		$Files[] = "lib/flot/jquery.flot.navigate.min.js";
		$Files[] = "lib/flot/jquery.flot.stack.min.js";

		return $Files;
	}

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

		$this->Options['yaxis']['color'] = "#FFF";
		$this->Options['xaxis']['color'] = "#FFF";
		$this->Options['xaxis']['monthNames'] = array('Jan', 'Feb', 'Mrz', 'Apr', 'Mai', 'Jun', 'Jul', 'Aug', 'Sep', 'Okt', 'Nov', 'Dez');

		$this->Options['grid']['backgroundColor'] = "rgba(255,255,255,0.2)";
		$this->Options['grid']['borderWidth'] = 1;
		$this->Options['grid']['labelMargin'] = 2;
		$this->Options['grid']['axisMargin'] = 2;

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
		return '
			<div style="position:relative;width:'.($width+2).'px;height:'.($height+2).'px;margin:2px auto;">
				'.self::getInnerDivFor($id, $width, $height).'
			</div>';
	}

	/**
	 * Get standard div for a special plot
	 * @param string $id
	 * @param int $width
	 * @param int $height
	 * @param bool $hidden
	 */
	static public function getInnerDivFor($id, $width, $height, $hidden = false) {
		return '<div class="flot waitImg'.($hidden ? ' flotHide' : '').'" id="'.$id.'" style="width:'.$width.'px;height:'.$height.'px;position:absolute;"></div>';
	}

	/**
	 * Get a div for this plot
	 */
	public function getDiv() {
		return self::getDivFor($this->cssID, $this->width, $this->height);
	}

	/**
	 * Output JavaScript
	 */
	public function outputJavaScript() {
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

			if (isset($this->Options['crosshair']))
				$bindedCode .= $this->getJSForTracking();

			if (!empty($this->Annotations))
				$bindedCode .= $this->getJSForAnnotations();
		}

		if (!empty($this->Titles)) {
			$bindedCode .= $this->getJSForTitles();
			$padding     = '1px 1px 16px 1px';
		}

		$bindedCode .= '$("#'.$this->cssID.'").removeClass("'.Ajax::$IMG_WAIT.'");';
		$bindedCode .= '$("#'.$this->cssID.'").css(\'padding\',\''.$padding.'\');';

		return Ajax::wrapJS('
			var '.$this->created.'=false;
			$(document).bind("createFlot",function () {
				if(!'.$this->created.' && $("#'.$this->cssID.'").width() > 0) {
					'.$this->created.'=true;
					'.$bindedCode.'
				}
			});');
	}

	/**
	 * Get main functionalities for this plot
	 * @return string
	 */
	private function getMainJS() {
		return '
			var '.$this->plot.' = $.plot(
				$("#'.$this->cssID.'"),
				'.json_encode($this->Data).',
				'.Ajax::json_encode_jsfunc($this->Options).'
			);'.NL;
	}

	/**
	 * Get code for displaying titles
	 * @return string
	 */
	private function getJSForTitles() {
		$title  = '<div class="flotTitle">';
		if (isset($this->Titles['left']))
			$title .= '<span class="left">'.$this->Titles['left'].'</span>';
		if (isset($this->Titles['right']))
			$title .= '<span class="right">'.$this->Titles['right'].'</span>';
		if (isset($this->Titles['center']))
			$title .= $this->Titles['center'];
		$title .= '</div>';

		return '$("#'.$this->cssID.'").append(\''.addslashes($title).'\');'.NL;
	}
	
	/**
	 * Get code for an error
	 * @return string
	 */
	private function getJSForError() {
		return'$("#'.$this->cssID.'").append(\'<div class="flotError"><span>'.$this->ErrorString.'</span></div>\');'.NL;
	}

	/**
	 * Get code for adding annotations
	 * @return string
	 */
	private function getJSForAnnotations() {
		$code = '';
		foreach ($this->Annotations as $Array)
			$code .= '
				o = '.$this->plot.'.pointOffset({x:'.$Array['x'].', y:'.$Array['y'].'});
				$("#'.$this->cssID.'").append(\'<div class="annotation" style="left:\'+(o.left)+\'px;top:\'+o.top+\'px;">'.$Array['text'].'</div>\');';

		return $code;
	}

	/**
	 * Get code for enable zooming
	 * @return string
	 */
	private function getJSForZooming() {
		return '
			$(\'<div class="arrow" style="right:20px;top:20px">zoom out</div>\').appendTo("#'.$this->cssID.'").click(function (e) {
				e.preventDefault();
				'.$this->plot.'.zoomOut();
			});'.NL;
	}

	/**
	 * Get code for enable panning
	 * @return string
	 */
	private function getJSForPanning() {
		return '
			function addArrow(dir, right, top, offset) {
					$(\'<img class="arrow" src="lib/flot/arrow-\' + dir + \'.gif" style="right:\' + right + \'px;top:\' + top + \'px">\').appendTo("#'.$this->cssID.'").click(function (e) {
					e.preventDefault();
					'.$this->plot.'.pan(offset);
				});
			}
		
			addArrow(\'left\', 55, 60, { left: -100 });
			addArrow(\'right\', 25, 60, { left: 100 });
			addArrow(\'up\', 40, 45, { top: -100 });
			addArrow(\'down\', 40, 75, { top: 100 });'.NL;
	}

	/**
	 * Get code for enable tracking
	 * @return string
	 */
	private function getJSForTracking() {
		return '
			$("#'.$this->cssID.'").qtip({
				prerender: true,
				content: \'Loading...\',
				position: { viewport: $(window), target: \'mouse\', adjust: { x: 7 } },
				show: false,
				style: { classes: \'ui-tooltip-shadow ui-tooltip-tipsy\', tip: false }
			});
			bindFlotForQTip'.($this->usesPoints()?'Points':'').'($("#'.$this->cssID.'"), '.$this->plot.');'.NL;
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
	 * Correct all values as JS-timestamps
	 * @param array $array
	 * @return array
	 */
	static public function correctValuesForTime($array) {
		return array_map("self::correctValuesMapper", $array);
	}

	/**
	 * Mapper for self::correctValuesForTime
	 */
	static private function correctValuesMapper($v) {
		return $v*1000;
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
	 */
	public function addAnnotation($x, $y, $text) {
		$this->Annotations[] = array('x' => $x, 'y' => $y, 'text' => $text);
	}

	/**
	 * Enable selection for this plot
	 * @param mixed $mode can be false
	 */
	public function enableSelection($mode = "x") {
		if ($mode === false)
			unset($this->Options['selection']);
		else
			$this->Options['selection']['mode'] = $mode;
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

		if ($withPadding)
			$this->Options['xaxis']['autoscaleMargin'] = 0.02;
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

	public function setXAxisLimitedTo($Year) {
		$this->Options['xaxis']['min'] = mktime(1,0,0,1,1,$Year).'000';
		$this->Options['xaxis']['max'] = mktime(1,0,0,1,0,$Year+1).'000';
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
	 * @return int
	 */
	public function addYAxis($i, $position, $align = true) {
		$this->Options['yaxes'][$i-1]['position'] = $position;

		if ($position == 'right' && $align)
			$this->Options['yaxes'][$i-1]['alignTicksWithAxis'] = 1;
	}

	/**
	 * Add unit to y axis
	 * @param int $i
	 * @param string $unit
	 */
	public function addYUnit($i, $unit) {
		$this->Options['yaxes'][$i-1]['tickFormatter'] = 'function (v) { return v + \' '.$unit.'\'; }';
	}

	/**
	 * Add unit to x axis
	 * @param string $unit
	 */
	public function setXUnit($unit) {
		$this->Options['xaxis']['tickFormatter'] = 'function (v) { return v + \' '.$unit.'\'; }';
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
	 * Correct special characters like umlaute to unicode-HTML
	 * @param string $string
	 */
	public static function correctSpecialChars($string) {
		$string    = utf8_encode($string);
		$encrypted = array("ß",      "Ä",      "Ö",      "Ü",      "ä",      "ö",      "ü");
		$correct   = array("&#223;", "&#196;", "&#214;", "&#220;", "&#228;", "&#246;", "&#252;");

		return str_replace($encrypted, $correct, $string);
	}
}
?>