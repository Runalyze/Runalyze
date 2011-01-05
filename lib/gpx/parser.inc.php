<?php
function tcxLoad($url) {
	return xml2array(getFileContent($url));
}

function getFileContent($url) {
	if (!($f = fopen($url, "r")))
		die('Can\'t open '.$url);
	while ($line = fread($f, 4096))
		$content .= $line;
	fclose($f);
	return $content;
}

function xml2array($contents) {
	if (!$contents)
		die('No $contents given for xml2array().');
		
	if (!function_exists('xml_parser_create'))
		die('The php-function xml_parser_create() doesn\' exist.');

	$parser = xml_parser_create();
	xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0);
	xml_parser_set_option($parser, XML_OPTION_SKIP_WHITE, 1);
	xml_parse_into_struct($parser, $contents, $xml_values);
	xml_parser_free($parser);

	if (!$xml_values)
		die('No values find in xml-file.');

	//Initializations
	$xml_array = array();
	$parents = array();
	$opened_tags = array();
	$arr = array();
	$current = &$xml_array;

	//Go through the tags.
	foreach($xml_values as $data) {
		unset($attributes, $value);

		// Extracts data to: tag(string), type(string), level(int), attributes(array).
		extract($data);
		$result = '';
		$tag = strtolower($tag);

		$result = array();
		if (isset($value))
			$result['value'] = $value;

		if (isset($attributes))
			foreach($attributes as $attr => $val)
				if($get_attributes == 1)
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

		} elseif($type == 'close') //End of tag '</tag>'
			$current = &$parent[$level-1];
	}

	return($xml_array);
}