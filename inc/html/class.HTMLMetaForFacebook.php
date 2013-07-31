<?php
/**
 * This file contains class::HTMLMetaForFacebook
 * @package Runalyze\HTML
 */
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
	static private $STEP_SIZE = 10;

	/**
	 * Training
	 * @var TrainingObject
	 */
	protected $Training = null;

	/**
	 * Properties
	 * @var array
	 */
	protected $Properties = array();

	/**
	 * Constructor
	 */
	public function __construct() {
		// TODO
		//if (System::isAtLocalhost())
		//	return;

		if ($this->canFindTrainingID()) {
			$this->setTraining();
			$this->checkTraining();
			$this->setProperties();
		}
	}

	/**
	 * Set training
	 */
	private function setTraining() {
		$this->Training = new TrainingObject( SharedLinker::getTrainingId() );
	}

	/**
	 * Can find training ID?
	 * @return boolean
	 */
	private function canFindTrainingID() {
		return Request::isOnSharedPage() && SharedLinker::getTrainingId() > 0;
	}

	/**
	 * Check training
	 */
	private function checkTraining() {
		if (!is_null($this->Training) && $this->Training->isDefaultId())
			$this->Training = null;

		if (!is_null($this->Training) && !$this->Training->isPublic())
			$this->Training = null;
	}

	/**
	 * Add property
	 * @param string $name
	 * @param string $content
	 */
	private function add($name, $content) {
		$this->Properties[$name] = $content;
	}

	/**
	 * Set properties
	 */
	private function setProperties() {
		$Exporter = new ExporterFacebook($this->Training);

		$this->add('fb:app_id', ExporterFacebook::$APP_ID);
		$this->add('og:type', 'fitness.course');
		$this->add('og:url', $this->Training->Linker()->publicUrl());
		$this->add('og:title', addslashes($Exporter->metaTitle()));
		$this->add('og:image', 'http://runalyze.de/wp-content/uploads/Account.png');

		$this->add('fitness:calories', $this->Training->getCalories());
		$this->add('fitness:distance:value', $this->Training->getDistance());
		$this->add('fitness:distance:units', 'km');
		$this->add('fitness:duration:value', $this->Training->getTimeInSeconds());
		$this->add('fitness:duration:units', 's');

		if ($this->Training->getDistance() > 0 && $this->Training->getTimeInSeconds() > 0) {
			$this->add('fitness:pace:value', $this->Training->getTimeInSeconds()/$this->Training->getDistance()/1000);
			$this->add('fitness:pace:units', 's/m');
			$this->add('fitness:speed:value', 1000/($this->Training->getTimeInSeconds()/$this->Training->getDistance()));
			$this->add('fitness:speed:units', 'm/s');
		}
	}

	/**
	 * Display
	 */
	public function display() {
		foreach ($this->Properties as $name => $content)
			echo '<meta property="'.$name.'" content="'.$content.'" />'.NL;

		if (!empty($this->Properties) && $this->Training->hasPositionData())
			echo '<link rel="opengraph" href="'.System::getFullDomain().'call/call.MetaCourse.php?id='.$this->Training->id().'" />';
	}

	/**
	 * Display course
	 */
	public function displayCourse() {
		$TrainingData   = Mysql::getInstance()->untouchedFetch('SELECT * FROM `'.PREFIX.'training` WHERE `id`="'.mysql_real_escape_string(Request::sendId()).'" LIMIT 1');
		$this->Training = new TrainingObject( $TrainingData );

		if ($this->Training->isDefaultId() || !$this->Training->isPublic())
			die('Don\'t do that!');

		echo '<meta property="og:type" content="metadata" />';
		echo '<link rel="origin" href="'.$this->Training->Linker()->publicUrl().'" />';

		$this->Training->GpsData()->startLoop();
		$this->Training->GpsData()->setStepSize(self::$STEP_SIZE);

		while ($this->Training->GpsData()->nextStep())
			$this->displayActivityDataPoint();
	}

	/**
	 * Display activity data point
	 */
	protected function displayActivityDataPoint() {
		echo '
<meta property="fitness:metrics:location:latitude"  content="'.$this->Training->GpsData()->getLatitude().'" />
<meta property="fitness:metrics:location:longitude" content="'.$this->Training->GpsData()->getLongitude().'" />
<meta property="fitness:metrics:location:altitude"  content="'.$this->Training->GpsData()->getElevation().'" />
<meta property="fitness:metrics:timestamp" content="'.date('Y-m-d\TH:i', ($this->Training->getTimestamp() + $this->Training->GpsData()->getTime())).'" />
<meta property="fitness:metrics:distance:value" content="'.$this->Training->GpsData()->getDistance().'" />
<meta property="fitness:metrics:distance:units" content="km" />
<meta property="fitness:metrics:pace:value" content="'.($this->Training->GpsData()->getPace()/1000).'" />
<meta property="fitness:metrics:pace:units" content="s/m" />
<meta property="fitness:metrics:speed:value" content="'.($this->Training->GpsData()->getPace() > 0 ? 1000/$this->Training->GpsData()->getPace() : 0).'" />
<meta property="fitness:metrics:speed:units" content="m/s" />';
	}
}