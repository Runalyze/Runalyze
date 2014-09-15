<?php
/**
 * This file contains class::ParameterSelectFile
 * @package Runalyze\Parameter
 */
/**
 * Select file
 * @author Hannes Christiansen
 * @package Runalyze\Parameter
 */
class ParameterSelectFile extends ParameterSelect {
	/**
	 * Construct
	 * @param string $default
	 * @param array $options [optional]
	 */
	public function __construct($default, $options = array()) {
		$options = array_merge(
			array(
				'folder' => '',
				'extensions' => array()
			),
			$options
		);

		parent::__construct($default, $options);
	}

	/**
	 * Allowed file extensions
	 * @return array
	 */
	public function extensions() {
		return $this->Options['extensions'];
	}

	/**
	 * File allowed?
	 * @param string $fileName
	 * @return bool
	 */
	protected function valueIsAllowed($fileName) {
		$firstChar = substr($fileName, 0, 1);

		if ($firstChar == '.' || $firstChar == '/')
			return false;

		if (!in_array(pathinfo($fileName, PATHINFO_EXTENSION), $this->Options['extensions']))
			return false;

		return true;
	}
}