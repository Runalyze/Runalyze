<?php
/**
 * This file contains class::Dataview
 * @package Runalyze\View\Activity
 */

namespace Runalyze\View\Activity;

use Runalyze\Configuration;
use Runalyze\Model\Activity;
use Runalyze\Model\Factory;
use Runalyze\Activity\HeartRate;
use Runalyze\Activity\Pace;
use Runalyze\Context;

use SessionAccountHandler;
use SportFactory;
use SearchLink;
use Running;
use Time;
use Icon;
use Ajax;
use HTML;
use Cadence;
use CadenceRunning;

/**
 * View for data of activities
 * 
 * @author Hannes Christiansen
 * @package Runalyze\View\Activity
 */
class Dataview {
	/**
	 * Activity
	 * @var \Runalyze\Model\Activity\Object
	 */
	protected $Activity;

	/**
	 * HR max
	 * @var \Runalyze\Activity\HeartRate
	 */
	protected $HRmax = null;

	/**
	 * HR average
	 * @var \Runalyze\Activity\HeartRate
	 */
	protected $HRavg = null;

	/**
	 * Pace
	 * @var \Runalyze\Activity\Pace
	 */
	protected $Pace = null;

	/**
	 * Construct data view
	 * @param \Runalyze\Model\Activity\Object $activity
	 */
	public function __construct(Activity\Object $activity) {
		$this->Activity = $activity;
	}

	/**
	 * Get or create object
	 * @param mixed $value
	 * @param \Closure $constructor
	 * @return mixed
	 */
	protected function object(&$value, \Closure $constructor) {
		if (is_null($value)) {
			$value = $constructor($this->Activity);
		}

		return $value;
	}

	/**
	 * Title for this training: type or sport name
	 * @return string
	 */
	public function titleByTypeOrSport() {
		$Factory = new Factory(SessionAccountHandler::getId());

		if ($this->Activity->typeid() != 0) {
			return $Factory->type($this->Activity->typeid())->name();
		}

		return $Factory->sport($this->Activity->sportid())->name();
	}

	/**
	 * Title with comment
	 * @return string
	 */
	public function titleWithComment() {
		if ($this->Activity->comment() != '') {
			return $this->titleByTypeOrSport().': '.$this->Activity->comment();
		}

		return $this->titleByTypeOrSport();
	}

	/**
	 * Date as string
	 * @param string $format [optional]
	 * @return string
	 */
	public function date($format = 'd.m.Y') {
		return date($format, $this->Activity->timestamp());
	}

	/**
	 * Daytime
	 * @param string $format
	 * @return string
	 */
	public function daytime() {
		$time = date('H:i', $this->Activity->timestamp());

		if ($time != '00:00') {
			return sprintf( __('%s Uhr'), $time);
		}

		return '';
	}

	/**
	 * Date and daytime
	 * @return string
	 */
	public function dateAndDaytime() {
		return $this->date().' '.$this->daytime();
	}

	/**
	 * Weekday
	 * @return string
	 */
	public function weekday() {
		return Time::Weekday( date('w', $this->Activity->timestamp()) );
	}

	/**
	 * Duration
	 * @return string
	 */
	public function duration() {
		return Time::toString($this->Activity->duration());
	}

	/**
	 * Get elapsed time
	 * @return string
	 */
	public function elapsedTime() {
		if ($this->Activity->elapsedTime() < $this->Activity->duration())
			return '-:--:--';

		return Time::toString($this->Activity->elapsedTime());
	}

	/**
	 * Get distance
	 * @return string
	 */
	public function distance($decimals = null) {
		if (is_null($decimals)) {
			$decimals = Configuration::ActivityView()->decimals();
		}

		if ($this->Activity->distance() > 0) {
			return Running::Km($this->Activity->distance(), $decimals, $this->Activity->isTrack());
		}

		return '';
	}

	/**
	 * Get distance without ",0"
	 * @return string
	 */
	public function distanceWithoutEmptyDecimals() {
		$distance = $this->Activity->distance();
		$decimals = ($distance == floor($distance)) ? 0 : null;

		return $this->distance($decimals);
	}

	/**
	 * Distance or duration
	 * @return string
	 */
	public function distanceOrDuration() {
		if ($this->Activity->distance() > 0) {
			return $this->distance();
		}

		return $this->duration();
	}

	/**
	 * Get a string for the speed depending on sportid
	 * @return \Runalyze\Activity\Pace
	 */
	public function pace() {
		return $this->object($this->Pace, function($Activity){
			return new Pace($Activity->duration(), $Activity->distance(), SportFactory::getSpeedUnitFor($Activity->sportid()));
		});
	}

