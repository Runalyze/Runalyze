<?php
/**
 * This file contains class::ParameterSelectRow
 * @package Runalyze\Parameter
 */
/**
 * Select row from database
 * @author Hannes Christiansen
 * @package Runalyze\Parameter
 */
class ParameterSelectRow extends ParameterSelect {
	/**
	 * Construct
	 * @param string $default
	 * @param array $options [optional]
	 */
	public function __construct($default, $options = array()) {
		$options = array_merge(
			array(
				'table' => '',
				'column' => ''
			),
			$options
		);

		parent::__construct($default, $options);
	}

	/**
	 * Table
	 * @return string
	 */
	public function table() {
		return $this->Options['table'];
	}

	/**
	 * Column
	 * @return string
	 */
	public function column() {
		return $this->Options['column'];
	}

	/**
	 * Value allowed?
	 * @param string $value
	 * @return bool
	 */
	protected function valueIsAllowed($value) {
		return true;
	}
}