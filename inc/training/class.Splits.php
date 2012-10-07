<?php
/**
 * Class for handling splits
 * @author Hannes Christiansen <mail@laufhannes.de> 
 */
class Splits {
	/**
	 * Enum for constructor: Take data from post
	 * @var enum
	 */
	static public $FROM_POST = 'TAKE_PARAMETER_FROM_POST';

	/**
	 * Splits as string
	 * @var string
	 */
	private $asString = '';

	/**
	 * Splits as array
	 * @var array
	 */
	private $asArray = array();

	/**
	 * Construct a new object of Splits
	 * @param string $data [optional]
	 */
	public function __construct($data = false) {
		if ($data == self::$FROM_POST)
			$this->createFromData( isset($_POST['splits']) ? $_POST['splits'] : array() );
		elseif ($data)
			$this->createFromData($data);

		$this->stringToArray();
		$this->cleanArray();
		$this->arrayToString();
	}

	/**
	 * Create splits from POST-data
	 * @param mixed $data
	 */
	private function createFromData($data) {
		// TODO escaping
		if (is_array($data)) {
			if (isset($data['km']) && isset($data['time'])) {
				$newData = array();

				foreach ($data['km'] as $i => $km)
					$newData[] = array('km' => $km, 'time' => $data['time'][$i]);

				$data = $newData;
			}

			$this->asArray = $data;
			$this->arrayToString();
		} else
			$this->asString = $data;
	}

	/**
	 * Are the splits empty?
	 * @return boolean
	 */
	public function areEmpty() {
		return empty($this->asArray);
	}

	/**
	 * Get splits as array
	 * @return array
	 */
	public function asArray() {
		return $this->asArray;
	}

	/**
	 * Get splits as string
	 * @return string
	 */
	public function asString() {
		return $this->asString;
	}

	/**
	 * Get splits as readable string
	 * @return string 
	 */
	public function asReadableString() {
		$strings = array();

		foreach ($this->asArray as $split)
			$strings[] = $split['km'].'&nbsp;km&nbsp;in&nbsp;'.$split['time'];

		return implode(', ', $strings);
	}

	/**
	 * Transform splits from internal string to array 
	 */
	private function stringToArray() {
		$this->asArray = array();
		$splits        = explode('-', str_replace('\r\n', '-', $this->asString));

		foreach ($splits as $split)
			if (strlen($split) > 3)
				$this->asArray[] = array('km' => rstrstr($split, '|'), 'time' => substr(strrchr($split, '|'), 1));
	}

	/**
	 * Clean internal array 
	 */
	private function cleanArray() {
		foreach ($this->asArray as $key => $split) {
			if ($split['km'] <= 0 || empty($split['time']))
				unset($this->asArray[$key]);
			else
				$this->asArray[$key]['km'] = number_format(Helper::CommaToPoint($split['km']), 2, '.', '');
		}
	}

	/**
	 * Transform internal array to string
	 */
	private function arrayToString() {
		$strings = array();

		foreach ($this->asArray() as $split)
			$strings[] = $split['km'].'|'.$split['time'];

		$this->asString = implode('-', $strings);
	}

	/**
	 * Get all times as array
	 * @return array 
	 */
	public function timesAsArray() {
		$times = array();

		foreach ($this->asArray as $split)
			$times[] = Time::toSeconds($split['time']);

		return $times;
	}

	/**
	 * Get all distances as array
	 * @return array 
	 */
	public function distancesAsArray() {
		$distances = array();

		foreach ($this->asArray as $split)
			$distances[] = $split['km'];

		return $distances;
	}

	/**
	 * Get all paces as array
	 * @return array 
	 */
	public function pacesAsArray() {
		$paces = array();

		foreach ($this->asArray as $split)
			$paces[] = $split['km'] > 0 ? (int)round(Time::toSeconds($split['time'])/$split['km']) : 0;

		return $paces;
	}

	/**
	 * Get fieldset
	 * @return FormularFieldset 
	 */
	public function getFieldset() {
		$Fieldset = new FormularFieldset('Zwischenzeiten');
		$Fieldset->addField( new TrainingInputSplits() );
		$Fieldset->addCSSclass( TrainingCreatorFormular::$ONLY_DISTANCES_CLASS );

		if ($this->areEmpty())
			$Fieldset->setCollapsed();

		return $Fieldset;
	}
}