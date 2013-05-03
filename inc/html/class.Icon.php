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
	static public $EDIT             = '<i class="icon-edit"></i>';
	static public $BACK             = '<i class="icon-back"></i>';
	static public $NEXT             = '<i class="icon-next"></i>';
	static public $ABC              = '<i class="icon-abc"></i>';
	static public $ATTACH           = '<i class="icon-attach"></i>';
	static public $CLOCK            = '<i class="icon-clock"></i>';
	static public $CLOCK_ORANGE     = '<i class="icon-clock-orange"></i>';
	static public $CLOCK_GREY       = '<i class="icon-clock-grey"></i>';
	static public $DOWNLOAD         = '<i class="icon-download"></i>';
	static public $DOWN             = '<i class="icon-down"></i>';
	static public $UP               = '<i class="icon-up"></i>';
	static public $ADD_SMALL        = '<i class="icon-add-small"></i>';
	static public $ADD_SMALL_GREEN  = '<i class="icon-add-small-green"></i>';
	static public $CONF             = '<i class="icon-conf"></i>';
	static public $ZOOM_IN_SMALL    = '<i class="icon-zoom-in-small"></i>';
	static public $ZOOM_OUT_SMALL   = '<i class="icon-zoom-out-small"></i>';
	static public $SAVE             = '<i class="icon-save"></i>';
	// Big, 16x16px
	static public $ADD              = '<i class="icon-add"></i>';
	static public $CROSS            = '<i class="icon-cross"></i>';
	static public $SEARCH           = '<i class="icon-search"></i>';
	static public $REFRESH          = '<i class="icon-refresh"></i>';
	static public $CALENDAR         = '<i class="icon-calendar"></i>';
	static public $BARS_BIG         = '<i class="icon-bars-big"></i>';
	static public $BARS_SMALL       = '<i class="icon-bars-small"></i>';
	static public $FATIGUE          = '<i class="icon-fatigue"></i>';
	static public $TABLE            = '<i class="icon-table"></i>';
	static public $DELETE           = '<i class="icon-delete"></i>';
	static public $PLUS             = '<i class="icon-plus"></i>';
	static public $ZOOM_IN          = '<i class="icon-zoom-in"></i>';
	static public $ZOOM_OUT         = '<i class="icon-zoom-out"></i>';
	static public $ZOOM_FIT         = '<i class="icon-zoom-fit"></i>';
	static public $CALCULATOR       = '<i class="icon-calculator"></i>';
	// Own pictures
	static public $INFO             = '<i class="icon-info"></i>';
	static public $WARNING          = '<i class="icon-warning"></i>';
	static public $ERROR            = '<i class="icon-error"></i>';

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

		$Image = '<img src="'.self::$PATH_TO_SPORT_ICONS.$data['img'].'" alt="'.$title.'" />';

		return Ajax::tooltip($Image, $tooltip);
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

		$class = 'vdot-';

		if ($gray)
			$class .= 'gray-';

		if ( $VDOT > (VDOT_FORM+3) )
			$class .= 'up-2';
		elseif ( $VDOT > (VDOT_FORM+1) )
			$class .= 'up';
		elseif ( $VDOT < (VDOT_FORM-3) )
			$class .= 'down-2';
		elseif ( $VDOT < (VDOT_FORM-1) )
			$class .= 'down';
		else
			$class .= 'normal';

		return Ajax::tooltip('<i class="vdot-icon '.$class.'"></i>', 'VDOT: '.$VDOT);
	}
}