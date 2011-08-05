<?php
/**
 * This file contains the class to draw a chart using pChart 2.1.1
 */
/**
 * Class: Draw
 * 
 * @author Hannes Christiansen <mail@laufhannes.de>
 * @version 1.0
 * @uses pChart2.1.1
 */
class Draw {
	/**
	 * Width of the image
	 * @var int
	 */
	private $width = 480;

	/**
	 * Height of the image
	 * @var int
	 */
	private $height = 190;

	/**
	 * Internal pData, is public for direct access
	 * @var pData
	 */
	public $pData = null;

	/**
	 * Internal pImage, is public for direct access
	 * @var pImage
	 */
	public $pImage = null;

	/**
	 * Internal pCache for caching
	 * @var pCache
	 */
	private $pCache = null;

	/**
	 * Boolea flag: Use a border (1px solid #000)
	 * @var bool
	 */
	private $useBorder = true;

	/**
	 * Boolean flag: Use cache (default true)
	 * @var bool
	 */
	private $useCache = true;

	/**
	 * Internal cache-hash
	 * @var string
	 */
	private $cacheHash = '';

	/**
	 * Boolean flag: Was the image already loaded from cache?
	 * @var bool
	 */
	private $loadedFromCache = false;

	/**
	 * Boolean flag: Has the default title been drawed?
	 * @var bool
	 */
	private $defaultTitleDrawed = false;

	/**
	 * Array with padding in pixels
	 * @var array
	 */
	public $padding = array('top' => 0, 'right' => 0, 'bottom' => 0, 'left' => 0);

	/**
	 * Constructor
	 * @param int $width
	 * @param int $height
	 */
	function __construct($width = 480, $height = 190) {
		$this->includePChart();

		$this->setSize($width, $height);
		$this->setCaching(true);
		$this->setBorder(true);
	}

	/**
	 * Included for pChart 2.1.1
	 */
	private function includePChart() {
		require_once 'draw/pChart/pData.class.php';
		require_once 'draw/pChart/pDraw.class.php';
		require_once 'draw/pChart/pImage.class.php';
		require_once 'draw/pChart/pCache.class.php';
		require_once 'draw/pChart/pScatter.class.php';

		$this->pData = new pData();
		$this->pCache = new pCache();
	}

	/**
	 * Load the image from cache if avaiable
	 * @return bool False if no image in cache, true for success (but exit() will be called)
	 */
	private function loadFromCache() {
		$this->cacheHash = $this->pCache->getHash($this->pData);

		if ($this->pCache->isInCache($this->cacheHash)) {
			$this->pCache->strokeFromCache($this->cacheHash);
			exit();

			return true;
		}

		return false;
	}

	/**
	 * Prepare image for drawing (will load image from cache if wanted and avaiable)
	 * @param bool $transparentBackground[optional] Set to true for using a transparent background
	 */
	public function startImage($transparentBackground = false) {
		if ($this->useCache)
			$this->loadFromCache();

		$this->pImage = new pImage($this->width, $this->height, $this->pData, $transparentBackground);

		if (!$transparentBackground)
			$this->drawDefaultBackground();

		if ($this->useBorder)
			$this->drawDefaultBorder();

		$this->setDefaultPalette();
		$this->setDefaultFont();
		$this->setDefaultPadding();

		$this->drawGraphArea();
	}

	/**
	 * Render the image to the browser
	 */
	public function finish() {
		if ($this->useCache && !$this->loadedFromCache)
			$this->pCache->writeToCache($this->cacheHash, $this->pImage);

		$this->pImage->stroke();
	}

	/**
	 * Draw the graph area defined with $this->padding
	 */
	private function drawGraphArea() {
		$x1 = $this->padding['left'];
		$x2 = $this->width - $this->padding['right'];
		$y1 = $this->padding['top'];
		$y2 = $this->height - $this->padding['bottom'];

		$this->pImage->setGraphArea($x1, $y1, $x2, $y2);
		$this->pImage->drawFilledRectangle($x1, $y1, $x2, $y2, array(
			"R" => 255, "G" => 255, "B" => 255,
			"Surrounding" => -200,
			"Alpha" => 20)); 
	}

	/**
	 * Draw (default) scale
	 * @param array $Format
	 */
	public function drawScale($Format = array()) {
		$this->pImage->drawScale($Format);
	}

	/**
	 * Draw (default) LineChart
	 */
	public function drawLineChart() {
		$this->pImage->drawLineChart(array(
			"BreakVoid" => TRUE));
	}

	/**
	 * Draw (default) SplineChart
	 */
	public function drawSplineChart() {
		$this->pImage->drawSplineChart(array(
			"BreakVoid" => FALSE));
	}

	/**
	 * Draw (default) FilledSplineChart
	 */
	public function drawFilledSplineChart() {
		$this->pImage->drawFilledSplineChart(array(
			"BreakVoid" => FALSE));
	}

	/**
	 * Draw (default) AreaChart
	 */
	public function drawAreaChart() {
		$this->pImage->drawAreaChart(array(
			"BreakVoid" => TRUE));
	}

	/**
	 * Draw (default) BarChart
	 */
	public function drawBarChart() {
		$this->pImage->drawBarChart(array(
			"Gradient" => TRUE,
			"DisplayShadow" => TRUE,
			"Surrounding" => 10));
	}

	/**
	 * Draw (default) StackedBarChart
	 */
	public function drawStackedBarChart() {
		$this->pImage->drawStackedBarChart(array(
			"Gradient" => TRUE,
			"DisplayShadow" => TRUE,
			"Surrounding" => 10));
	}

