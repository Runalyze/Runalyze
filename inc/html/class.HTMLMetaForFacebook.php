<?php
/**
 * This file contains class::HTMLMetaForFacebook
 * @package Runalyze\HTML
 */

use Runalyze\View\Activity\Context;
use Runalyze\View\Activity\Linker;
use Runalyze\Model;
use Runalyze\Export\Share\Facebook;

/**
 * Meta-tag creator for Facebook
 * 
 * Facebook accepts additional meta tags for fitness activities.
 * This class can show them, if there is a training id on a shared page.
 * @see https://developers.facebook.com/docs/reference/opengraph/object-type/fitness.course
 *
 * @author Hannes Christiansen
 * @package Runalyze\HTML
 */
class HTMLMetaForFacebook {
	/**
	 * Step size for meta course
	 * @var int
	 */
	const STEP_SIZE = 10;

	/**
	 * @var \Runalyze\View\Activity\Context
	 */
	protected $Context = null;

	/**
	 * @var \Runalyze\Model\Route\Loop
	 */
	protected $RouteLoop;

	/**
	 * @var \Runalyze\Model\Trackdata\Loop
	 */
	protected $TrackdataLoop;

	/**
	 * Properties
	 * @var array
	 */
	protected $Properties = array();

	/**
	 * Constructor
	 */
	public function __construct(Context $context = null) {
		if (null !== $context) {
			$this->Context = $context;
		} elseif ($this->canFindActivityID()) {
			$this->setContext();
		}

		if ($this->activityIsValid()) {
			$this->setProperties();
		}
	}

	/**
	 * Set context
	 */
	private function setContext() {
		$this->Context = new Context(SharedLinker::getTrainingId(), SessionAccountHandler::getId());
	}

	/**
	 * Can find activity ID?
	 * @return boolean
	 */
	protected function canFindActivityID() {
		return Request::isOnSharedPage() && SharedLinker::getTrainingId() > 0;
	}

	/**
	 * Is valid?
	 * @return boolean
	 */
	protected function activityIsValid() {
		return (!is_null($this->Context) && $this->Context->activity()->duration() > 0 && $this->Context->activity()->isPublic());
	}

	/**
	 * Add property
	 * @param string $name
	 * @param string $content
	 */
	protected function add($name, $content) {
		$this->Properties[$name] = $content;
	}

	/**
	 * Set properties
	 */
	protected function setProperties() {
		$Exporter = new Facebook($this->Context);

		$Linker = new Linker($this->Context->activity());

		$this->add('fb:app_id', Facebook::$APP_ID);
		$this->add('og:type', 'fitness.course');
		$this->add('og:url', $Linker->publicUrl());
		$this->add('og:title', addslashes($Exporter->metaTitle()));
		$this->add('og:image', System::getFullDomain(true).'web/assets/images/runalyze.png');

		$this->add('fitness:calories', $this->Context->activity()->calories());
		$this->add('fitness:distance:value', $this->Context->activity()->distance());
		$this->add('fitness:distance:units', 'km');
		$this->add('fitness:duration:value', $this->Context->activity()->duration());
		$this->add('fitness:duration:units', 's');

		if ($this->Context->activity()->distance() > 0) {
			$this->add('fitness:pace:value', $this->Context->activity()->duration()/$this->Context->activity()->distance()/1000);
			$this->add('fitness:pace:units', 's/m');
			$this->add('fitness:speed:value', 1000/($this->Context->activity()->duration()/$this->Context->activity()->distance()));
			$this->add('fitness:speed:units', 'm/s');
		}
	}

	/**
	 * Display
	 */
	public function display() {
		foreach ($this->Properties as $name => $content) {
			echo '<meta property="'.$name.'" content="'.$content.'">'.NL;
		}

		if (!empty($this->Properties) && $this->Context->hasRoute() && $this->Context->route()->hasPositionData()) {
			echo '<link rel="opengraph" href="'.System::getFullDomain().'call/call.MetaCourse.php?id='.$this->Context->activity()->id().'&account='.SessionAccountHandler::getId().'">';
		}
	}

	/**
	 * Display course
	 */
	public function displayCourse() {
		$this->Context = new Context(Request::sendId(), Request::param('account'));

		if (!$this->activityIsValid() || !$this->Context->hasRoute() || !$this->Context->route()->hasPositionData()) {
			die('Don\'t do that!');
		}

		$Linker = new Linker($this->Context->activity());

		echo '<meta property="og:type" content="metadata">'.NL;
		echo '<link rel="origin" href="'.$Linker->publicUrl().'">'.NL;

		$this->RouteLoop = new Model\Route\Loop($this->Context->route());
		$this->RouteLoop->setStepSize(self::STEP_SIZE);

		$this->TrackdataLoop = new Model\Trackdata\Loop($this->Context->trackdata());
		$this->TrackdataLoop->setStepSize(self::STEP_SIZE);

		while ($this->RouteLoop->nextStep()) {
			$this->TrackdataLoop->nextStep();

			$this->displayActivityDataPoint();
		}
	}

	/**
	 * Display activity data point
	 */
	protected function displayActivityDataPoint() {
		// TODO: Elevation?
		//$elevation = ...
		$pace = $this->TrackdataLoop->average(Model\Trackdata\Entity::PACE);

		echo '
<meta property="fitness:metrics:location:latitude"  content="'.$this->RouteLoop->latitude().'">
<meta property="fitness:metrics:location:longitude" content="'.$this->RouteLoop->longitude().'">
<meta property="fitness:metrics:timestamp" content="'.date('Y-m-d\TH:i', ($this->Context->activity()->timestamp() + $this->TrackdataLoop->time())).'">
<meta property="fitness:metrics:distance:value" content="'.$this->TrackdataLoop->distance().'">
<meta property="fitness:metrics:distance:units" content="km">
<meta property="fitness:metrics:pace:value" content="'.($pace/1000).'">
<meta property="fitness:metrics:pace:units" content="s/m">
<meta property="fitness:metrics:speed:value" content="'.($pace > 0 ? 1000/$pace : 0).'">
<meta property="fitness:metrics:speed:units" content="m/s">';
	}
}
