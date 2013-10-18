<?php
/**
 * This file contains class::MultiEditor
 * @package Runalyze\Import
 */
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
			echo HTML::error('Dem Multi-Editor wurden keine Trainings-IDs &uuml;bergeben.');
			echo HTML::info('Falls du noch keine Trainings eingetragen hast, kannst du hier auch nichts bearbeiten.');
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
		$Code .= '<h1>Multi-Editor</h1>';
		$Code .= '<table class="multi-edit-table fullWidth"><tbody>';

		foreach (self::$IDs as $i => $ID) {
			$Training = new TrainingObject($ID);
			$Daytime = substr($Training->DataView()->getDaytimeString(), 0, -4);

			if (!empty($Daytime))
				$Daytime = ' - <small>'.$Daytime.'</small>';

			$Code .= '<tr id="multi-edit-'.$ID.'" class="link '.HTML::trClass($i).($i == 0 ? ' highlight' : '').' show-on-hover-parent">';
			$Code .= '<td class="multi-edit-sport-icon c">';
			$Code .= '<span class="link show-on-hover multi-edit-remove-link">'.Icon::$CROSS_SMALL.'</span>';
			$Code .= $Training->Sport()->IconWithTooltip();
			$Code .= '</td><td>';
			$Code .= $Training->DataView()->getDate(false).$Daytime.'<br />';
			$Code .= '<small>'.Time::toString($Training->getTimeInSeconds(), true, true);
			if ($Training->hasDistance())
				$Code .= ' - '.$Training->DataView()->getDistanceStringWithFullDecimals();
			$Code .= '</small>';
			$Code .= '</td>';
			$Code .= '<td class="multi-edit-icon">'.$Training->DataView()->getPulseIcon().'</td>';
			$Code .= '<td class="multi-edit-icon">'.$Training->DataView()->getSplitsIcon().'</td>';
			$Code .= '<td class="multi-edit-icon">'.$Training->DataView()->getMapIcon().'</td>';
			$Code .= '</tr>';
		}

		$Code .= '</tbody></table>';
		$Code .= '</div>';

		echo Ajax::wrapJS('$(\'#ajax-navigation\').remove();$(\'body\').append(\''.$Code.'\')');
		echo Ajax::wrapJSasFunction('$("#ajax-navigation tr.link").click(function(e){
	$("#ajax-navigation tr.link.highlight").removeClass("highlight").addClass("edited");
	$(this).removeClass("edited").addClass("highlight");
	Runalyze.loadOverlay( "'.TrainingLinker::$EDITOR_URL.'?id=" + $(this).attr("id").substr(11) );
});');
		echo Ajax::wrapJSasFunction('$("#ajax-navigation .multi-edit-remove-link").click(function(e){
	$(this).parent().parent().remove();
	e.stopPropagation();
});');
	}

	/**
	 * Display editor
	 * 
	 * This function will just load the standard editor in the overlay
	 */
	protected function displayEditor() {
		echo Ajax::wrapJS('Runalyze.loadOverlay(\''.TrainingLinker::$EDITOR_URL.'?id='.self::$IDs[0].'\');');
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