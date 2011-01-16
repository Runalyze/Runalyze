<?php
/**
 * This file contains the class to handle all AJAX-Links
 */
/**
 * Class: Ajax
 * 
 * @author Hannes Christiansen <mail@laufhannes.de>
 * @version 1.0
 * @uses class:Error ($error)
 *
 * Last modified 2011/01/15 18:21 by Hannes Christiansen
 */
class Ajax {
	/**
	 * Gives a HTML-link for using jTraining which is calling inc/tpl/tpl.training.php
	 * @param int $training_id
	 * @param string $name
	 * @return string
	 */
	static function trainingLink($training_id, $name) {
		return '<a class="training" href="inc/class.Training.display.php?id='.$training_id.'" rel="'.$training_id.'">'.$name.'</a>';
		//return '<a class="training" href="inc/tpl/tpl.training.php?id='.$training_id.'" rel="'.$training_id.'">'.$name.'</a>';
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
	 * @param $link      The normal HTML-link
	 * @param $imgID     <img id="$imgID" ...
	 * @return string
	 */
	static function imgChange($link, $imgID) {
		$link = self::insertClass($link, 'jImg');
		$link = self::insertRel($link, $imgID);

		return $link;
	}

	/**
	 * Gives a HTML-link for using jWindow()
	 * @param $link     The normal HTML-link
	 * @param $size     Enum: big|normal|small
	 * @return string
	 */
	static function window($link, $size = 'normal') {
		$link = self::insertClass($link, 'window');
		if ($size == 'big' || $size == 'small')
			$link = self::insertRel($link, $size);

		return $link;
	}

	// TODO change()
	// TODO jImg()

	/**
	 * Adds a new class-value or creates a class-attribute
	 * @param $link    The full HTML-link
	 * @param $class   The new css-class
	 * @return string
	 */
	private static function insertClass($link, $class) {
		global $error;

		$text = preg_replace('#class="(.+?)"#i', 'class="'.$class.' \\1"', $link);
		if ($text == $link)
			$text = preg_replace('#<a#i', '<a class="'.$class.'"', $text);
		if ($text == $link)
			$error->add('WARNING','Unexpected error in using Ajax::insertClass(\''.$link.'\',\''.$class.'\')');

		return $text;
	}

	/**
	 * Overwrites an existing rel-attribute or creates a new one
	 * @param $link   The full HTML-link
	 * @param $rel    The new rel-value
	 * @return string
	 */
	private static function insertRel($link, $rel) {
		global $error;

		$text = preg_replace('#rel="(.+?)"#i', 'rel="'.$rel.'"', $link);
		if ($text == $link)
			$text = preg_replace('#<a#i', '<a rel="'.$rel.'"', $text);
		if ($text == $link)
			$error->add('WARNING','Unexpected error in using Ajax::insertRel(\''.$link.'\',\''.$rel.'\')');

		return $text;
	}
}
?>