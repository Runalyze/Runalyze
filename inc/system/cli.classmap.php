<?php
/**
 * CLI-Script to generate classmap
 */
/**
 * Output php file containing classmap as <?php $CLASSMAP = array(...); ?>
 * @var string
 */
$OUTPUT_FILE = dirname(__FILE__).'/classmap.php';

/**
 * Root directory
 * @var string
 */
$ROOT_DIR = dirname(__FILE__).'/../';

/**
 * Generate classmap
 */
$counter  = 0;
$classmap = "<?php\n\$CLASSMAP = array(\n"; scanDirectory($ROOT_DIR, $classmap, $counter); $classmap .= ");\n?>";
writeFile($OUTPUT_FILE, $classmap);

echo $counter.' classes found and written to '.$OUTPUT_FILE."\n";

/**
 * Write a file
 * @param string $fileName relative to FRONTEND_PATH
 * @param string $fileContent 
 */
function writeFile($fileName, $fileContent) {
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
?>