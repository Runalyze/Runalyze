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
		$this->type = Plugin::$PANEL;
		$this->name = 'Prognose';
		$this->description = 'Anzeige der aktuellen Wettkampfprognose.';
	}

	/**
	 * Display long description 
	 */
	protected function displayLongDescription() {
		echo HTML::p('Was wirst du beim n&auml;chsten Wettkampf laufen k&ouml;nnen?
					Runalyze unterst&uuml;tzt verschiedene Prognose-Modelle.
					Sinnvolle Prognosen k&ouml;nnen vor allem f&uuml;r die Distanzen zwischen 3.000m und 42 km erstellt werden.');
		echo HTML::fileBlock('<strong>Jack Daniels (VDOT)</strong><br />
					Aus deinen Trainingsleistungen wird dein aktueller VDOT-Wert approximiert.
					Tabellen aus &bdquo;<em>Die Laufformel</em>&rdquo; von Jack Daniels liefern daf&uuml;r entsprechende Prognosen.');
		echo HTML::fileBlock('<strong>Robert Bock (CPP, &bdquo;Competitive Performance Predictor&rdquo;)</strong><br />
					Robert Bock hat ein Modell zur Prognose anhand eines Erm&uuml;dungskoeffizientens aufgestellt.
					Dieser wird aus deinen beiden besten Ergebnissen (bei Distanzen ab 3.000m) berechnet.<br />
					<small>siehe <a href="http://www.robert-bock.de/Sport_0/lauf_7/cpp/cpp.html" title="Wettkampf Prognose Robert Bock">http://www.robert-bock.de/Sport_0/lauf_7/cpp/cpp.html</a></small>');
		echo HTML::fileBlock('<strong>Herbert Steffny (&bdquo;simple Methode&rdquo;)</strong><br />
					Im Buch &bdquo;<em>Das gro&szlig;e Laufbuch</em>&rdquo; von Herbert Steffny tauchen simple Faktoren auf,
					um die Leistungen auf verschiedene Distanzen umzurechnen. Daf&uuml;r wird dein bisher bestes Ergebnis ber&uuml;cksichtigt.');
		echo HTML::info('Nur die Prognose nach Jack Daniels ber&uuml;cksichtigt deine aktuelle Form.
					Die anderen Prognosen basieren nur auf deinen Wettkampfergebnissen.');
	}

	/**
	 * Set default config-variables
	 * @see PluginPanel::getDefaultConfigVars()
	 */
	protected function getDefaultConfigVars() {
		$config = array();
		$config['distances']     = array('type' => 'array', 'var' => array(1, 3, 5, 10, 21.1, 42.2), 'description' => Ajax::tooltip('Distanzen f&uuml;r die Prognose', 'kommagetrennt'));
		$config['model-jd']      = array('type' => 'bool', 'var' => true, 'description' => 'Prognose-Modell: Jack Daniels');
		$config['model-cpp']     = array('type' => 'bool', 'var' => false, 'description' => 'Prognose-Modell: CPP');
		$config['model-steffny'] = array('type' => 'bool', 'var' => false, 'description' => 'Prognose-Modell: Herbert Steffny');

		return $config;
	}

	/**
	 * Method for getting the right symbol(s)
	 * @see PluginPanel::getRightSymbol()
	 */
	protected function getRightSymbol() {
		$Links = array();
		$Links[] = Ajax::window('<a href="plugin/'.$this->key.'/window.plot.php" '.Ajax::tooltip('', 'Prognose-Verlauf anzeigen', true, true).'>'.Icon::$FATIGUE.'</a>');
		$Links[] = Ajax::window('<a href="plugin/'.$this->key.'/window.php" '.Ajax::tooltip('', 'Prognose-Rechner', true, true).'>'.Icon::$CALCULATOR.'</a>');
		$Links[] = Ajax::window('<a href="plugin/'.$this->key.'/window.info.html" '.Ajax::tooltip('', 'Erl&auml;uterungen zu den Prognosen', true, true).'>'.Icon::$INFO.'</a>');

		return implode(NBSP, $Links);
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
			echo HTML::info('F&uuml;r gute Prognosen sind zu wenig Wettk&auml;mpfe da.');
	}

	/**
	 * Prepare calculations 
	 */
	protected function prepareForPrognosis() {
		if ($this->config['model-cpp']['var'])
			$this->PrognosisStrategy = new RunningPrognosisBock;
		elseif ($this->config['model-steffny']['var'])
			$this->PrognosisStrategy = new RunningPrognosisSteffny;
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

		if ($PersonalBestInSeconds > 0 && $PersonalBestInSeconds <= $PrognosisInSeconds) {
			$oldTag = 'strong';
			$newTag = 'del';
		} else {
			$oldTag = 'del';
			$newTag = 'strong';
		}

		$oldTimeString  = Time::toString($PersonalBestInSeconds);
		$newTimeString  = Time::toString($PrognosisInSeconds);
		$paceString     = SportSpeed::minPerKm($distance, $PrognosisInSeconds);
		$distanceString = Running::Km($distance, 0, ($distance <= 3));

		echo '
			<p>
				<span class="right">
					<small>von</small> '.Ajax::tooltip('<'.$oldTag.'>'.$oldTimeString.'</'.$oldTag.'>', 'VDOT: '.$VDOTold).'
					<small>auf</small> '.Ajax::tooltip('<'.$newTag.'>'.$newTimeString.'</'.$newTag.'>', 'VDOT: '.$VDOTnew).'
					<small>('.$paceString.'/km)</small>
				</span>
				<strong>'.$distanceString.'</strong>
			</p>'.NL;
	}

	/**
	 * Are there not enough competitions?
	 * @return bool
	 */
	protected function thereAreNotEnoughCompetitions() {
		return 1 >= Mysql::getInstance()->num('SELECT 1 FROM `'.PREFIX.'training` WHERE `typeid`='.CONF_WK_TYPID);
	}

	/**
	 * Get string with distances for prognosis
	 * @return string
	 */
	public function getDistances() {
		return $this->config['distances']['var'];
	}
}
?>