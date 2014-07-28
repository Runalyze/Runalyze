<?php
/**
 * This file contains the class of the RunalyzePluginPanel "Prognose".
 * @package Runalyze\Plugins\Panels
 */
$PLUGINKEY = 'RunalyzePluginPanel_Prognose';
/**
 * Class: RunalyzePluginPanel_Prognose
 * @author Hannes Christiansen
 * @package Runalyze\Plugins\Panels
 */
class RunalyzePluginPanel_Prognose extends PluginPanel {
	/**
	 * Prognosis
	 * @var RunningPrognosis
	 */
	protected $Prognosis = null;

	/**
	 * Prognosis strategy
	 * @var RunningPrognosisStrategy
	 */
	protected $PrognosisStrategy = null;

	/**
	 * Initialize this plugin
	 * @see PluginPanel::initPlugin()
	 */
	protected function initPlugin() {
		$this->name = __('Prognosis');
		$this->description = __('Predict your race performance on various distances.');
	}

	/**
	 * Display long description 
	 */
	protected function displayLongDescription() {
		echo HTML::p( __('There are different models that can be used to predict your race performances:') );
		echo HTML::fileBlock( '<strong>Jack Daniels (VDOT, \'Running formula\')</strong><br>'.
					__('Your current VDOT is estimated based on the ratio of heart rate and pace. '.
						'This value is equivalent to specific performances.') );
		echo HTML::fileBlock('<strong>Robert Bock (CPP, \'Competitive Performance Predictor\')</strong><br>'.
					__('Robert Bock uses an individual coefficient for your fatigue over time/distance. '.
						'This model uses your two best results.').'<br>'.
						'<small>see <a href="http://www.robert-bock.de/Sport_0/lauf_7/cpp/cpp.html">http://www.robert-bock.de/Sport_0/lauf_7/cpp/cpp.html</a></small>');
		echo HTML::fileBlock('<strong>Herbert Steffny (\'Das gro&szlig;e Laufbuch\')</strong><br>'.
					__('Herbert Steffny uses fixed factors to transform performances from one distance to another. '.
						'This model uses your best result.') );
		echo HTML::fileBlock('<strong>David Cameron</strong><br>'.
					__('David Cameron uses a fixed coefficient for the fatigue over time/distance and slightly different formulas than Robert Bock. '.
						'This model uses your best result.').'<br>'.
						'<small>see <a href="http://www.infobarrel.com/Runners_Math_How_to_Predict_Your_Race_Time">http://www.infobarrel.com/Runners_Math_How_to_Predict_Your_Race_Time</a></small>');
		echo HTML::info( __('The VDOT model is the only one which considers your current shape. '.
							'The other models are based on your previous race results.') );
	}

	/**
	 * Set default config-variables
	 * @see PluginPanel::getDefaultConfigVars()
	 */
	protected function getDefaultConfigVars() {
		$config = array();
		$config['distances']     = array('type' => 'array', 'var' => array(1, 3, 5, 10, 21.1, 42.2), 'description' => Ajax::tooltip(__('Distances to predict'), __('comma seperated')) );
		$config['model-jd']      = array('type' => 'bool', 'var' => true, 'description' => __('Model: Jack Daniels') );
		$config['model-cpp']     = array('type' => 'bool', 'var' => false, 'description' => __('Model: Robert Bock') );
		$config['model-steffny'] = array('type' => 'bool', 'var' => false, 'description' => __('Model: Herbert Steffny') );
		$config['model-cameron'] = array('type' => 'bool', 'var' => false, 'description' => __('Model: David Cameron') );

		return $config;
	}

	/**
	 * Method for getting the right symbol(s)
	 * @see PluginPanel::getRightSymbol()
	 */
	protected function getRightSymbol() {
		$Links = '';
		$Links .= '<li>'.Ajax::window('<a href="plugin/'.$this->key.'/window.plot.php" '.Ajax::tooltip('', __('Show prognosis trend'), true, true).'>'.Icon::$FATIGUE.'</a>').'</li>';
		$Links .= '<li>'.Ajax::window('<a href="plugin/'.$this->key.'/window.php" '.Ajax::tooltip('', __('Prognosis calculator'), true, true).'>'.Icon::$CALCULATOR.'</a>').'</li>';

		return '<ul>'.$Links.'</ul>';
	}

	/**
	 * Display the content
	 * @see PluginPanel::displayContent()
	 */
	protected function displayContent() {
		$this->prepareForPrognosis();

		foreach ($this->config['distances']['var'] as $km)
			$this->showPrognosis($km);

		if ($this->thereAreNotEnoughCompetitions())
			echo HTML::info( __('There are not enough results for good predictions.') );
	}

	/**
	 * Prepare calculations 
	 */
	protected function prepareForPrognosis() {
		if ($this->config['model-cpp']['var'])
			$this->PrognosisStrategy = new RunningPrognosisBock;
		elseif ($this->config['model-steffny']['var'])
			$this->PrognosisStrategy = new RunningPrognosisSteffny;
		elseif ($this->config['model-cameron']['var'])
			$this->PrognosisStrategy = new RunningPrognosisCameron;
		else
			$this->PrognosisStrategy = new RunningPrognosisDaniels;

		$this->PrognosisStrategy->setupFromDatabase();

		$this->Prognosis = new RunningPrognosis;
		$this->Prognosis->setStrategy($this->PrognosisStrategy);
	}

	/**
	 * Show prognosis for a given distance
	 * @param double $distance
	 */
	protected function showPrognosis($distance) {
		$PrognosisInSeconds    = $this->Prognosis->inSeconds($distance);
		$PersonalBestInSeconds = Running::PersonalBest($distance, true);
		$VDOTold               = round(JD::Competition2VDOT($distance, $PersonalBestInSeconds), 2);
		$VDOTnew               = round(JD::Competition2VDOT($distance, $PrognosisInSeconds), 2);

		$oldTimeString  = Time::toString($PersonalBestInSeconds);
		$newTimeString  = Time::toString($PrognosisInSeconds);
		$paceString     = SportSpeed::minPerKm($distance, $PrognosisInSeconds);
		$distanceString = Running::Km($distance, 0, ($distance <= 3));

		if (true || $PersonalBestInSeconds > $PrognosisInSeconds)
			$newTimeString = '<strong>'.$newTimeString.'</strong>';

		echo '
			<p>
				<span class="right">
					'.sprintf( __('<small>from</small> %s <small>to</small> %s'),
							Ajax::tooltip($oldTimeString, 'VDOT: '.$VDOTold),
							Ajax::tooltip($newTimeString, 'VDOT: '.$VDOTnew)).'
					<small>('.$paceString.'/km)</small>
				</span>
				<strong>'.$distanceString.'</strong>
			</p>';
	}

	/**
	 * Are there not enough competitions?
	 * @return bool
	 */
	protected function thereAreNotEnoughCompetitions() {
		return 1 >= DB::getInstance()->query('SELECT COUNT(*) FROM `'.PREFIX.'training` WHERE `typeid`='.CONF_WK_TYPID)->fetchColumn();
	}

	/**
	 * Get string with distances for prognosis
	 * @return string
	 */
	public function getDistances() {
		return $this->config['distances']['var'];
	}
}