<?php
/**
 * @deprecated
 */
class ImporterWindow {
	/**
	 * URL for window
	 * @var string
	 */
	public static $URL = 'activity/add';

    /**
     * URL for window
     * @var string
     */
    public static $URL_FORM = 'activity/new';

	/**
	 * Get link for create window
	 */
	public static function link() {
		return Ajax::window('<a href="'.self::$URL.'">'.Icon::$ADD.' '.__('Add workout').'</a>', 'small');
	}

	/**
	 * Get link for create window for a given date
	 * @param int $timestampInServerTime
	 * @return string
	 */
	public static function linkForDate($timestampInServerTime) {
		if ($timestampInServerTime > time()) {
			return '<span style="opacity:.25;">'.Icon::$ADD_SMALL.'</span>';
		}

		$date = date('d.m.Y', $timestampInServerTime);

		return Ajax::window('<a href="'.self::$URL_FORM.'?date='.$date.'">'.Icon::$ADD_SMALL.'</a>', 'small');
	}
}
