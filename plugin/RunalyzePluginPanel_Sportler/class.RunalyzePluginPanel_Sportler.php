<?php
/**
 * This file contains the class of the RunalyzePluginPanel "Sportler".
 * @package Runalyze\Plugins\Panels
 */
use Runalyze\Activity\Weight;
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
		$this->removePanelContentPadding = true;
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
		$Links .= '<li>'.Ajax::window('<a href="my/body-values/add" '.Ajax::tooltip('', __('Add data'), true, true).'>'.Icon::$ADD.'</a>').'</li>';
		$Links .= '<li>'.Ajax::window('<a href="my/body-values/table" '.Ajax::tooltip('', __('Show table'), true, true).'>'.Icon::$TABLE.'</a>').'</li>';

		return '<ul>'.$Links.'</ul>';
	}

	/**
	 * Display the content
	 * @see PluginPanel::displayContent()
	 */
	protected function displayContent() {
			$this->displayContentInNewDesign();
	}

	/**
	 * Display the content (new design)
	 */
	protected function displayContentInNewDesign() {
		$Code = '';
		$userData = DB::getInstance()->query('SELECT `weight`, `pulse_rest`, `pulse_max`, `fat`, `water`, `muscles` FROM `'.PREFIX.'user` WHERE `accountid`="'.SessionAccountHandler::getId().'" ORDER BY `time` DESC LIMIT 1')->fetch();

		if (!is_array($userData)) {
		    $userData = ['weight' => 0.0, 'pulse_rest' => 0, 'pulse_max' => 0, 'fat' => 0.0, 'water' => 0.0, 'muscles' => 0.0];
        }

		if ($userData['pulse_max'] == 0){
			$topBox = new BoxedValue(__('Enter maximal HR'), '', __('Otherwise calculations will be wrong'));
			$topBox->addClass('colored-orange');
			$topBox->defineAsFloatingBlockWithFixedWidth(1);
			$Code .= $topBox->getCode();
		}

		$FirstValues = array();
		$SecondValues = array();

        $Weight = new Weight($userData['weight']);
		$FirstValues[] = new BoxedValue(Helper::Unknown($Weight->string(false), '-'), $Weight->unit(), __('Weight'));

		$FirstValues[] = new BoxedValue(Helper::Unknown($userData['pulse_rest'], '-'), 'bpm', __('Resting HR'));
        $FirstValues[] = new BoxedValue(Helper::Unknown($userData['pulse_max'], '-'), 'bpm', __('Maximal HR'));

		$NumberOfFirstValues = count($FirstValues);
		foreach ($FirstValues as &$Value) {
			$Value->defineAsFloatingBlock( ($NumberOfFirstValues == 2) ? "w50" : "w33");
			$Code .= $Value->getCode();
		}

		if (!empty($Code)) {
			$Code .= '<br>';
		}

        if ( $userData['fat'] ) {
            $SecondValues[] = new BoxedValue(Helper::Unknown($userData['fat'], '-'), '&#37;', __('Fat'));
        }
        if ( $userData['water'] ) {
            $SecondValues[] = new BoxedValue(Helper::Unknown($userData['water'], '-'), '&#37;', __('Water'));
        }
        if ( $userData['muscles'] ) {
            $SecondValues[] = new BoxedValue(Helper::Unknown($userData['muscles'], '-'), '&#37;', __('Muscles'));
        }

        if (count($SecondValues) > 0 ) {
            foreach ($SecondValues as &$Value) {
                $Value->defineAsFloatingBlock("w33");
                $Code .= $Value->getCode();
            }
        }


		if (!empty($Code)) {
			BoxedValue::wrapValues($Code);
		}

		$this->displayPlots();
	}

	/**
	 * Display plots
	 */
	protected function displayPlots() {
		echo '<div class="panel-content">';
        echo '<div class="flot-menu flot-menu-inline">';
        echo Ajax::flotChange(__('Weight'), 'sportler_flots', 'sportler_weights', true);
        echo Ajax::flotChange(__('Other values'), 'sportler_flots', 'sportler_analyse', false);
        echo '</div>';

		echo '<div id="sportler_flots" class="flot-changeable" style="position:relative;width:320px;height:150px;margin:0 auto;">
				<div class="flot '.Ajax::$IMG_WAIT.(false ? ' flot-hide' : '').'" id="sportler_weights" style="width:320px;height:150px;position:absolute;"></div>
				<div class="flot '.Ajax::$IMG_WAIT.(true ? ' flot-hide' : '').'" id="sportler_analyse" style="width:320px;height:150px;position:absolute;"></div>
			</div>';

		include FRONTEND_PATH.'../plugin/'.$this->key().'/Plot.gewicht.php';
		include FRONTEND_PATH.'../plugin/'.$this->key().'/Plot.analyse.php';

		echo '</div>';
	}

}
