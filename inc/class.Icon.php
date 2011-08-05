<?php
/**
 * This file contains the class::Icon
 */
/**
 * Class for all icons used in Runalyze
 * 
 * @author Hannes Christiansen <mail@laufhannes.de>
 * @version 1.0
 */
class Icon {
	/**
	 * All avaiable icons are set as class members
	 */
	// General
	static public $AJAX_LOADER		= 'img/ajax-loader-download.gif';
	static public $EDIT				= 'img/edit.png';
	static public $REFRESH			= 'img/Refresh.png';
	static public $ADD				= 'img/add.png';
	static public $CROSS			= 'img/cross.png';
	static public $CALENDAR			= 'img/calendar_month.png';
	static public $CLIPBOARD		= 'img/clipboard.png';
	static public $CLIPBOARD_PLUS	= 'img/clipboard__plus.png';
	static public $CLOCK			= 'img/clock.png';
	static public $SEARCH			= 'img/search.png';
	static public $WARNING			= 'img/warning.png';
	// Arrows
	static public $ARR_NEXT			= 'img/next.png';
	static public $ARR_BACK			= 'img/back.png';
	static public $ARR_BACK_BIG		= 'img/arrBack.png';
	static public $ARR_DOWN_BIG		= 'img/arrDown.png';
	static public $ARR_UP_BIG		= 'img/arrUp.png';
	// Config
	static public $CONF_EDIT		= 'img/confEdit.png';
	static public $CONF_HELP		= 'img/confHelp.png';
	static public $CONF_SETTINGS	= 'img/confSettings.png';
	static public $CONF_TOOL		= 'img/confTool.png';
	// Running-specific
	static public $ABC				= 'img/abc.png';
	static public $RUNNINGSHOE		= 'img/runningshoe.png';
	static public $MONTH_KM			= 'img/mk.png';
	static public $WEEK_KM			= 'img/wk.png';
	static public $FATIGUE			= 'img/fatigue.png';
	// Form
	static public $FORM_NORMAL		= 'img/form0.png';
	static public $FORM_UP			= 'img/form++.png';
	static public $FORM_UP_HALF		= 'img/form+.png';
	static public $FORM_DOWN_HALF	= 'img/form-.png';
	static public $FORM_DOWN		= 'img/form--.png';

	/**
	 * This class contains only static methods
	 */
	private function __construct() {}
	private function __destruct() {}

	/**
	 * Get an icon as img-tag
	 * @param enum $icon
	 * @param string $title [optional]
	 */
	public static function get($icon, $title = '') {
		return '<img src="'.$icon.'" alt="'.$title.'" title="'.$title.'" />';
	}

	/**
	 * Get only url for this icon
	 * @param string $icon Should be a valid icon as IconFactory::Help
	 */
	public static function getSrc($icon) {
		return $icon;
	}

	/**
	 * Get the sport-specific icon
	 * @param int $id
	 * @param string $title
	 */
	public static function getSportIcon($id, $title = '') {
		$data = Mysql::getInstance()->fetch(PREFIX.'sport', $id);
		if ($data === false)
			return '';

		if ($title == '')
			$title = $data['name'];

		return '<img src="img/sports/'.$data['img'].'" alt="'.$title.'" />';
	}

	/**
	 * Get the weather-specific icon
	 * @param int $id
	 * @param string $title
	 */
	public static function getWeatherIcon($id, $title = '') {
		$data = Mysql::getInstance()->fetch(PREFIX.'weather', $id);
		if ($data === false)
			return '';

		if ($title == '')
			$title = $data['name'];

		return '<img src="img/wetter/'.$data['img'].'" alt="'.$title.'" style="vertical-align:bottom;" />';
	}
}
?>