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
	static public $HEART            = '<i class="fa fa-fw fa-heart"></i>';
	static public $MAP              = '<i class="fa fa-fw fa-location-arrow"></i>';
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
	static public $LINE_CHART       = '<i class="fa fa-fw fa-line-chart"></i>';
	static public $AREA_CHART       = '<i class="fa fa-fw fa-area-chart"></i>';
	static public $PIE_CHART        = '<i class="fa fa-fw fa-pie-chart"></i>';
	static public $FATIGUE          = '<i class="fa fa-fw fa-signal"></i>';
	static public $TABLE            = '<i class="fa fa-fw fa-table"></i>';
	static public $DELETE           = '<i class="fa fa-fw fa-times"></i>';
	static public $PLUS             = '<i class="fa fa-fw fa-plus"></i>';
	static public $ZOOM_IN          = '<i class="fa fa-fw fa-search-plus"></i>';
	static public $ZOOM_OUT         = '<i class="fa fa-fw fa-search-minus"></i>';
	static public $ZOOM_FIT         = '<i class="fa fa-fw fa-search"></i>';
	static public $MAGIC            = '<i class="fa fa-fw fa-magic"></i>';
	static public $CALCULATOR       = '<i class="fa fa-fw fa-calculator"></i>';
	// Own pictures
	static public $INFO             = '<i class="fa fa-fw fa-info-circle"></i>';
	static public $WARNING          = '<i class="fa fa-fw fa-warning"></i>';
	static public $ERROR            = '<i class="fa fa-fw fa-minus-circle"></i>';

	static public $USER             = '<i class="fa fa-fw fa-user"></i>';
	static public $PASSWORD         = '<i class="fa fa-fw fa-lock"></i>';
	static public $MAIL             = '<i class="fa fa-fw fa-envelope"></i>';

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
		$data = SportFactory::DataFor($id);
		if ($data === false)
			return '';

		if ($title == '')
			$title = $data['name'];

		if ($tooltip == '')
			$tooltip = $title;

		return Ajax::tooltip('<i class="'.$data['img'].'"></i>', $tooltip);
	}

	/**
	 * Get sport icon
	 * @param string $gif filename.gif
	 * @param string $tooltip optional
	 * @param string $tooltipCssClass optional, e.g. 'atRight'
	 * @return string
	 */
	public static function getSportIconForGif($gif, $tooltip = '', $tooltipCssClass = '') {
		return Ajax::tooltip('<i class="'.$gif.'""></i>', $tooltip, $tooltipCssClass);
	}
}
