<?php
/**
 * CLI-Script to generate classmap
 */
if (!defined('RUNALYZE_BUILD'))
	die('You\'re not allowed to do that.');

echo 'Building classmap...'.PHP_EOL;

/**
 * Output php file containing classmap as <?php $CLASSMAP = array(...); ?>
 * @var string
 */
$OUTPUT_FILE = dirname(__FILE__).'/../inc/system/classmap.php';

/**
 * Root directory
 * @var string
 */
$ROOT_DIR = dirname(__FILE__).'/../inc/';

/**
 * Generate classmap
 */
$counter  = 0;
$classmap = "<?php\n\$CLASSMAP = array(\n"; scanDirectory($ROOT_DIR, $classmap, $counter); $classmap .= ");";
writeFileForClassmap($OUTPUT_FILE, $classmap);

echo $counter.' classes found and written to '.substr($OUTPUT_FILE, strlen($ROOT_DIR)-5)."\n";

/**
 * Write a file
 * @param string $fileName relative to FRONTEND_PATH
 * @param string $fileContent 
 */
function writeFileForClassmap($fileName, $fileContent) {
	$file = fopen($fileName, "w");

	if ($file !== false) {
		fwrite($file, $fileContent);
		fclose($file);
	}
}

/**
 * Scan directory
 * @param string $dir
 * @param string $classmap
 */
function scanDirectory($dir, &$classmap, &$counter) {
	global $ROOT_DIR;

	$handle = opendir($dir);
	while ($file = readdir($handle)) {
		if ($file != '.' && $file != '..') {
			if (is_dir($dir.$file)) {
				scanDirectory($dir.$file.'/', $classmap, $counter);
			} else {
				if (substr($file, 0, 6) == 'class.' && substr($file, -4) == '.php') {
					$counter++;
					$classname = substr( substr($file, 6), 0, -4 );
					$filename  = substr( $dir.$file, strlen($ROOT_DIR) );
					$classmap .= "'$classname' => '$filename',\n";
				}
			}
		}
	}

	closedir($handle);
}