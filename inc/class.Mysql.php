<?php
/**
 * This file contains the class::Mysql
 */
/**
 * Class for handling a mysql-connection and getting rows from database
 * 
 * @author Hannes Christiansen <mail@laufhannes.de>
 * @version 1.0
 * @uses class::Error
 *
 * Last modified 2011/03/05 13:00 by Hannes Christiansen
 */
final class Mysql {
	/**
	 * Internal instance
	 * @var Mysql
	 */
	private static $instance = NULL;

	/**
	 * Static getter for the singleton instnace
	 * @return Mysql
	 */
	public static function getInstance() {
		if (self::$instance == NULL)
			self::$instance = new self;

		return self::$instance;
	}

	/**
	 * Prohibit creating an object from outside
	 */
	private function __construct() {}

	/**
	 * Automatically closes connection after execution
	 */
	public function __destruct() {
		mysql_close();
	}

	/**
	 * Prohibit cloning
	 */
	private function __clone() {}

	/**
	 * Creates connection to mysql-database if possible
	 * @param $host string
	 * @param $user string
	 * @param $password string
	 * @param $database string
	 */
	public static function connect($host, $user, $password, $database) {
		@mysql_connect($host, $user, $password)
			or Error::getInstance()->displayFatalErrorMessage(mysql_error());
		@mysql_select_db($database)
			or Error::getInstance()->displayFatalErrorMessage(mysql_error());
	}

	/**
	 * Calls mysql_query and returns the result if there is any
	 * Be careful, unsafe query! Use $mysql->escape($values)!
	 * @param $query string full mysql-query
	 * @return resource|bool   resource for 'SELECT' and otherwise true, false for errors 
	 */
	public function query($query) {
		//Error::getInstance()->addDebug($query);

		$result = false;
		$result = mysql_query($query)
			or Error::getInstance()->addError(mysql_error().' &lt;Query: '.$query.'&gt;', __FILE__, __LINE__);
		return $result;
	}

	/**
	 * Updates a table for a given ID.
	 * @param $table  string
	 * @param $id     int
	 * @param $column mixed  might be an array
	 * @param $value  mixed  might be an array
	 */
	public function update($table, $id, $column, $value) {
		if (strncmp($table, PREFIX, strlen(PREFIX)) != 0)
			Error::getInstance()->addWarning('class::Mysql: Tablename should start with global prefix "'.PREFIX.'".');

		if (is_array($column) && count($column) == count($value)) {
			$set = '';
			foreach ($column as $i => $col)
				$set .= '`'.$col.'`="'.$value[$i].'", ';
		} else {
			$set = '`'.$column.'`="'.$value.'", ';
		}

		$this->query('UPDATE `'.$table.'` SET '.substr($set,0,-2).' WHERE `id`="'.$id.'" LIMIT 1');
	}

	/**
	 * Escapes and inserts the given $values to the $columns in $table
	 * @param $table   string
	 * @param $columns array
	 * @param $values  array
	 * @return int       ID of inserted row
	 */
	public function insert($table, $columns, $values) {
		if (strncmp($table, PREFIX, strlen(PREFIX)) != 0)
			Error::getInstance()->addWarning('class::Mysql: Tablename should start with global prefix "'.PREFIX.'".');

		foreach ($columns as $k => $v)
			$columns[$k] = '`'.$v.'`';
		$columns = implode(', ', $columns);
		$values = implode(', ', self::escape($values));

		if (!$this->query('INSERT INTO `'.$table.'` ('.$columns.') VALUES('.$values.')'))
			return false;

		return mysql_insert_id();
	}

	/**
	 * Fetch data from database as array
	 * @param string $query
	 * @return array $return[$i]['column']
	 */
	public function fetchAsArray($query) {
		return $this->fetch($query, false, true);
	}

	/**
	 * Fetch one single data from database as array
	 * @param string $query
	 * @return array $return['column']
	 */
	public function fetchSingle($query) {
		if (substr($query, -7, 7) != 'LIMIT 1')
			$query .= ' LIMIT 1';

		return $this->fetch($query);
	}

	/**
	 * Fetches the row of an given $id or all rows of a $query
	 * @param string $table    name of table or whole query
	 * @param int|bool $id     Must not be set if first argument is a query (default: false), otherwise the ID. Can be 'LAST' to get the highest ID
	 * @param bool $as_array   Method returns always $return[$i]['column'] if true, default: false 
	 * @return array           For count($return)=1: $return['column'], otherwise: $return[$i]['column']. For count($return)=0 && !$as_array: false.
	 */
	public function fetch($table, $id = false, $as_array = false) {
		$return = array();
		if ($id !== false && strncmp($table, PREFIX, strlen(PREFIX)) != 0)
			Error::getInstance()->addWarning('class::Mysql: Tablename should start with global prefix "'.PREFIX.'".');

		if ($id === false)
			$result = $this->query($table);
		elseif ($id == 'LAST')
			$result = $this->query('SELECT * FROM `'.$table.'` ORDER BY `id` DESC LIMIT 1');
		else
			$result = $this->query('SELECT * FROM `'.$table.'` WHERE `id`="'.$id.'" LIMIT 1');

		if ($result === false) {
			Error::getInstance()->addWarning(mysql_error());
		} else {
			while($data = mysql_fetch_assoc($result))
				$return[] = $data;
		}

		if (sizeof($return) == 0 && !$as_array)
			return false;
		if (sizeof($return) == 1 && !$as_array)
			return $return[0];

		return $return;
	}

	/**
	 * Counts the rows of $query
	 * @param $query   string
	 * @return int     number of rows
	 */
	public function num($query) {
		return mysql_num_rows($this->query($query));
	}

	/**
	 * Deletes the row of ID=$id from $table
	 * @param $table string
	 * @param $id    int
	 */
	public function delete($table, $id) {
		if (strncmp($table, PREFIX, strlen(PREFIX)) != 0)
		Error::getInstance()->addWarning('class::Mysql: Tablename should start with global prefix "'.PREFIX.'".');
		
		if (!is_int($id)) {
			Error::getInstance()->addError('Second parameter for Mysql::delete() must be an integer. <$id='.$id.'>', __FILE__, __LINE__);
			return;
		}
		$this->query('DELETE FROM `'.$table.'` WHERE `id`="'.$id.'" LIMIT 1');
	}

	/**
	 * Escapes values for safe mysql-queries:
	 *    - Sets null-objects to 'NULL'
	 *    - Sets true/false to 1/0
	 *    - Sets strings to "$value"
	 * @param $values mixed might be an array
	 * @param $quotes bool  true for adding quotes for strings
	 * @return mixed        safe value(s)
	 */
	public static function escape($values, $quotes = true) {
		if (is_array($values)) {
			foreach ($values as $key => $value)
				$values[$key] = self::escape($value, $quotes);
		} else if ($values === null) {
			$values = 'NULL';
		} else if (is_bool($values)) {
			$values = $values ? 1 : 0;
		} else if (!is_numeric($values)) {
			$values = mysql_real_escape_string($values);
        	if ($quotes)
            	$values = '"'.$values.'"';
		}

		return $values;
	}
}
?>