<?php
/**
 * Class: ConfigCategory
 * @author Hannes Christiansen <mail@laufhannes.de>
 */
class ConfigCategory {
	/**
	 * Internal array with all categories
	 * @var array
	 */
	private static $Categories = array();

	/**
	 * Internal array with all config values
	 * @var array
	 */
	protected $ConfigValues = array();

	/**
	 * Array with all keys for the fieldset
	 * @var array
	 */
	protected $KeysForFields = array();

	/**
	 * Key
	 * @var string
	 */
	protected $Key = '';

	/**
	 * Label for fieldset
	 * @var string
	 */
	protected $Label = '';

	/**
	 * Add a new category
	 * @param string $Key
	 * @param ConfigCategory $Object 
	 */
	static private function addCategory($Key, ConfigCategory $Object) {
		self::$Categories[$Key] = $Object;
	}

	/**
	 * Construct a new category
	 * @param string $Key
	 */
	public function __construct($Key, $Label) {
		$this->Key = $Key;
		$this->Label = $Label;
	}

	/**
	 * Add this object to internal category list 
	 */
	public function addToCategoryList() {
		self::addCategory($this->Key, $this);
	}

	/**
	 * Add a config value
	 * @param ConfigValue $ConfigValue Can be empty string for inserting a spacer
	 */
	public function addConfigValue($ConfigValue) {
		$this->ConfigValues[$ConfigValue->getKey()] = $ConfigValue;
	}

	/**
	 * Parse all config values 
	 */
	public function parseAllValues() {
		// Nothing has to be done
		// All values parse themselves while being constructed
	}

	/**
	 * Set keys for displayed fields
	 * @param array $Keys 
	 */
	public function setKeys($Keys) {
		$this->KeysForFields = $Keys;
	}

	/**
	 * Get fieldset for category
	 * @return FormularFieldset
	 */
	public function getFieldset() {
		$Fieldset = new FormularFieldset($this->Label);

		foreach ($this->KeysForFields as $Key) {
			if (isset($this->ConfigValues[$Key]))
				$Fieldset->addField($this->ConfigValues[$Key]->getField());
			else
				$Fieldset->addField('');
		}

		return $Fieldset;
	}

	/**
	 * Get all categories
	 * @return array
	 */
	static public function getAllCategories() {
		return self::$Categories;
	}
}