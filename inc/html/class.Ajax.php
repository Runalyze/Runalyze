<?php
/**
 * This file contains class::Ajax
 * @package Runalyze\HTML
 */
/**
 * JavaScript/jQuery class
 * 
 * @author Hannes Christiansen
 * @package Runalyze\HTML
 */
class Ajax {
	/**
	 * CSS-class for waiter-image
	 * @var string
	 */
	public static $IMG_WAIT = 'wait-img';

	/**
	 * ID for plugin to reload, can be empty to reload all plugins
	 * @var int
	 */
	public static $RELOAD_PLUGIN_ID = '';

	/**
	 * Enum: Reload flag - no reload
	 * @var int
	 */
	public static $RELOAD_NONE = 0;

	/**
	 * Enum: Reload flag - reload databrowser
	 * @var int
	 */
	public static $RELOAD_DATABROWSER = 1;

	/**
	 * Enum: Reload flag - reload training
	 * @var int
	 */
	public static $RELOAD_TRAINING = 2;

	/**
	 * Enum: Reload flag - reload databrowser and training
	 * @var int
	 */
	public static $RELOAD_DATABROWSER_AND_TRAINING = 3;

	/**
	 * Enum: Reload flag - reload all plugins
	 * @var int
	 */
	public static $RELOAD_PLUGINS = 4;

	/**
	 * Enum: Reload flag - reload all elements with jQuery
	 * @var int
	 */
	public static $RELOAD_ALL = 5;

	/**
	 * Enum: Reload flag - reload complete page
	 * @var int
	 */
	public static $RELOAD_PAGE = 6;

	/**
	 * Current reload flag
	 * @var int
	 */
	private static $currentReloadFlag = 0;

	/**
	 * Init own JS-library on frontend (direct output)
	 */
	public static function initJSlibrary() {
		$Options = array();
		$Options['sharedView'] = Request::isOnSharedPage();

		echo self::wrapJS('Runalyze.init('.json_encode($Options).');');

		if (defined('NOKIA_HERE_APPID') && NOKIA_HERE_APPID != '' && defined('NOKIA_HERE_TOKEN') && NOKIA_HERE_TOKEN != '') {
			echo self::wrapJS('Runalyze.Options.setNokiaLayerAuth("'.NOKIA_HERE_APPID.'", "'.NOKIA_HERE_TOKEN.'")');
		}
	}

	/**
	 * Set reload flag
	 * @param int $Flag 
	 */
	public static function setReloadFlag($Flag) {
		$BothFlags = array($Flag, self::$currentReloadFlag);

		if (min($BothFlags) == self::$RELOAD_DATABROWSER && max($BothFlags) == self::$RELOAD_PLUGINS)
			self::$currentReloadFlag = self::$RELOAD_ALL;
		elseif (min($BothFlags) == self::$RELOAD_DATABROWSER && max($BothFlags) == self::$RELOAD_TRAINING)
			self::$currentReloadFlag = self::$RELOAD_DATABROWSER_AND_TRAINING;
		else
			self::$currentReloadFlag = max($BothFlags);
	}

	/**
	 * Set ID of plugin to reload
	 * @param int $ID
	 */
	public static function setPluginIDtoReload($ID) {
		self::$RELOAD_PLUGIN_ID = $ID;
	}

	/**
	 * Get reload command
	 * @return string 
	 */
	public static function getReloadCommand() {
		switch(self::$currentReloadFlag) {
			case self::$RELOAD_PAGE:
				return self::wrapJS('Runalyze.reloadPage();');
			case self::$RELOAD_ALL:
				return self::wrapJS('Runalyze.reloadContent();');
			case self::$RELOAD_PLUGINS:
				return self::wrapJS('Runalyze.reloadAllPlugins('.self::$RELOAD_PLUGIN_ID.');');
			case self::$RELOAD_DATABROWSER_AND_TRAINING:
				return self::wrapJS('Runalyze.reloadDataBrowserAndTraining();');
			case self::$RELOAD_TRAINING:
				return self::wrapJS('Runalyze.Training.reload();');
			case self::$RELOAD_DATABROWSER:
				return self::wrapJS('Runalyze.DataBrowser.reload();');
			case self::$RELOAD_NONE:
			default:
				return '';
		}
	}

	/**
	 * Gives a HTML-link for using jTraining
	 * @param int $id ID of the training
	 * @param string $name Name of the link to be displayed
	 * @param bool $closeOverlay [optional] Boolean flag: Should the overlay be closed after clicking? (default: false)
	 * @return string
	 */
	public static function trainingLink($id, $name, $closeOverlay = false, $classes = '', $htmlID = '') {
		return '<a '.(!empty($htmlID) ? 'id="'.$htmlID.'" ' : '').'class="training '.$classes.'" href="call/call.Training.display.php?id='.$id.'" rel="'.$id.'"'.($closeOverlay ? ' onclick="Runalyze.Overlay.close()"' : '').'>'.$name.'</a>';
	}

