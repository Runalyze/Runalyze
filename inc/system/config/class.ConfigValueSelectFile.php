<?php
/**
 * This file contains class::ConfigValueSelectFile
 * @package Runalyze\System\Config
 */
/**
 * ConfigValueSelectFile
 * @author Hannes Christiansen
 * @package Runalyze\System\Config
 */
class ConfigValueSelectFile extends ConfigValue {
	/**
	 * Type - should be overwritten by subclass
	 * @var string
	 */
	protected $type = 'selectfile';

	/**
	 * Get value as string, should be overwritten
	 * @return string
	 */
	protected function getValueAsString() {
		return (string)$this->Value;
	}

	/**
	 * Set value from string, should be overwritten
	 * @param string $Value 
	 */
	protected function setValueFromString($Value) {
		$this->Value = (string)$Value;
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
		foreach ($Folder as $Fold) {
			$handle = opendir(FRONTEND_PATH.'../'.$Fold);
			if ($handle) {
				while (false !== ($file = readdir($handle))) {
					if (substr($file,0,1) != '.')
						$Field->addOption($Fold.$file, $file);
				}
			}
		}

		if (!empty($this->Options['layout']))
			$Field->setLayout($this->Options['layout']);

		return $Field;
	}
}