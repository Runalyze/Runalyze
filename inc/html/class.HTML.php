<?php
/**
 * This file contains class::HTML
 * @package Runalyze\HTML
 */

use Runalyze\Util\Time;

/**
 * Class: HTML
 *
 * @author Hannes Christiansen
 * @package Runalyze\HTML
 */
class HTML {
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
	 * @param string $string
	 * @return string
	 */
	public static function emptyTD($colspan = 0, $string = '&nbsp;', $class = '') {
		$colspan = ($colspan > 0) ? ' colspan="'.$colspan.'"' : '';
		$class   = ($class != '') ? ' class="'.$class.'"' : '';

		return '<td'.$colspan.$class.'>'.$string.'</td>';
	}

	/**
	 * Return an empty tr-Tag
	 * @param int $colspan colspan for empty TDs
	 * @return string
	 */
	public static function emptyTR($colspan = 0) {
		return '<tr>'.self::emptyTD($colspan).'</tr>';
	}

	/**
	 * Wrap string in td-tag
	 * @param string $string string for td-tag
	 * @param string $class optional css class
	 * @param string $style optional css inline style
	 * @return string
	 */
	public static function td($string, $class = '', $style = '') {
		if ($class != '')
			$class = ' class="'.$class.'"';

		if ($style != '')
			$style = ' style="'.$style.'"';

		return '<td'.$class.$style.'>'.$string.'</td>';
	}

	/**
	 * Get a tr-tag for a bold header-line containing all month-names
	 * @param int $fixedWidth Fixed width for every month-td in percent [set '0' for no fixed width]
	 * @param int $emptyTDs Number of empty td before the month-td
	 * @return string
	 */
	public static function monthTR($fixedWidth = 0, $emptyTDs = 1, $tag = 'td', $withTotal = false) {
		$width = ($fixedWidth > 0) ? ' width="'.$fixedWidth.'%"' : '';
		$html = '<tr class="b">';

		for ($i = 1; $i <= $emptyTDs; $i++)
			$html .= '<'.$tag.'></'.$tag.'>';

		for ($m = 1; $m <= 12; $m++)
			$html .= '<'.$tag.$width.'>'.Time::month($m, true).'</'.$tag.'>';

		if ($withTotal) {
			$html .= '<'.$tag.$width.'>'.__('Total').'</'.$tag.'>';
		}
		$html .= '</tr>';

		return $html;
	}

	/**
	 * Get a tr-tag for a bold header-line containing all years
	 * @param int $fixedWidth Fixed width for every year-td in percent [set '0' for no fixed width]
	 * @param int $emptyTDs Number of empty td before the year-td
	 * @param boolean $withTotal add last column for 'total'
	 * @return string
	 */
	public static function yearTR($fixedWidth = 0, $emptyTDs = 1, $tag = 'td', $withTotal = false) {
		$year = date('Y');
		$width = ($fixedWidth > 0) ? ' width="'.$fixedWidth.'%"' : '';
		$html = '<tr class="b">';

		for ($i = 1; $i <= $emptyTDs; $i++)
			$html .= '<'.$tag.' />';

		for ($y = START_YEAR; $y <= $year; $y++)
			$html .= '<'.$tag.$width.'>'.$y.'</'.$tag.'>';

		if ($withTotal) {
			$html .= '<'.$tag.$width.'>'.__('Total').'</'.$tag.'>';
		}

		$html .= '</tr>';

		return $html;
	}

	/**
	 * Get a tr-tag for a space-line
	 * @param int $colspan
	 * @return string
	 */
	public static function spaceTR($colspan) {
		return '<tr class="space"><td colspan="'.$colspan.'"></td></tr>';
	}

	/**
	 * Return a break
	 * @return string
	 */
	public static function br() {
		return '<br>';
	}

	/**
	 * Return a header
	 * @param string $text
	 * @return string
	 */
	public static function h1($text) {
		return '<h1>'.$text.'</h1>';
	}

	/**
	 * Return a break with class="clear"
	 * @return string
	 */
	public static function clearBreak() {
		return '<br class="clear">';
	}

	/**
	 * Wrap value in span for plus/minus
	 * @param float $value
	 * @param float $ignorance
	 * @return string
	 */
	public static function plusMinus($value, $ignorance = 0) {
		if ($value > +$ignorance)
			return '<span class="plus">'.$value.'</span>';
		if ($value < -$ignorance)
			return '<span class="minus">'.$value.'</span>';

		return $value;
	}

	/**
	 * Wrap a string into emphasize-tag
	 * @param string $string
	 * @return string
	 */
	public static function em($string) {
		return '<em>'.$string.'</em>';
	}

