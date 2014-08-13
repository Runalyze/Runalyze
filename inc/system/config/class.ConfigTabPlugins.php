<?php
/**
 * This file contains class::ConfigTabPlugins
 * @package Runalyze\System\Config
 */
/**
 * ConfigTabPlugins
 * @author Hannes Christiansen
 * @package Runalyze\System\Config
 */
class ConfigTabPlugins extends ConfigTab {
	/**
	 * Set key and title for form 
	 */
	protected function setKeyAndTitle() {
		$this->key = 'config_tab_plugins';
		$this->title = __('Plugins');
	}

	/**
	 * Get URL
	 * @return string 
	 */
	static public function getExternalUrl() {
		return ConfigTabs::$CONFIG_URL.'?key=config_tab_plugins&external=true';
	}

	/**
	 * Set all fieldsets and fields
	 */
	public function setFieldsetsAndFields() {
		$Panels = new FormularFieldset( __('Panels') );
		$Panels->addInfo( __('Panels are small statistics shown always on the right side.') );
		$Panels->setHtmlCode($this->getCodeFor( PluginType::Panel ));
		$Panels->setCollapsed();

		$Stats = new FormularFieldset( __('Statistics') );
		$Stats->addInfo( __('Normal statistics are shown below the activitiy log.') );
		$Stats->setHtmlCode($this->getCodeFor( PluginType::Stat ));

		$Tools = new FormularFieldset(__('Tools') );
		$Tools->addInfo( __('Complex tools for analyzing or processing the complete database will open in an overlay.') );
		$Tools->setHtmlCode($this->getCodeFor( PluginType::Tool ));
		$Tools->setCollapsed();

		$Install = new FormularFieldset( __('Install a new plugin') );
		$Install->addInfo( __('New plugins can be installed here.') );
		$Install->setHtmlCode($this->getCodeForInstall());
		$Install->setCollapsed();

		$this->Formular->addFieldset($Panels);
		$this->Formular->addFieldset($Stats);
		$this->Formular->addFieldset($Tools);
		$this->Formular->addFieldset($Install);
		$this->Formular->allowOnlyOneOpenedFieldset();
	}

	/**
	 * Get code for
	 * @param string $PluginType
	 * @return string 
	 */
	private function getCodeFor($PluginType) {
		$Plugins = DB::getInstance()->query('SELECT `id`, `key`, `order` FROM `'.PREFIX.'plugin` WHERE `type`="'.PluginType::string($PluginType).'" ORDER BY FIELD(`active`, 1, 2, 0), `order` ASC')->fetchAll();

		if (empty($Plugins)) {
			return HTML::info(__('No plugins available.'));
		}

		$Code = '
			<table class="zebra-style fullwidth more-padding">
				<thead>
					<tr class="top b">
						<th colspan="3">'.PluginType::readableString($PluginType).'</th>
						<th>'.__('Mode').'</th>
						<th>'.__('Order').'</th>
						<th></th>
					</tr>
				</thead>
				<tbody>';

		$Factory = new PluginFactory();

		foreach ($Plugins as $Data) {
			$Plugin = $Factory->newInstance($Data['key']);

			if ($Plugin === false) {
				$Code .= '
					<tr class="unimportant">
						<td>'.PluginInstaller::uninstallLink($Plugin->key()).'</td>
						<td class="b">'.$Plugin->key().'</td>
						<td colspan="4">'.__('The plugin cannot be found.').'</td>
					</tr>';
			} else {
				$Code .= '
					<tr class="a'.($Plugin->isInActive() ? ' unimportant' : '').'">
						<td>'.$Plugin->getConfigLink().'</td>
						<td class="b">'.$Plugin->name().'</td>
						<td>'.$Plugin->description().'</td>
						<td><select name="plugin_modus_'.$Plugin->id().'">
								<option value="'.Plugin::ACTIVE.'"'.HTML::Selected($Plugin->isActive()).'>'.__('enabled').'</option>
								<option value="'.Plugin::ACTIVE_VARIOUS.'"'.HTML::Selected($Plugin->isHidden()).'>'.__('hidden*').'</option>
								<option value="'.Plugin::ACTIVE_NOT.'"'.HTML::Selected($Plugin->isInActive()).'>'.__('not enabled').'</option>
							</select></td>
						<td><input type="text" name="plugin_order_'.$Plugin->id().'" size="3" value="'.$Plugin->order().'"></td>
						<td>'.PluginInstaller::uninstallLink($Plugin->key()).'</td>
					</tr>';
			}
		}

		$Code .= '
				</tbody>
			</table>';

		switch($PluginType) {
			case 'panel':
				$Code .= HTML::info(__('* Hidden plugins do only show the heading.'));
				break;
			case 'stat':
				$Code .= HTML::info(__('* Hidden plugins are grouped as \'Miscellaneous\'.'));
				break;
			case 'tool':
			default:
				$Code .= '';
		}

		return $Code;
	}

	/**
	 * Get code for install
	 * @return string 
	 */
	private function getCodeForInstall() {
		$Factory = new PluginFactory();
		$Plugins = $Factory->notInstalledPlugins();

		if (empty($Plugins)) {
			return HTML::fileBlock( __('There are no new plugins to install.') );
		}

		$Code = '
			<table class="fullwidth zebra-style more-padding">
				<thead>
					<tr class="b">
						<th colspan="3">'.__('Plugin').'</th>
						<th colspan="2">'.__('Type').'</th>
					</tr>
				</thead>
				<tbody>';

		foreach ($Plugins as $Data) {
			$Plugin = $Factory->newInstallerInstance($Data['key']);

			$Code .= '
				<tr>
					<td>'.Icon::$ADD.'</td>
					<td class="b">'.PluginInstaller::link($Plugin->key(), $Plugin->name()).'</td>
					<td>'.$Plugin->description().'</td>
					<td colspan="2">'.PluginType::readableString($Plugin->type()).'</td>
				</tr>';
		}

		$Code .= '
				</tbody>
			</table>';

		return $Code;
	}

	/**
	 * Parse all post values 
	 */
	public function parsePostData() {
		$Plugins = DB::getInstance()->query('SELECT `id` FROM `'.PREFIX.'plugin`')->fetchAll();
		foreach ($Plugins as $Plugin) {
			$id = $Plugin['id'];

			if (isset($_POST['plugin_modus_'.$id]) && isset($_POST['plugin_order_'.$id])) {
				

				DB::getInstance()->update('plugin', $id,
					array(
						'active',
						'order'
					),
					array(
						(int)$_POST['plugin_modus_'.$id],
						(int)$_POST['plugin_order_'.$id]
					)
				);
			}
		}

		Ajax::setReloadFlag(Ajax::$RELOAD_PLUGINS);
	}
}