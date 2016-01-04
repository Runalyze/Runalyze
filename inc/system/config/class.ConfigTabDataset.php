<?php
/**
 * This file contains class::ConfigTabDataset
 * @package Runalyze\System\Config
 */

use Runalyze\Configuration;
use Runalyze\Dataset;
use Runalyze\Model\Factory;

/**
 * ConfigTabDataset
 * @author Hannes Christiansen
 * @package Runalyze\System\Config
 */
class ConfigTabDataset extends ConfigTab {
	/** @var \Runalyze\Dataset\Configuration */
	protected $Configuration;

	/** @var bool */
	protected $ConfigurationIsNew = false;

	/** @var \Runalyze\Dataset\Context */
	protected $ExampleContext;

	/** @var \Runalyze\Model\Factory */
	protected $Factory;

	/**
	 * Construct config tab
	 */
	public function __construct() {
		parent::__construct();

		$this->loadConfiguration();
		$this->Factory = new Factory(SessionAccountHandler::getId());
		$this->ExampleContext = new Dataset\Context($this->getExampleTraining(), SessionAccountHandler::getId());
	}

	/**
	 * Load configuration
	 */
	protected function loadConfiguration() {
		$this->Configuration = new Dataset\Configuration(DB::getInstance(), SessionAccountHandler::getId(), false);

		if ($this->Configuration->isEmpty()) {
			$this->Configuration = new Dataset\DefaultConfiguration();
			$this->ConfigurationIsNew = true;
		} else {
			$this->ConfigurationIsNew = false;
		}
	}

	/**
	 * Set key and title for form 
	 */
	protected function setKeyAndTitle() {
		$this->key = 'config_tab_dataset';
		$this->title = __('Dataset');
	}

	/**
	 * Set all fieldsets and fields
	 */
	public function setFieldsetsAndFields() {
		$Dataset = new FormularFieldset(__('Your Dataset'));
		$Dataset->setHtmlCode($this->getCode());
		$Dataset->addInfo( __('You can specify which values show up in the overview of your activities.').'<br>'.
							__('This does not influence the detailed activity view, the form or any plugin.') );

		$this->Formular->addFieldset($Dataset);
	}

	/**
	 * Get code
	 * @return string 
	 */
	private function getCode() {
		$Code = '';
		$pos = 0;

		foreach ($this->Configuration->allKeys() as $keyid) {
			$Code .= $this->getCodeForKey($keyid, ++$pos);
		}

		if (!$this->ConfigurationIsNew) {
			foreach (Dataset\Keys::getEnum() as $keyid) {
				if (!$this->Configuration->exists($keyid)) {
					$Code .= $this->getCodeForKey($keyid, ++$pos, true);
				}
			}
		}

		return $this->getTableHeader().$Code.$this->getTableFooter();
	}

	/**
	 * @return string
	 */
	protected function getTableHeader() {
		return '<table class="c fullwidth zebra-style" id="conf-tab-dataset">
			<thead>
				<tr>
					<th>&nbsp;</th>
					<th>&nbsp;</th>
					<th>'.Ajax::tooltip(__('Display'), __('The information will be shown directly in the row.')).'</th>
					<th>'.Ajax::tooltip(__('Order'), __('Indicates the order of appearance.')).'</th>
					<th>'.Ajax::tooltip(__('CSS-Style'), __('any CSS-Code')).'</th>
					<th>'.__('Example').'</th>
				</tr>
			</thead>
			<tbody>';
	}

	/**
	 * @return string
	 */
	protected function getTableFooter() {
		return '</tbody></table>'.$this->getJS();
	}

	/**
	 * @param string $keyid
	 * @param int $pos
	 * @param bool $isNew flag to indicate that a key is new
	 * @return string
	 */
	protected function getCodeForKey($keyid, $pos, $isNew = false) {
		$KeyObject = Dataset\Keys::get($keyid);

		if ($KeyObject->description() != '') {
			$DescriptionIcon = new Runalyze\View\Icon(Runalyze\View\Icon::INFO.' '.Runalyze\View\Tooltip::POSITION_RIGHT);
			$DescriptionIcon->setTooltip($KeyObject->description());

			$Icon = $DescriptionIcon->code();
		} else {
			$Icon = '';
		}

		if ($isNew) {
			$newIndicator = '<sup class="colored-green">'.__('new').'</sup>';
		} else {
			$newIndicator = '';
		}

		return '<tr class="r" id="'.$keyid.'_tr">
				<td class="c">'.$Icon.'</td>
				<td class="l b">'.$KeyObject->label().$newIndicator.'</td>
				<td class="c">
					<input type="checkbox" name="'.$keyid.'_active"'.(!$isNew && $this->Configuration->isActive($keyid) ? ' checked' : '').($KeyObject->mustBeShown() ? ' disabled' : '').'>
				</td>
				<td class="c">
					<input class="dataset-position" type="text" name="'.$keyid.'_position" value="'.$pos.'" size="2">
					<span class="link" onclick="datasetMove('.$keyid.', \'up\')">'.Icon::$UP.'</span>
					<span class="link" onclick="datasetMove('.$keyid.', \'down\')">'.Icon::$DOWN.'</span>
				</td>
				<td class="c"><input type="text" name="'.$keyid.'_style" value="'.($isNew ? '' : $this->Configuration->getStyle($keyid)).'" size="15"></td>
				<td class="'.$KeyObject->cssClass().'" style="'.($isNew ? '' : $this->Configuration->getStyle($keyid)).'">'.$KeyObject->stringFor($this->ExampleContext).'</td>
			</tr>';
	}

