<?php
/**
 * This file contains class::TrainingLinker
 * @package Runalyze\DataObjects\Training\View
 */
/**
 * Creating links for a given training
 * 
 * @author Hannes Christiansen
 * @package Runalyze\DataObjects\Training\View
 */
class TrainingLinker {
	/**
	 * Training object
	 * @var \TrainingObject
	 */
	protected $Object = null;

	/**
	 * URL for editing trainings
	 * @var string
	 */
	static public $EDITOR_URL = 'call/call.Training.edit.php';

	/**
	 * URL to elevation info window
	 * @var string
	 */
	static public $ELEVATION_CORRECTION_URL = 'call/call.Training.elevationCorrection.php';

	/**
	 * URL to elevation info window
	 * @var string
	 */
	static public $ELEVATION_INFO_URL = 'call/call.Training.elevationInfo.php';

	/**
	 * URL to vdot info window
	 * @var string
	 */
	static public $VDOT_INFO_URL = 'call/call.Training.vdotInfo.php';

	/**
	 * URL to rounds info window
	 * @var string
	 */
	static public $ROUNDS_INFO_URL = 'call/call.Training.roundsInfo.php';

	/**
	 * Constructor
	 * @param \TrainingObject $TrainingObject
	 */
	public function __construct(TrainingObject &$TrainingObject) {
		$this->Object = $TrainingObject;
	}

	/**
	 * Get public url
	 * @return string
	 */
	public function publicUrl() {
		if ($this->Object->isPublic())
			return System::getFullDomain().SharedLinker::getUrlFor($this->Object->id());

		return '';
	}

	/**
	 * Get edit url
	 * @return string
	 */
	public function editUrl() {
		return self::$EDITOR_URL.'?id='.$this->Object->id();
	}

	/**
	 * Get link
	 * @param string $name displayed link name
	 * @return string HTML-link to this training
	 */
	public function link($name) {
		return Ajax::trainingLink($this->Object->id(), $name);
	}

	/**
	 * Get link with comment as text
	 * @return string HTML-link to this training
	 */
	public function linkWithComment() {
		if ($this->Object->hasComment())
			return $this->link($this->Object->getComment());

		return $this->link('<em>unbekannt</em>');
	}

	/**
	 * Get link with icon as text
	 * @return string HTML-link to this training
	 */
	public function linkWithSportIcon() {
		return $this->link( $this->Object->Sport()->Icon($this->tooltipForSport()) );
	}

	/**
	 * Tooltip for sport link
	 * @return string
	 */
	private function tooltipForSport() {
		return $this->Object->Sport()->name().': '.Time::toString( $this->Object->getTimeInSeconds() );
	}

	/**
	 * Navigation for editor
	 * @return string
	 */
	public function editNavigation() {
		return self::editPrevLink($this->Object->id(), $this->Object->getTimestamp()).
				self::editNextLink($this->Object->id(), $this->Object->getTimestamp());
	}

	/**
	 * URL to elevation correction
	 * @return string
	 */
	public function urlToElevationCorrection() {
		return self::$ELEVATION_CORRECTION_URL.'?id='.$this->Object->id();
	}

	/**
	 * URL to elevation info
	 * @param string $data
	 * @return string
	 */
	public function urlToElevationInfo($data = '') {
		return self::$ELEVATION_INFO_URL.'?id='.$this->Object->id().'&'.$data;
	}

	/**
	 * URL to vdot info
	 * @param string $data
	 * @return string
	 */
	public function urlToVDOTinfo($data = '') {
		return self::$VDOT_INFO_URL.'?id='.$this->Object->id().'&'.$data;
	}

	/**
	 * URL to rounds info
	 * @param string $data
	 * @return string
	 */
	public function urlToRoundsInfo($data = '') {
		return self::$ROUNDS_INFO_URL.'?id='.$this->Object->id().'&'.$data;
	}

	/**
	 * Link to editor
	 * @param int $id id of training
	 * @param string $text [optional] by default: Icon::$EDIT
	 * @param string $linkId [optional]
	 * @param string $linkClass [optional]
	 * @return string link to editor window
	 */
	static public function editLink($id, $text = '', $linkId = '', $linkClass = '') {
		if ($text == '')
			$text = Icon::$EDIT;

		if ($linkId != '')
			$linkId = ' id="'.$linkId.'"';

		if ($linkClass != '')
			$linkId .= ' class="'.$linkClass.'"';

		return Ajax::window('<a'.$linkId.' href="'.self::$EDITOR_URL.'?id='.$id.'">'.$text.'</a>', 'small');
	}

	/**
	 * Small edit link
	 * @return string
	 */
	public function smallEditLink() {
		return self::editLink($this->Object->id());
	}

	/**
	 * Get array for navigating back to previous training in editor
	 * @param int $id
	 * @param int $timestamp
	 * @return string
	 */
	static public function editPrevLink($id, $timestamp) {
		$PrevTraining = DB::getInstance()->query('SELECT id FROM '.PREFIX.'training WHERE (time<"'.$timestamp.'" AND id!='.$id.') OR (time="'.$timestamp.'" AND id<'.$id.') ORDER BY time DESC LIMIT 1')->fetch();

		if (isset($PrevTraining['id']))
			return self::editLink($PrevTraining['id'], Icon::$BACK, 'ajax-prev', 'black-rounded-icon');

		return '';
	}

	/**
	 * Get array for navigating for to next training in editor
	 * @param int $id
	 * @param int $timestamp
	 * @return string
	 */
	static public function editNextLink($id, $timestamp) {
		$NextTraining = DB::getInstance()->query('SELECT id FROM '.PREFIX.'training WHERE (time>"'.$timestamp.'" AND id!='.$id.') OR (time="'.$timestamp.'" AND id>'.$id.') ORDER BY time ASC LIMIT 1')->fetch();

		if (isset($NextTraining['id']))
			return self::editLink($NextTraining['id'], Icon::$NEXT, 'ajax-next', 'black-rounded-icon');

		return '';
	}
}