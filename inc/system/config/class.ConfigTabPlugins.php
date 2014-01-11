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
		$this->title = 'Plugins';
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
		$Panels = new FormularFieldset('Panels');
		$Panels->addInfo('Erweiterte Ansichten und Zusammenfassungen in der rechten Spalte');
		$Panels->setHtmlCode($this->getCodeFor( Plugin::$PANEL ));
		$Panels->setCollapsed();

		$Stats = new FormularFieldset('Statistiken');
		$Stats->addInfo('Gro&szlig;e Statistiken unterhalb des Kalenders.');
		$Stats->setHtmlCode($this->getCodeFor( Plugin::$STAT ));

		$Tools = new FormularFieldset('Tools');
		$Tools->addInfo('Extra ansteuerbare Tools, meist zur Auswertung oder Aufbereitung der kompletten Datenbank');
		$Tools->setHtmlCode($this->getCodeFor( Plugin::$TOOL ));
		$Tools->setCollapsed();

		$Install = new FormularFieldset('Neues Plugin installieren');
		$Install->addInfo('Neue Plugins k&ouml;nnen hier bequem installiert werden.');
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
		$Plugins = Mysql::getInstance()->fetchAsArray('SELECT `id`, `key`, `order` FROM `'.PREFIX.'plugin` WHERE `type`="'.Plugin::getTypeString($PluginType).'" ORDER BY FIELD(`active`, 1, 2, 0), `order` ASC');

		if (empty($Plugins))
			return HTML::info('Es sind keine Plugins vorhanden.');

		$Code = '
			<table class="zebra-style fullwidth">
				<thead>
					<tr class="top b">
						<th colspan="3">'.Plugin::getReadableTypeString($PluginType).'</th>
						<th>Modus</th>
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
						<td class="small" colspan="3">Das Plugin konnte nicht gefunden werden.</td>
					</tr>';
			else
				$Code .= '
					<tr class="a'.($Plugin->get('active') == Plugin::$ACTIVE_NOT ? ' unimportant' : '').'">
						<td>'.$Plugin->getConfigLink().'</td>
						<td class="b">'.$Plugin->get('name').'</td>
						<td class="small">'.$Plugin->get('description').'</td>
						<td><select name="plugin_modus_'.$Plugin->get('id').'">
								<option value="'.Plugin::$ACTIVE.'"'.HTML::Selected($Plugin->get('active') == Plugin::$ACTIVE).'>aktiviert</option>
								<option value="'.Plugin::$ACTIVE_VARIOUS.'"'.HTML::Selected($Plugin->get('active') == Plugin::$ACTIVE_VARIOUS).'>versteckt*</option>
								<option value="'.Plugin::$ACTIVE_NOT.'"'.HTML::Selected($Plugin->get('active') == Plugin::$ACTIVE_NOT).'>nicht aktiviert</option>
							</select></td>
						<td><input type="text" name="plugin_order_'.$Plugin->get('id').'" size="3" value="'.$Plugin->get('order').'" /></td>
					</tr>';
		}

		$Code .= '
				</tbody>
			</table>';

		switch($PluginType) {
			case 'panel':
				$Code .= HTML::info('* Versteckte Plugins sind eingeklappt.');
				break;
			case 'stat':
				$Code .= HTML::info('* Versteckte Plugins werden unter &quot;Sonstiges&quot; gruppiert.');
				$Code .= HTML::info('Es sollten nicht mehr als acht Plugins sichtbar sein, da es dann zu Design-Problemen kommen kann.');
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
			return HTML::info('Es sind keine Plugins zum Installieren vorhanden.');

		$Code = '
			<table class="fullwidth zebra-style">
				<thead>
					<tr class="b">
						<th colspan="3">Plugin</th>
						<th colspan="2">Typ</th>
					</tr>
				</thead>
				<tbody>';

		foreach ($Plugins as $i => $Data) {
			$Plugin = Plugin::getInstanceFor($Data['key']);

			if ($Plugin === false)
				$Code .= '<tr><td colspan="4"><em>Das Plugin '.$Data['key'].' konnte nicht gefunden werden.</em></td></tr>';
			else
				$Code .= '
				<tr>
					<td>'.$Plugin->getInstallLink().'</td>
					<td class="b">'.$Plugin->getInstallLink($Plugin->get('name')).'</td>
					<td class="small">'.$Plugin->get('description').'</td>
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
		$Plugins = Mysql::getInstance()->fetchAsArray('SELECT `id` FROM `'.PREFIX.'plugin`');
		foreach ($Plugins as $Plugin) {
			$id = $Plugin['id'];
			if (isset($_POST['plugin_modus_'.$id]) && isset($_POST['plugin_order_'.$id]))
				Mysql::getInstance()->update(PREFIX.'plugin', $id,
					array('active', 'order'),
					array($_POST['plugin_modus_'.$id], $_POST['plugin_order_'.$id]));
		}

		// TODO: 
		// Wenn ein Plugin ganz versteckt wurde, muss eigentlich die ganze Seite neugeladen werden.
		Ajax::setReloadFlag(Ajax::$RELOAD_PLUGINS);
	}
}