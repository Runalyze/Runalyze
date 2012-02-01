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
	static public $EDIT				= 'img/edit.gif';
	static public $REFRESH			= 'img/Refresh.png';
	static public $ADD				= 'img/add.png';
	static public $CROSS			= 'img/cross.png';
	static public $DELETE           = 'img/delete.gif';
	static public $DELETE_GRAY      = 'img/delete_gray.gif';
	static public $CALENDAR			= 'img/calendar_month.png';
	static public $TABLE			= 'img/table.png';
	static public $CLIPBOARD		= 'img/clipboard.png';
	static public $CLIPBOARD_PLUS	= 'img/clipboard__plus.png';
	static public $CLOCK			= 'img/clock.png';
	static public $SEARCH			= 'img/search.png';
	static public $WARNING			= 'img/warning.png';
	static public $INFO				= 'img/info.gif';
	// Arrows
	static public $ARR_NEXT			= 'img/next.png';
	static public $ARR_BACK			= 'img/back.png';
	static public $ARR_BACK_BIG		= 'img/arrBack.png';
	static public $ARR_NEXT_BIG		= 'img/arrNext.png';
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
	static public $COMPETITION		= 'img/competition.png';
	static public $COMPETITION_FUN	= 'img/competition_fun.png';
	// Shape
	static public $FORM_NORMAL		= 'img/form0.png';
	static public $FORM_UP			= 'img/form++.png';
	static public $FORM_UP_HALF		= 'img/form+.png';
	static public $FORM_DOWN_HALF	= 'img/form-.png';
	static public $FORM_DOWN		= 'img/form--.png';
	// Shoes
	static public $BROKEN_1			= 'img/running/broken-1.png';
	static public $BROKEN_2			= 'img/running/broken-2.png';
	static public $BROKEN_3			= 'img/running/broken-3.png';
	static public $BROKEN_4			= 'img/running/broken-4.png';
	static public $BROKEN_5			= 'img/running/broken-5.png';

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
	public static function get($icon, $title = '', $onclick= '') {
		if ($onclick != '')
			return '<img class="link" src="'.$icon.'" alt="'.$title.'" title="'.$title.'" onclick="'.$onclick.'" />';

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

	/**
	 * Get icon for VDOT as shape
	 * @param double $VDOT
	 * @return string
	 */
	public static function getVDOTicon($VDOT) {
		$distances = array();
		$distances['3.000m'] = 3;
		$distances['5 km']   = 5;
		$distances['10 km']  = 10;
		$distances['HM']     = 21.1;

		if ( $VDOT > (VDOT_FORM+3) )
			$icon = self::$FORM_UP;
		elseif ( $VDOT > (VDOT_FORM+1) )
			$icon = self::$FORM_UP_HALF;
		elseif ( $VDOT < (VDOT_FORM-3) )
			$icon = self::$FORM_DOWN;
		elseif ( $VDOT < (VDOT_FORM-1) )
			$icon = self::$FORM_DOWN_HALF;
		else
			$icon = self::$FORM_NORMAL;

		$title = $VDOT.': ';
		foreach ($distances as $key => $km)
			$title .= $key.' in '.Helper::Prognosis($km, 0, $VDOT).', ';

		return self::get($icon, substr($title, 0, -2));
	}
}
?>