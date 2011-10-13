<?php
/**
 * This file contains the class::HTML for easy html-manipulation.
 */
/**
 * Class: HTML
 * 
 * @author Hannes Christiansen <mail@laufhannes.de>
 * @version 1.0
 * @uses class::Error
 * @uses class::Mysql
 */
class HTML {
	/**
	 * Constructor
	 */
	public function __construct() {}

	/**
	 * Destructor
	 */
	public function __destruct() {}

	/**
	 * Encode opening and ending tag-character
	 * @param string $string
	 * @return string
	 */
	public static function encodeTags($string) {
		return str_replace(array('<', '>'), array('&lt;', '&gt;'), $string);
	}

	/**
	 * Return an empty td-Tag
	 * @param int $colspan
	 * @return string
	 */
	public static function emptyTD($colspan = 0) {
		$colspan = ($colspan > 0) ? ' colspan="'.$colspan.'"' : '';

		return '<td'.$colspan.'>&nbsp;</td>'.NL;
	}

	/**
	 * Get a tr-tag for a bold header-line containing all month-names
	 * @param int $fixedWidth Fixed width for every month-td in percent [set '0' for no fixed width]
	 * @param int $emptyTDs Number of empty td before the month-td
	 * @return string
	 */
	public static function monthTR($fixedWidth = 0, $emptyTDs = 1) {
		$width = ($fixedWidth > 0) ? ' width="'.$fixedWidth.'%"' : '';
		$html = '<tr class="b">'.NL;

		for ($i = 1; $i <= $emptyTDs; $i++)
			$html .= '<td />'.NL;

		for ($m = 1; $m <= 12; $m++)
			$html .= '<td'.$width.'>'.Helper::Month($m, true).'</td>'.NL;

		$html .= '</tr>'.NL;

		return $html;
	}

	/**
	 * Get a tr-tag for a bold header-line containing all years
	 * @param int $fixedWidth Fixed width for every year-td in percent [set '0' for no fixed width]
	 * @param int $emptyTDs Number of empty td before the year-td
	 * @return string
	 */
	public static function yearTR($fixedWidth = 0, $emptyTDs = 1) {
		$width = ($fixedWidth > 0) ? ' width="'.$fixedWidth.'%"' : '';
		$html = '<tr class="b">'.NL;

		for ($i = 1; $i <= $emptyTDs; $i++)
			$html .= '<td />'.NL;

		for ($y = START_YEAR; $y <= date("Y"); $y++)
			$html .= '<td'.$width.'>'.$y.'</td>'.NL;

		$html .= '</tr>'.NL;

		return $html;
	}

	/**
	 * Get a tr-tag for a space-line
	 * @param int $colspan
	 */
	public static function spaceTR($colspan) {
		return '
			<tr class="space">
				<td colspan="'.$colspan.'">
				</td>
			</tr>'.NL;
	}

	/**
	 * Return a break with class="clear"
	 * @return string
	 */
	public static function clearBreak() {
		return '<br class="clear" />';
	}

	/**
	 * Replace ampersands for a textarea
	 * @param string $text
	 * @return string
	 */
	public static function textareaTransform($text) {
		return stripslashes(str_replace("&", "&amp;", $text));
	}

	/**
	 * Get html-tag for a textarea
	 * @param string $name
	 * @param int $cols
	 * @param int $rows
	 * @param string $value if not set, uses post-data as value
	 */
	public static function textarea($name, $cols = 70, $rows = 3, $value = '') {
		if ($value == '' && isset($_POST[$name]))
			$value = $_POST[$name];

		return '<textarea name="'.$name.'" cols="'.$cols.'" rows="'.$rows.'">'.$value.'</textarea>'.NL;
		
	}

