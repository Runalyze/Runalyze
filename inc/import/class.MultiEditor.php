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
		$Code .= '<table class="fullWidth c"><tbody>';

		foreach (self::$IDs as $i => $ID) {
			$Training = new TrainingObject($ID);

			$Code .= '<tr class="link '.HTML::trClass($i).'" onclick="Runalyze.loadOverlay(\\\''.TrainingLinker::$EDITOR_URL.'?id='.$ID.'\\\')">';
			$Code .= '<td style="padding-top:7px;">';
			$Code .= $Training->Sport()->IconWithTooltip();
			$Code .= '</td><td>';
			$Code .= $Training->DataView()->getDate().'<br />';
			$Code .= '<small>'.Time::toString($Training->getTimeInSeconds(), true, true);
			if ($Training->hasDistance())
				$Code .= ', '.$Training->DataView()->getDistanceStringWithFullDecimals();
			$Code .= '</small>';
			$Code .= '</tr>';
		}

		$Code .= '</tbody></table>';
		$Code .= '</div>';

		echo Ajax::wrapJS('$(\'body\').append(\''.$Code.'\')');
	}

	/**
	 * Display editor
	 */
	protected function displayEditor() {
		$_POST = array();

		$Training = new TrainingObject( self::$IDs[0] );

		$Formular = new TrainingFormular($Training, StandardFormular::$SUBMIT_MODE_EDIT);
		$Formular->setId('training');
		$Formular->setHeader( $Training->DataView()->getTitleWithCommentAndDate() );
		$Formular->setLayoutForFields( FormularFieldset::$LAYOUT_FIELD_W50 );
		$Formular->display();
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