	/**
	 * Get cadence
	 * @return \Cadence
	 */
	public function cadence() {
		$this->object($this->Cadence, function($Activity){
			if ($this->Activity->sportid() == Configuration::General()->runningSport()) {
				return new CadenceRunning($Activity->cadence());
			} else {
				return new Cadence($Activity->cadence());
			}
		});
	}

	/**
	 * Get string for displaying colored trimp
	 * @return string
	 */
	public function trimp() {
		return Running::StresscoloredString($this->Activity->trimp());
	}
 
 	/**
	 * Get string for displaying JD points
	 * @return string
	 */
	public function jdIntensity() {
		return $this->Activity->jdIntensity();
	}
 
 	/**
	 * Get string for displaying JD points with stresscolor
	 * @return string
	 */
	public function jdIntensityWithStresscolor() {
		return Running::StresscoloredString($this->Activity->jdIntensity()/2, $this->Activity->jdIntensity());
	}

	/**
	 * Get power
	 * @return string power with unit
	 */
	public function power() {
		if ($this->Activity->power() > 0)
			return $this->Activity->power().'&nbsp;W';

		return '';
	}

	/**
	 * Get ground contact
	 * @return string ground contact time with unit
	 */
	public function groundcontact() {
		if ($this->Activity->groundcontact() > 0)
			return round($this->Activity->groundcontact()).'&nbsp;ms';

		return '';
	}

	/**
	 * Get vertical oscillation
	 * @return string vertical oscillation with unit
	 */
	public function verticalOscillation() {
		if ($this->Activity->verticalOscillation() > 0)
			return round($this->Activity->verticalOscillation()/10, 1).'&nbsp;cm';

		return '';
	}

	/**
	 * Get calories with unit
	 * @return string
	 */
	public function calories() {
		return Helper::Unknown($this->Activity->calories()).'&nbsp;kcal';
	}

	/**
	 * Maximal heart rate
	 * @return \Runalyze\Activity\HeartRate
	 */
	public function hrMax() {
		return $this->object($this->HRmax, function($Activity){
			return new HeartRate($Activity->hrMax, Context::Athlete());
		});
	}

	/**
	 * Average heart rate
	 * @return \Runalyze\Activity\HeartRate
	 */
	public function hrAvg() {
		return $this->object($this->HRavg, function($Activity){
			return new HeartRate($Activity->hrAvg, Context::Athlete());
		});
	}

	/**
	 * Get elevation
	 * @return string elevation with unit
	 */
	public function elevation() {
		if ($this->Activity->elevation() > 0) {
			return $this->elevation().'&nbsp;hm';
		}

		return '';
	}

	/**
	 * Get gradient
	 * @return string gradient in percent with percent sign
	 */
	public function gradientInPercent() {
		if ($this->Activity->distance() == 0)
			return '-';

		return round($this->Activity->elevation() / $this->Activity->distance()/10, 2).'&nbsp;&#37;';
	}

	/**
	 * Get trainingspartner
	 * @return string
	 */
	public function getPartner() {
		return HTML::encodeTags($this->Activity->partner()->asString());
	}

	/**
	 * Get trainingspartner as links
	 * @return string
	 */
	public function getPartnerAsLinks() {
		$links = array();

		foreach ($this->Activity->partner()->asArray() as $partner) {
			$links[] = SearchLink::to('partner', $partner, $partner, 'like');
		}

		return implode(', ', $links);
	}

	/**
	 * Get notes
	 * @return string
	 */
	public function notes() {
		return nl2br(HTML::encodeTags($this->Activity->notes()));
	}

	/**
	 * Get icon for 'running abc'
	 * @return string
	 */
	public function abcIcon() {
		if ($this->Activity->isWithRunningDrills())
			return Ajax::tooltip(Icon::$ABC, __('Running drills'));

		return '';
	}

	/**
	 * Get (corrected) vdot and icon
	 * @return string
	 */
	public function getVDOTAndIcon() {
		return round($this->Object->getCurrentlyUsedVdot(), 2).'&nbsp;'.$this->getVDOTicon();
	}

	/**
	 * Get icon with prognosis as title for VDOT-value
	 * @return string
	 */
	public function getVDOTicon() {
		if ($this->Object->getVdotUncorrected() == 0)
			return '';

		$Icon = new Runalyze\View\Icon\VdotIcon($this->Object->getCurrentlyUsedVdot());

		if (!$this->Object->usedForVdot()) {
			$Icon->setTransparent();
		}

		return $Icon->code();
	}
}