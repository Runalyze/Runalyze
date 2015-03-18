<?php
/**
 * This file contains the class of the RunalyzePluginTool "DbBackup".
 * @package Runalyze\Plugins\Tools
 */
$PLUGINKEY = 'RunalyzePluginTool_DbBackup';
/**
 * Class: RunalyzePluginTool_DbBackup
 * @author Hannes Christiansen
 * @package Runalyze\Plugins\Tools
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
	const TYPE_JSON = 1;

	/**
	 * Export type: *.sql.gz
	 * @var enum
	 */
	const TYPE_SQL = 2;

	/**
	 * ImportData: json 
	 * @var array
	 */
	protected $ImportData = array();

	/**
	 * ImportData Replaces Array 
	 * @var array
	 */
	protected $ImportReplace = array();

	/**
	 * Boolean flag: import on progress
	 * @var boolean
	 */
	protected $importIsOnProgress = false;

	/**
	 * Name
	 * @return string
	 */
	final public function name() {
		return __('Database import/export');
	}

	/**
	 * Description
	 * @return string
	 */
	final public function description() {
		return __('This plugin allows you to import and export your complete data from the database.');
	}

	/**
	 * Initialize this plugin
	 * @see PluginPanel::initPlugin()
	 */
	protected function initPlugin() {
		$this->fileNameStart = SessionAccountHandler::getId().'-runalyze-backup';

		if (isset($_GET['json'])) {
			if (move_uploaded_file($_FILES['qqfile']['tmp_name'], realpath(dirname(__FILE__)).'/import/'.$_FILES['qqfile']['name'])) {
				Error::getInstance()->footer_sent = true;
				echo '{"success":true}';
			} else {
				echo '{"error":"Moving file did not work. Set chmod 777 for '.realpath(dirname(__FILE__)).'/import/"}';
			}

			exit;
		}
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
		if (isset($_GET['file']) || isset($_POST['file'])) {
			$this->importIsOnProgress = true;
		}

		if (isset($_POST['backup'])) {
			if ($_POST['export-type'] == 'json')
				$this->createBackupJSON();
			else
				$this->createBackupSQL();
		}
	}

	/**
	 * Display export form 
	 */
	protected function displayExport() {
		$Select = new FormularSelectBox('export-type', __('File format'));
		$Select->addOption('json', __('Portable backup').' (*.json.gz)');
		$Select->addOption('sql', __('Database backup').' (*.sql.gz)');

		$Fieldset = new FormularFieldset( __('Export your data') );
		$Fieldset->addField($Select);
		$Fieldset->addField(new FormularSubmit(__('Create file'), ''));
		$Fieldset->setLayoutForFields( FormularFieldset::$LAYOUT_FIELD_W50 );
		$Fieldset->addInfo('<strong>'.__('JSON-format').' (*.json.gz)</strong><br>
			<small>'.
				__('Portable backup of your configuration and data -'.
					'This file can be imported into any other installation, using this plugin.<br />'.
					'This way you can transfer your data from to local to an online installation and back.').'
			</small>');
		$Fieldset->addInfo('<strong>'.__('SQL-format').' (*.sql.gz)</strong><br>
			<small>'.
				__('Backup of the complete database -'.
					'This file can be imported manually with e.g. PHPMyAdmin into any database.<br />'.
					'This is recommended to create a backup copy or to import your data into a new installation.').'
			</small>');

		if ($this->importIsOnProgress)
			$Fieldset->setCollapsed();

		$Formular = new Formular( $_SERVER['SCRIPT_NAME'].'?id='.$this->id() );
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
		if (isset($_GET['file'])) {
			$this->displayImportForm();
		} else {
			if (isset($_POST['file'])) {
				$this->displayImportFinish();
			}

			$this->displayImportUploader();
		}
	}

	/**
	 * Display import form 
	 */
	protected function displayImportForm() {
		$Fieldset = new FormularFieldset( __('Import file') );

		$Formular = new Formular( $_SERVER['SCRIPT_NAME'].'?id='.$this->id() );
		$Formular->setId('import-json-form');
		$Formular->addCSSclass('ajax');
		$Formular->addCSSclass('no-automatic-reload');
		$Formular->addHiddenValue('file', $_GET['file']);

		if (substr($_GET['file'], -8) != '.json.gz') {
			$Fieldset->addError( __('You can only import *.json.gz-files.'));

			Filesystem::deleteFile('../plugin/'.$this->key().'/import/'.$_GET['file']);
		} else {
			require_once __DIR__.'/class.RunalyzeJsonAnalyzer.php';

			$Analyzer = new RunalyzeJsonAnalyzer('../plugin/'.$this->key().'/import/'.$_GET['file']);

			if ($Analyzer->fileIsOkay()) {
				$Fieldset->addField( new FormularCheckbox('overwrite_config', __('Overwrite general configuration'), true) );
				$Fieldset->addField( new FormularCheckbox('overwrite_dataset', __('Overwrite dataset configuration'), true) );
				$Fieldset->addField( new FormularCheckbox('overwrite_plugin', __('Overwrite plugins'), true) );
				$Fieldset->addField( new FormularCheckbox('delete_trainings', __('Delete all old activities'), false) );
				$Fieldset->addField( new FormularCheckbox('delete_user_data', __('Delete all old body values'), false) );
				$Fieldset->addField( new FormularCheckbox('delete_shoes', __('Delete all old shoes'), false) );

				$Fieldset->addFileBlock( sprintf( __('There are <strong>%s</strong> activities in this file.'), $Analyzer->count('runalyze_training')) );
				$Fieldset->addFileBlock( sprintf( __('There are <strong>%s</strong> shoes in this file.'), $Analyzer->count('runalyze_shoe')) );
				$Fieldset->addFileBlock( sprintf( __('There are <strong>%s</strong> body values in this file.'), $Analyzer->count('runalyze_user')) );

				$Fieldset->setLayoutForFields(FormularFieldset::$LAYOUT_FIELD_W100);

				$Formular->addSubmitButton( __('Import') );
			} else {
				$Fieldset->addError( __('The file seems to be corrupted.') );

				foreach ($Analyzer->errors() as $Error)
					$Fieldset->addError($Error);
			}
		}

		$Formular->addFieldset($Fieldset);
		$Formular->display();
	}

	/**
	 * Display form: import finished 
	 */
	protected function displayImportFinish() {
		require_once __DIR__.'/class.RunalyzeBulkInsert.php';
		require_once __DIR__.'/class.RunalyzeJsonImporterResults.php';
		require_once __DIR__.'/class.RunalyzeJsonImporter.php';

		$fileName = '../plugin/'.$this->key().'/import/'.$_POST['file'];
		$Importer = new RunalyzeJsonImporter($fileName);
		$Importer->importData();

		Filesystem::deleteFile($fileName);

		Ajax::setReloadFlag(Ajax::$RELOAD_ALL);

		$Fieldset = new FormularFieldset( __('Import data') );
		$Fieldset->addText( __('All data have been imported.') );
		$Fieldset->addText( __('It is recommended to use the <em>Database cleanup</em> tool.') );
		$Fieldset->addInfo( $Importer->resultsAsString() );
		$Fieldset->addBlock(Ajax::getReloadCommand());

		$Formular = new Formular();
		$Formular->setId('import-finished');
		$Formular->addFieldset($Fieldset);
		$Formular->display();
	}

	/**
	 * Display uploader 
	 */
	protected function displayImportUploader() {
		$JScode = '
			new qq.FineUploaderBasic({
				button: $("#file-upload")[0],
				request: {
					endpoint: \''.$_SERVER['SCRIPT_NAME'].'?hideHtmlHeader=true&id='.$this->id().'&json=true\'
				},
				callbacks: {
					onError: function(id, name, errorReason, xhr) {
						$("#upload-container").append(\'<p class="error appended-by-uploader">\'+errorReason+\'</p>\').removeClass("loading");
					},
					onSubmit: function(id, fileName) {
						$("#upload-container").addClass("loading");
					},
					onComplete: function(id, fileName, responseJSON) {
						$(".appended-by-uploader").remove();
						$("#pluginTool").loadDiv(\''.$_SERVER['SCRIPT_NAME'].'?id='.$this->id().'&file=\'+encodeURIComponent(fileName));

						if (!responseJSON.success) {
							if (responseJSON.error == "")
								responseJSON.error = \'An unknown error occured.\';
							$("#pluginTool").append(\'<p class="error appended-by-uploader">\'+fileName+\': \'+responseJSON.error+\'</p>\');
							$("#upload-container").removeClass("loading");
						}
					}
				}
			});';

		$Text = '<div id="upload-container" style="margin-bottom:5px;"><div class="c button" id="file-upload">'.__('Upload file').'</div></div>';
		$Text .= Ajax::wrapJSasFunction($JScode);
		$Text .= HTML::info( __('Allowed file extension: *.json.gz') );
		$Text .= HTML::warning( __('The file has to be created with the same version of Runalyze!<br>'.
									'You won\'t be able to import a file from an older version.') );
		$Text .= HTML::warning( __('The importer will not change existing data for equipment, sport types or activity types.<br>'.
									'You have to make these changes by hand or delete the existing data in advance.') );

		$Fieldset = new FormularFieldset( __('Import data') );
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

		$Fieldset = new FormularFieldset( __('Export data') );

		if (empty($ListOfFiles)) {
			$Fieldset->addFileBlock('<em>You did not export anything.</em>');
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

		if ($this->importIsOnProgress)
			$Fieldset->setCollapsed();

		$Formular = new Formular();
		$Formular->setId('backup-list');
		$Formular->addFieldset($Fieldset);
		$Formular->display();
	}

	/**
	 * Get array with all existing 
	 * @return array 
	 */
	protected function getExistingFiles() {
		$Files = array();
		if ($handle = opendir(FRONTEND_PATH.$this->BackupPath)) {
			while (false !== ($file = readdir($handle))) {
				if (substr($file,0,1) != ".") {
					if (strpos($file, $this->fileNameStart) === 0)
						$Files[] = $file;
				}
			}

			closedir($handle);
		}

		sort($Files);

		return $Files;
	}
	/**
	 * Create backup: JSON
	 */
	protected function createBackupJSON() {
		require_once __DIR__.'/class.RunalyzeBackup.php';
		require_once __DIR__.'/class.RunalyzeJsonBackup.php';

		$Backup = new RunalyzeJsonBackup($this->getFileName(self::TYPE_JSON));
		$Backup->run();
	}

	/**
	 * Create backup: SQL
	 */
	protected function createBackupSQL() {
		require_once __DIR__.'/class.RunalyzeBackup.php';
		require_once __DIR__.'/class.RunalyzeSqlBackup.php';

		$Backup = new RunalyzeSqlBackup($this->getFileName(self::TYPE_SQL));
		$Backup->run();
	}

	/**
	 * Get filename
	 * @param enum $Type
	 * @return string
	 */
	protected function getFileName($Type) {
		if ($Type == self::TYPE_SQL) {
			$FileType = '.sql.gz';
		} else if ($Type == self::TYPE_JSON) {
			$FileType = '.json.gz';
		}

		return $this->BackupPath.$this->fileNameStart.'-'.date('Ymd-Hi').'-'.substr(uniqid(rand()),-4).$FileType;
	}
}