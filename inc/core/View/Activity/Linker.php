<?php
/**
 * This file contains class::Linker
 * @package Runalyze\View\Activity
 */

namespace Runalyze\View\Activity;

use Runalyze\Model\Activity;
use Runalyze\Activity\Duration;
use Runalyze\Util\Time;

use SessionAccountHandler;
use DataBrowserLinker;
use SharedLinker;
use System;
use Request;
use Icon;
use Ajax;
use DB;

/**
 * Linker for activities
 * 
 * @author Hannes Christiansen
 * @package Runalyze\View\Activity
 */
class Linker {
	/**
	 * Activity
	 * @var \Runalyze\Model\Activity\Entity
	 */
	protected $Activity;

	/**
	 * URL for editing trainings
	 * @var string
	 */
	const EDITOR_URL = 'call/call.Training.edit.php';

	/**
	 * URL to elevation info window
	 * @var string
	 */
	const ELEVATION_CORRECTION_URL = 'call/call.Training.elevationCorrection.php';

	/**
	 * URL to elevation info window
	 * @var string
	 */
	const ELEVATION_INFO_URL = 'call/call.Training.elevationInfo.php';

	/**
	 * URL to vdot info window
	 * @var string
	 */
	const VDOT_INFO_URL = 'call/call.Training.vdotInfo.php';

	/**
	 * URL to rounds info window
	 * @var string
	 */
	const ROUNDS_INFO_URL = 'call/call.Training.roundsInfo.php';

	/**
	 * Construct linker
	 * @param \Runalyze\Model\Activity\Entity $activity
	 */
	public function __construct(Activity\Entity $activity) {
		$this->Activity = $activity;
	}

	/**
	 * Get public url
	 * @return string
	 */
	public function publicUrl() {
		if ($this->Activity->isPublic()) {
			return System::getFullDomain().SharedLinker::getUrlFor($this->Activity->id());
		}

		return '';
	}

	/**
	 * Get edit url
	 * @return string
	 */
	public function editUrl() {
		return self::EDITOR_URL.'?id='.$this->Activity->id();
	}

	/**
	 * Get link
	 * @param string $name displayed link name
	 * @return string HTML-link to this training
	 */
	public function link($name) {
		return Ajax::trainingLink($this->Activity->id(), $name);
	}

	/**
	 * Get link with comment as text
	 * @return string HTML-link to this training
	 */
	public function linkWithComment() {
		if ($this->Activity->comment() != '') {
			return $this->link($this->Activity->comment());
		}

		return $this->link('<em>'.__('unknown').'</em>');
	}

	/**
	 * Get link with icon as text
	 * @param string $tooltipCssClass optional, e.g. 'atRight'
	 * @return string HTML-link to this training
	 */
	public function linkWithSportIcon($tooltipCssClass = '') {
		$Time = new Duration($this->Activity->duration());
		$Factory = new \Runalyze\Model\Factory(\SessionAccountHandler::getId());
		$Sport = $Factory->sport($this->Activity->sportid());
		$code = $Sport->icon()->code();

		$Tooltip = new \Runalyze\View\Tooltip($Sport->name().': '.$Time->string());
		$Tooltip->setPosition($tooltipCssClass);
		$Tooltip->wrapAround($code);

		return $this->link($code);
	}

	/**
	 * Week link
	 * @param string $name [optional]
	 * @return string
	 */
	public function weekLink($name = '') {
		if ($name == '') {
			$name = date('d.m.Y', $this->Activity->timestamp());
		}

		return DataBrowserLinker::link($name, Time::weekstart($this->Activity->timestamp()), Time::weekend($this->Activity->timestamp()));
	}

	/**
	 * Navigation for editor
	 * @return string
	 */
	public function editNavigation() {
		if (Request::param('mode') == 'multi') {
			return '';
		}

		return self::editPrevLink($this->Activity->id(), $this->Activity->timestamp()).
				self::editNextLink($this->Activity->id(), $this->Activity->timestamp());
	}

