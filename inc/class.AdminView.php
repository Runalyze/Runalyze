<?php
/**
 * This file contains class::AdminView
 * @package Runalyze\Frontend
 */
/**
 * Class for admin view
 *
 * @author Hannes Christiansen
 * @package Runalyze\Frontend
 */
class AdminView {
	/**
	 * User list
	 * @var array
	 */
	protected $UserList = array();

	/**
	 * Admin hash
	 * @var string
	 */
	private $adminHash = '';

	/**
	 * Is the administrator logged in?
	 * @var boolean
	 */
	private $isLoggedIn = false;

	/**
	 * Constructor
	 * @param string $adminHash
	 */
	public function __construct($adminHash) {
		$this->adminHash = $adminHash;

		$this->checkLogin();
		$this->init();
	}

	/**
	 * Check login
	 */
	private function checkLogin() {
		if (isset($_POST['password']) && $this->isAdminPassword($_POST['password']))
			$this->isLoggedIn = true;

		elseif (isset($_POST['hash']) && $this->isAdminHash($_POST['hash']))
			$this->isLoggedIn = true;

		elseif (isset($_POST['hash-files']) && $this->isAdminHash($_POST['hash-files']))
			$this->isLoggedIn = true;
	}

	/**
	 * Init
	 */
	private function init() {
		if (!$this->isLoggedIn)
			return;

		define('ADMIN_WINDOW', true);

		$this->handlePostData();

		$this->UserList = $this->getUserList();
	}

	/**
	 * Display
	 */
	public function display() {
		$title = 'Runalyze v'.RUNALYZE_VERSION;

		include 'inc/tpl/tpl.installerHeader.php';

		if (!$this->isLoggedIn)
			include 'inc/tpl/tpl.adminWindow.login.php';
		else
			$this->displayView();

		include 'inc/tpl/tpl.installerFooter.php';
	}

	/**
	 * Display view
	 */
	private function displayView() {
		$this->displaySettings();
		$this->displayUserList();
		$this->displayServerData();
		$this->displayPermissions();
		$this->displayFiles();
	}

	/**
	 * Display settings
	 */
	private function displaySettings() {
		$Formular = new Formular();
		$Formular->setId('admin-window-settings');
		$Formular->addHiddenValue('hash', $this->getAdminHash());
		$Formular->addFieldset( $this->getSettingsFieldset() );
		$Formular->display();
	}

	/**
	 * Display user list
	 */
	private function displayUserList() {
		$Formular = new Formular();
		$Formular->setId('admin-window-user');
		$Formular->addFieldset( $this->getUserListFieldset() );
		$Formular->display();
	}

	/**
	 * Display server data
	 */
	private function displayServerData() {
		$Formular = new Formular();
		$Formular->setId('admin-window-server');
		$Formular->addFieldset( $this->getServerDataFieldset() );
		$Formular->display();
	}

	/**
	 * Display files
	 */
	private function displayPermissions() {
		$Formular = new Formular();
		$Formular->setId('admin-permissions');
		$Formular->addFieldset( $this->getPermissionsFieldset() );
		$Formular->display();
	}

	/**
	 * Display files
	 */
	private function displayFiles() {
		$Formular = new Formular();
		$Formular->setId('admin-files');
		$Formular->addHiddenValue('hash-files', $this->getAdminHash());
		$Formular->addFieldset( $this->getFilesFieldset() );
		$Formular->display();
	}

