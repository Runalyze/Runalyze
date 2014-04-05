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
		$this->title = _('Plugins');
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
		$Panels = new FormularFieldset(__('Panels'));
		$Panels->addInfo(__('Extended view and summary in the right column'));
		$Panels->setHtmlCode($this->getCodeFor( Plugin::$PANEL ));
		$Panels->setCollapsed();

		$Stats = new FormularFieldset(__('Statistics'));
		$Stats->addInfo(__('Big statistic below the calendar'));
		$Stats->setHtmlCode($this->getCodeFor( Plugin::$STAT ));

		$Tools = new FormularFieldset(__('Tools'));
		$Tools->addInfo(__('Extra selectable tools usually for the analysis or processing of the complete database'));
		$Tools->setHtmlCode($this->getCodeFor( Plugin::$TOOL ));
		$Tools->setCollapsed();

		$Install = new FormularFieldset(__('Install a new plugin'));
		$Install->addInfo(__('New plugins can be convenient installed here.'));
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
		$Plugins = DB::getInstance()->query('SELECT `id`, `key`, `order` FROM `'.PREFIX.'plugin` WHERE `type`="'.Plugin::getTypeString($PluginType).'" ORDER BY FIELD(`active`, 1, 2, 0), `order` ASC')->fetchAll();

		if (empty($Plugins))
			return HTML::info(__('There are no plugins available.'));

		$Code = '
			<table class="zebra-style fullwidth more-padding">
				<thead>
					<tr class="top b">
						<th colspan="3">'.Plugin::getReadableTypeString($PluginType).'</th>
						<th>'.__('mode').'</th>
						<th>Pos.</th>
					</tr>
				</thead>
				<tbody>';

		foreach ($Plugins as $Data) {
			$Plugin = Plugin::getInstanceFor($Data['key']);

			if ($Plugin === false)
				$Code .= '
					<tr class="unimportant">
						<td>'.Plugin::getRemoveLink($Data['key']).'</td>
						<td class="b">'.$Data['key'].'</td>
						<td colspan="3">'.__('The plugin could not be found').'</td>
					</tr>';
			else
				$Code .= '
					<tr class="a'.($Plugin->get('active') == Plugin::$ACTIVE_NOT ? ' unimportant' : '').'">
						<td>'.$Plugin->getConfigLink().'</td>
						<td class="b">'.$Plugin->get('name').'</td>
						<td>'.$Plugin->get('description').'</td>
						<td><select name="plugin_modus_'.$Plugin->get('id').'">
								<option value="'.Plugin::$ACTIVE.'"'.HTML::Selected($Plugin->get('active') == Plugin::$ACTIVE).'>'.__('enabled').'</option>
								<option value="'.Plugin::$ACTIVE_VARIOUS.'"'.HTML::Selected($Plugin->get('active') == Plugin::$ACTIVE_VARIOUS).'>'.__('hidden*').'</option>
								<option value="'.Plugin::$ACTIVE_NOT.'"'.HTML::Selected($Plugin->get('active') == Plugin::$ACTIVE_NOT).'>'.__('not enabled').'</option>
							</select></td>
						<td><input type="text" name="plugin_order_'.$Plugin->get('id').'" size="3" value="'.$Plugin->get('order').'"></td>
					</tr>';
		}

		$Code .= '
				</tbody>
			</table>';

		switch($PluginType) {
			case 'panel':
				$Code .= HTML::info(__('* Hidden plugins are folded.'));
				break;
			case 'stat':
				$Code .= HTML::info(__('* Hidden plugins are grouped &quot;Others&quot;.'));
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
		$Plugins = Plugin::getPluginsToInstallAsArray();

		if (empty($Plugins))
			return HTML::info(__('There are no plugins to install.'));

		$Code = '
			<table class="fullwidth zebra-style more-padding">
				<thead>
					<tr class="b">
						<th colspan="3">'.__('Plugin').'</th>
						<th colspan="2">'.__('Type').'</th>
					</tr>
				</thead>
				<tbody>';

		foreach ($Plugins as $i => $Data) {
			$Plugin = Plugin::getInstanceFor($Data['key']);

			if ($Plugin === false)
				$Code .= '<tr><td colspan="4"><em>'.__('The Plugin ').$Data['key'].__(' could not be found').'</em></td></tr>';
			else
				$Code .= '
				<tr>
					<td>'.$Plugin->getInstallLink().'</td>
					<td class="b">'.$Plugin->getInstallLink($Plugin->get('name')).'</td>
					<td>'.$Plugin->get('description').'</td>
					<td colspan="2">'.Plugin::getReadableTypeString($Plugin->get('type')).'</td>
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
			if (isset($_POST['plugin_modus_'.$id]) && isset($_POST['plugin_order_'.$id]))
				DB::getInstance()->update('plugin', $id,
					array('active', 'order'),
					array($_POST['plugin_modus_'.$id], $_POST['plugin_order_'.$id]));
		}

		// TODO: 
		// Wenn ein Plugin ganz versteckt wurde, muss eigentlich die ganze Seite neugeladen werden.
		Ajax::setReloadFlag(Ajax::$RELOAD_PLUGINS);
	}
}