<?php
/**
 * This file contains class::MultiImporterFormular
 * @package Runalyze\Import
 */

use Runalyze\Model\Activity;
use Runalyze\View\Activity\Preview;

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
		$Fieldset = new FormularFieldset( __('Choose activities') );
		$Fieldset->addBlock( $this->getFieldsetBlock() );
		$Fieldset->setHtmlCode( $this->getConfigCode() );

		$this->addFieldset($Fieldset);
		$this->addSubmitButton( __('Import selected activities') );
		$this->addHiddenValue('number-of-trainings', count($this->TrainingObjects));
	}

	/**
	 * Get fieldset block
	 * @return string
	 */
	private function getFieldsetBlock() {
		$String = '';

		$String .= HTML::info( sprintf( __('Found %s activities.'), count($this->TrainingObjects)) );
		$String .= '<table class="fullwidth multi-import-table zebra-style c" id="multi-import-table">';
		$String .= '<thead><tr><th>'.__('Import').'</th><th>'.__('Date').'</th><th>'.__('Duration').'</th><th>'.__('Distance').'</th><th colspan="4"></th></tr></thead>';
		$String .= '<tbody>';

		foreach ($this->TrainingObjects as $i => $TrainingObject)
			$String .= '<tr>'.$this->getTableRowFor($TrainingObject, $i).'</tr>';

		$String .= '</tbody>';
		$String .= '</table>';

		$String .= Ajax::wrapJSforDocumentReady('
			$("#multi-import-table td").click(function(e){
				if ($(e.target).closest(\'input[type="checkbox"]\').length == 0)
					$(this).parent().find(\'input:checkbox\').attr(\'checked\', !$(this).parent().find(\'input:checkbox\').attr(\'checked\'));
			});
		');

		return $String;
	}

	/**
	 * Get table row for training
	 * @param TrainingObject $TrainingObject
	 * @param int $i
	 * @return string
	 */
	private function getTableRowFor(TrainingObject &$TrainingObject, $i) {
		$TrainingObject->updateAfterParsing();

		$Data  = urlencode(serialize($TrainingObject->getArray()));

		$Inputs  = HTML::checkBox('training-import['.$i.']', true);
		$Inputs .= HTML::hiddenInput('training-data['.$i.']', $Data);

		$Preview = new Preview(
			new Activity\Entity(array(
				Activity\Entity::TIMESTAMP => $TrainingObject->getTimestamp(),
				Activity\Entity::SPORTID => $TrainingObject->get('sportid'),
				Activity\Entity::TIME_IN_SECONDS => $TrainingObject->getTimeInSeconds(),
				Activity\Entity::DISTANCE => $TrainingObject->getDistance(),
				Activity\Entity::IS_TRACK => $TrainingObject->isTrack(),
				Activity\Entity::HR_AVG => $TrainingObject->getPulseAvg(),
				Activity\Entity::SPLITS => $TrainingObject->get('splits'),
				Activity\Entity::ROUTEID => $TrainingObject->hasPositionData()
			)
		));

		$Row  = '<td>'.$Inputs.'</td>';
		$Row .= '<td>'.$Preview->dateAndTime().'</td>';
		$Row .= '<td>'.$Preview->duration().'</td>';
		$Row .= '<td>'.$Preview->distance().'</td>';
		$Row .= '<td>'.$Preview->sportIcon().'</td>';
		$Row .= '<td>'.$Preview->hrIcon().'</td>';
		$Row .= '<td>'.$Preview->splitsIcon().'</td>';
		$Row .= '<td>'.$Preview->mapIcon().'</td>';

		return $Row;
	}

	/**
	 * Get config code
	 * @return string
	 */
	private function getConfigCode() {
		$Input = new FormularCheckbox('multi-edit', __('Show multi editor afterwards'), true);
		$Input->setLayout( FormularFieldset::$LAYOUT_FIELD_W100 );

		return $Input->getCode();
	}
}