	/**
	 * URL to elevation correction
	 * @return string
	 */
	public function urlToElevationCorrection() {
		return self::ELEVATION_CORRECTION_URL.'?id='.$this->Activity->id();
	}

	/**
	 * URL to elevation info
	 * @param string $data
	 * @return string
	 */
	public function urlToElevationInfo($data = '') {
		return self::ELEVATION_INFO_URL.'?id='.$this->Activity->id().'&'.$data;
	}

	/**
	 * URL to vdot info
	 * @param string $data
	 * @return string
	 */
	public function urlToVDOTinfo($data = '') {
		return self::VDOT_INFO_URL.'?id='.$this->Activity->id().'&'.$data;
	}

	/**
	 * URL to rounds info
	 * @param string $data
	 * @return string
	 */
	public function urlToRoundsInfo($data = '') {
		return self::ROUNDS_INFO_URL.'?id='.$this->Activity->id().'&'.$data;
	}

	/**
	 * Link to editor
	 * @param int $id id of training
	 * @param string $text [optional] by default: Icon::$EDIT
	 * @param string $linkId [optional]
	 * @param string $linkClass [optional]
	 * @return string link to editor window
	 */
	public static function editLink($id, $text = '', $linkId = '', $linkClass = '') {
		if ($text == '')
			$text = Icon::$EDIT;

		if ($linkId != '')
			$linkId = ' id="'.$linkId.'"';

		if ($linkClass != '')
			$linkId .= ' class="'.$linkClass.'"';

		return Ajax::window('<a'.$linkId.' href="'.self::EDITOR_URL.'?id='.$id.'">'.$text.'</a>', 'small');
	}

	/**
	 * Small edit link
	 * @return string
	 */
	public function smallEditLink() {
		return self::editLink($this->Activity->id());
	}

	/**
	 * @param int $id activity id
	 * @param int $timestamp
	 * @return bool|int
	 */
	public static function prevId($id, $timestamp) {
		$PrevTraining = DB::getInstance()->query('SELECT `id` FROM `'.PREFIX.'training` WHERE ((`time`<"'.$timestamp.'" AND `id`!='.$id.') OR (`time`="'.$timestamp.'" AND `id`<'.$id.')) AND `accountid` = '.SessionAccountHandler::getId().' ORDER BY `time` DESC, `id` DESC LIMIT 1')->fetch();

		return (isset($PrevTraining['id'])) ? $PrevTraining['id'] : false;
	}

	/**
	 * @param int $id activity id
	 * @param int $timestamp
	 * @return bool|int
	 */
	public static function nextId($id, $timestamp) {
		$NextTraining = DB::getInstance()->query('SELECT `id` FROM `'.PREFIX.'training` WHERE ((`time`>"'.$timestamp.'" AND `id`!='.$id.') OR (`time`="'.$timestamp.'" AND `id`>'.$id.')) AND `accountid` = '.SessionAccountHandler::getId().' ORDER BY `time` ASC, `id` ASC LIMIT 1')->fetch();

		return (isset($NextTraining['id'])) ? $NextTraining['id'] : false;
	}

	/**
	 * Get array for navigating back to previous training in editor
	 * @param int $id
	 * @param int $timestamp
	 * @return string
	 * @codeCoverageIgnore
	 */
	public static function editPrevLink($id, $timestamp) {
		$prevId = self::prevId($id, $timestamp);

		if ($prevId !== false)
			return self::editLink($prevId, Icon::$BACK, 'ajax-prev', 'black-rounded-icon');

		return '';
	}

	/**
	 * Get array for navigating for to next training in editor
	 * @param int $id
	 * @param int $timestamp
	 * @return string
	 * @codeCoverageIgnore
	 */
	public static function editNextLink($id, $timestamp) {
		$nextId = self::nextId($id, $timestamp);

		if ($nextId !== false)
			return self::editLink($nextId, Icon::$NEXT, 'ajax-next', 'black-rounded-icon');

		return '';
	}
}