	/**
	 * @return string
	 */
	protected function getJS() {
		return Ajax::wrapJS('
			function datasetMove(id, way) {
				var pos = parseInt($("input[name=\'"+id+"_position\']").val()),
					tr = $("#"+id+"_tr");

				if (way == "up" && pos > 1) {
					$("#"+id+"_tr .dataset-position").val(pos-1);
					tr.prev().find(".dataset-position").val(pos);
					tr.prev().toggleClass("swapped");
					tr.prev().before(tr);
				} else if (way == "down" && tr.next().find(".dataset-position").val() > 0) {
					$("#"+id+"_tr .dataset-position").val(pos+1);
					tr.next().find(".dataset-position").val(pos);
					tr.next().toggleClass("swapped");
					tr.next().after(tr);
				}

				tr.toggleClass("swapped");
			}
		');
	}

	/**
	 * Parse all post values 
	 */
	public function parsePostData() {
		$AccountID = SessionAccountHandler::getId();
		$UpdateStatement = DB::getInstance()->prepare(
			'UPDATE `'.PREFIX.'dataset` '.
			'SET `active`=:active, `style`=:style, `position`=:position '.
			'WHERE `accountid`=:accountid AND `keyid`=:keyid'
		);
		$InsertStatement = DB::getInstance()->prepare(
			'INSERT INTO `'.PREFIX.'dataset` '.
			'(`keyid`, `active`, `style`, `position`, `accountid`) '.
			'VALUES (:keyid, :active, :style, :position, :accountid)'
		);

		foreach (Dataset\Keys::getEnum() as $keyid) {
			$active = Dataset\Keys::get($keyid)->mustBeShown() || (isset($_POST[$keyid.'_active']) && $_POST[$keyid.'_active']);

			$data = array(
				':active' => $active ? 1 : 0,
				':style' => isset($_POST[$keyid.'_style']) ? htmlentities($_POST[$keyid.'_style']) : '',
				':position' => isset($_POST[$keyid.'_position']) ? (int)$_POST[$keyid.'_position'] : 99,
				':accountid' => $AccountID,
				':keyid' => $keyid
			);

			if (!$this->ConfigurationIsNew && $this->Configuration->exists($keyid)) {
				$UpdateStatement->execute($data);
			} else {
				$InsertStatement->execute($data);
			}
		}

		Ajax::setReloadFlag(Ajax::$RELOAD_DATABROWSER);
		Cache::delete(Dataset\Configuration::CACHE_KEY);

		$this->loadConfiguration();
	}

	/**
	 * Get array for exemplary training data
	 * @return array 
	 */
	protected function getExampleTraining() {
		return array(
			'id'		=> DataObject::$DEFAULT_ID,
			'sportid'	=> Configuration::General()->runningSport(),
			'typeid'	=> Configuration::General()->competitionType(),
			'time'		=> time(),
			'created'	=> time(),
			'edited'	=> time(),
			'is_public'	=> 1,
			'is_track'	=> 1,
			'distance'	=> 10,
			's'			=> 51*60+27,
			'elevation'	=> 57,
			'kcal'		=> 691,
			'pulse_avg'	=> 186,
			'pulse_max'	=> 193,
			'vdot_with_elevation'	=> Configuration::Data()->vdot() + 1,
			'vdot'		=> Configuration::Data()->vdot() + 2,
			'use_vdot'	=> 0,
			'fit_vdot_estimate'	=> round(Configuration::Data()->vdot()),
			'fit_recovery_time'	=> 800,
			'fit_hrv_analysis'	=> 800,
			'jd_intensity'	=> 27,
			'trimp'		=> 121,
			'cadence'	=> 90,
			'stride_length'	=> 108,
			'groundcontact'	=> 220,
			'vertical_oscillation'	=> 76,
			'power'		=> 520,
			'temperature'	=> 17,
			'weatherid'	=> 5,
			'splits'	=> '5|26:51-5|24:36',
			'comment'	=> str_replace(' ', '&nbsp;', __('Test activity')),
			'partner'	=> 'Peter',
			'notes'		=> str_replace(' ', '&nbsp;', __('Great run!')),
			'accountid'	=> SessionAccountHandler::getId(),
			'creator'	=> '',
			'creator_details'	=> '',
			'activity_id'	=> '',
			'elevation_corrected'	=> 1,
			'swolf'		=> 29,
			'total_strokes'	=> 1250,
			'vertical_ratio' => 79,
			'groundcontact_balance' => 4980,
			Dataset\Keys\Tags::CONCAT_TAGIDS_KEY => $this->exampleTagID(),
			Dataset\Keys\CompleteEquipment::CONCAT_EQUIPMENT_KEY => $this->exampleEquipmentIDs(2)
		);
	}

	/**
	 * @return string
	 */
	protected function exampleTagID() {
		$AllTags = $this->Factory->allTags();

		if (!empty($AllTags)) {
			return $AllTags[0]->id();
		}

		return '';
	}

	/**
	 * @param int $num
	 * @return string
	 */
	protected function exampleEquipmentIDs($num = 1) {
		$Sport = $this->Factory->sport(Configuration::General()->runningSport());

		if ($Sport->mainEquipmentTypeID() > 0) {
			$IDs = $this->Factory->equipmentForEquipmentType($Sport->mainEquipmentTypeID(), true);

			if (!empty($IDs)) {
				return $IDs[0];
			}
		}

		$AllEquipments = $this->Factory->allEquipments();

		if (!empty($AllEquipments)) {
			$max = min($num, count($AllEquipments));
			$ids = array();

			for ($i = 0; $i < $max; ++$i) {
				$ids[] = $AllEquipments[$i]->id();
			}

			return implode(',', $ids);
		}

		return '';
	}
}