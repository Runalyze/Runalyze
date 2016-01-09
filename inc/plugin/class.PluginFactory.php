<?php
/**
 * This file contains class::PluginFactory
 * @package Runalyze\Plugin
 */
/**
 * Plugin factory
 * @author Hannes Christiansen
 * @package Runalyze\Plugin
 */
class PluginFactory {
	/**
	 * @var string
	 */
	const CACHE_KEY = 'plugins';

	/**
	 * Array with all keys
	 * @var array|null
	 */
	private static $Plugins = null;

	/**
	 * Array with complete plugin data
	 * @return array
	 */
	private static function Plugins() {
		if (null === self::$Plugins) {
			self::$Plugins = self::fetchAllPlugins();
		}

		return self::$Plugins;
	}

	/**
	 * Cache all from Table plugin for a user
	 */
	private static function fetchAllPlugins() {
		$data = Cache::get(self::CACHE_KEY);

		if ($data == null) {
			$data = self::fetchAllPluginsFrom(DB::getInstance(), SessionAccountHandler::getId());
			Cache::set(self::CACHE_KEY, $data, '3600');
		}

		return $data;
	}

	/**
	 * Clear cache
	 */
	public static function clearCache() {
		self::$Plugins = null;
		Cache::delete(self::CACHE_KEY);
	}

	/**
	 * Fetch complete list from database
	 * @param \PDO $PDO
	 * @param int $accountID
	 * @return array
	 */
	private static function fetchAllPluginsFrom(PDO $PDO, $accountID) {
		return $PDO->query(
			'SELECT * FROM `'.PREFIX.'plugin` '.
			'WHERE `accountid`='.$accountID.' '.
			'ORDER BY `order` ASC'
		)->fetchAll();
	}

	/**
	 * @param int $id
	 * @return array
	 */
	public static function dataFor($id) {
		foreach (self::Plugins() as $data) {
			if ($data['id'] == $id) {
				return $data;
			}
		}

		return array();
	}

	/**
	 * @return array
	 */
	public static function allIDs() {
		$IDs = array();

		foreach (self::Plugins() as $data) {
			$IDs[] = $data['id'];
		}

		return $IDs;
	}

	/**
	 * Complete plugin data
	 * @param string|bool $type [optional]
	 * @return array
	 */
	public function completeData($type = false) {
		if ($type === false) {
			return self::Plugins();
		}

		$plugins = self::Plugins();
		foreach ($plugins as $k => $plugin) {
			if ($plugin['type'] != PluginType::string($type)) {
				unset($plugins[$k]);
			}
		}

		return $plugins;
	}

	/**
	 * New instance for key
	 * @param string $pluginkey
	 * @return Plugin
	 * @throws \InvalidArgumentException
	 */
	public function newInstance($pluginkey) {
		foreach (self::Plugins() as $plugin) {
			if ($plugin['key'] == $pluginkey) {
				return (new $pluginkey($plugin['id']));
			}
		}

		throw new InvalidArgumentException('Plugin with key "'.$pluginkey.'" is not installed.');
	}

	/**
	 * New instance for id
	 * @param int $id
	 * @return Plugin
	 */
	public function newInstanceFor($id) {
		return $this->newInstance( self::keyFor((int)$id) );
	}

	/**
	 * New instance for key
	 * @param string $Pluginkey
	 * @return Plugin
	 */
	public function newInstallerInstance($Pluginkey) {
		$Plugin = new $Pluginkey( PluginInstaller::ID );

		return $Plugin;
	}

	/**
	 * Is the plugin already installed?
	 * @param string $key
	 * @return bool
	 */
	public function isInstalled($key) {
		return in_array($key, $this->allPlugins());
	}

	/**
	 * Get all plugins
	 * @param mixed $type [optional] false or enum
	 * @return array array with plugin keys
	 */
	public function allPlugins($type = false) {
		return $this->getPlugins($type);
	}

	/**
	 * Get all active plugins
	 * @param mixed $type [optional] false or enum
	 * @return array array with plugin keys
	 */
	public function activePlugins($type = false) {
		return $this->getPlugins($type, Plugin::ACTIVE);
	}

	/**
	 * Get all inactive plugins
	 * @param mixed $type [optional] false or enum
	 * @return array array with plugin keys
	 */
	public function inactivePlugins($type = false) {
		return $this->getPlugins($type, Plugin::ACTIVE_NOT);
	}

	/**
	 * Get all various plugins
	 * @return array array with plugin keys
	 */
	public function variousPlugins() {
		return $this->getPlugins(PluginType::STAT, Plugin::ACTIVE_VARIOUS);
	}

	/**
	 * Get all enabled panels
	 * @return array array with plugin keys
	 */
	public function enabledPanels() {
		$keys = array();

		foreach (self::Plugins() as $plugin) {
			if ($plugin['type'] == PluginType::string(PluginType::PANEL) && $plugin['active'] != Plugin::ACTIVE_NOT) {
				$keys[] = $plugin['key'];
			}
		}

		return $keys;
	}

	/**
	 * Get plugins
	 * @param mixed $type [optional] false or enum
	 * @param mixed $active [optional] false or enum 
	 * @return array array with plugin keys
	 */
	protected function getPlugins($type = false, $active = false) {
		$keys = array();

		foreach (self::Plugins() as $plugin) {
			if (($type === false || $plugin['type'] == PluginType::string($type)) && ($active === false || $plugin['active'] == $active)) {
				$keys[] = $plugin['key'];
			}
		}

		return $keys;
	}

	/**
	 * Get uninstalled plugins
	 * @return array
	 */
	public function notInstalledPlugins() {
		$plugins = array();
		$dir = opendir(FRONTEND_PATH.'../plugin/');

		while ($file = readdir($dir)) {
			if ($file[0] != '.' && is_dir(FRONTEND_PATH.'../plugin/'.$file) && !$this->isInstalled($file)) {
				$plugins[] = array('key' => $file);
			}
		}

		closedir($dir);

		return $plugins;
	}

	/**
	 * Install a new plugin
	 * @param string $Pluginkey
	 */
	public function installPlugin($Pluginkey) {
		$Plugin = new $Pluginkey( Plugin::$INSTALLER_ID );
		$Plugin->key = $Pluginkey;

		return $Plugin->install();
	}

	/**
	 * Uninstall plugin
	 * @param string $key 
	 */
	public function uninstallPlugin($key) {
		DB::getInstance()->exec('DELETE FROM `'.PREFIX.'plugin` WHERE `key`='.DB::getInstance()->escape($key).' LIMIT 1');
		self::clearCache();
	}

	/**
	 * Get the PLUGINKEY for a given ID from database
	 * @param int $id
	 * @return string
	 * @throws \InvalidArgumentException
	 */
	public static function keyFor($id) {
		foreach (self::Plugins() as $plugin) {
			if ($id == $plugin['id']) {
				return $plugin['key'];
			}
		}

		throw new InvalidArgumentException('No plugin found for id "'.$id.'".');
	}
}