	/**
	 * Get fieldset for settings
	 * @return \FormularFieldset
	 */
	private function getSettingsFieldset() {
		FormularInput::setStandardSize( FormularInput::$SIZE_MIDDLE );

		$Fieldset = new FormularFieldset('Einstellungen');
		$Fieldset->addField( new FormularCheckbox('RUNALYZE_DEBUG', 'Debug-Modus') );
		$Fieldset->addField( new FormularCheckbox('USER_CANT_LOGIN', 'Wartungsmodus') );
		$Fieldset->addField( new FormularCheckbox('USER_CAN_REGISTER', 'Benutzer k&ouml;nnen sich registrieren') );
		$Fieldset->addField( new FormularCheckbox('USER_MUST_LOGIN', 'Benutzer m&uuml;ssen sich einloggen') );
		$Fieldset->addField( new FormularInput('GARMIN_API_KEY', Ajax::tooltip('Garmin API-Key', 'In Online-Version notwendig f&uuml;r Garmin-Communicator<br />siehe http://developer.garmin.com/web-device/garmin-communicator-plugin/get-your-site-key/')) );
		$Fieldset->addField( new FormularInput('MAIL_SENDER', 'Absenderadresse f&uuml;r E-Mails') );
		$Fieldset->addField( new FormularSubmit('Speichern', '') );
		$Fieldset->setLayoutForFields( FormularFieldset::$LAYOUT_FIELD_W100 );

		if (!is_writable(FRONTEND_PATH.'../config.php'))
			$Fieldset->addError('Die Konfigurationsdatei <strong>config.php</strong> ist nicht beschreibbar. <em>(chmod = '.substr(decoct(fileperms(FRONTEND_PATH.'../config.php')),1).')</em><br />Änderungen können nicht gespeichert werden.');

		return $Fieldset;
	}

	/**
	 * Handle post data for updating settings
	 */
	private function handlePostData() {
		if (isset($_POST['hash']) && $this->isLoggedIn) {
			$this->updateConfigFileFromPost();
		} else {
			$this->setPostDataFromConfig();
		}

		if (isset($_POST['hash-files']) && $this->isLoggedIn) {
			$this->cleanFiles();
		}
	}

	/**
	 * Set post data from configuration
	 */
	private function setPostDataFromConfig() {
		$Variables = self::getArrayOfConfigVariables();

		foreach ($Variables as $Variable)
			$_POST[$Variable] = constant($Variable);
	}

	/**
	 * Update config file from post data
	 */
	private function updateConfigFileFromPost() {
		if (!is_writable(FRONTEND_PATH.'../config.php'))
			return;

		$Variables     = self::getArrayOfConfigVariables();
		$NewFile       = '';
		$FileHandleOld = fopen( FRONTEND_PATH.'../config.php', 'r' );

		while ($Line = fgets($FileHandleOld)) {
			$Match = array();
			preg_match("/^define\(\'(.*?)\', (.*?)\);/", $Line, $Match);

			if (!empty($Match) && in_array($Match[1], $Variables)) {
				$Value = isset($_POST[$Match[1]]) ? 'true' : 'false';

				if ($Value == 'true' && $_POST[$Match[1]] != 'on')
					$Value = '\''.$_POST[$Match[1]].'\'';

				$NewFile .= 'define(\''.$Match[1].'\', '.$Value.');'.NL;
			} else {
				$NewFile .= $Line;
			}
		}

		fclose($FileHandleOld);

		$FileHandleNew = fopen( FRONTEND_PATH.'../config.php', 'w' );
		fwrite($FileHandleNew, $NewFile);
		fclose($FileHandleNew);
	}

	/**
	 * Get fieldset for user list
	 * @return \FormularFieldset
	 */
	private function getUserListFieldset() {
		$Fieldset = new FormularFieldset('Benutzerliste');
		$Fieldset->setCollapsed();

		if (empty($this->UserList)) {
			$Fieldset->addWarning('Es ist noch niemand registriert.');
		} else {
			$Code = '
			<table class="small fullwidth zebra-style" id="userTable">
				<thead>
					<tr>
						<th>ID</th>
						<th>User</th>
						<th>Name</th>
						<th>E-Mail</th>
						<th class="{sorter: \'germandate\'}">seit</th>
						<th class="{sorter: \'germandate\'}">zuletzt</th>
						<!--<th class="{sorter: false}">Funktionen</th>-->
					</tr>
				</thead>
				<tbody>';
						//<th class="{sorter: \'x\'}">Anz.</th>
						//<th class="{sorter: \'distance\'}">km</th>

			foreach ($this->UserList as $User) {
				$Code .= '
					<tr>
						<td class="small r">'.$User['id'].'</td>
						<td>'.$User['username'].'</td>
						<td>'.$User['name'].'</td>
						<td class="small">'.$User['mail'].'</td>
						<td class="small c">'.date("d.m.Y", $User['registerdate']).'</td>
						<td class="small c">'.date("d.m.Y", $User['lastaction']).'</td>
						<!--<td>User aktivieren Neues Passwort zusenden</td>-->
					</tr>';
					//<td class="small r">'.$User['num'].'x</td>
					//<td class="small r">'.Running::Km($User['km']).'</td>
			}

			$Code .= '
				</tbody>
			</table>

			<div class="small">
				'.Ajax::getTablesorterWithPagerFor('#userTable').'
			</div>';

			$Fieldset->addBlock($Code);
		}

		return $Fieldset;
	}

