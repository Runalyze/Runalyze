<?php
/**
 * This file contains class::PluginConfiguration
 * @package Runalyze\Plugin
 */
/**
 * Plugin configuration
 * @author Hannes Christiansen
 * @package Runalyze\Plugin
 */
class PluginConfiguration {
	/**
	 * @var string
	 */
	const CACHE_KEY = 'PluginConf';

	/**
	 * Values
	 * @var PluginConfigurationValue[]
	 */
	protected $Values = array();

	/**
	 * Plugin id
	 * @var int
	 */
	protected $PluginID;

	/**
	 * Catched values?
	 * @var bool
	 */
	private $CatchedValues = false;

	/**
	 * Constructor
	 * @param int $PluginID
	 */
	public function __construct($PluginID) {
		$this->PluginID = $PluginID;
	}

	/**
	 * Catch values if not done yet
	 */
	final public function catchValuesFromDatabaseIfNotDoneYet() {
		if (!$this->CatchedValues) {
			$this->catchValuesFromDatabase();

			$this->CatchedValues = true;
		}
	}

	/**
	 * Catch values
	 */
	final public function catchValuesFromDatabase() {
		$ResultFromDB = Cache::get(self::CACHE_KEY);

		if (is_null($ResultFromDB)) {
			$ResultFromDB = DB::getInstance()->query(
				'SELECT `pluginid`,`config`,`value` '.
				'FROM `'.PREFIX.'plugin_conf` '.
				'WHERE `pluginid` IN ('.implode(',', PluginFactory::allIDs()).')'
			)->fetchAll(PDO::FETCH_GROUP|PDO::FETCH_ASSOC);

			Cache::set(self::CACHE_KEY, $ResultFromDB, '60');
		}

		$this->checkValuesFromDatabase($ResultFromDB);
	}

	/**
	 * Check values
	 * @param array $ResultFromDB
	 */
	private function checkValuesFromDatabase(array $ResultFromDB) {
		$ValuesFromDB = array();

		if (isset($ResultFromDB[$this->PluginID])) {
			foreach ($ResultFromDB[$this->PluginID] as $Result) {
				$ValuesFromDB[$Result['config']] = $Result['value'];
			}
		}

		foreach ($this->Values as $Value) {
			if (isset($ValuesFromDB[$Value->key()])) {
				$Value->setValueFromString($ValuesFromDB[$Value->key()]);
			} else {
				$Value->setDefaultValueAsValue();

				$this->insertValueToDatabase($Value);
			}
		}
	}

	/**
	 * Insert value to database
	 * @param PluginConfigurationValue $Value
	 */
	private function insertValueToDatabase(PluginConfigurationValue $Value) {
		DB::getInstance()->insert('plugin_conf',
			array(
				'pluginid',
				'config',
				'value'
			),
			array(
				$this->PluginID,
				$Value->key(),
				$Value->valueAsString()
			)
		);

		Cache::delete(self::CACHE_KEY);
	}

	/**
	 * Update from post
	 */
	final public function updateFromPost() {
		foreach ($this->Values as $Value) {
			$Value->setValueFromPost();

			$this->update($Value->key());
		}

		Cache::delete(self::CACHE_KEY);
	}

	/**
	 * Update
	 * @param string $key
	 */
	final public function update($key) {
		if (isset($this->Values[$key])) {
			DB::getInstance()->updateWhere(
				'plugin_conf',
				'`pluginid`='.(int)$this->PluginID.' AND `config`="'.$key.'"',
				'value',
				$this->Values[$key]->valueAsString()
			);
		}
	}

	/**
	 * Add value
	 * @param PluginConfigurationValue $Value
	 */
	final public function addValue(PluginConfigurationValue $Value) {
		$this->Values[$Value->key()] = $Value;
	}

	/**
	 * Object for value
	 * @param string $key
	 * @return PluginConfigurationValue
	 * @throws InvalidArgumentException
	 */
	final public function object($key) {
		if (isset($this->Values[$key])) {
			return $this->Values[$key];
		}

		throw new InvalidArgumentException('There is no value for "'.$key.'".');
	}

	/**
	 * All objects
	 * @return PluginConfigurationValue[]
	 */
	final public function objects() {
		return $this->Values;
	}

	/**
	 * Is empty?
	 * @return bool
	 */
	final public function isEmpty() {
		return empty($this->Values);
	}

	/**
	 * Value
	 * @param string $key
	 * @return mixed
	 */
	final public function value($key) {
		return $this->object($key)->value();
	}
}
