<?php
/**
 * This file contains class::ConfigurationValueSelectFile
 * @package Runalyze\System\Configuration\Value
 */
/**
 * ConfigurationValueSelectFile
 * @author Hannes Christiansen
 * @package Runalyze\System\Configuration\Value
 */
class ConfigurationValueSelectFile extends ConfigurationValue {
	/**
	 * Set value
	 * @param mixed $value new value
	 * @throws InvalidArgumentException
	 */
	public function set($value) {
		if ($this->fileIsAllowed($value)) {
			parent::set($value);
		} else {
			throw new InvalidArgumentException('Invalid extention ("'.$value.'") for select file.');
		}
	}

	/**
	 * Get field, should be overwritten
	 * @return FormularInput 
	 */
	public function getField() {
		$Field  = new FormularSelectBox($this->getKey(), $this->getLabel(), $this->getValue());

		if (!is_array($this->Options['folder']))
			$this->Options['folder'] = array($this->Options['folder']);

		$Folder = $this->Options['folder'];
		foreach ($Folder as $dir) {
			$handle = opendir(FRONTEND_PATH.'../'.$dir);
			if ($handle) {
				while (false !== ($file = readdir($handle))) {
					if ($this->fileIsAllowed($file))
						$Field->addOption($dir.$file, $file);
				}
			}
		}

		if (!empty($this->Options['layout']))
			$Field->setLayout($this->Options['layout']);

		return $Field;
	}

	/**
	 * Is file allowed?
	 * @param string $fileName
	 * @return boolean
	 */
	private function fileIsAllowed($fileName) {
		$firstChar = substr($fileName, 0, 1);

		if ($firstChar == '.' || $firstChar == '/')
			return false;

		if (!in_array(pathinfo($fileName, PATHINFO_EXTENSION), $this->Options['extensions']))
			return false;

		return true;
	}
}