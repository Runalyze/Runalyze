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
	 * Link to editor
	 * @param int $id id of training
	 * @param string $text [optional] by default: Icon::$EDIT
	 * @param string $linkId [optional]
	 * @return string link to editor window
	 */
	static public function editLink($id, $text = '', $linkId = '') {
		if ($text == '')
			$text = Icon::$EDIT;

		if ($linkId != '')
			$linkId = ' id="'.$linkId.'"';

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
		$PrevTraining = Mysql::getInstance()->fetchSingle('SELECT id FROM '.PREFIX.'training WHERE id!='.$id.' AND time<="'.$timestamp.'" ORDER BY time DESC');

		if (isset($PrevTraining['id']))
			return self::editLink($PrevTraining['id'], Icon::$BACK, 'ajaxPrev');

		return '';
	}

	/**
	 * Get array for navigating for to next training in editor
	 * @param int $id
	 * @param int $timestamp
	 * @return string
	 */
	static public function editNextLink($id, $timestamp) {
		$NextTraining = Mysql::getInstance()->fetchSingle('SELECT id FROM '.PREFIX.'training WHERE id!='.$id.' AND time>="'.$timestamp.'" ORDER BY time ASC');

		if (isset($NextTraining['id']))
			return self::editLink($NextTraining['id'], Icon::$NEXT, 'ajaxNext');

		return '';
	}
}