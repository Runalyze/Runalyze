<?php
/**
 * This file contains class::Preview
 * @package Runalyze\View\Activity
 */

namespace Runalyze\View\Activity;

use Runalyze\Model\Activity;
use Runalyze\Context;
use Runalyze\View\Icon;

/**
 * Preview for data of activities
 * 
 * @author Hannes Christiansen
 * @package Runalyze\View\Activity
 */
class Preview {
	/**
	 * Activity
	 * @var \Runalyze\Model\Activity\Entity
	 */
	protected $Activity;

	/**
	 * Dataview
	 * @var \Runalyze\View\Activity\Dataview
	 */
	protected $Dataview;

	/**
	 * Sport
	 * @var \Runalyze\Model\Sport\Entity
	 */
	protected $Sport;

	/**
	 * Construct preview
	 * @param \Runalyze\Model\Activity\Entity $activity
	 */
	public function __construct(Activity\Entity $activity) {
		$this->Activity = $activity;
		$this->Dataview = new Dataview($activity);
		$this->Sport = Context::Factory()->sport($this->Activity->sportid());
	}

	/**
	 * Needed keys for activity objects
	 * @return array
	 */
	public static function keys() {
		return array(
			'id',
			Activity\Entity::TIMESTAMP,
			Activity\Entity::SPORTID,
			Activity\Entity::TIME_IN_SECONDS,
			Activity\Entity::DISTANCE,
			Activity\Entity::IS_TRACK,
			Activity\Entity::HR_AVG,
			Activity\Entity::SPLITS,
			Activity\Entity::ROUTEID
		);
	}

	/**
	 * @return string
	 */
	public function dateAndTime() {
		return $this->Dataview->date('d.m.Y H:i');
	}

	/**
	 * @return string
	 */
	public function dateAndSmallTime() {
		if ($this->Dataview->daytime() != '') {
			return $this->Dataview->date('d.m.Y - <\s\m\a\l\l>H:i</\s\m\a\l\l>');
		}

		return $this->Dataview->date();
	}

	/**
	 * @return string
	 */
	public function sportIcon() {
		$Icon = $this->Sport->icon();
		$Icon->setTooltip($this->Sport->name());

		return $Icon->code();
	}

	/**
	 * @return string
	 */
	public function durationAndDistance() {
		$Code = $this->Dataview->duration()->string();

		if ($this->Activity->distance() > 0) {
			$Code .= ' - '.$this->Dataview->distance();
		}

		return $Code;
	}

	/**
	 * @return string
	 */
	public function duration() {
		return $this->Dataview->duration()->string();
	}

	/**
	 * @return string
	 */
	public function distance() {
		if ($this->Activity->distance() > 0) {
			return $this->Dataview->distance();
		}

		return '';
	}

	/**
	 * @return string
	 */
	public function hrIcon() {
		if ($this->Activity->hrAvg() > 0) {
			$Icon = new Icon(Icon::HEART);
			$Icon->setTooltip(__('Heartrate data available'));

			return $Icon->code();
		}

		return '';
	}

	/**
	 * @return string
	 */
	public function splitsIcon() {
		if ($this->Activity->has(Activity\Entity::SPLITS)) {
			$Icon = new Icon(Icon::CLOCK);
			$Icon->setTooltip(__('Lap data available'));

			return $Icon->code();
		}

		return '';
	}

	/**
	 * @return string
	 */
	public function mapIcon() {
		if ($this->Activity->get(Activity\Entity::ROUTEID) > 0) {
			$Icon = new Icon(Icon::MAP_ARROW);
			$Icon->setTooltip(__('Route data available'));

			return $Icon->code();
		}

		return '';
	}
}