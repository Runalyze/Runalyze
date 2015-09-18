<?php
/**
 * This file contains class::Dataview
 * @package Runalyze\View\Activity
 */

namespace Runalyze\View\Activity;

use Runalyze\Configuration;
use Runalyze\Model\Activity;
use Runalyze\Model\Factory;
use Runalyze\Activity\Distance;
use Runalyze\Activity\Duration;
use Runalyze\Activity\HeartRate;
use Runalyze\Activity\Pace;
use Runalyze\Activity\StrideLength;
use Runalyze\Calculation\JD\VDOT;
use Runalyze\Calculation\JD\VDOTCorrector;
use Runalyze\View\Icon\VdotIcon;
use Runalyze\Context as GeneralContext;
use Runalyze\Util\Time;
use Runalyze\View\Stresscolor;

use SessionAccountHandler;
use SportFactory;
use SearchLink;
use Icon;
use Ajax;
use HTML;
use Cadence;
use CadenceRunning;
use Helper;

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
	 * Duration
	 * @var \Runalyze\Activity\Duration
	 */
	protected $Duration = null;

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
	 * VDOT
	 * @var \Runalyze\Calculation\JD\VDOT
	 */
	protected $VDOT = null;

	/**
	 * Cadence
	 * @var \Cadence
	 */
	protected $Cadence = null;

	/**
	 * Stride length
	 * @var \Runalyze\Activity\StrideLength
	 */
	protected $StrideLength = null;

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
			return $time;
			// TODO
			//return sprintf( __('%s&nbsp;Uhr'), $time);
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
	 * @return \Runalyze\Activity\Duration
	 */
	public function duration() {
		return $this->object($this->Duration, function($Activity){
			return new Duration($Activity->duration());
		});
	}

	/**
	 * Get elapsed time
	 * @return string
	 */
	public function elapsedTime() {
		if ($this->Activity->elapsedTime() < $this->Activity->duration())
			return '-:--:--';

		return Duration::format($this->Activity->elapsedTime());
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
			return Distance::format($this->Activity->distance(), $this->Activity->isTrack(), $decimals);
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

		return $this->duration()->string();
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
		return $this->object($this->Cadence, function($Activity){
			if ($Activity->sportid() == Configuration::General()->runningSport()) {
				return new CadenceRunning($Activity->cadence());
			}

			return new Cadence($Activity->cadence());
		});
	}

	/**
	 * Get stride length
	 * @return \Runalyze\Activity\StrideLength
	 */
	public function strideLength() {
		return $this->object($this->StrideLength, function($Activity){
			return new StrideLength($Activity->strideLength());
		});
	}

	/**
	 * Get total strokes
	 * @return \Runalyze\Activity\TotalStrokes
	 */
	public function totalStrokes() {
		return $this->object($this->totalStrokes());
	}

	/**
	 * Get swolf
	 * @return \Runalyze\Activity\Swolf
	 */
	public function swolf() {
		return $this->object($this->swolf());
	}

	/**
	 * Get string for displaying colored trimp
	 * @return string
	 */
	public function trimp() {
		$Stress = new Stresscolor($this->Activity->trimp());

		return $Stress->string();
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
		$Stress = new Stresscolor($this->Activity->jdIntensity());
		$Stress->scale(0, 50);

		return $Stress->string($this->Activity->jdIntensity());
	}
 
 	/**
	 * Get string for VDOT estimate
	 * @return string
	 */
	public function fitVdotEstimate() {
		if ($this->Activity->fitVdotEstimate() > 0) {
			return round($this->Activity->fitVdotEstimate());
		}

		return '';
	}
 
 	/**
	 * Get string for recovery time
	 * @return string
	 */
	public function fitRecoveryTime() {
		if ($this->Activity->fitRecoveryTime() > 0) {
			$hours = $this->Activity->fitRecoveryTime() / 60;

			if ($hours > 72) {
				return round($hours / 24).'d';
			}

			return round($hours).'h';
		}

		return '';
	}
 
 	/**
	 * Get string for hrv score
	 * @return string
	 */
	public function fitHRVscore() {
		if ($this->Activity->fitHRVscore() > 0) {
			$hue = 128 - 64*($this->Activity->fitHRVscore()/1000);
			$tooltip = Ajax::tooltip('', __('HRV score').': '.round($this->Activity->fitHRVscore()), false, true);

			return '<i class="fa fa-fw fa-dot-circle-o" style="color:hsl('.min(128, max(0, round($hue))).',74%,44%);" '.$tooltip.'></i>';
		}

		return '';
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
			return number_format($this->Activity->verticalOscillation()/10, 1).'&nbsp;cm';

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
			return new HeartRate($Activity->hrMax(), GeneralContext::Athlete());
		});
	}

	/**
	 * Average heart rate
	 * @return \Runalyze\Activity\HeartRate
	 */
	public function hrAvg() {
		return $this->object($this->HRavg, function($Activity){
			return new HeartRate($Activity->hrAvg(), GeneralContext::Athlete());
		});
	}

	/**
	 * Get elevation
	 * @return string elevation with unit
	 */
	public function elevation() {
		if ($this->Activity->elevation() > 0) {
			return $this->Activity->elevation().'&nbsp;hm';
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
	public function partner() {
		return HTML::encodeTags($this->Activity->partner()->asString());
	}

	/**
	 * Get trainingspartner as links
	 * @return string
	 */
	public function partnerAsLinks() {
		if (\Request::isOnSharedPage()) {
			return $this->partner();
		}

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
	 * VDOT
	 * @return \Runalyze\Calculation\JD\VDOT
	 */
	public function vdot() {
		$self = $this;

		return $this->object($this->VDOT, function($Activity) use($self){
			return new VDOT($self->usedVdot(), new VDOTCorrector);
		});
	}

	/**
	 * Value of used VDOT (uncorrected)
	 * @return float
	 */
	public function usedVdot() {
		if (Configuration::Vdot()->useElevationCorrection()) {
			if ($this->Activity->vdotWithElevation() > 0)  {
				return $this->Activity->vdotWithElevation();
			}
		}

		return $this->Activity->vdotByHeartRate();
	}

	/**
	 * VDOT icon
	 * @return string
	 */
	public function vdotIcon() {
		$value = $this->usedVdot() * Configuration::Data()->vdotFactor();

		if ($value > 0) {
			$Icon = new VdotIcon($value);

			if (!$this->Activity->usesVDOT()) {
				$Icon->setTransparent();
			}

			return $Icon->code();
		}

		return '';
	}
}