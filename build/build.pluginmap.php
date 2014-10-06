<?php
/**
 * CLI-Script to generate pluginmap
 */
if (!defined('RUNALYZE_BUILD'))
	die('You\'re not allowed to do that.');

/**
 * Output php file containing pluginmap as <?php $PLUGINMAP = array(...); ?>
 * @var string
 */
$OUTPUT_FILE = dirname(__FILE__).'/../inc/plugin/pluginmap.php';

/**
 * Root directory
 * @var string
 */
$ROOT_DIR = dirname(__FILE__).'/../plugin/';

/**
 * Generate pluginmap
 */
$counter  = 0;
$pluginmap = "<?php\n\$PLUGINMAP = array(\n"; scanDirectoryForPlugins($ROOT_DIR, $ROOT_DIR, $pluginmap, $counter); $pluginmap .= ");";
writeFileForPluginMap($OUTPUT_FILE, $pluginmap);

echo $counter.' plugins found and written to '.substr($OUTPUT_FILE, strlen($ROOT_DIR)-8)."\n";

/**
 * Write a file
 * @param string $fileName relative to FRONTEND_PATH
 * @param string $fileContent 
 */
function writeFileForPluginMap($fileName, $fileContent) {
	$file = fopen($fileName, "w");

	if ($file !== false) {
		fwrite($file, $fileContent);
		fclose($file);
	}
}

/**
 * Scan directory
 * @param string $dir
 * @param string $pluginmap
 * @param int $counter
 */
function scanDirectoryForPlugins($ROOT_DIR, $dir, &$pluginmap, &$counter) {
	$handle = opendir($dir);
	while ($file = readdir($handle)) {
		if ($file != '.' && $file != '..') {
			if (is_dir($dir.$file)) {
				scanDirectoryForPlugins($ROOT_DIR, $dir.$file.'/', $pluginmap, $counter);
			} else {
				if (substr($file, 0, 20) == 'class.RunalyzePlugin' && substr($file, -4) == '.php') {
					$counter++;
					$classname = substr( substr($file, 6), 0, -4 );
					$filename  = "../plugin/".substr( $dir.$file, strlen($ROOT_DIR) );
					$pluginmap .= "'$classname' => '$filename',\n";
				}
			}
		}
	}

	closedir($handle);
}