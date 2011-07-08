<?php
/**
 * This file contains the class to handle all AJAX-Links
 */
/**
 * Class: Ajax
 * 
 * @author Hannes Christiansen <mail@laufhannes.de>
 * @version 1.0
 * @uses class::Error
 *
 * Last modified 2011/07/07 19:00 by Hannes Christiansen
 */
class Ajax {
	/**
	 * Gives a HTML-link for using jTraining
	 * @param int $training_id
	 * @param string $name
	 * @return string
	 */
	static function trainingLink($training_id, $name, $closeOverlay = false) {
		return '<a class="training" href="inc/class.Training.display.php?id='.$training_id.'" rel="'.$training_id.'" '.($closeOverlay ? ' onclick="closeOverlay()"' : '').'>'.$name.'</a>';
	}

	/**
	 * Gives a HTML-link for using jToggle()
	 * @param string $link        The normal HTML-link
	 * @param string $toggle_id   The ID of the css-container to toggle
	 * @return string
	 */
	static function toggle($link, $toggle_id) {
		$link = self::insertClass($link, 'toggle');
		$link = self::insertRel($link, $toggle_id);

		return $link;
	}

	/**
	 * Gives a HTML-link for using jImgChange()
	 * @param string $link      The normal HTML-link
	 * @param string $imgID     <img id="$imgID" ...
	 * @return string
	 */
	static function imgChange($link, $imgID) {
		$link = self::insertClass($link, 'jImg');
		$link = self::insertRel($link, $imgID);

		return $link;
	}

	/**
	 * Gives a HTML-link for using jWindow()
	 * @param string $link     The normal HTML-link
	 * @param string $size     Enum: big|normal|small
	 * @return string
	 */
	static function window($link, $size = 'normal') {
		$link = self::insertClass($link, 'window');
		if ($size == 'big' || $size == 'small')
			$link = self::insertRel($link, $size);

		return $link;
	}

	/**
	 * Gives a HTML-link for using jChange()
	 * @param string $name   Displayed name for this link
	 * @param string $target ID of surrounding div-container
	 * @param string $href   ID of div-container to be displayed
	 * @return string
	 */
	static function change($name, $target, $href, $additional_class = '') {
		if ($additional_class != '')
			$additional_class .= ' ';

		return '<a class="'.$additional_class.'change" target="'.$target.'" href="'.$href.'">'.$name.'</a>';
	}

	/**
	 * Gives a HTML-link for using jLinks()
	 * @param string $name   Displayed name for this link
	 * @param string $target ID of target div-container
	 * @param string $href   URL to be loaded
	 * @param string $data   data to be passed
	 * @return string
	 */
	static function link($name, $target, $href, $data = '') {
		return '<a class="ajax" href="'.$href.'"target="'.$target.'" rel="'.$data.'" >'.$name.'</a>';
	}

	/**
	 * Adds a new class-value or creates a class-attribute
	 * @param string $link    The full HTML-link
	 * @param string $class   The new css-class
	 * @return string
	 */
	private static function insertClass($link, $class) {
		$text = preg_replace('#class="(.+?)"#i', 'class="'.$class.' \\1"', $link);
		if ($text == $link)
			$text = str_replace('<a', '<a class="'.$class.'"', $text);
		if ($text == $link)
			Error::getInstance()->addWarning('Unexpected error in using Ajax::insertClass(\''.$link.'\',\''.$class.'\')');

		return $text;
	}

	/**
	 * Overwrites an existing rel-attribute or creates a new one
	 * @param string $link   The full HTML-link
	 * @param string $rel    The new rel-value
	 * @return string
	 */
	private static function insertRel($link, $rel) {
		$text = preg_replace('#rel="(.+?)"#i', 'rel="'.$rel.'"', $link);
		if ($text == $link)
			$text = str_replace('<a', '<a rel="'.$rel.'"', $text);
		if ($text == $link)
			Error::getInstance()->addWarning('Unexpected error in using Ajax::insertRel(\''.$link.'\',\''.$rel.'\')');

		return $text;
	}
}
?>