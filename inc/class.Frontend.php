<?php
/**
 * This file contains the class::Frontend to create and print the HTML-Page.
 * @package Runalyze\Frontend
 */

use Runalyze\Configuration;
use Runalyze\Error;
use Runalyze\Timezone;
use Symfony\Component\Yaml\Yaml;

/**
 * Frontend class for setting up everything
 *
 * The frontend initializes everything for Runalyze.
 * It sets the autoloader, constants and mysql-connection.
 * By default, constructing a new frontend will print a html-header.
 *
 * Standard initialization of Runalyze:
 * <code>
 *  require 'inc/class.Frontend.php';
 *  $Frontend = new Frontend();
 * </code>
 *
 * @author Hannes Christiansen
 * @package Runalyze\Frontend
 */
class Frontend {
	/**
	 * Symfony token storage for user
	 * @var null|\Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage
	 */
	protected $symfonyToken = null;

	/**
	 * Yaml Configuration
	 * @var array
	 */
	protected $yamlConfig = array();

	/**
	 * Constructor
	 *
	 * Constructing a new Frontend includes all files and sets the correct header.
	 * Runalyze is not usable without setting up the environment with this class.
	 *
	 * @param bool $hideHeaderAndFooter By default a html-header is directly shown
	 * @param null|\Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage $symfonyToken
	 */
	public function __construct($hideHeaderAndFooter = false, $symfonyToken = null) {
		$this->symfonyToken = $symfonyToken;

		$this->initSystem();
		$this->defineConsts();

		if (!$hideHeaderAndFooter)
			$this->displayHeader();
		else
			Error::getInstance()->footer_sent = true;
	}

	/**
	 * Destructor
	 */
	public function __destruct() {
		if (!Error::getInstance()->footer_sent)
			$this->displayFooter();
	}

	/**
	 * Init system
	 */
	private function initSystem() {
		define('RUNALYZE', true);
		define('FRONTEND_PATH', dirname(__FILE__).'/');

		$this->setAutoloader();
		$this->initCache();
		$this->initErrorHandling();
		$this->initConfig();
		$this->initDatabase();
		$this->initSessionAccountHandler();
		$this->initTimezone();
		$this->forwardAccountIDtoDatabaseWrapper();
	}

	/**
	 * Set up Autloader
	 */
	private function setAutoloader() {
		require_once FRONTEND_PATH.'../vendor/autoload.php';
	}

	/**
	 * Setup config
	 */
	private function initConfig() {
		$this->yamlConfig = array_merge(
			Yaml::parse(file_get_contents(FRONTEND_PATH.'/../app/config/config.yml'))['parameters'],
            Yaml::parse(file_get_contents(FRONTEND_PATH.'/../app/config/expert_config.yml'))['parameters'],
            Yaml::parse(file_get_contents(FRONTEND_PATH.'/../app/config/default_config.yml'))['parameters'],
			Yaml::parse(file_get_contents(FRONTEND_PATH.'/../data/config.yml'))['parameters']
		);

        define('DARKSKY_API_KEY', $this->yamlConfig['darksky_api_key']);
        define('OPENWEATHERMAP_API_KEY', $this->yamlConfig['openweathermap_api_key']);
	    define('NOKIA_HERE_APPID', $this->yamlConfig['nokia_here_appid']);
	    define('NOKIA_HERE_TOKEN', $this->yamlConfig['nokia_here_token']);
	    define('THUNDERFOREST_API_KEY', $this->yamlConfig['thunderforest_api_key']);
        define('MAPBOX_API_KEY', $this->yamlConfig['mapbox_api_key']);
	    define('PERL_PATH', $this->yamlConfig['perl_path']);
	    define('TTBIN_PATH', $this->yamlConfig['ttbin_path']);
	    define('GEONAMES_USERNAME', $this->yamlConfig['geonames_username']);
	    define('USER_DISABLE_ACCOUNT_ACTIVATION', $this->yamlConfig['user_disable_account_activation']);
	    define('SQLITE_MOD_SPATIALITE', $this->yamlConfig['sqlite_mod_spatialite']);
        define('RUNALYZE_VERSION', $this->yamlConfig['RUNALYZE_VERSION']);
        define('DATA_DIRECTORY', $this->yamlConfig['data_directory']);
	}

	/**
	 * Setup timezone
	 */
	private function initTimezone() {
		Timezone::setPHPTimezone(SessionAccountHandler::getTimezone());
		Timezone::setMysql();
	}

        /**
	 * Setup Language
	 */
	private function initCache() {
		require_once FRONTEND_PATH.'/system/class.Cache.php';

		try {
			new Cache();
		} catch (Exception $E) {
			die('Cache directory "./'.Cache::PATH.'/cache/" must be writable.');
		}
	}

	/**
	 * Init constants
	 */
	private function defineConsts() {
		require_once FRONTEND_PATH.'system/define.consts.php';

		Configuration::loadAll();

		\Runalyze\Calculation\JD\LegacyEffectiveVO2maxCorrector::setGlobalFactor( Configuration::Data()->vo2maxCorrectionFactor() );

		require_once FRONTEND_PATH.'class.Helper.php';
	}

	/**
	 * Include class::Error and and initialise it
	 */
	protected function initErrorHandling() {
		\Runalyze\Error::init(Request::Uri());
	}

	/**
	 * Connect to database
	 */
	private function initDatabase() {
		define('PREFIX', $this->yamlConfig['database_prefix']);

		DB::connect($this->yamlConfig['database_host'], $this->yamlConfig['database_port'], $this->yamlConfig['database_user'], $this->yamlConfig['database_password'], $this->yamlConfig['database_name']);
	}

	/**
	 * Init SessionAccountHandler
	 */
	protected function initSessionAccountHandler() {
		new SessionAccountHandler();

		if (!is_null($this->symfonyToken) && $this->symfonyToken->getToken()->getUser() != 'anon.') {
			/** @var \Runalyze\Bundle\CoreBundle\Entity\Account $user */
		    $user = $this->symfonyToken->getToken()->getUser();

		    SessionAccountHandler::setAccount(array(
			    'id' => $user->getId(),
			    'username' => $user->getUsername(),
			    'language' => $user->getLanguage(),
			    'timezone' => $user->getTimezone(),
			    'mail' => $user->getMail(),
				'gender' => $user->getGender(),
				'birthyear' => $user->getBirthyear()
		    ));
		}
	}

	/**
	 * Forward accountid to database wraper
	 */
	protected function forwardAccountIDtoDatabaseWrapper() {
		DB::getInstance()->setAccountID( SessionAccountHandler::getId() );
	}

	/**
	 * Display the HTML-Header
	 */
	public function displayHeader() {

		if (!Request::isAjax() && !isset($_GET['hideHtmlHeader']))
			include 'tpl/tpl.Frontend.header.php';
	}

	/**
	 * Display the HTML-Footer
	 */
	public function displayFooter() {
		if (Error::getInstance()->hasErrors()) {
			Error::getInstance()->display();
		}

		if (!Request::isAjax() && !isset($_GET['hideHtmlHeader'])) {
			include 'tpl/tpl.Frontend.footer.php';
		}

		Error::getInstance()->footer_sent = true;
	}
}
