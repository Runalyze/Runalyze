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
		$Formular->addHiddenValue('job', 'settings');
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

		$Fieldset = new FormularFieldset( __('Settings') );
		$Fieldset->addField( new FormularCheckbox('RUNALYZE_DEBUG', __('Debug mode')) );
		$Fieldset->addField( new FormularCheckbox('USER_CANT_LOGIN', __('Maintenance mode')) );
		$Fieldset->addField( new FormularCheckbox('USER_CAN_REGISTER', __('Users can register')) );
		$Fieldset->addField( new FormularInput('GARMIN_API_KEY', Ajax::tooltip(__('Garmin API-key'), __('Needed for any online-version of the Garmin Communicator<br>see http://developer.garmin.com/web-device/garmin-communicator-plugin/get-your-site-key/') )) );
		$Fieldset->addField( new FormularInput('PERL_PATH', __('Perl Path')) );
		$Fieldset->addField( new FormularInput('TTBIN_PATH', __('TTBIN Converter Path')) );
                $Fieldset->addField( new FormularInput('MAIL_SENDER', __('Sender e-mail')) );
		$Fieldset->addField( new FormularInput('MAIL_NAME', __('Sender e-mail name')) );
		$Fieldset->addField( new FormularInput('SMTP_HOST', __('SMTP: host')) );
		$Fieldset->addField( new FormularInput('SMTP_PORT', __('SMTP: port')) );
		$Fieldset->addField( new FormularInput('SMTP_SECURITY', __('SMTP: encryption')) );
		$Fieldset->addField( new FormularInput('SMTP_USERNAME', __('SMTP: username')) );
		$Fieldset->addField( new FormularInputPassword('SMTP_PASSWORD', __('SMTP: password')) );
		$Fieldset->addField( new FormularInput('OPENWEATHERMAP_API_KEY', Ajax::tooltip(__('OpenWeatherMap API-Key'), __('Loading weather data requires an api key, see openweathermap.org/appid'))) );
		$Fieldset->addField( new FormularInput('NOKIA_HERE_APPID', Ajax::tooltip(__('Nokia/Here App-ID'), __('Nokia maps require an app-id/-token, see developer.here.com'))) );
		$Fieldset->addField( new FormularInput('NOKIA_HERE_TOKEN', Ajax::tooltip(__('Nokia/Here Token'), __('Nokia maps require an app-id/-token, see developer.here.com'))) );
		$Fieldset->addField( new FormularSubmit(__('Save'), '') );
		$Fieldset->setLayoutForFields( FormularFieldset::$LAYOUT_FIELD_W100 );

		if (!is_writable(FRONTEND_PATH.'../config.php')) {
			$Fieldset->addError( __('<strong>config.php</strong> is not writable').', <em>(chmod = '.substr(decoct(fileperms(FRONTEND_PATH.'../config.php')),1).')</em> '.__('Changes can\'t be saved.') );
		}

		return $Fieldset;
	}

	/**
	 * Handle post data for updating settings
	 */
	private function handlePostData() {
		if (isset($_POST['hash']) && $this->isLoggedIn && $_POST['job'] == 'settings') {
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
		$Fieldset = new FormularFieldset( __('User list') );
		$Fieldset->setCollapsed();

		if (empty($this->UserList)) {
			$Fieldset->addWarning( __('There are no registered users.') );
		} else {
			$Code = '
			<table class="small fullwidth zebra-style" id="userTable">
				<thead>
					<tr>
						<th>ID</th>
						<th>'.__('User').'</th>
						<th>'.__('Name').'</th>
						<th>'.__('Email').'</th>
						<th class="{sorter: \'germandate\'}">'.__('since').'</th>
						<th class="{sorter: \'germandate\'}">'.__('last').'</th>
						<!--<th class="{sorter: false}">'.__('Functions').'</th>-->
					</tr>
				</thead>
				<tbody>';
						//<th class="{sorter: \'x\'}">'.__('times').'</th>
						//<th class="{sorter: \'distance\'}">'.__('km').'</th>

			foreach ($this->UserList as $User) {
				$Code .= '
					<tr>
						<td class="small r">'.$User['id'].'</td>
						<td>'.$User['username'].'</td>
						<td>'.$User['name'].'</td>
						<td class="small">'.$User['mail'].'</td>
						<td class="small c">'.date("d.m.Y", $User['registerdate']).'</td>
						<td class="small c">'.date("d.m.Y", $User['lastaction']).'</td>
						<!--<td>'.__('Activate user').' - '.__('Set new password').'</td>-->
					</tr>';
					//<td class="small r">'.$User['num'].'x</td>
					//<td class="small r">'.Distance::format($User['km']).'</td>
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
		$Fieldset = new FormularFieldset( __('Server information') );
		$Fieldset->addSmallInfo( __('PHP version:').' '.PHP_VERSION);
		$Fieldset->addSmallInfo( __('MySQL version:').' '.DB::getInstance()->getAttribute(PDO::ATTR_SERVER_VERSION));
		$Fieldset->addSmallInfo( __('Time limit:').' '.ini_get('max_execution_time'));
		$Fieldset->addSmallInfo( __('Memory limit:').' '.ini_get('memory_limit'));
		$Fieldset->addSmallInfo( __('Upload limit:').' '.ini_get('upload_max_filesize'));
		$Fieldset->addSmallInfo( __('Post limit:').' '.ini_get('post_max_size'));

		if (Shell::isPerlAvailable())
			$Fieldset->addSmallInfo( __('Perl scripts can be used.') );
		else
			$Fieldset->addWarning( __('Perl scripts cannot be used.') );

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

		$Fieldset = new FormularFieldset( __('Permissions') );
		$Fieldset->addBlock( __('The following directories do need write permissions. (And the right owner has to be set!)') );

		foreach ($CHMOD_FOLDERS as $folder) {
			$realfolder = FRONTEND_PATH.'../'.$folder;
			$chmod = substr(decoct(fileperms($realfolder)),1);

			if (!is_writable($realfolder)) {
				$Fieldset->addError( sprintf(__('The directory <strong>%s</strong> is not writable.'), $folder).' <em>(chmod = '.$chmod.')</em>' );
				$failures++;
			} else {
				$Fieldset->addOkay( sprintf(__('The directory <strong>%s</strong> is writable.'), $folder).' <em>(chmod = '.$chmod.')</em>' );
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
		$Fieldset = new FormularFieldset( __('Unused files') );
		$Fieldset->addFileBlock( $this->getBlockForFiles('../data/import/') );
		$Fieldset->addFileBlock( $this->getBlockForFiles('../data/log/') );
		$Fieldset->addFileBlock( $this->getBlockForFiles('../data/DbBackup/backup/') );
		$Fieldset->addFileBlock( $this->getBlockForFiles('../data/DbBackup/import/') );
		$Fieldset->addBlock( '<input type="submit" value="'.__('Clear directories').'">' );
		$Fieldset->setCollapsed();

		return $Fieldset;
	}

	/**
	 * Get block for files
	 * @param string $pathToFiles
	 * @return string
	 */
	private function getBlockForFiles($pathToFiles) {
		$Text  = '<label class="right"><input type="checkbox" name="clean[]" value="'.$pathToFiles.'"> '.__('clean up').'</label>';
		$Text .= '<small>';
		$Text .= '<strong>'.$pathToFiles.'</strong><br>';
		$Files = Filesystem::getFileNamesFromPath($pathToFiles);

		if (empty($Files)) {
			$Text .= '<em>'.__('No files found').'</em>';
		} else {
			foreach ($Files as $File) {
				$Text .= '<em>'.$File.'</em>, '.Filesystem::getFilesize(FRONTEND_PATH.$pathToFiles.$File).'<br>';
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
	public static function getArrayOfConfigVariables() {
		return array(
			'RUNALYZE_DEBUG',
                        'USER_CANT_LOGIN',
			'USER_CAN_REGISTER',
                        'PERL_PATH',
			'TTBIN_PATH',
			'GARMIN_API_KEY',
			'MAIL_SENDER',
			'MAIL_NAME',
			'OPENWEATHERMAP_API_KEY',
			'NOKIA_HERE_APPID',
			'NOKIA_HERE_TOKEN',
			'SMTP_HOST',
			'SMTP_PORT',
			'SMTP_SECURITY',
			'SMTP_USERNAME',
			'SMTP_PASSWORD'
		);
	}

	/**
	 * Check for missing variables in config file and update if needed
	 */
	public static function checkAndUpdateConfigFile() {
		$Variables = self::getArrayOfConfigVariables();

		foreach ($Variables as $Variable)
			if (!defined($Variable))
				self::addVariableToConfigFile($Variable);
	}

	/**
	 * Add variable to config file
	 * @param string $Variable
	 */
	private static function addVariableToConfigFile($Variable) {
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
	private static function defineAndGetConfigLinesFor($Variable) {
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

			case 'PERL_PATH':
				define('PERL_PATH', '/usr/bin/perl');
				return '/**
 * Path to perl scripts
 * Relative to FRONTEND_PATH
 * @var string PERL_PATH Path for perl scripts
 */
define(\'PERL_PATH\', \'/usr/bin/perl\');';
				
			case 'TTBIN_PATH':
				define('TTBIN_PATH', FRONTEND_PATH.'../call/perl/ttbincnv');
				return '/**
 * Path to TTBIN Converter script
 * @var string TTBIN_PATH for perl scripts
 */
define(\'TTBIN_PATH\', FRONTEND_PATH.\'../call/perl/ttbincnv\');';				
                                
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
				define('MAIL_SENDER', 'mail@runalyze.de');
				return '/**
 * Adress for sending mails to users
 * @var string MAIL_SENDER Adress for sending mails to users
 */
define(\'MAIL_SENDER\', \'mail@runalyze.de\');';

			case 'MAIL_NAME':
				define('MAIL_NAME', 'Runalyze');
				return '/**
 * Sender name for sending mails to users
 * @var string
 */
define(\'MAIL_NAME\', \'Runalyze\');';

			case 'OPENWEATHERMAP_API_KEY':
				define('OPENWEATHERMAP_API_KEY', '');
				return '/**
 * OpenWeatherMap: API key
 * @var string OPENWEATHERMAP_API_KEY api key
 * @see http://openweathermap.org/appid
 */
define(\'OPENWEATHERMAP_API_KEY\', \'\');';

			case 'NOKIA_HERE_APPID':
				define('NOKIA_HERE_APPID', '');
				return '/**
 * App-ID for Nokia/Here maps in Leaflet
 * @var string
 * @see https://developer.here.com
 */
define(\'NOKIA_HERE_APPID\', \'\');';

			case 'NOKIA_HERE_TOKEN':
				define('NOKIA_HERE_TOKEN', '');
				return '/**
 * Token/App-Code for Nokia/Here maps in Leaflet
 * @var string
 * @see https://developer.here.com
 */
define(\'NOKIA_HERE_TOKEN\', \'\');';

			case 'SMTP_HOST':
				define('SMTP_HOST', 'localhost');
				return '/**
 * Define the mail sending server
 * @var string
 */
define(\'SMTP_HOST\', \'localhost\');';

			case 'SMTP_PORT':
				define('SMTP_PORT', '25');
				return '/**
 * Define the smtp port
 * @var string
 */
define(\'SMTP_PORT\', \'25\');';

			case 'SMTP_SECURITY':
				define('SMTP_SECURITY', '');
				return '/**
 * Define the smtp encryption
 * @var string
 */
define(\'SMTP_SECURITY\', \'\');';

			case 'SMTP_USERNAME':
				define('SMTP_USERNAME', '');
				return '/**
 * Define the auth username for the smtp server
 * @var string
 */
define(\'SMTP_USERNAME\', \'\');';

			case 'SMTP_PASSWORD':
				define('SMTP_PASSWORD', '');
				return '/**
 * Define the auth password for the smtp server
 * @var string
 */
define(\'SMTP_PASSWORD\', \'\');';

			default:
				return '// Whoo! Runalyze tried to add an nonexisting configuration variable to this file. ($Variable = '.$Variable.')';
		}
	}
}
