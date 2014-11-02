<?php
/**
 * This file contains class::TraningDataView
 * @package Runalyze\DataObjects\Training\View
 */

use Runalyze\Configuration;

/**
 * Display training data
 *
 * @author Hannes Christiansen
 * @package Runalyze\DataObjects\Training\View
 */
class TrainingDataView {
	/**
	 * Training object
	 * @var \TrainingObject
	 */
	protected $Object = null;

	/**
	 * Constructor
	 * @param \TrainingObject $TrainingObject
	 */
	public function __construct(TrainingObject &$TrainingObject) {
		$this->Object = $TrainingObject;
	}

	/**
	 * Get the title for this training
	 * @return string
	 */
	public function getNameOfTypeOrSport() {
		if (!$this->Object->Type()->isUnknown())
			return $this->Object->Type()->name();

		return $this->Object->Sport()->name();
	}

	/**
	 * Get the date
	 * @param bool $withTime [optional] adding daytime to string
	 * @return string
	 */
	public function getDate($withTime = true) {
		$day = date('d.m.Y', $this->Object->getTimestamp());

		if ($withTime && strlen($this->getDaytimeString()) > 0)
			return $day.' '.$this->getDaytimeString();

		return $day;
	}

	/**
	 * Get title
	 * @return stirng
	 */
	public function getTitle() {
		if (!$this->Object->Type()->isUnknown())
			return $this->Object->Type()->name();

		return $this->Object->Sport()->name();
	}

	/**
	 * Get title with comment
	 * @return string
	 */
	public function getTitleWithComment() {
		if ($this->Object->hasComment())
			return $this->getTitle().': '.$this->Object->getComment();

		return $this->getTitle();
	}

	/**
	 * Get title with date
	 * @return string
	 */
	public function getTitleWithDate() {
		return $this->getTitle().', '.$this->getDate();
	}

	/**
	 * Get title with comment and date
	 * @return string
	 */
	public function getTitleWithCommentAndDate() {
		return $this->getTitleWithComment().', '.$this->getDate();
	}

	/**
	 * Get title for a training-plot
	 * @return string
	 */
	public function getTitleForPlot() {
		$text  = $this->getDate(false).', ';
		$text .= $this->getTitle();

		if ($this->Object->hasComment())
			$text .= ': '.$this->Object->getComment();

		return $text;
	}

	/**
	 * Get date as link to that week in DataBrowser
	 * @return string
	 */
	public function getDateAsWeeklink() {
		$time = $this->Object->getTimestamp();

		return DataBrowserLinker::link(date("d.m.Y", $time), Time::Weekstart($time), Time::Weekend($time));
	}

	/**
	 * Get weekday, date and daytime
	 * @return string
	 */
	public function getFullDateWithWeekLink() {
		$String  = $this->getWeekday();
		$String .= ', '.$this->getDateAsWeeklink();

		if (strlen($this->getDaytimeString()) > 0)
			$String .= ', '.$this->getDaytimeString();

		return $String;
	}

	/**
	 * Get date link for menu
	 * @return string
	 */
	public function getDateLinkForMenu() {
		return Ajax::tooltip($this->getDateAsWeeklink(), '<em>'.__('Show week').'</em><br>'.$this->getWeekday().' '.$this->getDate());
	}

	/**
	 * Get weekday
	 * @return string
	 */
	public function getWeekday() {
		return Time::Weekday( date('w', $this->Object->getTimestamp()) );
	}

	/**
	 * Get string for datetime
	 * @return string
	 */
	public function getDaytimeString() {
		return Time::daytimeString($this->Object->getTimestamp());
	}

	/**
	 * Get trainingtime as string
	 * @return string
	 */
	public function getTimeString() {
		return Time::toString($this->Object->getTimeInSeconds());
	}

	/**
	 * Get elapsed time as string
	 * @return string
	 */
	public function getElapsedTimeString() {
		if ($this->Object->getElapsedTime() < $this->Object->getTimeInSeconds())
			return '-:--:--';

		return Time::toString($this->Object->getElapsedTime());
	}

	/**
	 * Get distance as string
	 * @return string
	 */
	public function getDistanceString() {
		if ($this->Object->hasDistance())
			return Running::Km($this->Object->getDistance(), Configuration::ActivityView()->decimals(), $this->Object->isTrack());

		return '';
	}

