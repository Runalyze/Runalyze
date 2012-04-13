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
 */
final class Mysql {
	/**
	 * Internal instance
	 * @var Mysql
	 */
	private static $instance = NULL;

	/**
	 * Boolean flag: Debug all queries
	 * @var bool
	 */
	public static $debugQuery = false;

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
	private function __construct() {
		$this->query('SET NAMES utf8');
	}

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
	 * @param $addAccountId bool flag for automatic adding of accountid
	 * @return resource|bool   resource for 'SELECT' and otherwise true, false for errors 
	 */
	public function query($query, $addAccountId = true) {
		if (self::$debugQuery)
			Error::getInstance()->addDebug($query);

		$result = false;
		if ($addAccountId)
			$query = $this->addAccountId($query);

		$result = mysql_query($query)
			or Error::getInstance()->addError(mysql_error().' &lt;Query: '.$query.'&gt;', __FILE__, __LINE__);
		return $result;
	}

	/**
	 * Calls mysql_query and returns the result if there is any
	 * Be careful, unsafe query! Use $mysql->escape($values) and add accountid if needed
	 * @param $query string full mysql-query
	 * @return resource|bool   resource for 'SELECT' and otherwise true, false for errors 
	 */
	public function untouchedQuery($query) {
		if (self::$debugQuery)
			Error::getInstance()->addDebug($query);

		$result = false;

		$result = mysql_query($query)
			or Error::getInstance()->addError(mysql_error().' &lt;Query: '.$query.'&gt;', __FILE__, __LINE__);
		return $result;
	}

	/**
	 * Fetch from database without adding accountid
	 * @param string $query
	 * @return boolean|array
	 */
	public function untouchedFetch($query) {
		return $this->fetchAsCorrectType( $this->untouchedQuery($query) );
	}

	/**
	 *	Adds a WHERE clause to the Query.
	 *	@param $query string full mysql-query
	 *	@return string $query (MySQL query)
	 **/
	private function addAccountId($query) {
		if (!SessionHandler::isLoggedIn())
			return $query;

		$ID = SessionHandler::getId();

		if (strpos($query, 'SET NAMES') !== false || empty($ID))
			return $query;

		if (strpos($query, '`accountid`') === false && strpos($query, 'accountid=') === false) {
			if (strpos($query, 'WHERE') >= 7) {
				return str_replace('WHERE', 'WHERE `accountid`="'.$ID.'" AND ', $query);
			} elseif (strpos($query, 'GROUP BY') >= 7) {
				return str_replace('GROUP BY', 'WHERE `accountid`="'.$ID.'" GROUP BY ', $query);
			} elseif (strpos($query, 'ORDER BY') >= 7) {
				return str_replace('ORDER BY', 'WHERE `accountid`="'.$ID.'" ORDER BY ', $query);
			} else {
				return $query.' WHERE `accountid`="'.$ID.'"';
			}
		}

		return $query;
	}
	/**
	 * Updates a table for a given ID.
	 * @param $table  string
	 * @param $id     int
	 * @param $column mixed  might be an array
	 * @param $value  mixed  might be an array
	 * @param $addAccountId bool flag for adding accountid
	 */
	public function update($table, $id, $column, $value, $addAccountId = true) {
		if (strncmp($table, PREFIX, strlen(PREFIX)) != 0)
			Error::getInstance()->addWarning('class::Mysql: Tablename should start with global prefix "'.PREFIX.'".');

		if ($table == PREFIX.'account')
			$addAccountId = false;

		if (is_array($column) && count($column) == count($value)) {
			$set = '';
			foreach ($column as $i => $col)
				$set .= '`'.$col.'`='.self::escape($value[$i], true, ($col=='clothes')).', ';
		} else {
			$set = '`'.$column.'`='.self::escape($value, true, ($column=='clothes')).', ';
		}

		$this->query('UPDATE `'.$table.'` SET '.substr($set,0,-2).' WHERE `id`="'.$id.'" LIMIT 1', $addAccountId);
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

		if ($table != PREFIX.'account' && !key_exists('accountid', $columns)) {
			$columns[] = 'accountid';
			$values[] = SessionHandler::getId();
		}

		foreach ($columns as $k => $v)
			$columns[$k] = '`'.$v.'`';
		$columns = implode(', ', $columns);
		$values = implode(', ', self::escape($values));

		if (!$this->query('INSERT INTO `'.$table.'` ('.$columns.') VALUES('.$values.')'))
			return false;

		return mysql_insert_id();
	}