	/**
	 * Get a simple input field, filled with post-data for this name
	 * @param string $name name for this field
	 * @param int $size size for this input
	 * @param string $default default value if $_POST[$name] is not set
	 * @return string
	 */
	public static function simpleInputField($name, $size = 20, $default = '') {
		$value = isset($_POST[$name]) ? $_POST[$name] : $default;
		$value = self::textareaTransform($value);

		return '<input type="text" name="'.$name.'" size="'.$size.'" value="'.$value.'" />';
	}

	/**
	 * Get a disabled input field, filled with post-data for this name
	 * @param string $name name for this field
	 * @param int $size size for this input
	 * @param string $default default value if $_POST[$name] is not set
	 * @return string
	 */
	public static function disabledInputField($name, $size = 20, $default = '') {
		$value = isset($_POST[$name]) ? $_POST[$name] : $default;
		$value = self::textareaTransform($value);

		return '<input type="text" name="'.$name.'" size="'.$size.'" value="'.$value.'" disabled="disabled" />';
	}

	/**
	 * Get a hidden input field, filled with post-data for this name
	 * @param string $name name for this field
	 * @param string $value value, if empty uses post-data
	 * @return string
	 */
	public static function hiddenInput($name, $value = '') {
		if ($value == '' && isset($_POST[$name]))
			$value = $_POST[$name];

		return '<input type="hidden" name="'.$name.'" value="'.$value.'" />';
	}

	/**
	 * Get a select box with given options
	 * @param string $name Name for this select-box
	 * @param mixed $checked [optional] bool for beeing checked or not, otherwise checks for post-data
	 * @return string
	 */
	public static function checkBox($name, $checked = -1) {
		if ($checked == -1)
			$checked = isset($_POST[$name]) && $_POST[$name] != 0;

		return '<input type="checkbox" name="'.$name.'"'.self::Checked($checked).' />';
	}

	/**
	 * Get ' checked="checked"' if boolean value is true
	 * @param bool $value
	 * @param mixed $value_to_be_checked [optional]
	 * @return string
	 */
	public static function Checked($value, $value_to_be_checked = NULL) {
		if ($value_to_be_checked != NULL)
			$value = ($value == $value_to_be_checked);
		if ($value == NULL || !isset($value))
			$value = false;

		return ($value === true)
			? ' checked="checked"'
			: '';
	}

	/**
	 * Get a select box with given options
	 * @param string $name Name for this select-box
	 * @param array $options Array containing values as indices, displayed text as values
	 * @param mixed $selected Value to be selected
	 * @return string
	 */
	public static function selectBox($name, $options, $selected = -1) {
		if ($selected == -1 && isset($_POST[$name]))
			$selected = $_POST[$name];

		$html = '<select name="'.$name.'">'.NL;

		foreach ($options as $value => $text)
			$html .= '<option value="'.$value.'"'.self::Selected($value, $selected).'>'.$text.'</option>'.NL;

		return $html.'</select>'.NL;
	}

	/**
	 * Get ' selected="selected"' if boolean value is true
	 * @param bool $value
	 * @param mixed $value_to_be_checked [optional]
	 * @return string
	 */
	public static function Selected($value, $value_to_be_checked = NULL) {
		if ($value_to_be_checked != NULL)
			$value = ($value == $value_to_be_checked);

		return ($value === true)
			? ' selected="selected"'
			: '';
	}

	/**
	 * Wrap given image-tag in a div-tag for showing a background-image for loading
	 * @param string $img complete <img>-tag
	 * @param int $width [px]
	 * @param int $height [px]
	 * @param string $addClass [optional] string added to class-attribute
	 * @return string
	 */
	public static function wrapImgForLoading($img, $width, $height, $addClass = '') {
		if (!empty($addClass))
			$addClass = ' '.$addClass;

		return '<div class="bigImg'.$addClass.'" style="width:'.$width.'px;height:'.$height.'px;">'.$img.'</div>'.NL;
	}

	/**
	 * Check if currently used browser is IE
	 * @return bool
	 */
	public static function isInternetExplorer() {
		return (strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE') !== false);
	}
}
?>