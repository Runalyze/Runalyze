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
	 * Name
	 * @return string
	 */
	final public function name() {
		return __('Body values');
	}

	/**
	 * Description
	 * @return string
	 */
	final public function description() {
		return __('Show body values: weight, resting heart rate and values like fat-, water- and muscles-percentage.');
	}

	/**
	 * Initialize this plugin
	 * @see PluginPanel::initPlugin()
	 */
	protected function initPlugin() {
		$this->dontReloadForTraining = true;

		if (!$this->Configuration()->value('use_old_design')) {
			$this->removePanelContentPadding = true;
		}
	}

	/**
	 * Display long description
	 */
	protected function displayLongDescription() {
		echo HTML::p( __('Show body values: weight, resting heart rate and values like fat-, water- and muscles-percentage.') );
	}

	/**
	 * Init configuration
	 */
	protected function initConfiguration() {
		$Configuration = new PluginConfiguration($this->id());
		$Configuration->addValue( new PluginConfigurationValueBool('use_old_design', __('Use old design'), '', false) );
		$Configuration->addValue( new PluginConfigurationValueBool('use_weight', __('Record body weight'), '', true) );
		$Configuration->addValue( new PluginConfigurationValueBool('use_body_fat', __('Record body fat'), '', true) );
		$Configuration->addValue( new PluginConfigurationValueBool('use_pulse', __('Record resting heart rate'), '', true) );
		$Configuration->addValue( new PluginConfigurationValueInt('wunschgewicht', __('Desired body weight'), '', 0) );
		$Configuration->addValue( new PluginConfigurationValueInt('plot_points', __('Plot: number of points'), '', 20) );
		$Configuration->addValue( new PluginConfigurationValueInt('plot_timerange', __('<small>or</small> fixed number of days'), __('Enter a value &ge; 0 to show a fixed time range.'), 0) );

		$this->setConfiguration($Configuration);
	}

	/**
	 * Method for getting the right symbol(s)
	 * @see PluginPanel::getRightSymbol()
	 */
	protected function getRightSymbol() {
		$Links = '';
		$Links .= '<li>'.Ajax::window('<a href="plugin/'.$this->key().'/window.sportler.php" '.Ajax::tooltip('', __('Add data'), true, true).'>'.Icon::$ADD.'</a>').'</li>';
		$Links .= '<li>'.Ajax::window('<a href="plugin/'.$this->key().'/window.sportler.table.php" '.Ajax::tooltip('', __('Show table'), true, true).'>'.Icon::$TABLE.'</a>').'</li>';

		return '<ul>'.$Links.'</ul>';
	}

	/**
	 * Display the content
	 * @see PluginPanel::displayContent()
	 */
	protected function displayContent() {
		if ($this->Configuration()->value('use_old_design')) {
			$this->displayContentInOldDesign();
		} else {
			$this->displayContentInNewDesign();
		}

		if (!$this->Configuration()->value('use_weight') && !$this->Configuration()->value('use_pulse') && !$this->Configuration()->value('use_body_fat')) {
			echo HTML::warning( __('You have to specify which values to record. (see configuration)') );
		}
	}

	/**
	 * Display the content (new design)
	 */
	protected function displayContentInNewDesign() {
		$Code = '';
		$UserData = new UserData( DataObject::$LAST_OBJECT );

		if ($UserData->getPulseMax()==0){
			$topBox = new BoxedValue(__('Enter maximal HR'), '', __('Otherwise calculations will be wrong'));
			$topBox->addClass('colored-orange');
			$topBox->defineAsFloatingBlockWithFixedWidth(1);
			$Code .= $topBox->getCode();
		}

		$FirstValues = array();
		$SecondValues = array();

		if ($this->Configuration()->value('use_weight')) {
			$FirstValues[] = new BoxedValue(Helper::Unknown($UserData->getWeight()), 'kg', __('Weight'));
		}

		if ($this->Configuration()->value('use_pulse')) {
			$FirstValues[] = new BoxedValue(Helper::Unknown($UserData->getPulseRest()), 'bpm', __('Resting HR'));
			$FirstValues[] = new BoxedValue(Helper::Unknown($UserData->getPulseMax()), 'bpm', __('Maximal HR'));
		}

		$NumberOfFirstValues = count($FirstValues);
		foreach ($FirstValues as &$Value) {
			$Value->defineAsFloatingBlock( ($NumberOfFirstValues == 2) ? "w50" : "w33");
			$Code .= $Value->getCode();
		}

		if (!empty($Code)) {
			$Code .= '<br>';
		}

		if ($this->Configuration()->value('use_body_fat')) {
			$SecondValues[] = new BoxedValue(Helper::Unknown($UserData->getBodyFat()), '&#37;', __('Fat'));
			$SecondValues[] = new BoxedValue(Helper::Unknown($UserData->getWater()), '&#37;', __('Water'));
			$SecondValues[] = new BoxedValue(Helper::Unknown($UserData->getMuscles()), '&#37;', __('Muscles'));
		}

		foreach ($SecondValues as &$Value) {
			$Value->defineAsFloatingBlock( "w33");
			$Code .= $Value->getCode();
		}

		if (!empty($Code)) {
			echo BoxedValue::wrapValues($Code);
		}

		$this->displayPlots();
	}

	/**
	 * Display plots
	 */
	protected function displayPlots() {
		$AnalyseIsHidden = $this->Configuration()->value('use_weight') || $this->Configuration()->value('use_pulse');

		if (!$AnalyseIsHidden && !$this->Configuration()->value('use_body_fat')) {
			return;
		}

		echo '<div class="panel-content">';

		if ($AnalyseIsHidden && $this->Configuration()->value('use_body_fat')) {
			echo '<div class="flot-menu flot-menu-inline">';
			echo Ajax::flotChange(__('Weight'), 'sportler_flots', 'sportler_weights', $AnalyseIsHidden);
			echo Ajax::flotChange(__('Other values'), 'sportler_flots', 'sportler_analyse', !$AnalyseIsHidden);
			echo '</div>';
		}

		echo '<div id="sportler_flots" class="flot-changeable" style="position:relative;width:320px;height:150px;margin:0 auto;">
				<div class="flot '.Ajax::$IMG_WAIT.(!$AnalyseIsHidden ? ' flot-hide' : '').'" id="sportler_weights" style="width:320px;height:150px;position:absolute;"></div>
				<div class="flot '.Ajax::$IMG_WAIT.($AnalyseIsHidden ? ' flot-hide' : '').'" id="sportler_analyse" style="width:320px;height:150px;position:absolute;"></div>
			</div>';

		include FRONTEND_PATH.'../plugin/'.$this->key().'/Plot.gewicht.php';
		include FRONTEND_PATH.'../plugin/'.$this->key().'/Plot.analyse.php';

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

		if ($this->Configuration()->value('use_weight'))
			$Weight = __('Weight').': <strong>'.Helper::Unknown($UserData->getWeight()).' kg</strong><br>';

		if ($this->Configuration()->value('use_pulse'))
			$Pulse = Helper::Unknown($UserData->getPulseRest()).' bpm / '.Helper::Unknown($UserData->getPulseMax()).' bpm';
		else
			$Pulse = Helper::Unknown($UserData->getPulseMax()).' bpm';

		if ($this->Configuration()->value('use_body_fat'))
			$Analyse = __('Fat').': '.Helper::Unknown($UserData->getBodyFat()).' &#37;, '.__('Water').': '.Helper::Unknown($UserData->getWater()).' &#37;, '.__('Muscles').': '.Helper::Unknown($UserData->getMuscles()).' &#37;';

		$AnalyseIsHidden = $this->Configuration()->value('use_weight') || $this->Configuration()->value('use_pulse');

		if (!$AnalyseIsHidden && !$this->Configuration()->value('use_body_fat'))
			return;

		echo('
			<div id="sportler-content">
				<span class="right">'.$Pulse.'</span>
				'.Ajax::flotChange($Weight, 'sportler_flots', 'sportler_weights').'
				'.Ajax::flotChange($Analyse, 'sportler_flots', 'sportler_analyse', !$AnalyseIsHidden).'

				<div id="sportler_flots" class="flot-changeable" style="position:relative;width:320px;height:150px;margin:0 auto;">
					<div class="flot '.Ajax::$IMG_WAIT.'" id="sportler_weights" style="width:320px;height:150px;position:absolute;"></div>
					<div class="flot '.Ajax::$IMG_WAIT.($AnalyseIsHidden ? ' flot-hide' : '').'" id="sportler_analyse" style="width:320px;height:150px;position:absolute;"></div>
				</div>
			</div>');

		include FRONTEND_PATH.'../plugin/'.$this->key().'/Plot.gewicht.php';
		include FRONTEND_PATH.'../plugin/'.$this->key().'/Plot.analyse.php';
	}

	/**
	 * Table link
	 * @return string
	 */
	public function tableLink() {
		return Ajax::window('<a href="plugin/'.$this->key().'/window.sportler.table.php">'.Icon::$TABLE.' '.__('Show table').'</a>');
	}

	/**
	 * Add link
	 * @return string
	 */
	public function addLink() {
		return Ajax::window('<a href="plugin/'.$this->key().'/window.sportler.php">'.Icon::$ADD.' '.__('Add a new entry').'</a>');
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