	/**
	 * Fetch data from database as associative array
	 * @param string $query
	 * @return array $return[$i]['column']
	 */
	public function fetchAsArray($query) {
		return $this->fetch($query, false, true);
	}

	/**
	 * Fetch data from database as numeric array
	 * @param string $query
	 * @return array $return[$i]['column']
	 */
	public function fetchAsNumericArray($query) {
		return $this->fetch($query, false, true, true);
	}

	/**
	 * Fetch one single data from database as array
	 * @param string $query
	 * @return array $return['column']
	 */
	public function fetchSingle($query) {
		if (mb_substr($query, -7, 7) != 'LIMIT 1')
			$query .= ' LIMIT 1';

		return $this->fetch($query);
	}

	/**
	 * Fetches the row of an given $id or all rows of a $query
	 * @param string $table    name of table or whole query
	 * @param int|bool $id     Must not be set if first argument is a query (default: false), otherwise the ID. Can be 'LAST' to get the highest ID
	 * @param bool $as_array   Method returns always $return[$i]['column'] if true, default: false
	 * @param bool $numeric    Use mysql_fetch_row instead of mysql_fetch_assoc 
	 * @return array           For count($return)=1: $return['column'], otherwise: $return[$i]['column']. For count($return)=0 && !$as_array: false.
	 */
	public function fetch($table, $id = false, $as_array = false, $numeric = false) {
		$return = array();
		if ($id !== false && strncmp($table, PREFIX, strlen(PREFIX)) != 0)
			Error::getInstance()->addWarning('class::Mysql: Tablename should start with global prefix "'.PREFIX.'".');

		if ($id === false)
			$result = $this->query($table);
		elseif ($id === 'LAST')
			$result = $this->query('SELECT * FROM `'.$table.'` ORDER BY `id` DESC LIMIT 1');
		else
			$result = $this->query('SELECT * FROM `'.$table.'` WHERE `id`="'.$id.'" LIMIT 1');

		return $this->fetchAsCorrectType($result, $as_array, $numeric);
	}

	/**
	 * Fetch rows from result in given format
	 * @param mysql_result $result
	 * @param boolean $as_array [optional]
	 * @param boolean $numeric [optional]
	 * @return array|boolean 
	 */
	public function fetchAsCorrectType($result, $as_array = false, $numeric = false) {
		$return = array();

		if ($result === false) {
			return $return;
		} else {
			if ($numeric)
				while($data = mysql_fetch_array($result, MYSQL_NUM))
					$return[] = $data;
			else
				while($data = mysql_fetch_array($result, MYSQL_ASSOC))
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
	 * @param $forceAsString bool
	 * @return mixed        safe value(s)
	 */
	public static function escape($values, $quotes = true, $forceAsString = false) {
		if (is_array($values)) {
			foreach ($values as $key => $value)
				$values[$key] = self::escape($value, $quotes);
		} else if (is_bool($values)) {
			$values = $values ? 1 : 0;
		} else if (is_numeric($values)) {
			$values = $values;
		} else if ($values === null || $values == 'NULL') {
			$values = 'NULL';
		} else if (!is_numeric($values) || $forceAsString) {
			$values = mysql_real_escape_string($values);
        	if ($quotes)
            	$values = '"'.$values.'"';
		}

		return $values;
	}
}
?>