<?php
/**
 * This file contains the class of the RunalyzePluginPanel "Prognose".
 * @package Runalyze\Plugins\Panels
 */

use Runalyze\Configuration;
use Runalyze\Calculation\JD\VDOT;
use Runalyze\Activity\Duration;

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
	 * Name
	 * @return string
	 */
	final public function name() {
		return __('Prognosis');
	}

	/**
	 * Description
	 * @return string
	 */
	final public function description() {
		return __('Predict your race performance on various distances.');
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
	 * Init configuration
	 */
	protected function initConfiguration() {
		$Distances = new PluginConfigurationValueArray('distances', __('Distances to predict'));
		$Distances->setDefaultValue( array(1, 3, 5, 10, 21.1, 42.2) );

		$Model = new PluginConfigurationValueSelect('model', __('Prediction model'));
		$Model->setOptions( array(
			'jd'		=> 'Jack Daniels',
			'cpp'		=> 'Robert Bock (CPP)',
			'steffny'	=> 'Herbert Steffny',
			'cameron'	=> 'David Cameron'
		) );
		$Model->setDefaultValue('jd');

		$Configuration = new PluginConfiguration($this->id());
		$Configuration->addValue($Distances);
		$Configuration->addValue($Model);

		$this->setConfiguration($Configuration);
	}

	/**
	 * Method for getting the right symbol(s)
	 * @see PluginPanel::getRightSymbol()
	 */
	protected function getRightSymbol() {
		$Links = '';
		$Links .= '<li>'.Ajax::window('<a href="plugin/'.$this->key().'/window.plot.php" '.Ajax::tooltip('', __('Show prognosis trend'), true, true).'>'.Icon::$LINE_CHART.'</a>').'</li>';
		$Links .= '<li>'.Ajax::window('<a href="plugin/'.$this->key().'/window.php" '.Ajax::tooltip('', __('Prognosis calculator'), true, true).'>'.Icon::$CALCULATOR.'</a>').'</li>';

		return '<ul>'.$Links.'</ul>';
	}

	/**
	 * Display the content
	 * @see PluginPanel::displayContent()
	 */
	protected function displayContent() {
		$this->prepareForPrognosis();

		foreach ($this->getDistances() as $km) {
			$this->showPrognosis($km);
		}

		if ($this->thereAreNotEnoughCompetitions()) {
			echo HTML::info( __('There are not enough results for good predictions.') );
		}
	}

	/**
	 * Prepare calculations 
	 */
	protected function prepareForPrognosis() {
		switch ($this->Configuration()->value('model')) {
			case 'cpp':
				$this->PrognosisStrategy = new RunningPrognosisBock;
				break;

			case 'steffny':
				$this->PrognosisStrategy = new RunningPrognosisSteffny;
				break;

			case 'cameron':
				$this->PrognosisStrategy = new RunningPrognosisCameron;
				break;

			case 'jd':
			default:
				$this->PrognosisStrategy = new RunningPrognosisDaniels;
				break;
		}

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

		$oldTimeString  = Duration::format($PersonalBestInSeconds);
		$newTimeString  = '<strong>'.Duration::format($PrognosisInSeconds).'</strong>';
		$paceString     = SportSpeed::minPerKm($distance, $PrognosisInSeconds);
		$distanceString = Running::Km($distance, 0, ($distance <= 3));

		echo '
			<p>
				<span class="right">
					'.sprintf( __('<small>from</small> %s <small>to</small> %s'), $oldTimeString, $newTimeString ).'
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
		return !DB::getInstance()->query('SELECT `id` FROM `'.PREFIX.'training` WHERE `typeid`='.Configuration::General()->competitionType().' LIMIT 1')->fetchColumn();
	}

	/**
	 * Get string with distances for prognosis
	 * @return string
	 */
	public function getDistances() {
		return $this->Configuration()->value('distances');
	}
}