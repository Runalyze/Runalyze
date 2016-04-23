<?php
/**
 * Class: RunalyzeBackupFile
 * @author Michael Pohl
 * @package Runalyze\Plugins\Tools
 */

class RunalyzeBackupFileHandler {
    
	/**
	 * Path for all backups, relative to FRONTEND_PATH
	 * @var string
	 */
	static $BackupPath = '../data/backup-tool/backup/';
	
	public static function download($backup) {
	    if (self::validateFile($backup)) {
		$file = FRONTEND_PATH.self::$BackupPath.$backup;
		$fp = fopen($file, 'rb');

		header("Content-Type: application/x-gzip");
		header("Content-Length: " . filesize($file));
		header("Content-Disposition: attachment; filename= ".$backup);

		fpassthru($fp);
		exit;

	    }

	}
	
	public static function delete($backup) {
	    $file = FRONTEND_PATH.self::$BackupPath.$backup;
	    if (self::validateFile($backup) && file_exists($file)) {
		unlink($file);
	    }
	}
	
	public static function validateFile($backup) {
	    if (preg_match('#^'.SessionAccountHandler::getId().'-.*(json.gz|sql.gz)#', $backup) === 1 && !(strpos($backup, '..'))) {
		return true;
	    } else {
		return false;
	    }
	}

    
}