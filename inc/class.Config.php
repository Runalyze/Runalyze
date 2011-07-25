<?php
/**
 * This file contains the class::Config for handling all config-data.
 */
/**
 * Class: Config
 * 
 * @author Hannes Christiansen <mail@laufhannes.de>
 * @version 1.0
 *
 * Last modified 2011/07/24 16:00 by Hannes Christiansen
 */
class Config {
	/**
	 * Config-Array
	 * @var array
	 */
	public $data;

	/**
	 * Constructor
	 */
	public function __construct() {}

	/**
	 * Destructor
	 */
	public function __destruct() {}

	/**
	 * Register a variable
	 * @param string $category Descriptive category for config-window
	 * @param string $KEY Internal key, must be unique, should start with an equivalent for the category
	 * @param string $type Type of this value
	 * @param mixed $default Default value 
	 */
	static public function register($category, $KEY, $type, $default, $description = '', $select_description = array()) {
		$conf = Mysql::getInstance()->fetchSingle('SELECT * FROM `'.PREFIX.'conf` WHERE `key`="'.$KEY.'"');
		if ($conf === false) {
			$select_description = self::valueToString($select_description, 'array');
			$default = self::valueToString($default, $type);
			$columns = array('category', 'key', 'type', 'value', 'description', 'select_description');
			$values  = array($category,  $KEY,  $type,  $default, $description, $select_description);

			Mysql::getInstance()->insert(PREFIX.'conf', $columns, $values);

			$conf = array('value' => $default);
		}

		$value = $conf['value'];
		$value = self::stringToValue($value, $type);

		if ($type == 'array')
			$value = serialize($value);
		if ($type == 'select') {
			foreach ($value as $k => $v)
				if ($v)
					$value = $k;
		}

		// TODO: Can only save scalar falues ...
		// TODO: Config::get($KEY);
		define('CONF_'.$KEY, $value);
	}

	// TODO: Add type "select-sportid"
	// TODO: Add type "select-typid"

	/**
	 * Transform given value to string for saving in database
	 * @param mixed $value
	 * @param string $type
	 * @return string
	 */
	static public function valueToString($value, $type) {
		switch ($type) {
			case 'select':
				$string = '';
				foreach ($value as $key => $val)
					$string .= $key.($val ? '=true' : '=false').'|';
				return substr($string, 0, -1);
			case 'array':
				return implode(', ', $value);
			case 'bool':
				return ($value ? 'true' : 'false');
			case 'int':
				return (string)$value;
			case 'float':
				return (string)$value;
			case 'string':
			default:
				return $value;
		}
	}

	/**
	 * Transform given value to string for saving in database
	 * @param string $value
	 * @param string $type
	 * @return mixed
	 */
	static public function stringToValue($value, $type) {
		switch ($type) {
			case 'select':
				$return = array();
				$array  = explode('|', $value);
				foreach ($array as $option) {
					$splits = explode('=', $option);
					$return[$splits[0]] = $splits[1] == 'true'; 
				}
				return $return;
			case 'array':
				$array = explode(',', $value);
				if (count($array) == 1)
					$array = explode('|', $array[0]);
				foreach ($array as $k => $v)
					$array[$k] = trim($v);
				return $array;
			case 'bool':
				return ($value == 'true');
			case 'int':
				return (int)$value;
			case 'float':
				return (float)$value;
			case 'string':
			default:
				return $value;
		}
	}

	/**
	 * Get input field for formular for a given conf-variable
	 * @param array $conf Equivalent line from database for conf-variable as array
	 */
	static public function getInputField($conf) {
		$name  = 'CONF_'.$conf['key'];
		$value = self::stringToValue($conf['value'], $conf['type']);

		switch ($conf['type']) {
			case 'select':
				$descr  = self::stringToValue($conf['select_description'], 'array');
				$select = '<select name="'.$name.'">';
				$i      = 0;
				foreach ($value as $key => $val) {
					$select .= '<option value="'.$key.'"'.Helper::Selected($val).'>'.$descr[$i].'&nbsp;</option>';
					$i++;
				}
				$select .= '</select>';
				return $select;
			case 'array':
				return '<input type="text" name="'.$name.'" value="'.$value.'" />';
			case 'bool':
				return '<input type="checkbox" name="'.$name.'"'.Helper::Checked($value).' />';
			case 'int':
			case 'float':
				return '<input type="text" size="6" name="'.$name.'" value="'.$value.'" />';
			case 'string':
			default:
				return '<input type="text" name="'.$name.'" value="'.$value.'" />';
		}
	}

