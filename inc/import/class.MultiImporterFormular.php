<?php
/**
 * This file contains class::MultiImporterFormular
 * @package Runalyze\Import
 */
/**
 * Formular to import multiple trainings
 *
 * @author Hannes Christiansen
 * @package Runalyze\Import
 */
class MultiImporterFormular extends Formular {
	/**
	 * Training objects
	 * @var array[TrainingObject]
	 */
	protected $TrainingObjects = array();

	/**
	 * Construct a new formular
	 * @param string $action
	 * @param string $method 
	 */
	public function __construct($action = '', $method = 'post') {
		parent::__construct($action, $method);

		$this->init();
	}

	/**
	 * Init
	 */
	protected function init() {
		$this->setHeader('Mehrere Trainings importieren');
		$this->setId('multi-importer');
		$this->addCSSclass('ajax');
		$this->addHiddenValue('multi-importer', 'true');
	}

	/**
	 * Set objects
	 * @param array[TrainingObject] $TrainingObjects
	 */
	public function setObjects(array $TrainingObjects) {
		$this->TrainingObjects = $TrainingObjects;

		$this->initForObjects();
	}

	/**
	 * Init for given objects
	 */
	protected function initForObjects() {
		$Fieldset = new FormularFieldset('Trainings ausw&auml;hlen');
		$Fieldset->addBlock( $this->getFieldsetBlock() );
		$Fieldset->setHtmlCode( $this->getConfigCode() );

		$this->addFieldset($Fieldset);
		$this->addSubmitButton('Auswahl importieren');
		$this->addHiddenValue('number-of-trainings', count($this->TrainingObjects));
	}

	/**
	 * Get fieldset block
	 * @return string
	 */
	private function getFieldsetBlock() {
		$String = '';

		$String .= HTML::info('Es wurden '.count($this->TrainingObjects).' Trainings gefunden.');
		$String .= '<table class="fullWidth multi-import-table c">';
		$String .= '<thead><tr><th>Importieren?</th><th>Datum</th><th>Dauer</th><th>Distanz</th><th colspan="4"></th></tr></thead>';
		$String .= '<tbody>';

		foreach ($this->TrainingObjects as $i => $TrainingObject)
			$String .= '<tr class="'.HTML::trClass($i).'" onclick="$(this).find(\'input:checkbox\').attr(\'checked\', !$(this).find(\'input:checkbox\').attr(\'checked\'));">'.$this->getTableRowFor($TrainingObject, $i).'</tr>';

		$String .= '</tbody>';
		$String .= '</table>';

		return $String;
	}

	/**
	 * Get table row for training
	 * @param TrainingObject $TrainingObject
	 * @param int $i
	 */
	private function getTableRowFor(TrainingObject &$TrainingObject, $i) {
		$TrainingObject->updateAfterParsing();

		$Data  = urlencode(serialize($TrainingObject->getArray()));

		$Inputs  = HTML::checkBox('training-import['.$i.']', true);
		$Inputs .= HTML::hiddenInput('training-data['.$i.']', $Data);

		$Row  = '<td>'.$Inputs.'</td>';
		$Row .= '<td>'.$TrainingObject->DataView()->getDate().'</td>';
		$Row .= '<td>'.Time::toString(round($TrainingObject->getTimeInSeconds()), true, true).'</td>';
		$Row .= '<td>'.$TrainingObject->DataView()->getDistanceStringWithFullDecimals().'</td>';
		$Row .= '<td>'.$TrainingObject->Sport()->IconWithTooltip().'</td>';
		$Row .= '<td>'.$TrainingObject->DataView()->getPulseIcon().'</td>';
		$Row .= '<td>'.$TrainingObject->DataView()->getSplitsIcon().'</td>';
		$Row .= '<td>'.$TrainingObject->DataView()->getMapIcon().'</td>';

		return $Row;
	}

	/**
	 * Get config code
	 * @return string
	 */
	private function getConfigCode() {
		$Input = new FormularCheckbox('multi-edit', 'Trainings anschlie&szlig;end bearbeiten', true);
		$Input->setLayout( FormularFieldset::$LAYOUT_FIELD_W100 );

		return $Input->getCode();
	}
}