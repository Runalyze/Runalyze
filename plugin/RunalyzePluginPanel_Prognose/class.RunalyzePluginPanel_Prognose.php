<?php
/**
 * This file contains the class of the RunalyzePluginPanel "Prognose".
 * @package Runalyze\Plugins\Panels
 */

use Runalyze\Configuration;
use Runalyze\Activity\Distance;
use Runalyze\Activity\Duration;
use Runalyze\Activity\Pace;
use Runalyze\Activity\PersonalBest;
use Runalyze\Calculation\Prognosis;

$PLUGINKEY = 'RunalyzePluginPanel_Prognose';
/**
 * Class: RunalyzePluginPanel_Prognose
 * @author Hannes Christiansen
 * @package Runalyze\Plugins\Panels
 */
class RunalyzePluginPanel_Prognose extends PluginPanel {
	/**
	 * Prognosis
	 * @var \Runalyze\Sports\Running\Prognosis\PrognosisInterface
	 */
	protected $Prognosis = null;

	/**
	 * Number of successfully fetched PBs
	 * @var int
	 */
	protected $NumberOfPBs = 0;

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
		echo HTML::fileBlock( '<strong>VO2max</strong><br>'.
					__('Your current effective VO2max is estimated based on the ratio of heart rate and pace. '.
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
		echo HTML::info( __('The VO2max model is the only one which considers your current shape. '.
							'The other models are based on your previous race results.') );
	}

	/**
	 * Init configuration
	 */
	protected function initConfiguration() {
		$Distances = new PluginConfigurationValueDistances('distances', __('Distances to predict'), '', array(1, 3, 5, 10, 21.1, 42.2));

		$Model = new PluginConfigurationValueSelect('model', __('Prediction model'));
		$Model->setOptions( array(
			'vo2max'	=> __('Effective VO2max'),
			'cpp'		=> 'Robert Bock (CPP)',
			'steffny'	=> 'Herbert Steffny',
			'cameron'	=> 'David Cameron'
		) );
		$Model->setDefaultValue('vo2max');

		$BasicEndurance = new PluginConfigurationValueBool('use_be', __('Use marathon shape'), __('Use marathon shape factor to adapt prognosis for long distances (VO2max only).'));
		$BasicEndurance->setDefaultValue(true);

		$Configuration = new PluginConfiguration($this->id());
		$Configuration->addValue($Distances);
		$Configuration->addValue($Model);
		$Configuration->addValue($BasicEndurance);

		$this->setConfiguration($Configuration);
	}

	/**
	 * Method for getting the right symbol(s)
	 * @see PluginPanel::getRightSymbol()
	 */
	protected function getRightSymbol() {
		$Links = '';
        $Links .= '<li>'.Ajax::window('<a href="my/raceresult/performance-chart" '.Ajax::tooltip('', __('Race results').': '.__('Performance chart'), true, true).'><i class="fa fa-fw fa-dashboard"></i></a>').'</li>';
		$Links .= '<li>'.Ajax::window('<a href="plugin/'.$this->key().'/window.plot.php" '.Ajax::tooltip('', __('Show prognosis trend'), true, true).'>'.Icon::$LINE_CHART.'</a>').'</li>';
		$Links .= '<li>'.Ajax::window('<a href="plugin/'.$this->key().'/window.php" '.Ajax::tooltip('', __('Prognosis calculator'), true, true).'>'.Icon::$CALCULATOR.'</a>').'</li>';

		return '<ul>'.$Links.'</ul>';
	}

	/**
	 * Display the content
	 * @see PluginPanel::displayContent()
	 */
	protected function displayContent() {
		$this->lookupPersonalBests();
		$this->prepareForPrognosis();

		foreach ($this->getDistances() as $km) {
			$this->showPrognosis($km);
		}

		if (!$this->Prognosis->areValuesValid()) {
			echo HTML::warning(__('Prognoses can\'t be calculated.'));
		}

		if ($this->thereAreNotEnoughCompetitions()) {
			echo HTML::info(__('There are not enough results for good predictions.'));
		}
	}

	/**
	 * Lookup all personal bests at once
	 */
	protected function lookupPersonalBests() {
		PersonalBest::activateStaticCache();

		$this->NumberOfPBs = PersonalBest::lookupDistances($this->getDistances(), Configuration::General()->runningSport());
	}

    /**
     * @param int $num
     * @return array
     */
	protected function getTopResult($num = 1) {
	    return (new Prognosis\TopResults())->getTopResults($num);
    }

	/**
	 * Prepare calculations
	 */
	protected function prepareForPrognosis() {
		switch ($this->Configuration()->value('model')) {
			case 'cpp':
			    $topResults = $this->getTopResult(2);
			    $this->Prognosis = new \Runalyze\Sports\Running\Prognosis\Bock();

			    if (count($topResults) == 2) {
			        $this->Prognosis->setFromResults($topResults[0]['distance'], $topResults[0]['s'], $topResults[1]['distance'], $topResults[1]['s']);
                }

				break;

			case 'steffny':
                $topResults = $this->getTopResult(2);
			    $this->Prognosis = new \Runalyze\Sports\Running\Prognosis\Steffny();

                if (count($topResults) == 2) {
                    $this->Prognosis->setReferenceResult($topResults[0]['distance'], $topResults[0]['s']);
                }

				break;

			case 'cameron':
                $topResults = $this->getTopResult(2);
                $this->Prognosis = new \Runalyze\Sports\Running\Prognosis\Cameron();

                if (count($topResults) == 2) {
                    $this->Prognosis->setReferenceResult($topResults[0]['distance'], $topResults[0]['s']);
                }

                break;

			case 'vo2max':
			default:
			    $this->Prognosis = new \Runalyze\Sports\Running\Prognosis\VO2max(
                    Configuration::Data()->vo2max(),
                    $this->Configuration()->value('use_be'),
                    \Runalyze\Calculation\BasicEndurance::getConst()
                );

				break;
		}
	}

	/**
	 * Show prognosis for a given distance
	 * @param double $distance
	 */
	protected function showPrognosis($distance) {
		$PB = new PersonalBest($distance, Configuration::General()->runningSport());
		$PB->lookupWithDetails();
		$PBTime = $PB->exists() ? Duration::format( $PB->seconds() ) : '-';
		$PBString = $PB->exists() ? Ajax::trainingLink($PB->activityId(),$PBTime,true) : $PBTime;
		$Distance = new Distance($distance);

		if ($this->Prognosis->areValuesValid()) {
			$prognosis = new Duration($this->Prognosis->getSeconds($distance));
			$pace = new Pace($prognosis->seconds(), $distance, SportFactory::getSpeedUnitFor(Configuration::General()->runningSport()));
			$prognosisString = $prognosis->string(Duration::FORMAT_AUTO, 0);
		} else {
			$prognosisString = '-';
			$pace = new Pace(0, $distance, SportFactory::getSpeedUnitFor(Configuration::General()->runningSport()));
		}

		echo '<p>
				<span class="right">
					'.sprintf(__('<small>from</small> %s <small>to</small> <strong>%s</strong>'), $PBString, $prognosisString).'
					<small>('.$pace->valueWithAppendix().')</small>
				</span>
				<strong>'.$Distance->stringAuto(true, 1).'</strong>
			</p>';
	}

	/**
	 * Are there not enough competitions?
	 * @return bool
	 */
	protected function thereAreNotEnoughCompetitions() {
		return (0 == $this->NumberOfPBs);
	}

	/**
	 * @return array
	 */
	public function getDistances() {
		return array_filter($this->Configuration()->value('distances'), 'is_numeric');
	}
}
