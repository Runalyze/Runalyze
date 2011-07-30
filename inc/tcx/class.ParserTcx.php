<?php
/**
 * This file contains the class parsing tcx-files.
 */
/**
 * Class: ParserTcx
 * 
 * @author Hannes Christiansen <mail@laufhannes.de>
 * @version 1.0
 * @uses class::Mysql
 * @uses class:Error
 *
 * Last modified 2011/07/09 14:30 by Hannes Christiansen
 */

class ParserTcx {
	/**
	 * File path to load
	 * @var string
	 */
	private $file_path = NULL;

	/**
	 * Content of the loaded file
	 * @var string
	 */
	private $content = NULL;

	/**
	 * Constructor
	 * @param string $xml [optional] Otherwise use ->loadFile($file_path)
	 */
	public function __construct($xml = '') {
		if ($xml != '')
			$this->content = $xml;
	}

	/**
	 * Get the content as parsed array
	 * @return array
	 */
	public function getContentAsArray() {
		return $this->xml2Array();
	}

	/**
	 * Load file and get contents
	 * @param string $file_path
	 */
	public function loadFile($file_path) {
		$this->file_path = $file_path;

		if ($this->file_path == NULL || !($file = fopen($this->file_path, 'r'))) {
			Error::getInstance()->addError('Parser is unable to find the given file ('.$this->file_path.').');
			return;
		}

		while ($line = fread($file, 4096))
			$this->content .= $line;

		fclose($file);
	}

	/**
	 * Parse internal content and transform to array
	 * @return array
	 */
	private function xml2Array() {
		$Values = $this->getXmlValues();

		//Initializations
		$xml_array = array();
		$parents = array();
		$opened_tags = array();
		$arr = array();
		$current = &$xml_array;

		//Go through the tags.
		foreach ($Values as $data) {
			unset($attributes, $value);
	
			// Extracts data to: tag(string), type(string), level(int), attributes(array).
			extract($data);
			$result = '';
			$tag = strtolower($tag);
	
			$result = array();
			if (isset($value))
				$result['value'] = $value;
	
			if (isset($attributes))
				foreach ($attributes as $attr => $val)
					$result['attr'][$attr] = $val;
	
			if ($type == "open") { //The starting of the tag '<tag>'
				$parent[$level-1] = &$current;
				if (!is_array($current) || (!in_array($tag, array_keys($current)))) { //Insert New tag
					$current[$tag] = $result;
					$current = &$current[$tag];
				} else { //There was another element with the same tag name
					if (isset($current[$tag][0]))
						array_push($current[$tag], $result);
					else
						$current[$tag] = array($current[$tag],$result);
					$last = count($current[$tag]) - 1;
					$current = &$current[$tag][$last];
				}
			} elseif ($type == "complete") { //Tags that ends in 1 line '<tag />'
				if (!isset($current[$tag]))
					$current[$tag] = $result;
				else {
					if ((is_array($current[$tag]) && $get_attributes == 0)
							|| (isset($current[$tag][0]) && is_array($current[$tag][0]) && $get_attributes == 1))
						array_push($current[$tag],$result);
					else
						$current[$tag] = array($current[$tag],$result);
				}
	
			} elseif ($type == 'close') //End of tag '</tag>'
				$current = &$parent[$level-1];
		}

		return $xml_array;
	}

	/**
	 * Parse given content with PHP-internal XML-Parser
	 * @return array
	 */
	private function getXmlValues() {
		if (!$this->content) {
			Error::getInstance()->addError('Nothing to parse: Content of loaded file is empty.');
			return array();
		} elseif (!function_exists('xml_parser_create')) {
			Error::getInstance()->addError('PHP-internal function \'xml_parser_create\' is not available.');
			return array();
		}

		$Values = array();
		$Parser = xml_parser_create();
		xml_parser_set_option($Parser, XML_OPTION_CASE_FOLDING, 0);
		xml_parser_set_option($Parser, XML_OPTION_SKIP_WHITE, 1);
		xml_parse_into_struct($Parser, $this->content, $Values);
		xml_parser_free($Parser);

		return $Values;
	}
}
?>