<?php
/**
 * This file contains the class of the RunalyzePluginTool "DbBackup".
 */
$PLUGINKEY = 'RunalyzePluginTool_DbBackup';
/**
 * Class: RunalyzePluginTool_DbBackup
 * @author Hannes Christiansen <mail@laufhannes.de>
 */
class RunalyzePluginTool_DbBackup extends PluginTool {
	/**
	 * Start of backup-files
	 * @var string 
	 */
	protected $fileNameStart = '';

	/**
	 * Path for all backups, relative to FRONTEND_PATH
	 * @var string
	 */
	protected $BackupPath = '../plugin/RunalyzePluginTool_DbBackup/backup/';

	/**
	 * Export type: *.json
	 * @var enum
	 */
	static private $TYPE_JSON = 1;

	/**
	 * Export type: *.sql.gz
	 * @var enum
	 */
	static private $TYPE_SQL = 2;

	/**
	 * Initialize this plugin
	 * @see PluginPanel::initPlugin()
	 */
	protected function initPlugin() {
		$this->type = Plugin::$TOOL;
		$this->name = 'Datenbank-Import/Export';
		$this->description = 'Dieses Plugin sichert die komplette Datenbank und kann ein vorhandenes Backup importieren.';

		$this->fileNameStart = SessionAccountHandler::getId().'-runalyze-backup';

		if (isset($_GET['json'])) {
			move_uploaded_file($_FILES['userfile']['tmp_name'], realpath(dirname(__FILE__)).'/'.$_FILES['userfile']['name']);
			Error::getInstance()->footer_sent = true;
			echo 'success';
			exit;
		}
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
		$this->handleRequest();
		$this->displayExport();
		$this->displayImport();
		$this->displayList();
	}

	/**
	 * Handle request 
	 */
	protected function handleRequest() {
		if (isset($_GET['file'])) {
			// TODO: Import json
		}

		if (isset($_POST['backup'])) {
			if ($_POST['export-type'] == 'json')
				$this->createBackup( self::$TYPE_JSON );
			else
				$this->createBackup( self::$TYPE_SQL );
		}
	}

