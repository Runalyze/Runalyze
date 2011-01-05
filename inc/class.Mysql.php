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
 * Last modified 2010/08/08 21:33 by Hannes Christiansen
 */
class Mysql {
	private static $host,
		$user,
		$password,
		$database;

	/**
	 * Creates connection to mysql-database if possible
	 * @param $host
	 * @param $user
	 * @param $password
	 * @param $database
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
	 * @param $query           full mysql-query
	 * @return resource|bool   resource for 'SELECT' and otherwise true, false for errors 
	 */
	function query($query) {
		global $error;

		$result = false;
		$result = mysql_query($query) or $error->add('ERROR',mysql_error().' <Query: '.$query.'>',__FILE__,__LINE__);
		return $result;
	}

	/**
	 * Updates a table for a given ID.
	 * @param $table
	 * @param $id
	 * @param $column   might be an array
	 * @param $value    might be an array
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
	 * @param $table
	 * @param $columns
	 * @param $values
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
	 * @param $table|$query      Must be set: name of table or whole query
	 * @param $id|false|'LAST'   Must not be set if first argument is a query, otherwise the ID.
	 * @param $as_array          Method returns always $return[$i]['column'] if true. 
	 * @return array             For sizeof($return)=1: $return['column'], otherwise: $return[$i]['column']. For sizeof($return)=0: false.
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
	 * @param $query
	 * @return int     number of rows
	 */
	function num($query) {
		global $error;

		$db = $this->query($query);
		return mysql_num_rows($db);
	}

	/**
	 * Deletes the row of ID=$id from $table
	 * @param $table
	 * @param $id
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
	 * @param $values   might be an array
	 * @param $quotes   true for adding quotes for strings
	 * @return mixed    safe value(s)
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