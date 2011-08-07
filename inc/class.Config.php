<?php
/**
 * This file contains the class::Config for handling all config-data.
 */
/**
 * Class: Config
 * 
 * @author Hannes Christiansen <mail@laufhannes.de>
 * @version 1.0
 * @uses class::Error
 * @uses class::Mysql
 */
class Config {
	/**
	 * Name for hidden category, not editable
	 * @var string
	 */
	public static $HIDDEN_CAT = 'hidden';

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
	 * Get link to the configuration overlay
	 * @return string
	 */
	static public function getOverlayLink() {
		return Ajax::window('<a class="left" href="call/window.config.php" title="Einstellungen">'.Icon::get(Icon::$CONF_EDIT, 'Einstellungen').'</a>');
	}

	/**
	 * Update a value, should primary be used for hidden keys
	 * @param string $KEY
	 * @param mixed $value
	 */
	static public function update($KEY, $value) {
		Mysql::getInstance()->query('UPDATE `'.PREFIX.'conf` SET `value`="'.$value.'" WHERE `key`="'.$KEY.'" LIMIT 1');
	}

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

		define('CONF_'.$KEY, $value);
	}

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
			case 'selectdb':
			case 'int':
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
			case 'selectdb':
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
			case 'selectdb':
				$settings = self::stringToValue($conf['select_description'], 'array');
				$db       = $settings[0];
				$col      = $settings[1];

				$select = '<select name="'.$name.'">';
				$values = Mysql::getInstance()->fetchAsArray('SELECT `id`, `'.$col.'` FROM `'.PREFIX.$db.'` ORDER BY `'.$col.'` ASC');
				foreach ($values as $v)
					$select .= '<option value="'.$v['id'].'"'.HTML::Selected($v['id'] == $conf['value']).'>'.$v[$col].'&nbsp;</option>';
				$select .= '</select>';
				return $select;
			case 'select':
				$descr  = self::stringToValue($conf['select_description'], 'array');
				$select = '<select name="'.$name.'">';
				$i      = 0;
				foreach ($value as $key => $val) {
					$select .= '<option value="'.$key.'"'.HTML::Selected($val).'>'.$descr[$i].'&nbsp;</option>';
					$i++;
				}
				$select .= '</select>';
				return $select;
			case 'array':
				return '<input type="text" name="'.$name.'" value="'.$value.'" />';
			case 'bool':
				return '<input type="checkbox" name="'.$name.'"'.HTML::Checked($value).' />';
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
		$confs = Mysql::getInstance()->fetchAsArray('SELECT * FROM `'.PREFIX.'conf` WHERE `category`!="'.self::$HIDDEN_CAT.'"');
		foreach ($confs as $conf) {
			$str_value = $conf['value']; // TODO
			$value     = self::stringToValue($str_value, $conf['type']);
			$post      = isset($_POST['CONF_'.$conf['key']]) ? $_POST['CONF_'.$conf['key']] : false;

			switch ($conf['type']) {
				case 'selectdb':
					$str_value = $post;
					break;
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
				'summary',
				'position');
			$values  = array(
				$modus,
				(isset($_POST[$id.'_summary']) && $_POST[$id.'_summary'] == 'on' ? 1 : 0),
				isset($_POST[$id.'_position']) ? $_POST[$id.'_position'] : 0);

			Mysql::getInstance()->update(PREFIX.'dataset', $id, $columns, $values);
		}
	}

	/**
	 * Parse post-data for editing sports
	 */
	static public function parsePostDataForSports() {
		$sports = Mysql::getInstance()->fetchAsArray('SELECT `id` FROM `'.PREFIX.'sport`');
		$sports[] = array('id' => -1);

		foreach ($sports as $i => $sport) {
			$columns = array(
				'name',
				'short',
				'online',
				'kcal',
				'HFavg',
				'RPE',
				'distances',
				'kmh',
				'types',
				'pulse',
				'outside',
				);
			$values  = array(
				$_POST['sport']['name'][$i],
				isset($_POST['sport']['short'][$i]),
				isset($_POST['sport']['online'][$i]),
				$_POST['sport']['kcal'][$i],
				$_POST['sport']['HFavg'][$i],
				$_POST['sport']['RPE'][$i],
				isset($_POST['sport']['distances'][$i]),
				isset($_POST['sport']['kmh'][$i]),
				isset($_POST['sport']['types'][$i]),
				isset($_POST['sport']['pulse'][$i]),
				isset($_POST['sport']['outside'][$i]),
				);

			if ($sport['id'] != -1)
				Mysql::getInstance()->update(PREFIX.'sport', $sport['id'], $columns, $values);
			elseif (strlen($_POST['sport']['name'][$i]) > 2)
				Mysql::getInstance()->insert(PREFIX.'sport', $columns, $values);
		}
	}

	/**
	 * Parse post-data for editing trainingtypes
	 */
	static public function parsePostDataForTypes() {
		$typen = Mysql::getInstance()->fetchAsArray('SELECT `id` FROM `'.PREFIX.'type`');
		$typen[] = array('id' => -1);

		foreach ($typen as $i => $typ) {
			$rpe = (int)$_POST['type']['RPE'][$i];
			if ($rpe < 1)
				$rpe = 1;
			elseif ($rpe > 10)
				$rpe = 10;

			$columns = array(
				'name',
				'abbr',
				'RPE',
				'splits',
				);
			$values  = array(
				$_POST['type']['name'][$i],
				$_POST['type']['abbr'][$i],
				$rpe,
				isset($_POST['type']['splits'][$i]),
				);

			if (isset($_POST['type']['delete'][$i]))
				Mysql::getInstance()->delete(PREFIX.'type', (int)$typ['id']);
			elseif ($typ['id'] != -1)
				Mysql::getInstance()->update(PREFIX.'type', $typ['id'], $columns, $values);
			elseif (strlen($_POST['type']['name'][$i]) > 2)
				Mysql::getInstance()->insert(PREFIX.'type', $columns, $values);
		}
	}

	/**
	 * Parse post-data for editing clothes
	 */
	static public function parsePostDataForClothes() {
		$kleidungen = Mysql::getInstance()->fetchAsArray('SELECT `id` FROM `'.PREFIX.'clothes`');
		$kleidungen[] = array('id' => -1);

		foreach ($kleidungen as $kleidung) {
			$id = $kleidung['id'];
			$columns = array(
				'name',
				'short',
				'order',
				);
			$values  = array(
				$_POST['clothes']['name'][$id],
				$_POST['clothes']['short'][$id],
				$_POST['clothes']['order'][$id],
				);

			if (isset($_POST['clothes']['delete'][$id]))
				Mysql::getInstance()->delete(PREFIX.'clothes', (int)$kleidung['id']);
			elseif ($kleidung['id'] != -1)
				Mysql::getInstance()->update(PREFIX.'clothes', $kleidung['id'], $columns, $values);
			elseif (strlen($_POST['clothes']['name'][$id]) > 2)
				Mysql::getInstance()->insert(PREFIX.'clothes', $columns, $values);
		}
	}
}
?>