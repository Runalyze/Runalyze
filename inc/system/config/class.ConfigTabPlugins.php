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
	public static function getExternalUrl() {
		return ConfigTabs::$CONFIG_URL.'?key=config_tab_plugins&external=true';
	}

	/**
	 * Set all fieldsets and fields
	 */
	public function setFieldsetsAndFields() {
		$Panels = new FormularFieldset( __('Panels') );
		$Panels->addInfo( __('Panels are small statistics always shown on the right side.') );
		$Panels->setHtmlCode($this->getCodeFor( PluginType::PANEL ));
		$Panels->setCollapsed();

		$Stats = new FormularFieldset( __('Statistics') );
		$Stats->addInfo( __('Normal statistics are shown below the activitiy log.') );
		$Stats->setHtmlCode($this->getCodeFor( PluginType::STAT ));

		$Tools = new FormularFieldset(__('Tools') );
		$Tools->addInfo( __('Complex tools for analyzing or processing the complete database will open in an overlay.') );
		$Tools->setHtmlCode($this->getCodeFor( PluginType::TOOL ));
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
		$Factory = new PluginFactory();
		$Plugins = $Factory->completeData($PluginType);
		usort($Plugins, $this->pluginOrderFunction());

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

		foreach ($Plugins as $pos => $Data) {
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
					<tr id="'.$Plugin->id().'_tr" class="a'.($Plugin->isInActive() ? ' unimportant' : '').'">
						<td>'.$Plugin->getConfigLink().'</td>
						<td class="b">'.$Plugin->name().'</td>
						<td>'.$Plugin->description().'</td>
						<td><select name="plugin_modus_'.$Plugin->id().'">
								<option value="'.Plugin::ACTIVE.'"'.HTML::Selected($Plugin->isActive()).'>'.__('enabled').'</option>
								<option value="'.Plugin::ACTIVE_VARIOUS.'"'.HTML::Selected($Plugin->isHidden()).'>'.__('hidden*').'</option>
								<option value="'.Plugin::ACTIVE_NOT.'"'.HTML::Selected($Plugin->isInActive()).'>'.__('not enabled').'</option>
							</select></td>
						<td style="white-space:nowrap;">
							<input class="plugin-position" type="text" name="plugin_order_'.$Plugin->id().'" size="3" value="'.($pos + 1).'">
							<span class="link" onclick="pluginMove('.$Plugin->id().', \'up\')">'.Icon::$UP.'</span>
							<span class="link" onclick="pluginMove('.$Plugin->id().', \'down\')">'.Icon::$DOWN.'</span>
						</td>
						<td>'.PluginInstaller::uninstallLink($Plugin->key()).'</td>
					</tr>';
			}
		}

		$Code .= '
				</tbody>
			</table>';

		$Code .= Ajax::wrapJS('
			function pluginMove(id, way) {
				var pos = parseInt($("input[name=\'plugin_order_"+id+"\']").val()),
					tr = $("#"+id+"_tr");

				if (way == "up" && pos > 1) {
					$("#"+id+"_tr .plugin-position").val(pos-1);
					tr.prev().find(".plugin-position").val(pos);
					tr.prev().toggleClass("swapped");
					tr.prev().before(tr);
				} else if (way == "down" && tr.next().find(".plugin-position").val() > 0) {
					$("#"+id+"_tr .plugin-position").val(pos+1);
					tr.next().find(".plugin-position").val(pos);
					tr.next().toggleClass("swapped");
					tr.next().after(tr);
				}

				tr.toggleClass("swapped");
			}
		');

		switch($PluginType) {
			case 'panel':
				$Code .= HTML::info(__('* Hidden plugins only show their headings.'));
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
	 * @return \Closure
	 */
	protected function pluginOrderFunction() {
		return function($a, $b){
			if ($a['active'] == $b['active']) {
				if ($a['order'] == $b['order']) {
					return ($a['id'] > $b['id']) ? 1 : -1;
				}

				return ($a['order'] > $b['order']) ? 1 : -1;
			}

			if ($a['active'] == 0) {
				return 1;
			}

			if ($b['active'] == 0) {
				return -1;
			}

			return ($a['active'] > $b['active']) ? 1 : -1;
		};
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
		$Factory = new PluginFactory();
		foreach ($Factory->completeData() as $Plugin) {
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

		$Factory->clearCache();

		Ajax::setReloadFlag(Ajax::$RELOAD_PLUGINS);
	}
}