	/**
	 * Draw the default background
	 */
	private function drawDefaultBackground() {
		$this->pImage->drawFilledRectangle(0, 0, $this->width, $this->height, array(
			"R" => 170, "G" => 183, "B" => 87,
			"DashR" => 190, "DashG" => 203, "DashB" => 107,
			"Dash" => 1));

		$this->pImage->drawGradientArea(0, 0, $this->width, $this->height, DIRECTION_VERTICAL, array(
			"StartR" => 219, "StartG" => 231, "StartB" => 139,
			"EndR" => 1, "EndG" => 138, "EndB" => 68,
			"Alpha" => 50));
	}

	/**
	 * Draw the default border
	 */
	private function drawDefaultBorder() {
		$this->pImage->drawRectangle(0, 0, ($this->width-1), ($this->height-1), array(
			"R" => 0, "G" => 0, "B" => 0)); 
	}

	/**
	 * Draw the default title-background
	 */
	private function drawDefaultTitleBackground() {
		$this->pImage->drawGradientArea(0, ($this->height-20), $this->width, $this->height, DIRECTION_VERTICAL, array(
			"StartR" => 0, "StartG" => 0, "StartB" => 0,
			"EndR" => 50, "EndG" => 50, "EndB" => 50,
			"Alpha" => 80));

		$this->defaultTitleDrawed = true;
	}

	/**
	 * Draw the title into the default title-background
	 * @param string $title
	 * @param string $align[optional] can be LEFT | CENTER | RIGHT
	 */
	private function drawTitle($title, $align = 'CENTER') {
		if (!$this->defaultTitleDrawed)
			$this->drawDefaultTitleBackground();

		switch ($align) {
			case 'LEFT':
				$x = 5;
				$Align = TEXT_ALIGN_MIDDLELEFT;
				break;
			case 'RIGHT':
				$x = $this->width - 5;
				$Align = TEXT_ALIGN_MIDDLERIGHT;
				break;
			case 'CENTER':
			default:
				$x = round($this->width / 2);
				$Align = TEXT_ALIGN_MIDDLEMIDDLE;
				break;
		}

		$title = self::correctSpecialChars($title);
		$this->pImage->drawText($x, ($this->height-10), $title, array("Align" => $Align));
	}

	/**
	 * Draw a title to the center of the GraphArea
	 * @param string $title
	 */
	public function drawCenteredTitle($title) {
		$x = round( ($this->padding['left'] + $this->width - $this->padding['right']) / 2);
		$y = round( ($this->padding['top'] + $this->height - $this->padding['bottom']) / 2);
		$this->pImage->drawText($x, $y, $title, array("Align" => TEXT_ALIGN_MIDDLEMIDDLE));
	}

	/**
	 * Draw the left title into the default title-background
	 * @param string $title
	 */
	public function drawLeftTitle($title) {
		$this->drawTitle($title, 'LEFT');
	}

	/**
	 * Draw the centered title into the default title-background
	 * @param string $title
	 */
	public function drawCenterTitle($title) {
		$this->drawTitle($title, 'CENTER');
	}

	/**
	 * Draw the right title into the default title-background
	 * @param string $title
	 */
	public function drawRightTitle($title) {
		$this->drawTitle($title, 'RIGHT');
	}

	/**
	 * Set size for this image
	 * @param int $width
	 * @param int $height
	 */
	public function setSize($width, $height) {
		if (!is_numeric($width) || !is_numeric($height))
			return false;

		$this->width  = (int)$width;
		$this->height = (int)$height;
	}

	/**
	 * Set caching to true/false
	 * @param bool $var
	 */
	public function setCaching($var) {
		$this->useCache = (bool)$var;
	}

	/**
	 * Set border to true/false
	 * @param bool $var
	 */
	public function setBorder($var) {
		$this->useBorder = (bool)$var;
	}

	/**
	 * Set the font
	 * @param string $name Name of the font (e.g. 'calibri.ttf')
	 * @param int $size Font-size
	 */
	public function setFont($name, $size = 8) {
		$this->pImage->setFontProperties(array(
			"FontName" => FRONTEND_PATH."draw/fonts/".$name,
			"FontSize" => $size)); 
	}

	/**
	 * Set font-properties: Color (and alpha)
	 * @param int $R (0-255)
	 * @param int $G (0-255)
	 * @param int $B (0-255)
	 * @param int $alpha (0-100)
	 */
	public function setFontColor($R, $G, $B, $alpha = 100) {
		$this->pImage->setFontProperties(array(
			"R" => $R,
			"G" => $G,
			"B" => $B,
			"Alpha" => $alpha));
	}

	/**
	 * Set the default font
	 */
	public function setDefaultFont() {
		$this->setFont('calibri.ttf');
		$this->setFontColor(255, 255, 255, 100);
	}

	/**
	 * Set the default padding
	 */
	private function setDefaultPadding() {
		if ($this->padding['top'] == 0)
			$this->padding['top'] = 10;
		if ($this->padding['left'] == 0)
			$this->padding['left'] = 38;
		if ($this->padding['right'] == 0)
			$this->padding['right'] = 18;
		if ($this->padding['bottom'] == 0)
			$this->padding['bottom'] = 40;
	}

	/**
	 * Load default color-palette
	 */
	private function setDefaultPalette() {
		$this->pData->loadPalette(FRONTEND_PATH."/draw/default.color");
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