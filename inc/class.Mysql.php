<?php
/**
 * This file contains the class::Mysql
 */
/**
 * Class for handling a mysql-connection and getting rows from database
 * 
 * @author Hannes Christiansen <mail@laufhannes.de>
 * @version 1.0
 * @uses class::Error ($error)
 *
 * Last modified 2011/01/08 19:45 by Hannes Christiansen
 */
class Mysql {
	private static $host,
		$user,
		$password,
		$database;

	/**
	 * Creates connection to mysql-database if possible
	 * @param $host string
	 * @param $user string
	 * @param $password string
	 * @param $database string
	 */
	function __construct($host, $user, $password, $database) {
		global $error;

		mysql_connect($host, $user, $password) or $error->add('ERROR',mysql_error(),__FILE__,__LINE__);
		mysql_select_db($database) or $error->add('ERROR',mysql_error(),__FILE__,__LINE__);
	}

	/**
	 * Automatically closes connection after execution
	 */
	function __destruct() {
		mysql_close();
	}

	/**
	 * Calls mysql_query and returns the result if there is any
	 * Be careful, unsafe query! Use $mysql->escape($values)!
	 * @param $query string full mysql-query
	 * @return resource|bool   resource for 'SELECT' and otherwise true, false for errors 
	 */
	function query($query) {
		global $error;

		$result = false;
		$result = mysql_query($query) or $error->add('ERROR',mysql_error().' &lt;Query: '.$query.'&gt;',__FILE__,__LINE__);
		return $result;
	}

	/**
	 * Updates a table for a given ID.
	 * @param $table  string
	 * @param $id     int
	 * @param $column mixed  might be an array
	 * @param $value  mixed  might be an array
	 */
	function update($table, $id, $column, $value) {
		global $error;

		if (is_array($column) && sizeof($column) == sizeof($value)) {
			$set = '';
			foreach ($column as $i => $col) {
				$set .= '`'.$col.'`="'.$value[$i].'", ';
			}
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
	function insert($table, $columns, $values) {
		global $error;

		$columns = implode(', ', $columns);
		$values = implode(', ', self::escape($values));

		$this->query('INSERT INTO `'.$table.'` ('.$columns.') VALUES('.$values.')');
		return mysql_insert_id();
	}

	/**
	 * Fetches the row of an given $id or all rows of a $query
	 * @param string $table    name of table or whole query
	 * @param int|bool $id     Must not be set if first argument is a query (default: false), otherwise the ID. Can be 'LAST' to get the highest ID
	 * @param bool $as_array   Method returns always $return[$i]['column'] if true, default: false 
	 * @return array           For sizeof($return)=1: $return['column'], otherwise: $return[$i]['column']. For sizeof($return)=0: false.
	 */
	function fetch($table, $id = false, $as_array = false) {
		global $error;

		$return = array();
		if ($id === false)
			$result = $this->query($table);
		elseif ($id == 'LAST')
			$result = $this->query('SELECT * FROM `'.$table.'` ORDER BY `id` DESC LIMIT 1');
		else
			$result = $this->query('SELECT * FROM `'.$table.'` WHERE `id`='.$id.' LIMIT 1');

		if ($result === false) {
			$error->add('WARNING',mysql_error());
			return false;
		}

		while($data = mysql_fetch_assoc($result))
			$return[] = $data;

		if (sizeof($return) == 0)
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
	function num($query) {
		global $error;

		$db = $this->query($query);
		return mysql_num_rows($db);
	}

	/**
	 * Deletes the row of ID=$id from $table
	 * @param $table string
	 * @param $id    int
	 */
	function delete($table, $id) {
		global $error;

		if (!is_int($id)) {
			$error->add('ERROR','Second parameter for Mysql::delete() must be an integer. <$id='.$id.'>',__FILE__,__LINE__);
			return;
		}
		$this->query('DELETE FROM `'.$table.'` WHERE `id`='.$id.' LIMIT 1');
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
	static function escape($values, $quotes = true) {
		global $error;

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