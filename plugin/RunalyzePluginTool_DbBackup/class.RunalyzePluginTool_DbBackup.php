<?php
/**
 * This file contains the class of the RunalyzePluginTool "DbBackup".
 */
$PLUGINKEY = 'RunalyzePluginTool_DbBackup';
/**
 * Class: RunalyzePluginTool_DbBackup
 * 
 * @author Hannes Christiansen <mail@laufhannes.de>
 * @version 1.0
 * @uses class::Plugin
 * @uses class::PluginTool
 * @uses class::Mysql
 * @uses class::Helper
 */
class RunalyzePluginTool_DbBackup extends PluginTool {
	/**
	 * Path for all backups, relative to FRONTEND_PATH
	 * @var string
	 */
	protected $BackupPath = '../plugin/RunalyzePluginTool_DbBackup/backup/';

	/**
	 * Initialize this plugin
	 * @see PluginPanel::initPlugin()
	 */
	protected function initPlugin() {
		$this->type = Plugin::$TOOL;
		$this->name = 'Datenbank-Backup';
		$this->description = 'Dieses Plugin sichert die komplette Datenbank, um sie sp&auml;ter wieder einspielen zu k&ouml;nnen.';
	}

	/**
	 * Set default config-variables
	 * @see PluginPanel::getDefaultConfigVars()
	 */
	protected function getDefaultConfigVars() {
		$config = array();

		return $config;
	}

	/**
	 * Display the content
	 * @see PluginPanel::displayContent()
	 */
	protected function displayContent() {
		if (isset($_GET['backup'])) {
			$this->createBackup();

			echo '<em>Die Datenbank wurde erfolgreich gespeichert.</em>';
		} else {
			echo '<ul>';
			echo '<li>'.self::getLink('<strong>Datenbank speichern</strong>', 'backup=true').'</li>'.NL;
			echo '</ul>'.NL;
		}

		$this->listFiles();
	}

	/**
	 * List all files
	 */
	protected function listFiles() {
		$Files = array();
		if ($handle = opendir(FRONTEND_PATH.$this->BackupPath)) {
			while (false !== ($file = readdir($handle))) {
				if (substr($file,0,1) != ".") {
					$Files[] = $file;
				}
			}

			closedir($handle);
		}

		echo HTML::br();
		echo '<strong>Vorhandene Backups:</strong>';

		if (empty($Files)) {
			echo '<em>keine</em>';
			return;
		}

		echo '<ul>';
		foreach ($Files as $File)
			if(USER_MUST_LOGIN) {
				if(strpos($File, SessionAccountHandler::getId()."-runalyze-backup-")!==false)
					echo '<li><a href="inc/'.$this->BackupPath.$File.'" target="_blank">'.$File.'</a></li>';
			} else 
				echo '<li><a href="inc/'.$this->BackupPath.$File.'" target="_blank">'.$File.'</a></li>';
			
			
		echo '</ul>';
	}

	/**
	 * Create backup
	 */
	protected function createBackup() {
		$Backup = '';
		$Mysql  = Mysql::getInstance();
		$Tables = $Mysql->untouchedFetchArray('SHOW TABLES');

		foreach ($Tables as $ATable) {
			foreach($ATable as $Table) {
				$TableName    = $Table;

				$CreateResult = $Mysql->untouchedFetchArray('SHOW CREATE TABLE '.$TableName);
				
				
				if($TableName == "runalyze_account" && USER_MUST_LOGIN)
					$ArrayOfRows  = $Mysql->fetchAsNumericArray('SELECT * FROM '.$TableName.' WHERE id = \''.SessionAccountHandler::getId().'\'');
				else 
					$ArrayOfRows  = $Mysql->fetchAsNumericArray('SELECT * FROM '.$TableName);

				$Backup .= 'DROP TABLE IF EXISTS '.$TableName.';'.NL.NL;
				$Backup .= $CreateResult[0]['Create Table'].';'.NL.NL;

				foreach ($ArrayOfRows as $Row) {
					$Values = implode(',', array_map('DB_BACKUP_mapperForValues', $Row));
					$Backup .= 'INSERT INTO '.$TableName.' VALUES('.$Values.');'.NL;
				} 

				$Backup .= NL.NL.NL;
			}
		}
		if(USER_MUST_LOGIN) 
			$File = fopen(FRONTEND_PATH.$this->BackupPath.SessionAccountHandler::getId().'-runalyze-backup-'.date("Ymd-Hi").'-'.substr(uniqid(rand()), -4).'.sql.gz', 'w+');
		else
			$File = fopen(FRONTEND_PATH.$this->BackupPath.'runalyze-backup-'.date("Ymd-Hi").'-'.substr(uniqid(rand()), -4).'.sql.gz', 'w+');
		fwrite($File, gzencode($Backup));
		fclose($File);
	}
}

/**
 * Mapper for values of a row
 * @param string $v
 * @return string
 */
function DB_BACKUP_mapperForValues($v) {
	return '"'.str_replace("\n", "\\n", addslashes($v)).'"';
}
?>