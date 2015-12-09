<?php
/**
 * This file contains the class::SummaryTable
 * @package Runalyze\Plugins\Stats\RunalyzePluginStat_Statistiken
 */

use Runalyze\Dataset;

/**
 * Summary table for dataset/data browser
 * 
 * @author Hannes Christiansen
 * @package Runalyze\Plugins\Stats\RunalyzePluginStat_Statistiken
 */
abstract class SummaryTable {
	/** @var int */
	protected $AccountID;

	/** @var \Runalyze\Dataset\Query */
	protected $DatasetQuery;

	/** @var \Runalyze\View\Dataset\Table */
	protected $DatasetTable;

	/**
	 * Compare kilometers
	 * @var boolean
	 */
	protected $CompareKilometers = false;

	/**
	 * Sport id
	 * @var int
	 */
	protected $Sportid;

	/**
	 * Year
	 * @var int
	 */
	protected $Year;

	/**
	 * Title
	 * @var string
	 */
	protected $Title = '';

	/**
	 * Timerange to group for one row
	 * @var int
	 */
	protected $Timerange = 0;

	/**
	 * First timestamp to consider
	 * @var int
	 */
	protected $TimeStart = 0;

	/**
	 * Last timestamp to consider
	 * @var int
	 */
	protected $TimeEnd = 0;

	/**
	 * Additional columns
	 * @var int
	 */
	protected $AdditionalColumns = 0;

	/**
	 * Construct summary table
	 * @param \Runalyze\Dataset\Configuration $datasetConfig
	 * @param int $sportid
	 * @param int $year
	 */
	public function __construct(Dataset\Configuration $datasetConfig, $sportid, $year) {
		$this->AccountID = SessionAccountHandler::getId();
		$this->DatasetQuery = new Dataset\Query($datasetConfig, DB::getInstance(), $this->AccountID);
		$this->DatasetTable = new Runalyze\View\Dataset\Table($datasetConfig);

		$this->Sportid = $sportid;
		$this->Year = $year;
	}

	/**
	 * Compare kilometers
	 * @param bool $flag
	 */
	public function compareKilometers($flag = true) {
		$this->CompareKilometers = $flag;
	}

	/**
	 * Prepare summary
	 */
	abstract protected function prepare();

	/**
	 * Head for row
	 * @param int $index
	 * @return string
	 */
	abstract protected function rowHead($index);

	/**
	 * Display additional columns
	 * @param array $data
	 */
	protected function displayAdditionalColumns($data) {}

	/**
	 * Check preparation
	 * @throws Exception
	 */
	private function checkPreparation() {
		if ($this->Timerange == 0 || ($this->TimeStart == 0 && $this->TimeEnd == 0)) {
			throw new Exception('Timerange, -start and -end have to be properly defined.');
		}
	}

	/**
	 * Display
	 */
	public function display() {
		$this->prepare();
		$this->checkPreparation();

		$this->displayTableHeader();
		$this->displayTableBody();
		$this->displayTableFooter();

	}

	/**
	 * Display table header
	 */
	protected function displayTableHeader() {
		echo '<table class="r fullwidth zebra-style">';
		echo '<thead><tr><th colspan="'.($this->DatasetTable->numColumns() + 2 + $this->AdditionalColumns).'">'.$this->Title.'</th></tr></thead>';
		echo '<tbody>';
	}

	/**
	 * Display table body
	 */
	protected function displayTableBody() {
		$maxIndex = ceil(($this->TimeEnd - $this->TimeStart) / $this->Timerange) - 1;
		$CompleteData = array();
		$CompleteResult = $this->DatasetQuery->fetchSummaryForTimerange($this->Sportid, $this->Timerange, $this->TimeStart, $this->TimeEnd);
		$Context = new Dataset\Context(new Runalyze\Model\Activity\Entity(), $this->AccountID);
		$hiddenKeys = array(
			Dataset\Keys::SPORT
		);

		foreach ($CompleteResult as $Data) {
			$CompleteData[$Data['timerange']] = $Data;
		}

		for ($index = 0; $index <= $maxIndex; ++$index) {
			echo '<tr><td class="l"><span class="b">'.$this->rowHead($index).'</span></td>';

			if (isset($CompleteData[$index]) && !empty($CompleteData[$index])) {
				echo '<td class="small">'.$CompleteData[$index]['num'].'x</td>';

				$this->displayAdditionalColumns($CompleteData[$index]);

				if ($this->CompareKilometers) {
					$value = isset($CompleteData[$index+1]) ? $CompleteData[$index+1]['distance'] : 0;
					$CompleteData[$index][Dataset\Keys\Distance::KEY_DISTANCE_COMPARISON] = $value;
				}

				$Context->setActivityData($CompleteData[$index]);
				echo $this->DatasetTable->codeForColumns($Context, $hiddenKeys);
			} else {
				echo HTML::emptyTD($this->DatasetTable->numColumns() + 1 + $this->AdditionalColumns, '<em>'.__('No activities').'</em>', 'c small');
			}

			echo '</tr>';
		}
	}

	/**
	 * Display table footer
	 */
	protected function displayTableFooter() {
		echo '</tbody>';
		echo '</table>';
	}
}