	/**
	 * Get fieldset for server data
	 * @return \FormularFieldset
	 */
	private function getServerDataFieldset() {
		$Fieldset = new FormularFieldset('Serverdaten');
		$Fieldset->addSmallInfo('Derzeit l&auml;uft PHP '.PHP_VERSION);
		$Fieldset->addSmallInfo('Es l&auml;uft MySQL '.@mysql_get_server_info());
		$Fieldset->addSmallInfo('Zeit-Limit: '.ini_get('max_execution_time'));
		$Fieldset->addSmallInfo('Memory-Limit: '.ini_get('memory_limit'));
		$Fieldset->addSmallInfo('Upload-Limit: '.ini_get('upload_max_filesize'));
		$Fieldset->addSmallInfo('Post-Limit: '.ini_get('post_max_size'));
		$Fieldset->setCollapsed();

		return $Fieldset;
	}

	/**
	 * Get fieldset for permissions
	 * @return \FormularFieldset
	 */
	private function getPermissionsFieldset() {
		$CHMOD_FOLDERS = array();
		$failures = 0;

		include FRONTEND_PATH.'system/define.chmod.php';

		$Fieldset = new FormularFieldset('Berechtigungen');
		$Fieldset->addBlock('F&uuml;r die folgenden Ordner werden Schreibrechte ben&ouml;tigt. Wenn der falsche Owner eingetragen ist, stimmt die folgende Angabe aber eventuell nicht.');

		foreach ($CHMOD_FOLDERS as $folder) {
			$realfolder = FRONTEND_PATH.'../'.$folder;

			if (!is_writable($realfolder)) {
				$Fieldset->addError('Das Verzeichnis <strong>'.$folder.'</strong> ist nicht beschreibbar. <em>(chmod = '.substr(decoct(fileperms($realfolder)),1).')</em>');
				$failures++;
			} else {
				$Fieldset->addOkay('Das Verzeichnis <strong>'.$folder.'</strong> ist beschreibbar. <em>(chmod = '.substr(decoct(fileperms($realfolder)),1).')</em>');
			}
		}

		if ($failures == 0)
			$Fieldset->setCollapsed();

		return $Fieldset;
	}

	/**
	 * Get fieldset for files
	 * @return \FormularFieldset
	 */
	private function getFilesFieldset() {
		$Fieldset = new FormularFieldset('Nicht mehr ben&ouml;tigte Dateien');
		$Fieldset->addFileBlock( $this->getBlockForFiles('/import/files/') );
		$Fieldset->addFileBlock( $this->getBlockForFiles('/export/files/') );
		$Fieldset->addFileBlock( $this->getBlockForFiles('../log/') );
		$Fieldset->addFileBlock( $this->getBlockForFiles('../plugin/RunalyzePluginTool_DbBackup/backup/') );
		$Fieldset->addFileBlock( $this->getBlockForFiles('../plugin/RunalyzePluginTool_DbBackup/import/') );
		$Fieldset->addBlock( '<input type="submit" value="Verzeichnisse s&auml;ubern" />' );
		$Fieldset->setCollapsed();

		return $Fieldset;
	}

	/**
	 * Get block for files
	 * @param string $pathToFiles
	 * @return string
	 */
	private function getBlockForFiles($pathToFiles) {
		$Text  = '<label class="right"><input type="checkbox" name="clean[]" value="'.$pathToFiles.'" /> leeren</label>';
		$Text .= '<small>';
		$Text .= '<strong>'.$pathToFiles.'</strong><br />';
		$Files = Filesystem::getFileNamesFromPath($pathToFiles);

		if (empty($Files)) {
			$Text .= '<em>Keine Dateien gefunden</em>';
		} else {
			foreach ($Files as $File) {
				$Text .= '<em>'.$File.'</em>, '.Filesystem::getFilesize(FRONTEND_PATH.$pathToFiles.$File).'<br />';
			}
		}

		$Text .= '</small>';

		return $Text;
	}

