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
		echo HTML::p('Bei Runalyze werden viele Tabellen und daraus abgeleitete Formeln von &quot;Jack Daniels - Die Laufformel&quot; verwendet.
					Unter anderem wird aus dem Verh&auml;ltnis von Herzfrequenz und Tempo auf die aktuelle Form geschlossen.');
		echo HTML::p('Mittels dieser kann f&uuml;r alle gew&uuml;nschten Distanzen eine Prognose berechnet werden.
					Sinnvolle Werte erh&auml;lt man vor allem f&uuml;r die Distanzen zwischen 3 und 42 km.');
		echo HTML::p('Alternativ kann die Prognose nach einem Modell von Robert Bock erstellt werden.<br />
					Weiteres dazu:
					<a href="http://www.robert-bock.de/Sport_0/lauf_7/cpp/cpp.html" title="Wettkampf Prognose Robert Bock">http://www.robert-bock.de/Sport_0/lauf_7/cpp/cpp.html</a>.');
	}

	/**
	 * Set default config-variables
	 * @see PluginPanel::getDefaultConfigVars()
	 */
	protected function getDefaultConfigVars() {
		$config = array();
		$config['distances']        = array('type' => 'array', 'var' => array(1, 3, 5, 10, 21.1, 42.2), 'description' => Ajax::tooltip('Distanzen f&uuml;r die Prognose', 'kommagetrennt'));
		$config['cpp']              = array('type' => 'bool', 'var' => false, 'description' => Ajax::tooltip('Prognose nach CPP', 'Anstelle der VDOT-basierten Prognose kann das CPP-Modell von Robert Bock verwendet werden.'));
		$config['cpp_min_distance'] = array('type' => 'int', 'var' => 3, 'description' => Ajax::tooltip('minimale Distanz f&uuml;r CPP', 'CPP berechnet einen Erm&uuml;dungsfaktor aus deinen zwei besten L&auml;ufen. Resultate auf sehr kurzen Distanzen k&ouml;nnen die Prognose daher stark ver&auml;ndern.'));

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
		if ($this->config['cpp']['var']) {
			$this->PrognosisStrategy = new RunningPrognosisBock;
			$this->PrognosisStrategy->setMinimalDistance( $this->config['cpp_min_distance']['var'] );
			$this->PrognosisStrategy->setupFromDatabase();
		} else {
			$this->PrognosisStrategy = new RunningPrognosisDaniels;
			$this->PrognosisStrategy->setupFromDatabase();
		}

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
	 * Get array with distances for prognosis
	 * @return array
	 */
	public function getDistances() {
		return $this->config['distances']['var'];
	}
}
?>