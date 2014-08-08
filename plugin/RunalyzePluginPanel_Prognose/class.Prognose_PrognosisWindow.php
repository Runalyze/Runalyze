<?php
/**
 * This file contains the class Prognose_PrognosisWindow
 * @package Runalyze\Plugins\Panels
 */
/**
 * Prognosis calculator window
 * 
 * Additional window for calculating special prognoses.
 * @author Hannes Christiansen
 * @package Runalyze\Plugins\Panels
 */
class Prognose_PrognosisWindow {
	/**
	 * Formular
	 * @var Formular
	 */
	protected $Formular = null;

	/**
	 * Fieldset: Input
	 * @var FormularFieldset
	 */
	protected $FieldsetInput = null;

	/**
	 * Fieldset: Result
	 * @var FormularFieldset
	 */
	protected $FieldsetResult = null;

	/**
	 * Prognosis object
	 * @var RunningPrognosis
	 */
	protected $PrognosisObject = null;

	/**
	 * Prognosis strategies
	 * @var array
	 */
	protected $PrognosisStrategies = array();

	/**
	 * Distances
	 * @var array
	 */
	protected $Distances = array();

	/**
	 * Prognoses as array
	 * @var array
	 */
	protected $Prognoses = array();

	/**
	 * Result table
	 * @var string
	 */
	protected $ResultTable = '';

