<?php
/**
 * This file contains class::TraningDataView
 * @package Runalyze\DataObjects\Training\View
 */
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
	 * Get distance as string
	 * @return string
	 */
	public function getDistanceString() {
		if ($this->Object->hasDistance())
			return Running::Km($this->Object->getDistance(), CONF_TRAINING_DECIMALS, $this->Object->isTrack());

		return '';
	}

	/**
	 * Get distance as string
	 * @return string
	 */
	public function getDistanceStringWithoutEmptyDecimals() {
		if ($this->hasDistance())
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
		return SportFactory::getSpeedWithAppendixAndTooltip($this->Object->getDistance(), $this->Object->getTimeInSeconds(), $this->Object->Sport()->id());
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
		return Trimp::coloredString($this->Object->getTrimp());
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

		return Ajax::tooltip($this->getElevation(), '&oslash; Steigung: '.$this->getGradientInPercent());
	}

	/**
	 * Get elevation
	 * @return string elevation with unit
	 */
	public function getElevation() {
		$preSign = (CONF_TRAINING_DO_ELEVATION && !$this->Object->elevationWasCorrected()) ? '~' : '';

		return $preSign.$this->Object->getElevation().'&nbsp;hm';
	}

	/**
	 * Get gradient
	 * @return string gradient in percent with percent sign
	 */
	public function getGradientInPercent() {
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
		if (!$this->hasPartner())
			return '';

		$links = array();
		$partners = explode(', ', $this->getPartner());
		foreach ($partners as $partner)
			$links[] = DataBrowserLinker::searchLink($partner, 'opt[partner]=is&val[partner]='.$partner);

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
			return Ajax::tooltip(Icon::$ABC, 'Lauf-ABC');

		return '';
	}

	/**
	 * Get (corrected) vdot and icon
	 * @return string
	 */
	public function getVDOTAndIcon() {
		return round($this->Object->getVdotCorrected(), 2).'&nbsp;'.$this->getVDOTicon();
	}

	/**
	 * Get icon with prognosis as title for VDOT-value
	 * @return string
	 */
	public function getVDOTicon() {
		return Icon::getVDOTicon($this->Object->getVdotCorrected(), $this->Object->usedForVdot());
	}

	/**
	 * Get pulse icon
	 * @return string Icon for heartrate, if average heartrate is set
	 */
	public function getPulseIcon() {
		if ($this->Object->getPulseAvg() > 0)
			return $this->getCheckedToggleIcon('pulse', 'Pulsdaten vorhanden');

		return '';
	}

	/**
	 * Get splits icon
	 * @return string Icon for splits, if splits are not empty
	 */
	public function getSplitsIcon() {
		if (!$this->Object->Splits()->areEmpty())
			return $this->getCheckedToggleIcon('splits', 'Zwischenzeiten vorhanden');

		return '';
	}

	/**
	 * Get map icon
	 * @return string Icon for map, if gps track is set
	 */
	public function getMapIcon() {
		if (!$this->Object->hasPositionData())
			return $this->getCheckedToggleIcon('map', 'Streckenverlauf vorhanden');

		return '';
	}

	/**
	 * Get checked toggle icon
	 * @param string $key
	 * @param string $tooltip
	 * @return string
	 */
	private function getCheckedToggleIcon($key, $tooltip) {
		return '<icon class="toggle-icon-'.$key.' checked" '.Ajax::tooltip('', $tooltip, false, true).'></i>';
	}
}