	/**
	 * Get onclick-string for loading training
	 * @param int $id ID of the training
	 * @return string
	 */
	public static function trainingLinkAsOnclick($id) {
		if (FrontendShared::$IS_SHOWN)
			return 'onclick="Runalyze.Training.load('.$id.', \''.SharedLinker::getUrlFor($id).'\')"';

		return 'onclick="Runalyze.Training.load('.$id.')"';
	}

	/**
	 * Get html-code for jquery-tooltip
	 * @param string $html
	 * @param string $tooltip
	 * @param boolean $atLeft [optional] can be 'atRight'
	 * @param boolean $onlyAttributes [optional]
	 * @return string
	 */
	public static function tooltip($html, $tooltip, $atLeft = false, $onlyAttributes = false) {
		if ($tooltip == '')
			return $html;

		$class = $atLeft ? is_string($atLeft) ? 'class="'.$atLeft.'"' : 'class="atLeft" ' : '';

		if ($onlyAttributes)
			return $class.'rel="tooltip" title="'.htmlspecialchars($tooltip).'"';

		return '<span '.$class.'rel="tooltip" title="'.htmlspecialchars($tooltip).'">'.$html.'</span>';
	}

	/**
	 * Get code for toolbar navigation for links as array (tag => ..., subs => array(..., ...))
	 * @param array $Links
	 * @param string $AdditionalClasses
	 * @returns string
	 */
	public static function toolbarNavigation($Links, $AdditionalClasses = '') {
		if (empty($Links))
			return '';

		$code = '<ul class="'.$AdditionalClasses.'">';

		foreach ($Links as $Link) {
			if (is_array($Link) && isset($Link['tag'])) {
				$hasSubcontainer = isset($Link['subs']) && is_array($Link['subs']);

				$code .= '<li'.($hasSubcontainer ? ' class="with-submenu"' : '').'>';
				$code .= $Link['tag'];

				if ($hasSubcontainer) {
					$code .= '<ul class="submenu">';

					foreach ($Link['subs'] as $Sublink)
						$code .= '<li>'.$Sublink.'</li>';

					$code .= '</ul>';
				}

				$code .= '</li>';
			}
		}

		$code .= '</ul>';

		return $code;
	}

	/**
	 * Gives a HTML-link for using jToggle()
	 * @param string $link        The normal HTML-link
	 * @param string $toggle_id   The ID of the css-container to toggle
	 * @return string
	 */
	public static function toggle($link, $toggle_id) {
		$link = self::insertClass($link, 'toggle');
		$link = self::insertRel($link, $toggle_id);

		return $link;
	}

	/**
	 * Gives a HTML-link for using jWindow()
	 * @param string $link     The normal HTML-link
	 * @param string $size     Enum: big|normal|small
	 * @return string
	 */
	public static function window($link, $size = 'normal') {
		$link = self::insertClass($link, 'window');
		if ($size == 'big' || $size == 'small')
			$link = self::insertDataSize($link, $size);

		return $link;
	}

	/**
	 * Gives a HTML-link for using jChange()
	 * @param string $name   Displayed name for this link
	 * @param string $target ID of surrounding div-container
	 * @param string $href   ID of div-container to be displayed
	 * @return string
	 */
	public static function change($name, $target, $href, $additional_class = '') {
		if (substr($href, 0, 1) != '#')
			$href = '#'.$href;

		if ($additional_class != '')
			$additional_class .= ' ';

		return '<a class="'.$additional_class.'change" target="'.$target.'" href="'.$href.'">'.$name.'</a>';
	}

	/**
	 * Gives a HTML-link for using jLinks()
	 * @param string $name    Displayed name for this link
	 * @param string $target  ID of target div-container
	 * @param string $href    URL to be loaded
	 * @param string $data    data to be passed
	 * @param string $title   title
	 * @return string
	 */
	public static function link($name, $target, $href, $data = '', $title = '') {
		return '<a class="ajax" href="'.$href.'" target="'.$target.'" rel="'.$data.'"'.(!empty($title) ? ' title="'.$title.'"' : '').'>'.$name.'</a>';
	}

	/**
	 * Transform text to link for changing flot
	 * @param string $text
	 * @param string $divID
	 * @param string $flotID
	 * @param boolean $active
	 * @return string
	 */
	public static function flotChange($text, $divID, $flotID, $active = true) {
		return '<span class="link'.($active ? '' : ' unimportant').' flotChanger-'.$divID.' flotChanger-id-'.$flotID.'" onclick="Runalyze.flotChange(\''.$divID.'\',\''.$flotID.'\')">'.$text.'</span>';
	}

	/**
	 * Adds a new class-value or creates a class-attribute
	 * @param string $link    The full HTML-link
	 * @param string $class   The new css-class
	 * @return string
	 */
	public static function insertClass($link, $class) {
		$text = preg_replace('#<a ([^>]*?)class="(.+?)"#i', '<a \\1class="'.$class.' \\2"', $link);
		if ($text == $link)
			$text = str_replace('<a ', '<a class="'.$class.'" ', $text);

		return $text;
	}

