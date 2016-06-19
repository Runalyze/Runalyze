<?php
use Symfony\Component\Yaml\Yaml;

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
		include '../inc/tpl/tpl.installerHeader.php';

		if (!$this->isLoggedIn)
			include '../inc/tpl/tpl.adminWindow.login.php';
		else
			$this->displayView();

		include '../inc/tpl/tpl.installerFooter.php';
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
		$Fieldset->addField( new FormularCheckbox('user_cant_login', __('Maintenance mode')) );
		$Fieldset->addField( new FormularCheckbox('user_can_register', __('Users can register')) );
		$Fieldset->addField( new FormularCheckbox('user_disable_account_activation', __('Users don\'t need to activate their account')) );
		$Fieldset->addField( new FormularInput('garmin_api_key', Ajax::tooltip(__('Garmin API-key'), __('Needed for any online-version of the Garmin Communicator<br>see http://developer.garmin.com/web-device/garmin-communicator-plugin/get-your-site-key/') )) );
		$Fieldset->addField( new FormularInput('perl_path', __('Perl Path')) );
		$Fieldset->addField( new FormularInput('ttbin_path', __('TTBIN Converter Path')) );
		$Fieldset->addField( new FormularInput('geonames_username', __('Geonames Username')) );
		$Fieldset->addField( new FormularInput('sqlite_mod_spatialite', __('SQLITE Spatialite Extension')) );
                $Fieldset->addField( new FormularInput('mail_sender', __('Sender e-mail')) );
		$Fieldset->addField( new FormularInput('mail_name', __('Sender e-mail name')) );
		$Fieldset->addField( new FormularInput('smtp_host', __('SMTP: host')) );
		$Fieldset->addField( new FormularInput('smtp_port', __('SMTP: port')) );
		$Fieldset->addField( new FormularInput('smtp_security', __('SMTP: encryption')) );
		$Fieldset->addField( new FormularInput('smtp_username', __('SMTP: username')) );
		$Fieldset->addField( new FormularInputPassword('smtp_password', __('SMTP: password')) );
		$Fieldset->addField( new FormularInput('openweathermap_api_key', Ajax::tooltip(__('OpenWeatherMap API-Key'), __('Loading weather data requires an api key, see openweathermap.org/appid'))) );
		$Fieldset->addField( new FormularInput('nokia_here_appid', Ajax::tooltip(__('Nokia/Here App-ID'), __('Nokia maps require an app-id/-token, see developer.here.com'))) );
		$Fieldset->addField( new FormularInput('nokia_here_token', Ajax::tooltip(__('Nokia/Here Token'), __('Nokia maps require an app-id/-token, see developer.here.com'))) );
		$Fieldset->addField( new FormularSubmit(__('Save'), '') );
		$Fieldset->setLayoutForFields( FormularFieldset::$LAYOUT_FIELD_W100 );

		if (!is_writable(FRONTEND_PATH.'../data/config.yml')) {
			$Fieldset->addError( __('<strong>data/config.yml</strong> is not writable').', <em>(chmod = '.substr(decoct(fileperms(FRONTEND_PATH.'../data/config.yml')),1).')</em> '.__('Changes can\'t be saved.') );
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
		$config = Yaml::parse(file_get_contents('../data/config.yml'));
		
		foreach ($config['parameters'] as $key => $value)
		    $_POST[$key] = $value;
	}

	/**
	 * Update config file from post data
	 */
	private function updateConfigFileFromPost() {
		if (!is_writable(FRONTEND_PATH.'../data/config.yml'))
			return;

		$config = Yaml::parse(file_get_contents('../data/config.yml'));

		foreach ($this->getArrayOfConfigVariables() as $key) {
		    switch ($_POST[$key]) {
			case 'on':
			    $config['parameters'][$key] = true;
			    break;
			default:
			    $config['parameters'][$key] = $_POST[$key];
		    }
		}

		$yaml = Yaml::dump($config);
		$FileHandleNew = fopen( FRONTEND_PATH.'../data/config.yml', 'w' );
		fwrite($FileHandleNew, $yaml);
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
					</tr>
				</thead>
				<tbody>';

			foreach ($this->UserList as $User) {
				$Code .= '
					<tr>
						<td class="small r">'.$User['id'].'</td>
						<td>'.$User['username'].'</td>
						<td>'.$User['name'].'</td>
						<td class="small">'.$User['mail'].'</td>
						<td class="small c">'.date("d.m.Y", $User['registerdate']).'</td>
						<td class="small c">'.date("d.m.Y", $User['lastaction']).'</td>
					</tr>';
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
		$Fieldset->addFileBlock( $this->getBlockForFiles('../data/backup-tool/backup/') );
		$Fieldset->addFileBlock( $this->getBlockForFiles('../data/backup-tool/import/') );
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
		    'user_can_register',
		    'user_cant_login',
		    'user_disable_account_activation',
		    'garmin_api_key',
		    'openweathermap_api_key',
		    'nokia_here_appid',
		    'nokia_here_token',
		    'geonames_username',
		    'perl_path',
		    'ttbin_path',
		    'sqlite_mod_spatialite',
		    'mail_sender',
		    'mail_name',
		    'smtp_host',
		    'smtp_port',
		    'smtp_security',
		    'smtp_username',
		    'smtp_password',
		);
	}


}
