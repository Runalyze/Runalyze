<?php
/**
 * Class for all icons used in Runalyze
 * @author Hannes Christiansen <mail@laufhannes.de>
 */
class Icon {
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
	// Own pictures
	static public $INFO             = '<i class="icon-info"></i>';
	static public $WARNING          = '<i class="icon-warning"></i>';
	static public $ERROR            = '<i class="icon-error"></i>';

	// Shoes
	static public $BROKEN_1			= '<img src="img/running/broken-1.png" />';
	static public $BROKEN_2			= '<img src="img/running/broken-2.png" />';
	static public $BROKEN_3			= '<img src="img/running/broken-3.png" />';
	static public $BROKEN_4			= '<img src="img/running/broken-4.png" />';
	static public $BROKEN_5			= '<img src="img/running/broken-5.png" />';

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

		$Image = '<img src="img/sports/'.$data['img'].'" alt="'.$title.'" />';

		return Ajax::tooltip($Image, $tooltip);
	}

	/**
	 * Get url to icon for given sportid
	 * @param int $id
	 * @return string 
	 */
	public static function getSportIconUrl($id) {
		$data = Mysql::getInstance()->fetch(PREFIX.'sport', $id);

		return 'img/sports/'.$data['img'];
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
	 * @return string
	 */
	public static function getVDOTicon($VDOT) {
		if ( $VDOT > (VDOT_FORM+3) )
			$class = 'vdot-up-2';
		elseif ( $VDOT > (VDOT_FORM+1) )
			$class = 'vdot-up';
		elseif ( $VDOT < (VDOT_FORM-3) )
			$class = 'vdot-down-2';
		elseif ( $VDOT < (VDOT_FORM-1) )
			$class = 'vdot-down';
		else
			$class = 'vdot-normal';

		return Ajax::tooltip('<i class="vdot-icon '.$class.'"></i>', 'VDOT: '.$VDOT);
	}
}