	/**
	 * Display export form 
	 */
	protected function displayExport() {
		$Select = new FormularSelectBox('export-type', 'Dateiformat');
		$Select->addOption('json', 'Portables Backup (*.json)');
		$Select->addOption('sql', 'Datenbank Backup (*.sql.gz)');

		$Fieldset = new FormularFieldset('Daten exportieren');
		$Fieldset->addField($Select);
		$Fieldset->addField(new FormularSubmit('Datei erstellen', ''));
		$Fieldset->setLayoutForFields( FormularFieldset::$LAYOUT_FIELD_W50 );
		$Fieldset->addInfo('<strong>JSON-Format (*.json)</strong><br />
			<small>
				Portables Backup deiner Einstellungen und Daten -
				Die Datei kann &uuml;ber dieses Plugin in eine bestehende Installation importiert werden.
				Dabei werden alle Einstellungen und Daten deines Accounts &uuml;berschrieben.<br />
				Dieser Export/Import ist sinnvoll, um deine Daten von einer Runalyze-Installation (z.B. lokal)
				in eine andere (z.B. Online-Version) zu verschieben.
			</small>');
		$Fieldset->addInfo('<strong>SQL-Format (*.sql.gz)</strong><br />
			<small>
				Backup der gesamten Datenbank -
				Die Datei kann manuell &uuml;ber einen PhpMyAdmin eingelesen werden.
				Dabei werden alle Daten &uuml;berschrieben.<br />
				Dieser Export/Import ist sinnvoll, um eine Sicherheitskopie zu erstellen oder die Daten
				in eine Neuinstallation einzuf&uuml;gen.
			</small>');

		$Formular = new Formular( $_SERVER['SCRIPT_NAME'].'?id='.$this->id );
		$Formular->setId('database-backup');
		$Formular->addCSSclass('ajax');
		$Formular->addCSSclass('no-automatic-reload');
		$Formular->addFieldset($Fieldset);
		$Formular->addHiddenValue('backup', 'true');
		$Formular->display();
	}

	/**
	 * Display import form
	 */
	protected function displayImport() {
		$JScode = '
			$("#file-upload").removeClass("hide");
			new AjaxUpload(\'#file-upload\', {
				allowedExtensions: [\'json\'],
				action: \''.$_SERVER['SCRIPT_NAME'].'?hideHtmlHeader=true&id='.$this->id.'&json=true\',
				onSubmit : function(file, extension){ $("#upload-container").addClass(\'loading\'); },
				onComplete : function(file, response){
					if (response.substring(0,7) == \'success\')
						$("#ajax").loadDiv(\''.$_SERVER['SCRIPT_NAME'].'?id='.$this->id.'&file=\'+encodeURIComponent(file));
					else
						$("#ajax").append(\'<p class="error">An unknown error occured.</p>\');
				}		
			});';

		$Text = '<div id="upload-container" style="margin-bottom:5px;"><div class="c button small hide" id="file-upload">Datei hochladen</div></div>';
		$Text .= Ajax::wrapJSasFunction($JScode);
		$Text .= HTML::info('Unterst&uuml;tzte Formate: *.json');

		$Fieldset = new FormularFieldset('Daten importieren');
		$Fieldset->setCollapsed();
		$Fieldset->addBlock($Text);

		$Formular = new Formular();
		$Formular->setId('backup-import');
		$Formular->addFieldset($Fieldset);
		$Formular->display();
	}

	/**
	 * Display list with files 
	 */
	protected function displayList() {
		$ListOfFiles = $this->getExistingFiles();

		$Fieldset = new FormularFieldset('Exportierte Daten');

		if (empty($ListOfFiles)) {
			$Fieldset->addFileBlock('<em>Es wurden noch keine Daten exportiert</em>');
		} else {
			foreach ($ListOfFiles as $File) {
				$String = '';

				$FileNameParts = explode('-', $File);
				$Year          = isset($FileNameParts[3]) ? $FileNameParts[3] : '';
				if (strlen($Year) == 8)
					$String .= '<strong>'.substr($Year, 6, 2).'.'.substr($Year, 4, 2).'.'.substr($Year, 0, 4).':</strong> ';

				$String .= $File;
				$String .= ', '.Filesystem::getFilesize(FRONTEND_PATH.$this->BackupPath.$File);

				$Fieldset->addFileBlock('<a href="inc/'.$this->BackupPath.$File.'" target="_blank">'.$String.'</a>');
			}
		}

		$Formular = new Formular();
		$Formular->setId('backup-list');
		$Formular->addFieldset($Fieldset);
		$Formular->display();
	}

	/**
	 * Get array with all existing 
	 * @return type 
	 */
	protected function getExistingFiles() {
		$Files = array();
		if ($handle = opendir(FRONTEND_PATH.$this->BackupPath)) {
			while (false !== ($file = readdir($handle))) {
				if (substr($file,0,1) != ".") {
					if (strpos($file, $this->fileNameStart) !== false)
						$Files[] = $file;
				}
			}

			closedir($handle);
		}

		return $Files;
	}

	/**
	 * Create backup
	 * @param enum $Type
	 */
	protected function createBackup($Type) {
		$ExportData   = array();
		$ExportString = '';
		$Mysql        = Mysql::getInstance();
		$AllTables    = $Mysql->untouchedFetchArray('SHOW TABLES');

		foreach ($AllTables as $Tables) {
			foreach ($Tables as $Table) {
				$TableName    = $Table;
				$CreateResult = $Mysql->untouchedFetchArray('SHOW CREATE TABLE '.$TableName);
				$Query        = 'SELECT * FROM `'.$TableName.'`';

				if ($TableName == "runalyze_account" && USER_MUST_LOGIN)
					$Query .= ' WHERE id="'.SessionAccountHandler::getId().'"';

				if ($Type == self::$TYPE_SQL) {
					$ArrayOfRows   = $Mysql->fetchAsNumericArray($Query);
					$ExportString .= 'DROP TABLE IF EXISTS '.$TableName.';'.NL.NL;
					$ExportString .= $CreateResult[0]['Create Table'].';'.NL.NL;

					foreach ($ArrayOfRows as $Row) {
						$Values        = implode(',', array_map('DB_BACKUP_mapperForValues', $Row));
						$ExportString .= 'INSERT INTO '.$TableName.' VALUES('.$Values.');'.NL;
					}

					$ExportString .= NL.NL.NL;
				} elseif ($Type == self::$TYPE_JSON) {
					$ArrayOfRows = $Mysql->fetchAsArray($Query);

					foreach ($ArrayOfRows as $Row) {
						$ExportData[$Table][$Row['id']] = $Row;
					}
				}
			}
		}

		if ($Type == self::$TYPE_SQL) {
			$ExportString = gzencode($ExportString);
			$FileType = '.sql.gz';
		} elseif ($Type == self::$TYPE_JSON) {
			$ExportString = gzencode(json_encode($ExportData));
			$FileType = '.json.gz';
		}

		$FileName = $this->BackupPath.$this->fileNameStart.'-'.date('Ymd-Hi').'-'.substr(uniqid(rand()),-4).$FileType;
		$File     = fopen(FRONTEND_PATH.$FileName, 'w+');

		fwrite($File, $ExportString);
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