<?php
/**
 * This file contains class::MultiEditor
 * @package Runalyze\Import
 */

use Runalyze\Model\Activity;
use Runalyze\View\Activity\Preview;
use Runalyze\View\Activity\Linker;

/**
 * Multi editor
 *
 * @author Hannes Christiansen
 * @package Runalyze\Import
 */
class MultiEditor {
	/**
	 * IDs
	 * @var array
	 */
	static protected $IDs = array();

	/**
	 * Statement to fetch activities
	 * @var \PDOStatement
	 */
	protected $FetchStatement = null;

	/**
	 * Constructor
	 * 
	 * Construct a new editor to display it directly
	 * @param array $IDs
	 */
	public function __construct(array $IDs) {
		self::setIDs($IDs);
	}

	/**
	 * Display
	 */
	public function display() {
		if (empty(self::$IDs)) {
			echo HTML::error( __('No activities for editing were set.') );
		} else {
			$this->displayEditor();
			$this->displayNavigation();
		}
	}

	/**
	 * Display navigation
	 */
	protected function displayNavigation() {
		$Code  = '<div id="ajax-navigation" class="panel">';
		$Code .= '<div class="panel-heading">';
		$Code .= '<h1>'.__('Multi editor').'</h1>';
		$Code .= '</div>';
		$Code .= '<div class="panel-content">';
		$Code .= '<table class="multi-edit-table fullwidth zebra-style"><tbody>';

		foreach (self::$IDs as $i => $ID) {
			$Preview = new Preview(
				new Activity\Object($this->fetchActivity($ID))
			);

			$Code .= '<tr id="multi-edit-'.$ID.'" class="link '.($i == 0 ? ' highlight' : '').' show-on-hover-parent">';
			$Code .= '<td class="multi-edit-sport-icon c"><span class="link show-on-hover multi-edit-remove-link">'.Icon::$CROSS_SMALL.'</span>'.$Preview->sportIcon().'</td>';
			$Code .= '<td>'.$Preview->dateAndSmallTime().'<br><small>'.$Preview->durationAndDistance().'</small></td>';
			$Code .= '<td class="multi-edit-icon">'.$Preview->hrIcon().'</td>';
			$Code .= '<td class="multi-edit-icon">'.$Preview->splitsIcon().'</td>';
			$Code .= '<td class="multi-edit-icon">'.$Preview->mapIcon().'</td>';
			$Code .= '</tr>';
		}

		$Code .= '</tbody></table>';
		$Code .= '</div>';
		$Code .= '</div>';

		echo Ajax::wrapJS('$(\'#ajax-navigation\').remove();$(\'#ajax-outer\').append(\''.$Code.'\')');
		echo Ajax::wrapJSasFunction('$("#ajax-navigation tr.link").click(function(e){
	$("#ajax-navigation tr.link.highlight").removeClass("highlight").addClass("edited");
	$(this).removeClass("edited").addClass("highlight");
	Runalyze.Overlay.load( "'.Linker::EDITOR_URL.'?mode=multi&id=" + $(this).attr("id").substr(11) );
});');
		echo Ajax::wrapJSasFunction('$("#ajax-navigation .multi-edit-remove-link").click(function(e){
	$(this).parent().parent().remove();
	e.stopPropagation();
});');
	}

	/**
	 * Fetch activity for preview
	 * @param int $id
	 * @return array
	 */
	protected function fetchActivity($id) {
		if (is_null($this->FetchStatement)) {
			$this->FetchStatement = DB::getInstance()->prepare(
				'SELECT
					'.implode(',', Preview::keys()).'
				FROM `'.PREFIX.'training`
				WHERE `id`=:id
				LIMIT 1'
			);
		}

		$this->FetchStatement->execute(array(':id' => $id));
		return $this->FetchStatement->fetch();
	}

	/**
	 * Display editor
	 * 
	 * This function will just load the standard editor in the overlay
	 */
	protected function displayEditor() {
		echo Ajax::wrapJS('Runalyze.Overlay.load(\''.Linker::EDITOR_URL.'?mode=multi&id='.self::$IDs[0].'\');');
	}

	/**
	 * Set IDs
	 * @param array $IDs
	 */
	static public function setIDs(array $IDs) {
		self::$IDs = $IDs;
	}

	/**
	 * Get IDs
	 * @return array
	 */
	static public function IDs() {
		if (empty(self::$IDs))
			if (strlen(Request::param('multi-editor-ids')) > 0)
				self::$IDs = explode(',', Request::param('multi-editor-ids'));

		return self::$IDs;
	}

	/**
	 * Is the multi editor currently used?
	 * @return bool
	 */
	static public function isUsed() {
		return count(self::IDs()) > 0;
	}

	/**
	 * Get hidden input field
	 * @return string
	 */
	static public function hiddenInput() {
		return HTML::hiddenInput('multi-editor-ids', implode(',', self::IDs()));
	}
}