	/**
	 * Get distance as string
	 * @return string
	 */
	public function getDistanceStringWithoutEmptyDecimals() {
		if ($this->Object->hasDistance())
			return Running::Km($this->Object->getDistance(), (round($this->Object->getDistance()) != $this->Object->getDistance() ? 1 : 0), $this->Object->isTrack());

		return '';
	}

	/**
	 * Get distance as string
	 * @return string
	 */
	public function getDistanceStringWithFullDecimals() {
		if ($this->Object->hasDistance())
			return Running::Km($this->Object->getDistance(), 2, $this->Object->isTrack());

		return '';
	}

	/**
	 * Get distance or time if distance is zero
	 * @return string
	 */
	public function getKmOrTime() {
		if (!$this->Object->hasDistance())
			return $this->getTimeString();

		return $this->getDistanceString();
	}

	/**
	 * Get a string for the speed depending on sportid
	 * @return string
	 */
	public function getSpeedString() {
		return $this->getSpeedStringForTime( $this->Object->getTimeInSeconds() );
	}

	/**
	 * Get a string for the speed depending on sportid
	 * @param int $timeInSeconds
	 * @return string
	 */
	public function getSpeedStringForTime($timeInSeconds) {
		return SportFactory::getSpeedWithAppendixAndTooltip($this->Object->getDistance(), $timeInSeconds, $this->Object->Sport()->id());
	}
	
	/**
	* Get pace as string without unit
	* @return string
	*/
	public function getPace() {
		return SportSpeed::minPerKm($this->Object->getDistance(), $this->Object->getTimeInSeconds());
	}
	
	/**
	* Get km/h as string without unit
	* @return string
	*/
	public function getKmh() {
		return SportSpeed::kmPerHour($this->Object->getDistance(), $this->Object->getTimeInSeconds());
	}

	/**
	 * Get string for displaying colored trimp
	 * @return string
	 */
	public function getTrimpString() {
		return Running::StresscoloredString($this->Object->getTrimp());
	}
 
 	/**
	 * Get string for displaying JD points
	 * @return string
	 */
	public function getJDintensity() {
		return $this->Object->getJDintensity();
	}
 
 	/**
	 * Get string for displaying JD points with stresscolor
	 * @return string
	 */
	public function getJDintensityWithStresscolor() {
		$Intensity = $this->Object->getJDintensity();

		return Running::StresscoloredString($Intensity/2, $Intensity);
	}

	/**
	 * Get cadence
	 * @return string cadence with unit
	 */
	public function getCadence() {
		if ($this->Object->getCadence() > 0)
			return $this->Object->Cadence()->asStringWithTooltip();

		return '';
	}

	/**
	 * Get power with tooltip
	 * @return string power as tooltip
	 */
	public function getPowerWithTooltip() {
		if ($this->Object->getPower() > 0)
			return Ajax::tooltip($this->getPower(), '&oslash; '.__('Power').': '.$this->getPower());

		return '';
	}

	/**
	 * Get power
	 * @return string power with unit
	 */
	public function getPower() {
		if ($this->Object->getPower() > 0)
			return $this->Object->getPower().'&nbsp;W';

		return '';
	}

	/**
	 * Get calories with unit
	 * @return string
	 */
	public function getCalories() {
		return Helper::Unknown($this->Object->getCalories()).'&nbsp;kcal';
	}

	/**
	 * Get average heartrate
	 * @return string
	 */
	public function getPulseAvg() {
		return Running::PulseString($this->Object->getPulseAvg(), $this->Object->getTimestamp());
	}

	/**
	 * Get average heartrate in bpm
	 * @return string
	 */
	public function getPulseAvgInBpm() {
		return Running::PulseStringInBpm($this->Object->getPulseAvg());
	}

	/**
	 * Get average heartrate in percent
	 * @return string
	 */
	public function getPulseAvgInPercent() {
		return Running::PulseStringInPercent($this->Object->getPulseAvg());
	}

	/**
	 * Get average heartrate in percent of HRmax
	 * @return string
	 */
	public function getPulseAvgInPercentHRmax() {
		return Running::PulseStringInPercentHRmax($this->Object->getPulseAvg());
	}

	/**
	 * Get average heartrate in bpm and percent
	 * @return string
	 */
	public function getPulseAvgAsBpmAndPercent() {
		return Running::PulseStringInBpm($this->Object->getPulseAvg()).' <small>('.Running::PulseStringInPercent($this->Object->getPulseAvg()).')</small>';
	}

