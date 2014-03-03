<?php
/**
 * This file contains the class of the RunalyzePluginPanel "Sportler".
 * @package Runalyze\Plugins\Panels
 */
$PLUGINKEY = 'RunalyzePluginPanel_Sportler';
/**
 * Class: RunalyzePluginPanel_Sportler
 * @author Hannes Christiansen
 * @package Runalyze\Plugins\Panels
 */
class RunalyzePluginPanel_Sportler extends PluginPanel {
	/**
	 * Initialize this plugin
	 * @see PluginPanel::initPlugin()
	 */
	protected function initPlugin() {
		$this->type = Plugin::$PANEL;
		$this->name = 'Sportler';
		$this->description = 'Anzeige der Sportlerdaten wie Gewicht und aktueller Ruhepuls (auch als Diagramm).';
		$this->dontReloadForTraining = true;

		if (!$this->config['use_old_design']['var'])
			$this->removePanelContentPadding = true;
	}

	/**
	 * Display long description 
	 */
	protected function displayLongDescription() {
		echo HTML::p('Das Gewicht ist oft eine Motivation f&uuml;r einen L&auml;ufer.
					Mit diesem Plugin ist das aktuelle Gewicht immer im Blick.
					Au&szlig;erdem k&ouml;nnen auch Ruhepuls, K&ouml;rperfett-, Wasser- und Muskelanteil protokolliert werden.');
	}

	/**
	 * Set default config-variables
	 * @see PluginPanel::getDefaultConfigVars()
	 */
	protected function getDefaultConfigVars() {
		$config = array();
		$config['use_old_design'] = array('type' => 'bool', 'var' => false, 'description' => 'Altes Design verwenden');
		$config['use_weight']     = array('type' => 'bool', 'var' => true, 'description' => 'Gewicht protokollieren');
		$config['use_body_fat']   = array('type' => 'bool', 'var' => true, 'description' => 'Fettanteil protokollieren');
		$config['use_pulse']      = array('type' => 'bool', 'var' => true, 'description' => 'Ruhepuls protokollieren');
		$config['wunschgewicht']  = array('type' => 'int', 'var' => 0, 'description' => 'Wunschgewicht');
		$config['plot_points']    = array('type' => 'int', 'var' => 20, 'description' => 'Diagramm: Datenpunkte');
		$config['plot_timerange'] = array('type' => 'int', 'var' => 0, 'description' => Ajax::tooltip('<small>oder</small> fester Zeitraum in Tagen', 'Gib einen Wert gr&ouml;&szlig;er 0 ein, um einen fixen Zeitraum anzuzeigen.'));

		return $config;
	}

	/**
	 * Method for getting the right symbol(s)
	 * @see PluginPanel::getRightSymbol()
	 */
	protected function getRightSymbol() {
		$Links = '';
		$Links .= '<li>'.Ajax::window('<a href="plugin/'.$this->key.'/window.sportler.php" '.Ajax::tooltip('', 'Daten hinzuf&uuml;gen', true, true).'>'.Icon::$ADD.'</a>').'</li>';
		$Links .= '<li>'.Ajax::window('<a href="plugin/'.$this->key.'/window.sportler.table.php" '.Ajax::tooltip('', 'Daten in Tabelle anzeigen', true, true).'>'.Icon::$TABLE.'</a>').'</li>';

		return '<ul>'.$Links.'</ul>';
	}

	/**
	 * Display the content
	 * @see PluginPanel::displayContent()
	 */
	protected function displayContent() {
		if ($this->config['use_old_design']['var'])
			$this->displayContentInOldDesign();
		else
			$this->displayContentInNewDesign();

		if (!$this->config['use_weight']['var'] && !$this->config['use_pulse']['var'] && !$this->config['use_body_fat']['var'])
			echo HTML::warning('Du musst in der Konfiguration festlegen, welche Werte du protokollieren m&ouml;chtest.');
	}

	/**
	 * Display the content (new design)
	 */
	protected function displayContentInNewDesign() {
		$Code = '';
		$UserData = new UserData( DataObject::$LAST_OBJECT );

		$FirstValues = array();
		$SecondValues = array();

		if ($this->config['use_weight']['var'])
			$FirstValues[] = new BoxedValue(Helper::Unknown($UserData->getWeight()), 'kg', 'Gewicht');

		if ($this->config['use_pulse']['var']) {
			$FirstValues[] = new BoxedValue(Helper::Unknown($UserData->getPulseRest()), 'bpm', 'Ruhepuls');
			$FirstValues[] = new BoxedValue(Helper::Unknown($UserData->getPulseMax()), 'bpm', 'Maximalpuls');
		}

		$NumberOfFirstValues = count($FirstValues);
		foreach ($FirstValues as &$Value) {
			$Value->defineAsFloatingBlock( ($NumberOfFirstValues == 2) ? "w50" : "w33");
			$Code .= $Value->getCode();
		}

		if (!empty($Code))
			$Code .= '<br />';

		if ($this->config['use_body_fat']['var']) {
			$SecondValues[] = new BoxedValue(Helper::Unknown($UserData->getBodyFat()), '&#37;', 'Fett');
			$SecondValues[] = new BoxedValue(Helper::Unknown($UserData->getWater()), '&#37;', 'Wasser');
			$SecondValues[] = new BoxedValue(Helper::Unknown($UserData->getMuscles()), '&#37;', 'Muskeln');
		}

		foreach ($SecondValues as &$Value) {
			$Value->defineAsFloatingBlock( "w33");
			$Code .= $Value->getCode();
		}

		if (!empty($Code))
			echo BoxedValue::wrapValues($Code);

		$this->displayPlots();
	}

	/**
	 * Display plots
	 */
	protected function displayPlots() {
		$AnalyseIsHidden = $this->config['use_weight']['var'] || $this->config['use_pulse']['var'];

		if (!$AnalyseIsHidden && !$this->config['use_body_fat']['var'])
			return;

		echo '<div class="panel-content">';

		if ($AnalyseIsHidden && $this->config['use_body_fat']['var']) {
			echo '<div class="flot-menu flot-menu-inline">';
			echo Ajax::flotChange('Gewicht anzeigen', 'sportler_flots', 'sportler_weights', $AnalyseIsHidden);
			echo Ajax::flotChange('K&ouml;rperdaten anzeigen', 'sportler_flots', 'sportler_analyse', !$AnalyseIsHidden);
			echo '</div>';
		}

		echo '<div id="sportler_flots" class="flot-changeable" style="position:relative;width:322px;height:150px;margin:2px auto;">
				<div class="flot '.Ajax::$IMG_WAIT.(!$AnalyseIsHidden ? ' flot-hide' : '').'" id="sportler_weights" style="width:320px;height:148px;position:absolute;"></div>
				<div class="flot '.Ajax::$IMG_WAIT.($AnalyseIsHidden ? ' flot-hide' : '').'" id="sportler_analyse" style="width:320px;height:148px;position:absolute;"></div>
			</div>';

		include FRONTEND_PATH.'../plugin/'.$this->key.'/Plot.gewicht.php';
		include FRONTEND_PATH.'../plugin/'.$this->key.'/Plot.analyse.php';

		echo '</div>';
	}

	/**
	 * Display the content (old design)
	 */
	protected function displayContentInOldDesign() {
		$Weight   = '';
		$Pulse    = '';
		$Analyse  = '';
		$UserData = new UserData( DataObject::$LAST_OBJECT );

		if ($this->config['use_weight']['var'])
			$Weight = 'Gewicht: <strong>'.Helper::Unknown($UserData->getWeight()).' kg</strong><br />';

		if ($this->config['use_pulse']['var'])
			$Pulse = Helper::Unknown($UserData->getPulseRest()).' bpm / '.Helper::Unknown($UserData->getPulseMax()).' bpm';
		else
			$Pulse = Helper::Unknown($UserData->getPulseMax()).' bpm';

		if ($this->config['use_body_fat']['var'])
			$Analyse = 'Fett: '.Helper::Unknown($UserData->getBodyFat()).' &#37;, Wasser: '.Helper::Unknown($UserData->getWater()).' &#37;, Muskeln: '.Helper::Unknown($UserData->getMuscles()).' &#37;';

		$AnalyseIsHidden = $this->config['use_weight']['var'] || $this->config['use_pulse']['var'];

		if (!$AnalyseIsHidden && !$this->config['use_body_fat']['var'])
			return;

		echo('
			<div id="sportler-content">
				<span class="right">'.$Pulse.'</span>
				'.Ajax::flotChange($Weight, 'sportler_flots', 'sportler_weights').'
				'.Ajax::flotChange($Analyse, 'sportler_flots', 'sportler_analyse', !$AnalyseIsHidden).'

				<div id="sportler_flots" class="flot-changeable" style="position:relative;width:322px;height:150px;margin:2px auto;">
					<div class="flot '.Ajax::$IMG_WAIT.'" id="sportler_weights" style="width:320px;height:148px;position:absolute;"></div>
					<div class="flot '.Ajax::$IMG_WAIT.($AnalyseIsHidden ? ' flot-hide' : '').'" id="sportler_analyse" style="width:320px;height:148px;position:absolute;"></div>
				</div>
			</div>');

		include FRONTEND_PATH.'../plugin/'.$this->key.'/Plot.gewicht.php';
		include FRONTEND_PATH.'../plugin/'.$this->key.'/Plot.analyse.php';
	}

	/**
	 * Table link
	 * @return string
	 */
	public function tableLink() {
		return Ajax::window('<a href="plugin/'.$this->get('key').'/window.sportler.table.php">'.Icon::$TABLE.' Alle Daten anzeigen</a>');
	}

	/**
	 * Add link
	 * @return string
	 */
	public function addLink() {
		return Ajax::window('<a href="plugin/'.$this->get('key').'/window.sportler.php">'.Icon::$ADD.' Einen neuen Eintrag hinzuf&uuml;gen</a>');
	}

	/**
	 * Get edit link for an entry
	 * @param int $id
	 * @return string
	 */
	static public function getEditLinkFor($id) {
		return Ajax::window('<a href="plugin/RunalyzePluginPanel_Sportler/window.sportler.php?id='.$id.'">'.Icon::$EDIT.'</a>');
	}

	/**
	 * Get delete link for an entry
	 * @param int $id
	 * @return string
	 */
	static public function getDeleteLinkFor($id) {
		return Ajax::window('<a href="plugin/RunalyzePluginPanel_Sportler/window.sportler.php?id='.$id.'&delete=true">'.Icon::$DELETE.'</a>');
	}
}