	/**
	 * Clean files
	 */
	private function cleanFiles() {
		if (isset($_POST['clean']) && is_array($_POST['clean'])) {
			foreach ($_POST['clean'] as $Folder) {
				$Files = Filesystem::getFileNamesFromPath($Folder);
				foreach ($Files as $File)
					unlink(FRONTEND_PATH.$Folder.$File);
			}
		}
	}

	/**
	 * Is the given password the right one for the administrator?
	 * @param string $password
	 * @return boolean
	 */
	protected function isAdminPassword($password) {
		return $this->isAdminHash(md5($password));
	}

	/**
	 * Is the given hash the right one for the administrator?
	 * @param string $hash
	 * @return boolean
	 */
	protected function isAdminHash($hash) {
		return ($hash == $this->getAdminHash());
	}

	/**
	 * Get administrator hash
	 * @return string
	 */
	private function getAdminHash() {
		return $this->adminHash;
	}

	/**
	 * Get user list
	 * @return array
	 */
	private function getUserList() {
		DB::getInstance()->stopAddingAccountID();
		$List = DB::getInstance()->query('
			SELECT '.PREFIX.'account.*
			FROM '.PREFIX.'account
			ORDER BY id ASC
		')->fetchAll();
		DB::getInstance()->startAddingAccountID();

		return $List;
	}

	/**
	 * Get array of config variables for editing
	 * @return array
	 */
	static public function getArrayOfConfigVariables() {
		return array(
			'RUNALYZE_DEBUG',
			'USER_CANT_LOGIN',
			'USER_CAN_REGISTER',
			'USER_MUST_LOGIN',
			'GARMIN_API_KEY',
			'MAIL_SENDER'
		);
	}

	/**
	 * Check for missing variables in config file and update if needed
	 */
	static public function checkAndUpdateConfigFile() {
		$Variables = self::getArrayOfConfigVariables();

		foreach ($Variables as $Variable)
			if (!defined($Variable))
				self::addVariableToConfigFile($Variable);
	}

	/**
	 * Add variable to config file
	 * @param string $Variable
	 */
	static private function addVariableToConfigFile($Variable) {
		$ConfigFile  = str_replace('?>', NL, Filesystem::openFile('../config.php'));
		$ConfigFile .= self::defineAndGetConfigLinesFor($Variable);
		$ConfigFile .= NL.'?>';

		Filesystem::writeFile('../config.php', $ConfigFile);
	}

	/**
	 * Get config lines for a given variable for adding to config file
	 * @param string $Variable
	 * @return string
	 */
	static private function defineAndGetConfigLinesFor($Variable) {
		switch ($Variable) {
			case 'USER_CANT_LOGIN':
				define('USER_CANT_LOGIN', false);
				return '/**
 * Working on your site? Disable login with this variable.
 * @var bool USER_CANT_LOGIN Set to disable login
 */
define(\'USER_CANT_LOGIN\', false);';

			case 'USER_CAN_REGISTER':
				define('USER_CAN_REGISTER', false);
				return '/**
 * Allow registration for new users
 * @var bool USER_CAN_REGISTER Set to false to close registration
 */
define(\'USER_CAN_REGISTER\', true);';

			case 'GARMIN_API_KEY':
				$APIKeyResults = DB::getInstance()->query('SELECT `value` FROM `'.PREFIX.'conf` WHERE `key`="GARMIN_API_KEY" LIMIT 1')->fetch();
				$APIKey        = isset($APIKeyResults['value']) ? $APIKeyResults['value'] : '';

				define('GARMIN_API_KEY', $APIKey);
				return '/**
 * Garmin API key is needed for using Garmin Communicator
 * @var bool GARMIN_API_KEY Garmin API key
 * @see http://developer.garmin.com/web-device/garmin-communicator-plugin/get-your-site-key/
 */
define(\'GARMIN_API_KEY\', \''.$APIKey.'\');';

			case 'MAIL_SENDER':
				define('MAIL_SENDER', 'Runalyze <mail@runalyze.de>');
				return '/**
 * Adress for sending mails to users
 * @var string MAIL_SENDER Adress for sending mails to users
 */
define(\'MAIL_SENDER\', \'Runalyze <mail@runalyze.de>\');';

			default:
				return '// Whoo! Runalyze tried to add an nonexisting configuration variable to this file. ($Variable = '.$Variable.')';
		}
	}
}