	/**
	 * Info lines
	 * @var array
	 */
	protected $InfoLines = array();

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->setDefaultValues();
		$this->readPostData();
		$this->runCalculations();
		$this->fillResultTable();
		$this->initFieldsets();
		$this->initFormular();
	}

	/**
	 * Set default values
	 */
	protected function setDefaultValues() {
		$Strategy = new RunningPrognosisBock;
		$TopResults = $Strategy->getTopResults(2);

		if (empty($_POST)) {
			$Factory = new PluginFactory();
			$Plugin = $Factory->newInstance('RunalyzePluginPanel_Prognose');

			$_POST['model'] = 'jack-daniels';
			$_POST['distances'] = implode(', ', $Plugin->getDistances());

			$_POST['vdot'] = JD::getConstVDOTform();
			$_POST['endurance'] = true;
			$_POST['endurance-value'] = BasicEndurance::getConst();

			$_POST['best-result-km'] = !empty($TopResults) ? $TopResults[0]['distance'] : '5.0';
			$_POST['best-result-time'] = !empty($TopResults) ? Time::toString($TopResults[0]['s'], false, true) : '0:26:00';
			$_POST['second-best-result-km'] = !empty($TopResults) ? $TopResults[1]['distance'] : '10.0';
			$_POST['second-best-result-time'] = !empty($TopResults) ? Time::toString($TopResults[1]['s'], false, true) : '1:00:00';
		}

		$this->InfoLines['jack-daniels']  = __('Your current VDOT:').' '.JD::getConstVDOTform().'. ';
		$this->InfoLines['jack-daniels'] .= __('Your current Basic Endurance:').' '.BasicEndurance::getConst().'.';

		$ResultLine = empty($TopResults) ? __('none') : sprintf( __('%s in %s and %s in %s'),
				Running::km($TopResults[0]['distance']), Time::toString($TopResults[0]['s']),
				Running::km($TopResults[1]['distance']), Time::toString($TopResults[1]['s'])
		);
		$this->InfoLines['robert-bock'] = __('Your two best results:').' '.$ResultLine;

		$this->setupJackDanielsStrategy();
		$this->setupBockStrategy();
		$this->setupSteffnyStrategy();
		$this->setupCameronStrategy();
	}

	/**
	 * Read post data
	 */
	protected function readPostData() {
		$this->PrognosisObject = new RunningPrognosis;
		$this->Distances = Helper::arrayTrim(explode(',', $_POST['distances']));

		$this->PrognosisObject->setStrategy( $this->PrognosisStrategies[$_POST['model']] );
	}

	/**
	 * Setup prognosis strategy: Jack Daniels
	 */
	protected function setupJackDanielsStrategy() {
		$Strategy = new RunningPrognosisDaniels;
		$Strategy->adjustVDOT( isset($_POST['endurance']) );
		$Strategy->setVDOT( (float)Helper::CommaToPoint($_POST['vdot']) );
		$Strategy->setBasicEnduranceForAdjustment( (int)$_POST['endurance-value'] );

		$this->PrognosisStrategies['jack-daniels'] = $Strategy;
	}

	/**
	 * Setup prognosis strategy: Robert Bock
	 */
	protected function setupBockStrategy() {
		$Strategy = new RunningPrognosisBock;
		$Strategy->setFromResults(
			$_POST['best-result-km'],
			Time::toSeconds($_POST['best-result-time']),
			$_POST['second-best-result-km'],
			Time::toSeconds($_POST['second-best-result-time'])
		);

		$this->PrognosisStrategies['robert-bock'] = $Strategy;
	}

	/**
	 * Setup prognosis strategy: Herbert Steffny
	 */
	protected function setupSteffnyStrategy() {
		$Strategy = new RunningPrognosisSteffny;
		$Strategy->setReferenceResult($_POST['best-result-km'], Time::toSeconds($_POST['best-result-time']));

		$this->PrognosisStrategies['herbert-steffny'] = $Strategy;
	}

	/**
	 * Setup prognosis strategy: David Cameron
	 */
	protected function setupCameronStrategy() {
		$Strategy = new RunningPrognosisCameron;
		$Strategy->setReferenceResult($_POST['best-result-km'], Time::toSeconds($_POST['best-result-time']));

		$this->PrognosisStrategies['david-cameron'] = $Strategy;
	}

	/**
	 * Init calculations
	 */
	protected function runCalculations() {
		$DateQuery = DB::getInstance()->prepare('SELECT `time` FROM `'.PREFIX.'training` WHERE `typeid`="'.CONF_WK_TYPID.'" AND `distance`=:distance ORDER BY `s` ASC LIMIT 1');
		foreach ($this->Distances as $km) {
			$PB         = Running::PersonalBest($km, true);
			$Prognosis  = $this->PrognosisObject->inSeconds( $km );

			if ($PB > 0) {
				$DateQuery->execute(array('distance' => $km));
				$PBdate = $DateQuery->fetch();
			}

			$this->Prognoses[] = array(
				'distance'	=> Running::Km($km, 1, $km <= 3),
				'prognosis'		=> Time::toString($Prognosis),
				'prognosis-pace'=> SportSpeed::minPerKm($km, $Prognosis).'/km',
				'prognosis-vdot'=> round(JD::Competition2VDOT($km, $Prognosis), 2),
				'diff'			=> $PB == 0 ? '-' : ($PB>$Prognosis?'+ ':'- ').Time::toString(abs(round($PB-$Prognosis)),false,true),
				'diff-class'	=> $PB > $Prognosis ? 'plus' : 'minus',
				'pb'			=> $PB > 0 ? Time::toString($PB) : '-',
				'pb-pace'		=> $PB > 0 ? SportSpeed::minPerKm($km, $PB).'/km' : '-',
				'pb-vdot'		=> $PB > 0 ? round(JD::Competition2VDOT($km, $PB),2) : '-',
				'pb-date'		=> $PB > 0 ? date('d.m.Y', $PBdate['time']) : '-'
			);
		}
	}

	/**
	 * Fill result table
	 */
	protected function fillResultTable() {
		$this->startResultTable();
		$this->fillResultTableWithResults();
		$this->finishResultTable();
	}

	/**
	 * Start result table
	 */
	protected function startResultTable() {
		$this->ResultTable = '<table class="fullwidth zebra-style"><thead><tr>
					<th>'.__('Distance').'</th>
					<th>'.__('Prognosis').'</th>
					<th class="small">'.__('Pace').'</th>
					<th class="small">'.__('VDOT').'</th>
					<th>'.__('Difference').'</th>
					<th>'.__('Personal best').'</th>
					<th class="small">'.__('Pace').'</th>
					<th class="small">'.__('VDOT').'</th>
					<th class="small">'.__('Date').'</th>
				</tr></thead><tbody>';
	}

	/**
	 * Fill result table with results
	 */
	protected function fillResultTableWithResults() {
		foreach ($this->Prognoses as $Prognosis) {
			$this->ResultTable .= '
				<tr class="r">
					<td class="c">'.$Prognosis['distance'].'</td>
					<td class="b">'.$Prognosis['prognosis'].'</td>
					<td class="small">'.$Prognosis['prognosis-pace'].'</td>
					<td class="small">'.$Prognosis['prognosis-vdot'].'</td>
					<td class="small '.$Prognosis['diff-class'].'">'.$Prognosis['diff'].'</td>
					<td class="b">'.$Prognosis['pb'].'</td>
					<td class="small">'.$Prognosis['pb-pace'].'</td>
					<td class="small">'.$Prognosis['pb-vdot'].'</td>
					<td class="small">'.$Prognosis['pb-date'].'</td>
				</tr>';
		}
	}

	/**
	 * Finish result table
	 */
	protected function finishResultTable() {
		$this->ResultTable .= '</tbody></table>';

		if ($_POST['model'] == 'robert-bock' && $this->PrognosisStrategies['robert-bock'] instanceof RunningPrognosisBock) {
			$K = $this->PrognosisStrategies['robert-bock']->getK();
			$e = $this->PrognosisStrategies['robert-bock']->getE();
			$this->ResultTable .= HTML::info( sprintf( __('The results give the constants K = %f and e = %f.'), $K, $e) ).'<br>';
		}
	}

	/**
	 * Init fields
	 */
	protected function initFieldsets() {
		$this->initFieldsetForInputData();
		$this->initFieldsetForResults();
	}

	/**
	 * Init fieldset for input data
	 */
	protected function initFieldsetForInputData() {
		$this->FieldsetInput = new FormularFieldset( __('Input') );

		foreach ($this->InfoLines as $InfoMessage)
			$this->FieldsetInput->addInfo($InfoMessage);

		$FieldModel = new FormularSelectBox('model', __('Model'));
		$FieldModel->addOption('jack-daniels', 'Jack Daniels (VDOT)');
		$FieldModel->addOption('robert-bock', 'Robert Bock (CPP)');
		$FieldModel->addOption('herbert-steffny', 'Herbert Steffny');
		$FieldModel->addOption('david-cameron', 'David Cameron');
		$FieldModel->addAttribute('onchange', '$(\'#prognosis-calculator .only-\'+$(this).val()).closest(\'div\').show();$(\'#prognosis-calculator .hide-on-model-change:not(.only-\'+$(this).val()+\')\').closest(\'div\').hide();');
		$FieldModel->setLayout( FormularFieldset::$LAYOUT_FIELD_W50_AS_W100 );

		$FieldDistances = new FormularInput('distances', __('Distances'));
		$FieldDistances->setLayout( FormularFieldset::$LAYOUT_FIELD_W50_AS_W100 );
		$FieldDistances->setSize( FormularInput::$SIZE_FULL_INLINE );

		$this->FieldsetInput->addField($FieldModel);
		$this->FieldsetInput->addField($FieldDistances);

		$this->addFieldsForJackDaniels();
		$this->addFieldsForBockAndSteffny();
	}

	/**
	 * Add fields for jack daniels
	 */
	protected function addFieldsForJackDaniels() {
		$FieldVdot = new FormularInput('vdot', __('New VDOT'));
		$FieldVdot->setLayout( FormularFieldset::$LAYOUT_FIELD_W50_AS_W100 );
		$FieldVdot->addCSSclass('hide-on-model-change');
		$FieldVdot->addCSSclass('only-jack-daniels');

		$FieldEndurance = new FormularCheckbox('endurance', __('Use Basic Endurance'));
		$FieldEndurance->setLayout( FormularFieldset::$LAYOUT_FIELD_W50 );
		$FieldEndurance->addCSSclass('hide-on-model-change');
		$FieldEndurance->addCSSclass('only-jack-daniels');

		$FieldEnduranceValue = new FormularInput('endurance-value', __('Basic Endurance'));
		$FieldEnduranceValue->setLayout( FormularFieldset::$LAYOUT_FIELD_W50 );
		$FieldEnduranceValue->addCSSclass('hide-on-model-change');
		$FieldEnduranceValue->addCSSclass('only-jack-daniels');
		$FieldEnduranceValue->setUnit( FormularUnit::$PERCENT );

		$this->FieldsetInput->addField($FieldVdot);
		$this->FieldsetInput->addField($FieldEnduranceValue);
		$this->FieldsetInput->addField($FieldEndurance);
	}

	/**
	 * Add fields for robert bock and herbert steffny
	 */
	protected function addFieldsForBockAndSteffny() {
		$BestResult = new FormularInput('best-result-km', __('Best result'));
		$BestResult->setLayout( FormularFieldset::$LAYOUT_FIELD_W50 );
		$BestResult->addCSSclass('hide-on-model-change');
		$BestResult->addCSSclass('only-robert-bock');
		$BestResult->addCSSclass('only-herbert-steffny');
		$BestResult->addCSSclass('only-david-cameron');
		$BestResult->setUnit( FormularUnit::$KM );

		$BestResultTime = new FormularInput('best-result-time', __('in'));
		$BestResultTime->setLayout( FormularFieldset::$LAYOUT_FIELD_W50 );
		$BestResultTime->addCSSclass('hide-on-model-change');
		$BestResultTime->addCSSclass('only-robert-bock');
		$BestResultTime->addCSSclass('only-herbert-steffny');
		$BestResultTime->addCSSclass('only-david-cameron');

		$SecondBestResult = new FormularInput('second-best-result-km', __('Second best result'));
		$SecondBestResult->setLayout( FormularFieldset::$LAYOUT_FIELD_W50 );
		$SecondBestResult->addCSSclass('hide-on-model-change');
		$SecondBestResult->addCSSclass('only-robert-bock');
		$SecondBestResult->setUnit( FormularUnit::$KM );

		$SecondBestResultTime = new FormularInput('second-best-result-time', __('in'));
		$SecondBestResultTime->setLayout( FormularFieldset::$LAYOUT_FIELD_W50 );
		$SecondBestResultTime->addCSSclass('hide-on-model-change');
		$SecondBestResultTime->addCSSclass('only-robert-bock');

		$this->FieldsetInput->addField($BestResult);
		$this->FieldsetInput->addField($BestResultTime);
		$this->FieldsetInput->addField($SecondBestResult);
		$this->FieldsetInput->addField($SecondBestResultTime);
	}

	/**
	 * Init fieldset for results
	 */
	protected function initFieldsetForResults() {
		$this->FieldsetResult = new FormularFieldset( __('Prognosis'));
		$this->FieldsetResult->addBlock( $this->ResultTable );
	}

	/**
	 * Init formular
	 */
	protected function initFormular() {
		$this->Formular = new Formular();
		$this->Formular->setId('prognosis-calculator');
		$this->Formular->addCSSclass('ajax');
		$this->Formular->addCSSclass('no-automatic-reload');
		$this->Formular->addFieldset( $this->FieldsetInput );
		$this->Formular->addFieldset( $this->FieldsetResult );
		$this->Formular->addSubmitButton( __('Show prognosis'));
	}

	/**
	 * Display
	 */
	public function display() {
		echo '<div class="panel-heading">';
		$this->displayHeading();
		echo '</div>';
		echo '<div class="panel-content">';
		$this->displayFormular();
		echo '</div>';
	}

	/**
	 * Display heading
	 */
	protected function displayHeading() {
		echo HTML::h1( __('Prognosis calculator') );
	}

	/**
	 * Display formular
	 */
	protected function displayFormular() {
		$this->Formular->display();

		echo Ajax::wrapJSasFunction('$(\'#prognosis-calculator .hide-on-model-change:not(.only-'.$_POST['model'].'\').closest(\'div\').hide();');
	}
}