	/**
	 * Get maximal heartrate
	 * @return string
	 */
	public function getPulseMax() {
		return Running::PulseString($this->Object->getPulseMax(), $this->Object->getTimestamp());
	}

	/**
	 * Get maximal heartrate in bpm
	 * @return string
	 */
	public function getPulseMaxInBpm() {
		return Running::PulseStringInBpm($this->Object->getPulseMax());
	}

	/**
	 * Get maximal heartrate in percent
	 * @return string
	 */
	public function getPulseMaxInPercent() {
		return Running::PulseStringInPercent($this->Object->getPulseMax());
	}

	/**
	 * Get maximal heartrate in percent of HRmax
	 * @return string
	 */
	public function getPulseMaxInPercentHRmax() {
		return Running::PulseStringInPercentHRmax($this->Object->getPulseMax());
	}

	/**
	 * Get maximal heartrate in bpm and percent
	 * @return string
	 */
	public function getPulseMaxAsBpmAndPercent() {
		return Running::PulseStringInBpm($this->Object->getPulseMax()).' <small>('.Running::PulseStringInPercent($this->Object->getPulseMax()).')</small>';
	}

	/**
	 * Get elevation with tooltip
	 * @return string elevation with gradient as tooltip
	 */
	public function getElevationWithTooltip() {
		if ($this->Object->getElevation() == 0)
			return '';

		return Ajax::tooltip($this->getElevation(), '&oslash; '.__('Gradient').': '.$this->getGradientInPercent());
	}

	/**
	 * Get elevation up and down
	 * @return string elevation with up and down
	 */
	public function getElevationUpAndDown() {
		if (!$this->Object->hasArrayAltitude())
			return '';

		$updown = $this->Object->GpsData()->getElevationUpDownOfStep(true);

		return '+'.$updown[0].'/-'.$updown[1].'&nbsp;m';
	}

	/**
	 * Get elevation
	 * @return string elevation with unit
	 */
	public function getElevation() {
		$preSign = (Configuration::ActivityForm()->correctElevation() && $this->Object->hasArrayAltitude() && !$this->Object->elevationWasCorrected()) ? '~' : '';

		return $preSign.$this->Object->getElevation().'&nbsp;hm';
	}

	/**
	 * Get gradient
	 * @return string gradient in percent with percent sign
	 */
	public function getGradientInPercent() {
		if ($this->Object->getDistance() == 0)
			return '-';

		return round($this->Object->getElevation() / $this->Object->getDistance()/10, 2).' &#37;';
	}

	/**
	 * Get trainingspartner
	 * @return string
	 */
	public function getPartner() {
		return HTML::encodeTags($this->Object->getPartner());
	}

	/**
	 * Get trainingspartner as links
	 * @return string
	 */
	public function getPartnerAsLinks() {
		if ($this->Object->getPartner() == '')
			return '';

		$links = array();
		$partners = explode(', ', $this->getPartner());
		foreach ($partners as $partner)
			$links[] = SearchLink::to('partner', $partner, $partner, 'like');

		return implode(', ', $links);
	}

	/**
	 * Get notes
	 * @return string
	 */
	public function getNotes() {
		return nl2br(HTML::encodeTags($this->Object->getNotes()));
	}

	/**
	 * Get icon for 'running abc'
	 * @return string
	 */
	public function getABCicon() {
		if ($this->Object->wasWithABC())
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

	/**
	 * Get pulse icon
	 * @return string Icon for heartrate, if average heartrate is set
	 */
	public function getPulseIcon() {
		if ($this->Object->getPulseAvg() > 0)
			return Ajax::tooltip(Icon::$HEART, __('Heartrate data available'));

		return '';
	}

	/**
	 * Get splits icon
	 * @return string Icon for splits, if splits are not empty
	 */
	public function getSplitsIcon() {
		if (!$this->Object->Splits()->areEmpty())
			return Ajax::tooltip(Icon::$CLOCK, __('Laps available'));

		return '';
	}

	/**
	 * Get map icon
	 * @return string Icon for map, if gps track is set
	 */
	public function getMapIcon() {
		if ($this->Object->hasPositionData())
			return Ajax::tooltip(Icon::$MAP, __('GPS course available'));

		return '';
	}
}