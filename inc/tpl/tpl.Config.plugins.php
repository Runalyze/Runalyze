<h1>Plugins</h1>

<small class="right">
	<?php echo Ajax::change(Icon::get(Icon::$ADD).' Plugin installieren', 'plugin_div', 'install').NL; ?>
</small>

Plugins erweitern den Funktionsumfang dieses Lauftagebuchs ganz nach deinem Belieben.<br />
<br />

<div class="c">
	<?php
	$plugin_types = array();
	$plugin_types[] = array('type' => 'stat', 'name' => 'Statistiken', 'text' => 'Gro&szlig;e Statistiken unterhalb des Kalenders.');
	$plugin_types[] = array('type' => 'panel', 'name' => 'Panels', 'text' => 'Erweiterte Ansichten und Zusammenfassungen in der rechten Spalte');
	$plugin_types[] = array('type' => 'tool', 'name' => 'Tool', 'text' => 'Extra ansteuerbare Tools, meist zur Auswertung oder Aufbereitung der kompletten Datenbank.');
	
	foreach ($plugin_types as $i => $type)
		echo Ajax::change('<strong>'.$type['name'].'</strong>', 'plugin_div', $type['type']).($i < count($plugin_types)-1 ? ' &nbsp;&nbsp; - &nbsp;&nbsp; ' : '').NL;
	
	$plugin_types[] = array('type' => 'install', 'name' => 'Installieren', 'text' => 'Neue Plugins k&ouml;nnen hier bequem installiert werden.');
	?>
</div>

<hr />

<div id="plugin_div">
	<?php
	foreach ($plugin_types as $i => $type) {
		echo '<div id="'.$type['type'].'" class="change"'.($i == 0 ? '' : ' style="display:none;"').'>';
		echo '<table style="width:100%;">';
	
		if ($type['type'] == 'install')
			echo '<tr class="top b"><td colspan="3">Plugin</td><td colspan="2">Typ</td></tr>';
		else
			echo '<tr class="top b"><td colspan="3">'.$type['name'].'</td><td>Modus</td><td>Pos.</td></tr>';
		echo HTML::spaceTR(5);
	
		if ($type['type'] == 'install') {
			$plugins = Plugin::getPluginsToInstallAsArray();
	
			if (empty($plugins))
				echo '<tr><td colspan="5"><em>Keine Plugins zum Installieren vorhanden.</em></td></tr>';
	
			foreach ($plugins as $i => $plug) {
				$Plugin = Plugin::getInstanceFor($plug['key']);
			
				echo('
					<tr class="a'.($i%2+1).'">
						<td>'.$Plugin->getInstallLink().'</td>
						<td class="b">'.$Plugin->getInstallLink($Plugin->get('name')).'</td>
						<td class="small">'.$Plugin->get('description').'</td>
						<td colspan="2">'.Plugin::getReadableTypeString($Plugin->get('type')).'</td>
					</tr>');
			}
		} else {
			$plugins = $Mysql->fetchAsArray('SELECT `id`, `key`, `order` FROM `'.PREFIX.'plugin` WHERE `type`="'.$type['type'].'" ORDER BY FIELD(`active`, 1, 2, 0), `order` ASC');
	
			if (empty($plugins))
				echo '<tr><td colspan="5"><em>Keine Plugins vorhanden.</em></td></tr>';
	
			foreach ($plugins as $i => $plug) {
				$Plugin = Plugin::getInstanceFor($plug['key']);

				if ($Plugin === false) {
					echo '
						<tr class="a'.($i%2+1).' unimportant">
							<td></td>
							<td class="b">'.$plug['key'].'</td>
							<td colspan="3">Das Plugin konnte nicht gefunden werden.</td>
						</tr>';
				} else
					echo '
						<tr class="a'.($i%2+1).($Plugin->get('active') == Plugin::$ACTIVE_NOT ? ' unimportant' : '').'">
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
		}
		
		echo HTML::spaceTR(5);
		echo '</table>';
		echo '</div>';
	}
	?>

	<small>
		* Versteckte Plugins sind als Panel eingeklappt, als Statistik unter &quot;Sonstiges&quot; gruppiert.<br />
		Es sollten nicht mehr als acht Plugins sichtbar sein, da es dann zu Design-Problemen kommen kann.
	</small>
</div>