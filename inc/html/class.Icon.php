<?php
/**
 * This file contains class::Icon
 * @package Runalyze\HTML
 */
/**
 * Class for all icons used in Runalyze
 * @author Hannes Christiansen
 * @package Runalyze\HTML
 */
class Icon {
	/**
	 * Path to sport icons
	 * @var string
	 */
	static public $PATH_TO_SPORT_ICONS = 'img/sports/';

	/**
	 * All avaiable icons are set as class members
	 */
	// Small, 12x12px
	static public $EDIT             = '<i class="fa fa-fw fa-pencil"></i>';
	static public $BACK             = '<i class="fa fa-fw fa-chevron-left"></i>';
	static public $NEXT             = '<i class="fa fa-fw fa-chevron-right"></i>';
	static public $ABC              = '<small>ABC</small>';
	static public $ATTACH           = '<i class="fa fa-fw fa-chain"></i>';
	static public $CLOCK            = '<i class="fa fa-fw fa-clock-o"></i>';
	static public $CLOCK_ORANGE     = '<i class="fa fa-fw fa-clock-o"></i>';
	static public $CLOCK_GREY       = '<i class="fa fa-fw fa-clock-o unimportant"></i>';
	static public $DOWNLOAD         = '<i class="fa fa-fw fa-external-link"></i>';
	static public $DOWN             = '<i class="fa fa-fw fa-arrow-down"></i>';
	static public $UP               = '<i class="fa fa-fw fa-arrow-up"></i>';
	static public $ADD_SMALL        = '<i class="fa fa-fw fa-plus fa-grey"></i>';
	static public $ADD_SMALL_GREEN  = '<i class="fa fa-fw fa-plus fa-green"></i>';
	static public $CONF             = '<i class="fa fa-fw fa-cog"></i>';
	static public $ZOOM_IN_SMALL    = '<i class="fa fa-fw fa-search-plus"></i>';
	static public $ZOOM_OUT_SMALL   = '<i class="fa fa-fw fa-search-minus"></i>';
	static public $SAVE             = '<i class="fa fa-fw fa-save"></i>';
	static public $CROSS_SMALL      = '<i class="fa fa-fw fa-times"></i>';
	static public $INFO_SMALL       = '<i class="fa fa-fw fa-info"></i>';
	static public $REFRESH_SMALL    = '<i class="fa fa-fw fa-refresh"></i>';
	// Big, 16x16px
	static public $ADD              = '<i class="fa fa-fw fa-plus"></i>';
	static public $CROSS            = '<i class="fa fa-fw fa-times"></i>';
	static public $SEARCH           = '<i class="fa fa-fw fa-search"></i>';
	static public $REFRESH          = '<i class="fa fa-fw fa-refresh"></i>';
	static public $CALENDAR         = '<i class="fa fa-fw fa-calendar"></i>';
	static public $BARS_BIG         = '<i class="fa fa-fw fa-bar-chart-o"></i>';
	static public $BARS_SMALL       = '<i class="fa fa-fw fa-bar-chart-o"></i>';
	static public $FATIGUE          = '<i class="fa fa-fw fa-signal"></i>';
	static public $TABLE            = '<i class="fa fa-fw fa-table"></i>';
	static public $DELETE           = '<i class="fa fa-fw fa-times"></i>';
	static public $PLUS             = '<i class="fa fa-fw fa-plus"></i>';
	static public $ZOOM_IN          = '<i class="fa fa-fw fa-search-plus"></i>';
	static public $ZOOM_OUT         = '<i class="fa fa-fw fa-search-minus"></i>';
	static public $ZOOM_FIT         = '<i class="fa fa-fw fa-search"></i>';
	static public $CALCULATOR       = '<i class="fa fa-fw fa-magic"></i>';
	// Own pictures
	static public $INFO             = '<i class="fa fa-fw fa-info-circle"></i>';
	static public $WARNING          = '<i class="fa fa-fw fa-warning"></i>';
	static public $ERROR            = '<i class="fa fa-fw fa-minus-circle"></i>';

	// Shoes
	static public $BROKEN_1			= '<i class="broken-icon-1"></i>';
	static public $BROKEN_2			= '<i class="broken-icon-2"></i>';
	static public $BROKEN_3			= '<i class="broken-icon-3"></i>';
	static public $BROKEN_4			= '<i class="broken-icon-4"></i>';
	static public $BROKEN_5			= '<i class="broken-icon-5"></i>';

	/**
	 * This class contains only static methods
	 */
	private function __construct() {}
	private function __destruct() {}

	/**
	 * Get the sport-specific icon
	 * @param int $id
	 * @param string $title
	 */
	public static function getSportIcon($id, $title = '', $tooltip = '') {
		$data = Mysql::getInstance()->fetch(PREFIX.'sport', $id);
		if ($data === false)
			return '';

		if ($title == '')
			$title = $data['name'];

		if ($tooltip == '')
			$tooltip = $title;

		return Ajax::tooltip('<i class="sport-icon-'.str_replace('.gif', '', $data['img']).'"></i>', $tooltip);
	}

	/**
	 * Get sport icon
	 * @param string $gif filename.gif
	 * @return string
	 */
	public static function getSportIconForGif($gif) {
		return Ajax::tooltip('<i class="sport-icon-'.str_replace('.gif', '', $gif).'"></i>', $gif);
	}

	/**
	 * Get url to icon for given sportid
	 * @param int $id
	 * @return string 
	 */
	public static function getSportIconUrl($id) {
		$data = Mysql::getInstance()->fetch(PREFIX.'sport', $id);

		return self::$PATH_TO_SPORT_ICONS.$data['img'];
	}

	/**
	 * Get the weather-specific icon
	 * @param int $id
	 */
	public static function getWeatherIcon($id) {
		$data = Weather::getDataFor($id);

		return '<i class="weather-icon '.$data['img-class'].'"></i>';
	}

	/**
	 * Get icon for VDOT as shape
	 * @param double $VDOT
	 * @param bool $gray optional, default false
	 * @return string
	 */
	public static function getVDOTicon($VDOT, $gray = false) {
		if ($VDOT == 0)
			return '';

		$class = ' vdot-icon small';

		if ($gray)
			$class .= ' vdot-ignore';

		if ( $VDOT > (VDOT_FORM+3) )
			$class .= ' fa-arrow-up';
		elseif ( $VDOT > (VDOT_FORM+1) )
			$class .= ' fa-arrow-up fa-rotate-45';
		elseif ( $VDOT < (VDOT_FORM-3) )
			$class .= ' fa-arrow-down';
		elseif ( $VDOT < (VDOT_FORM-1) )
			$class .= ' fa-arrow-right fa-rotate-45';
		else
			$class .= ' fa-arrow-right';

		return Ajax::tooltip('<i class="fa fa-fw'.$class.'"></i>', 'VDOT: '.$VDOT);
	}
}