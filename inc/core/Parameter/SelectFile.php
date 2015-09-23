<?php
/**
 * This file contains class::SelectFile
 * @package Runalyze\Parameter
 */

namespace Runalyze\Parameter;

/**
 * Select file
 * @author Hannes Christiansen
 * @package Runalyze\Parameter
 */
class SelectFile extends Select {
	/**
	 * Boolean flag: allow uppercase variants of file extensions
	 * @var boolean
	 */
	protected $AllowUppercaseVariants = true;

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
	 * @param boolean $flag
	 */
	public function allowUppercaseVariants($flag = true) {
		$this->AllowUppercaseVariants = $flag;
	}

	/**
	 * File allowed?
	 * @param string $fileName
	 * @return bool
	 */
	protected function valueIsAllowed($fileName) {
		$firstChar = substr($fileName, 0, 1);
		$extension = pathinfo($fileName, PATHINFO_EXTENSION);

		if ($firstChar == '.' || $firstChar == '/')
			return false;

		if ($this->AllowUppercaseVariants) {
			$extension = strtolower($extension);
		}

		if (!in_array($extension, $this->Options['extensions']))
			return false;

		return true;
	}

	/**
	 * Options
	 * @return array
	 */
	public function options() {
		$Options = array();
		$Folder = $this->Options['folder'];

		if (!is_array($Folder)) {
			$Folder = array($Folder);
		}

		foreach ($Folder as $Fold) {
			$handle = opendir(FRONTEND_PATH.'../'.$Fold);
			if ($handle) {
				while (false !== ($file = readdir($handle))) {
					if ($this->valueIsAllowed($file)) {
						$Options[$Fold.$file] = $file;
					}
				}
			}
		}

		return $Options;
	}
}