	/**
	 * Wrap a string into paragraph-tag
	 * @param string $string
	 * @return string
	 */
	public static function p($string) {
		return '<p class="text">'.$string.'</p>';
	}

	/**
	 * Wrap a string into p-tag with class="info"
	 * @param string $string
	 * @return string
	 */
	public static function info($string) {
		return '<p class="info">'.$string.'</p>';
	}

	/**
	 * Wrap a string into p-tag with class="error"
	 * @param string $string
	 * @return string
	 */
	public static function error($string) {
		return '<p class="error">'.$string.'</p>';
	}

	/**
	 * Wrap a string into p-tag with class="warning"
	 * @param string $string
	 * @return string
	 */
	public static function warning($string) {
		return '<p class="warning">'.$string.'</p>';
	}

	/**
	 * Wrap a string into p-tag with class="okay"
	 * @param string $string
	 * @return string
	 */
	public static function okay($string) {
		return '<p class="okay">'.$string.'</p>';
	}

	/**
	 * Wrap a string into p-tag with class="file"
	 * @param string $string
	 * @return string
	 */
	public static function fileBlock($string) {
		return '<p class="file">'.$string.'</p>';
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
	 * Get a hidden input field, filled with post-data for this name
	 * @param string $name name for this field
	 * @param string $value value, if empty uses post-data
	 * @return string
	 */
	public static function hiddenInput($name, $value = '') {
		if ($value == '' && isset($_POST[$name]))
			$value = $_POST[$name];

		return '<input type="hidden" name="'.$name.'" value="'.$value.'">';
	}

	/**
	 * Get a select box with given options
	 * @param string $name Name for this select-box
	 * @param mixed $checked [optional] bool for beeing checked or not, otherwise checks for post-data
	 * @param bool $noHiddenSent
	 * @return string
	 */
	public static function checkBox($name, $checked = -1, $noHiddenSent = false) {
		if ($checked === -1)
			$checked = isset($_POST[$name]) && $_POST[$name] != 0;

		$hiddenSent = self::hiddenInput($name.'_sent','true');

		return (!$noHiddenSent ? $hiddenSent : '').'<input type="checkbox" name="'.$name.'"'.self::Checked($checked).'>';
	}

	/**
	 * Get ' checked' if boolean value is true
	 * @param bool $value
	 * @param mixed $value_to_be_checked [optional]
	 * @return string
	 */
	public static function Checked($value, $value_to_be_checked = null) {
		if ($value_to_be_checked !== null)
			$value = ($value == $value_to_be_checked);
		if ($value === null || !isset($value))
			$value = false;

		return ($value === true)
			? ' checked'
			: '';
	}

	/**
	 * Get a select box with given options
	 * @param string $name Name for this select-box
	 * @param array $options Array containing values as indices, displayed text as values (may be array: 'text' => ..., 'data-...' => ...)
	 * @param mixed $selected Value to be selected
	 * @param string $id [optional]
	 * @param string $class
	 * @return string
	 */
	public static function selectBox($name, $options, $selected = false, $id = '', $class = '') {
		if ($selected === false && isset($_POST[$name]))
			$selected = $_POST[$name];

		$html = '<select name="'.$name.'"'.(!empty($id) ? ' id="'.$id.'"' : '').(!empty($class) ? ' class="'.$class.'"' : '').'>';

		foreach ($options as $value => $text) {
			$additionalAttributes = array();
			$displayedText = $text;

			if (is_array($text)) {
				$displayedText = (isset($text['text'])) ? $text['text'] : '?';

				foreach ($text as $attr => $attrVal) {
					if ($attr != 'text')
						$additionalAttributes[] = ' '.$attr.'="'.$attrVal.'"';
				}
			}

			$html .= '<option value="'.$value.'"'.self::Selected($value, $selected).implode($additionalAttributes, '').'>'.$displayedText.'</option>';
		}

		return $html.'</select>';
	}

	/**
	 * Get ' selected' if boolean value is true
	 * @param bool $value
	 * @param mixed $value_to_be_checked [optional]
	 * @return string
	 */
	public static function Selected($value, $value_to_be_checked = null) {
		if ($value_to_be_checked !== null) {
			if (is_array($value_to_be_checked))
				$value = in_array($value, $value_to_be_checked);
			else
				$value = ($value_to_be_checked === '') ? ($value === $value_to_be_checked) : ($value == $value_to_be_checked);
		}

		return ($value === true)
			? ' selected'
			: '';
	}
}
