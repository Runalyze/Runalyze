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
	 * Array with all keys
	 * @var array
	 */
	static private $Plugins = array();

	/**
	 * Plugins as array
	 * @return array
	 */
	protected function pluginList() {
		if (empty(self::$Plugins)) {
			$this->readInstalledPlugins();
		}

		return self::$Plugins;
	}

	/**
	 * New instance for key
	 * @param string $Pluginkey
	 * @return Plugin
	 * @throws InvalidArgumentException
	 */
	public function newInstance($Pluginkey) {
		$data = DB::getInstance()->query('SELECT `id` FROM `'.PREFIX.'plugin` WHERE `key`='.DB::getInstance()->escape($Pluginkey).' LIMIT 1')->fetch();

		if ($data === false) {
			throw new InvalidArgumentException('Plugin with key "'.$Pluginkey.'" is not installed.');
		}

		return (new $Pluginkey($data['id']));
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
	 * Read all installed plugins
	 */
	protected function readInstalledPlugins() {
		self::$Plugins = DB::getInstance()->query('SELECT `key`, `type`, `active` FROM `'.PREFIX.'plugin` ORDER BY `order` ASC')->fetchAll();
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
		return $this->getPlugins(PluginType::Stat, Plugin::ACTIVE_VARIOUS);
	}

	/**
	 * Get all inactive plugins
	 * @param mixed $type [optional] false or enum
	 * @return array array with plugin keys
	 */
	public function enabledPanels() {
		$keys = array();
		$plugins = $this->pluginList();

		foreach ($plugins as $plugin) {
			if ($plugin['type'] == PluginType::string(PluginType::Panel) && $plugin['active'] != Plugin::ACTIVE_NOT) {
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
		$plugins = $this->pluginList();

		foreach ($plugins as $plugin) {
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
	}

	/**
	 * Get the PLUGINKEY for a given ID from database
	 * @param int $id
	 * @return string
	 */
	static public function keyFor($id) {
		$data = DB::getInstance()->fetchByID('plugin', $id);

		if ($data === false) {
			throw new RuntimeException('No plugin found for id "'.$id.'".');
		}

		return $data['key'];
	}
}