	/**
	 * Parse post-data for editing conf-data in databse
	 */
	static public function parsePostDataForConf() {
		$confs = Mysql::getInstance()->fetchAsArray('SELECT * FROM `'.PREFIX.'conf`');
		foreach ($confs as $conf) {
			$str_value = $conf['value']; // TODO
			$value     = self::stringToValue($str_value, $conf['type']);
			$post      = isset($_POST['CONF_'.$conf['key']]) ? $_POST['CONF_'.$conf['key']] : false;

			switch ($conf['type']) {
				case 'select':
					$array = array();
					foreach ($value as $key => $val)
						$array[$key] = ($post == $key);

					$str_value = self::valueToString($array, 'select');
					break;
				case 'array':
					// TODO: Fehlermeldungen bei falschem Parsing (kommagetrennt ...)
					$str_value = $post;
					break;
				case 'bool':
					$value = $post || $post == 'on';
					$str_value = self::valueToString($value, 'bool');
					break;
				case 'int':
				case 'float':
				case 'string':
				default:
					$str_value = trim(Helper::CommaToPoint($post));
			}

			Mysql::getInstance()->update(PREFIX.'conf', $conf['id'], 'value', $str_value);
		}
	}

	/**
	 * Parse post-data for editing plugins
	 */
	static public function parsePostDataForPlugins() {
		$plugins = Mysql::getInstance()->fetchAsArray('SELECT `id` FROM `'.PREFIX.'plugin`');
		foreach ($plugins as $plugin) {
			$id = $plugin['id'];
			Mysql::getInstance()->update(PREFIX.'plugin', $id,
				array('active', 'order'),
				array($_POST['plugin_modus_'.$id], $_POST['plugin_order_'.$id]));
		}
	}

	/**
	 * Parse post-data for editing dataset
	 */
	static public function parsePostDataForDataset() {
		$dataset = Mysql::getInstance()->fetchAsArray('SELECT `id` FROM `'.PREFIX.'dataset`');

		foreach ($dataset as $set) {
			$id = $set['id'];
			$modus = isset($_POST[$id.'_modus']) && $_POST[$id.'_modus'] == 'on' ? 2 : 1;
			if (isset($_POST[$id.'_modus_3']) && $_POST[$id.'_modus_3'] == 3)
				$modus = 3;

			$columns = array(
				'modus',
				'zusammenfassung',
				'position');
			$values  = array(
				$modus,
				(isset($_POST[$id.'_zusammenfassung']) && $_POST[$id.'_zusammenfassung'] == 'on' ? 1 : 0),
				isset($_POST[$id.'_position']) ? $_POST[$id.'_position'] : 0);

			Mysql::getInstance()->update(PREFIX.'dataset', $id, $columns, $values);
		}
	}

	/**
	 * Parse post-data for editing sports
	 */
	static public function parsePostDataForSports() {
		$sports = Mysql::getInstance()->fetchAsArray('SELECT `id` FROM `'.PREFIX.'sports`');
		$sports[] = array('id' => -1);

		foreach ($sports as $i => $sport) {
			$columns = array(
				'name',
				'short',
				'online',
				'kalorien',
				'HFavg',
				'RPE',
				'distanztyp',
				'kmh',
				'typen',
				'pulstyp',
				'outside',
				);
			$values  = array(
				$_POST['sport']['name'][$i],
				isset($_POST['sport']['short'][$i]),
				isset($_POST['sport']['online'][$i]),
				$_POST['sport']['kalorien'][$i],
				$_POST['sport']['HFavg'][$i],
				$_POST['sport']['RPE'][$i],
				isset($_POST['sport']['distanztyp'][$i]),
				isset($_POST['sport']['kmh'][$i]),
				isset($_POST['sport']['typen'][$i]),
				isset($_POST['sport']['pulstyp'][$i]),
				isset($_POST['sport']['outside'][$i]),
				);

			if ($sport['id'] != -1)
				Mysql::getInstance()->update(PREFIX.'sports', $sport['id'], $columns, $values);
			elseif (strlen($_POST['sport']['name'][$i]) > 2)
				Mysql::getInstance()->insert(PREFIX.'sports', $columns, $values);
		}
	}

	/**
	 * Parse post-data for editing clothes
	 */
	static public function parsePostDataForClothes() {
		$kleidungen = Mysql::getInstance()->fetchAsArray('SELECT `id` FROM `'.PREFIX.'kleidung`');
		$kleidungen[] = array('id' => -1);

		foreach ($kleidungen as $i => $kleidung) {
			$columns = array(
				'name',
				'name_kurz',
				'order',
				);
			$values  = array(
				$_POST['kleidung']['name'][$i],
				$_POST['kleidung']['name_kurz'][$i],
				$_POST['kleidung']['order'][$i],
				);

			if (isset($_POST['kleidung']['delete'][$i]))
				Mysql::getInstance()->delete(PREFIX.'kleidung', (int)$kleidung['id']);
			elseif ($kleidung['id'] != -1)
				Mysql::getInstance()->update(PREFIX.'kleidung', $kleidung['id'], $columns, $values);
			elseif (strlen($_POST['kleidung']['name'][$i]) > 2)
				Mysql::getInstance()->insert(PREFIX.'kleidung', $columns, $values);
		}
	}
}
?>