	/**
	 * Overwrites an existing rel-attribute or creates a new one
	 * @param string $link   The full HTML-link
	 * @param string $rel    The new rel-value
	 * @return string
	 */
	public static function insertRel($link, $rel) {
		$text = preg_replace('#rel="(.+?)"#i', 'rel="'.$rel.'"', $link);
		if ($text == $link)
			$text = str_replace('<a ', '<a rel="'.$rel.'" ', $text);

		return $text;
	}

	/**
	 * Overwrites an existing data-size-attribute or creates a new one
	 * @param string $link   The full HTML-link
	 * @param string $rel    The new data-size-value
	 * @return string
	 */
	public static function insertDataSize($link, $rel) {
		$text = preg_replace('#data-size="(.+?)"#i', 'data-size="'.$rel.'"', $link);
		if ($text == $link)
			$text = str_replace('<a ', '<a data-size="'.$rel.'" ', $text);

		return $text;
	}

	/**
	 * Wrap JavaScript into code block
	 * @param string $code
	 * @return string
	 */
	public static function wrapJS($code) {
		return '<script>'.$code.'</script>';
	}

	/**
	 * Wrap JavaScript into code block for beeing executed on document ready
	 * @param string $code
	 * @return string
	 */
	public static function wrapJSforDocumentReady($code) {
		return self::wrapJS('(function($){$(document).ready(function(){ '.$code.' });})(jQuery);');
	}

	/**
	 * Wrap JavaScript into code block for beeing an unnamed function
	 * @param string $code
	 * @return string
	 */
	public static function wrapJSasFunction($code) {
		return self::wrapJS('$(function(){ '.$code.' });');
	}

	/**
	 * JSON encode with function
	 * @param array $input
	 * @param array $funcs
	 * @param int $level
	 * @return string
	 */
	public static function json_encode_jsfunc($input, $funcs = array(), $level = 0) {
		foreach($input as $key => $value) {
			if (is_array($value)) {
				$ret = self::json_encode_jsfunc($value, $funcs, 1);
				$input[$key] = $ret[0];
				$funcs = $ret[1];
			} elseif (substr($value,0,8) == 'function' || substr($value,0,13) == 'RunalyzePlot.') {
                  $func_key = "#".uniqid(rand(), true)."#";
                  $funcs[$func_key] = $value;
                  $input[$key] = $func_key;
			}
		}
		if ($level == 1)
			return array($input, $funcs);
		else {
			$input_json = json_encode($input);
			foreach($funcs as $key => $value)
				$input_json = str_replace('"'.$key.'"', $value, $input_json);
			return $input_json;
		}
	}

	/**
	 * Get code for closing overlay
	 * @return string
	 */
	public static function closeOverlay() {
		return self::wrapJS('Runalyze.Overlay.close();');
	}

	/**
	 * Create code for binding tablesorter
	 * @param string $selector
	 * @param boolean $reinit [optional]
	 */
	public static function createTablesorterFor($selector, $reinit = false) {
		echo self::wrapJSforDocumentReady('$("'.$selector.'").tablesorterAutosort('.($reinit?'true':'').');');
	}

	/**
	 * Create code for binding tablesorter with pager
	 * @param string $selector
	 * @param bool $reinit [optional]
	 */
	public static function createTablesorterWithPagerFor($selector, $reinit = false) {
		echo self::getTablesorterWithPagerFor($selector, $reinit);
	}

	/**
	 * Create code for binding tablesorter with pager
	 * @param string $selector
	 * @param bool $reinit [optional]
	 * @return string
	 */
	public static function getTablesorterWithPagerFor($selector, $reinit = false) {
		$Code = self::getPagerDiv();
		$Code .= self::wrapJSforDocumentReady('$("'.$selector.'").tablesorterWithPager('.($reinit?'true':'').');');

		return $Code;
	}

	/**
	 * Print div for pager for tables
	 */
	private static function printPagerDiv() {
		echo self::getPagerDiv();
	}

	/**
	 * Get div for pager for tables
	 */
	private static function getPagerDiv() {
		return '
<div id="pager" class="pager c">
	<form>
		<span class="link first">|&laquo; '.__('Start').'</span>
		<span class="link prev">&laquo; '.__('previous').'</span>
		<input type="text" class="pagedisplay">
		<span class="link next">'.__('next').' &raquo;</span>
		<span class="link last">'.__('End').' &raquo;|</span>

		<select class="pagesize">
			<option value="10">10 '.__('per page').'&nbsp;</option>
			<option value="20" selected>20 '.__('per page').'&nbsp;</option>
			<option value="30">30 '.__('per page').'&nbsp;</option>
			<option value="40">40 '.__('per page').'&nbsp;</option>
			<option value="50">50 '.__('per page').'&nbsp;</option>
		</select>
	</form>
</div>';
	}
}