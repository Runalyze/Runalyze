<?php
/**
 * File displaying the config panel
 * Call:   inc/tpl/window.config.php
 */
require('../class.Frontend.php');
$Frontend = new Frontend(true, __FILE__);
$Mysql = Mysql::getInstance();
$Error = Error::getInstance();

if (isset($_POST) && isset($_POST['type']) && $_POST['type'] == "config") {
	Config::parsePostDataForConf();
	Config::parsePostDataForPlugins();
	Config::parsePostDataForDataset();
	Config::parsePostDataForSports();
	Config::parsePostDataForTypes();
	Config::parsePostDataForClothes();

	$submit = '<em>Die Einstellungen wurden gespeichert!</em><br /><br />';
}


// Because constants can't be redefinied, $config has to be used instead of CONFIG_...
$config = $Mysql->fetchSingle('SELECT * FROM `'.PREFIX.'config`');

$Frontend->displayHeader();

if (isset($submit))
	echo ('<div id="submit-info">'.$submit.'</div>');
?>

<form class="ajax" action="<?php echo $_SERVER['SCRIPT_NAME']; ?>" id="config" onsubmit="return false;" method="post">
	<div id="config_all">
		<input type="hidden" name="type" value="config" />

		<span class="right">
			<a class="change" href="#config_allgemein" target="config_all">Allgemeines</a> |
			<a class="change" href="#config_plugins" target="config_all">Plugins</a> |
			<a class="change" href="#config_dataset" target="config_all">Dataset</a> |
			<a class="change" href="#config_sport" target="config_all">Sportarten</a> |
			<a class="change" href="#config_typen" target="config_all">Trainingstypen</a>
<?php if ($config['use_kleidung'] == 1): ?> |
			<a class="change" href="#config_kleidung" target="config_all">Kleidung</a>
<?php endif; ?>
		</span>

<div id="config_allgemein" class="change">
	<h1>Allgemeine Einstellungen</h1>

	<div class="c">
<?php
$categories = $Mysql->fetch('SELECT `category` FROM `'.PREFIX.'conf` GROUP BY `category`');
foreach ($categories as $i => $cat)
	echo Ajax::change('<strong>'.$cat['category'].'</strong>', 'conf_div', strtolower($cat['category'])).($i < count($categories)-1 ? ' &nbsp; - &nbsp; ' : '').NL;
?>
	</div>

	<hr />

	<div id="conf_div">
<?php
foreach ($categories as $i => $cat) {
	echo '<div id="'.strtolower($cat['category']).'" class="change"'.($i == 0 ? '' : ' style="display:none;"').'>';

	$confs = $Mysql->fetchAsArray('SELECT * FROM `'.PREFIX.'conf` WHERE `category`="'.$cat['category'].'"');

	if (empty($confs))
		echo '<em>Keine Konfigurationsvariablen vorhanden vorhanden.</em>';

	foreach ($confs as $i => $conf) {
		echo '<label>';
		echo Config::getInputField($conf).NL;
		echo '<strong>'.$conf['description'].'</strong>';
		if ($conf['type'] == 'array')
			echo ' <small>(kommagetrennt)</small>';
		echo '</label><br />';
	}

	echo '</div>';
}
?>
	</div>
</div>

<?php
// TODO: Download-Link
?>
<div id="config_plugins" class="change" style="display:none;">
	<h1>Plugins</h1>

	<small class="right">
		<?php echo Ajax::change(Icon::get(Icon::$ADD, 'Plugin installieren').' Plugin installieren', 'plugin_div', 'install').NL; ?>
	</small>

	Plugins erweitern den Funktionsumfang dieses Lauftagebuchs ganz nach deinem Belieben.<br />
	<br />

	<div class="c">
<?php
$plugin_types = array();
$plugin_types[] = array('type' => 'stat', 'name' => 'Statistiken', 'text' => 'Gro&szlig;e Statistiken unterhalb des Kalenders.');
$plugin_types[] = array('type' => 'panel', 'name' => 'Panels', 'text' => 'Erweiterte Ansichten und Zusammenfassungen in der rechten Spalte');
$plugin_types[] = array('type' => 'draw', 'name' => 'Diagramme', 'text' => 'Zus&auml;tzliche Diagramme, meist eingebunden durch ein Statistik-Plugin.');
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
	echo Helper::spaceTR(5);

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
		
			echo('
				<tr class="a'.($i%2+1).($Plugin->get('active') == Plugin::$ACTIVE_NOT ? ' unimportant' : '').'">
					<td>'.$Plugin->getConfigLink().'</td>
					<td class="b">'.$Plugin->get('name').'</td>
					<td class="small">'.$Plugin->get('description').'</td>
					<td><select name="plugin_modus_'.$Plugin->get('id').'">
							<option value="'.Plugin::$ACTIVE.'"'.Helper::Selected($Plugin->get('active') == Plugin::$ACTIVE).'>aktiviert</option>
							<option value="'.Plugin::$ACTIVE_VARIOUS.'"'.Helper::Selected($Plugin->get('active') == Plugin::$ACTIVE_VARIOUS).'>versteckt*</option>
							<option value="'.Plugin::$ACTIVE_NOT.'"'.Helper::Selected($Plugin->get('active') == Plugin::$ACTIVE_NOT).'>nicht aktiviert</option>
						</select></td>
					<td><input type="text" name="plugin_order_'.$Plugin->get('id').'" size="3" value="'.$Plugin->get('order').'" /></td>
				</tr>');
		}
	}
	
	echo Helper::spaceTR(5);
	echo '</table>';
	echo '</div>';
}
?>

		<small>* Versteckte Plugins sind als Panel eingeklappt, als Statistik unter &quot;Sonstiges&quot; gruppiert.</small>
	</div>
</div>

<div id="config_dataset" class="change" style="display:none;">
	<h1>Dataset</h1>

	Das Dataset bestimmt, welche Daten der Trainings in der &Uuml;bersicht angezeigt werden.<br />
		<br />

	<table class="c">
		<tr>
			<td title="Die Information wird in der Tabelle direkt angezeigt">Anzeige</td>
			<td title="Die Daten werden f&uuml;r die Zusammenfassung der Sportart angezeigt">Zusammenfassung</td>
			<td style="width: 170px;" />
			<td title="Gibt die Reihenfolge der Anzeige vor">Position</td>
		</tr>
		<tr class="space">
			<td colspan="4"></td>
		</tr>
<?php
$datasets = $Mysql->fetchAsArray('SELECT *, (`position` = 0) as `hidden` FROM `'.PREFIX.'dataset` ORDER BY `hidden` ASC, ABS(2.5-`modus`) ASC, `position` ASC');
foreach($datasets as $i => $dataset) {
	// Modus=1 has been deleted
	$disabled = ($dataset['modus'] == 3) ? ' disabled="disabled"' : '';
	$checked_2 = ($dataset['modus'] >= 2) ? ' checked="checked"' : '';
	$checked_0 = ($dataset['modus'] == 0) ? ' checked="checked"' : '';
	$checked = ($dataset['zusammenfassung'] == 1) ? ' checked="checked"' : '';
	if ($dataset['zf_mode'] == "YES" || $dataset['zf_mode'] == "NO")
		$checked .= ' disabled="disabled"';
	echo('
		<tr class="a'.($i%2+1).'">
			<td>
				<input type="hidden" name="'.$dataset['id'].'_modus_3" value="'.$dataset['modus'].'" />
				<input type="checkbox" name="'.$dataset['id'].'_modus"'.$checked_2.$disabled.' />
			</td>
			<td><input type="checkbox" name="'.$dataset['id'].'_zusammenfassung"'.$checked.' /></td>
			<td title="'.$dataset['beschreibung'].'">'.$dataset['name'].'</td>
			<td><input type="text" name="'.$dataset['id'].'_position" value="'.$dataset['position'].'" size="2" /></td>
		</tr>');
}
?>
	</table>
</div>

<div id="config_sport" class="change" style="display:none;">
	<h1>Sportarten</h1>

	Fahre mit der Maus &uuml;ber die &Uuml;berschrift, falls dir die Bezeichnungen unklar sind.<br />

		<br />

	<table class="c" style="width:100%;">
		<tr class="b">
			<td title="Diese Sportart wird verwendet" class="small">Aktiv</td>
			<td class="small" title="Es wird nur ein Symbol vor dem jeweiligen Tag angezeigt">Kurz</td>
			<td colspan="2">Sportart</td>
			<td title="Durchschnittlicher Energieumsatz in Kilokalorien pro Stunde">kcal/h</td>
			<td title="Die durchschnittliche Herzfrequenz (wird z.B. f&uuml;r TRIMP verwendet)">&Oslash; HF</td>
			<td title="Rating of Perceived Exertion (nach Borg) = durchschnittliche Anstrengung auf einer Skala von 1 (leicht) bis 10 (extrem hart)">RPE</td>
			<td title="Es wird eine Distanz zur&uuml;ckgelegt">km</td>
			<td title="Tempoanzeige in km/h statt min/km">kmh</td>
			<td title="Es werden Trainingstypen wie Intervalltraining verwendet">Typen</td>
			<td title="Der Puls wird dabei aufgezeichnet">Puls</td>
			<td title="Der Sport wird an der freien Luft betrieben (Strecke/Wetter)">Drau&szlig;en</td>
		</tr>
		<tr class="space">
			<td colspan="12"></td>
		</tr>
<?php
$sports = $Mysql->fetchAsArray('SELECT * FROM `'.PREFIX.'sports` ORDER BY `id` ASC');
$sports[] = array('new' => true, 'online' => 1, 'short' => 0, 'kalorien' => '', 'HFavg' => '', 'RPE' => '', 'distanztyp' => 0, 'kmh' => 0, 'typen' => 0, 'pulstyp' => 0, 'outside' => '');
foreach($sports as $i => $sport) {
	if (isset($sport['new'])) {
		$icon = '?';
		$name = '<input type="text" name="sport[name]['.$i.']" value="" />';
	} else {
		$icon = Icon::getSportIcon($sport['id']);
		$name = '<input type="hidden" name="sport[name]['.$i.']" value="'.$sport['name'].'" />'.$sport['name'];
	}
	echo('
		<tr class="a'.($i%2+1).($icon == '?' ? ' unimportant' : '').'">
			<td><input type="checkbox" name="sport[online]['.$i.']" '.($sport['online'] == 1 ? 'checked="checked" ' : '').'/></td>
			<td><input type="checkbox" name="sport[short]['.$i.']" '.($sport['short'] == 1 ? 'checked="checked" ' : '').'/></td>
			<td>'.$icon.'</td>
			<td>'.$name.'</td>
			<td><input type="text" size="3" name="sport[kalorien]['.$i.']" value="'.$sport['kalorien'].'" /></td>
			<td><input type="text" size="3" name="sport[HFavg]['.$i.']" value="'.$sport['HFavg'].'" /></td>
			<td><input type="text" size="1" name="sport[RPE]['.$i.']" value="'.$sport['RPE'].'" /></td>
			<td><input type="checkbox" name="sport[distanztyp]['.$i.']" '.($sport['distanztyp'] == 1 ? 'checked="checked" ' : '').'/></td>
			<td><input type="checkbox" name="sport[kmh]['.$i.']" '.($sport['kmh'] == 1 ? 'checked="checked" ' : '').'/></td>
			<td><input type="checkbox" name="sport[typen]['.$i.']" '.($sport['typen'] == 1 ? 'checked="checked" ' : '').'/></td>
			<td><input type="checkbox" name="sport[pulstyp]['.$i.']" '.($sport['pulstyp'] == 1 ? 'checked="checked" ' : '').'/></td>
			<td><input type="checkbox" name="sport[outside]['.$i.']" '.($sport['outside'] == 1 ? 'checked="checked" ' : '').'/></td>
		</tr>');
}
?>
	</table>
</div>

<div id="config_typen" class="change" style="display:none;">
	<h1>Trainingstypen:</h1>

	Mit Trainingstypen k&ouml;nnen die Trainings bequem in Kategorien sortiert werden,
	das dient vor allem der Trainingsanalyse.
	Bestehende Trainingstypen k&ouml;nnen aber nur gel&ouml;scht werden, wenn keine Referenzen bestehen.
	Daher sind die Trainingstypen mit ihren Trainings verlinkt.

	<hr />

	<table class="c">
		<tr class="b">
			<td>Trainingstyp</td>
			<td>Abk&uuml;rzung</td>
			<td title="Rating of Perceived Exertion (nach Borg) = durchschnittliche Anstrengung auf einer Skala von 1 (leicht) bis 10 (extrem hart)">RPE</td>
			<td title="Es werden einzelne Kilometerabschnitte aufgezeichnet">Splits</td>
			<td title="Ein Trainingstyp kann nur gel&ouml;scht werden, wenn keine Referenzen bestehen">l&ouml;schen?</td>
		</tr>
		<tr class="space">
			<td colspan="5"></td>
		</tr>
<?php
$typen = $Mysql->fetchAsArray('SELECT * FROM `'.PREFIX.'typ` ORDER BY `id` ASC');
$typen[] = array('id' => -1, 'name' => '', 'abk' => '', 'RPE' => 5, 'splits' => 0);

foreach($typen as $i => $typ) {
	$num = $Mysql->num('SELECT `id` FROM `'.PREFIX.'training` WHERE `typid`="'.$typ['id'].'"');
	if ($typ['id'] == -1)
		$delete = '';
	elseif ($num == 0)
		$delete = '<input type="checkbox" name="typ[delete]['.$i.']" />';
	else
		$delete = DataBrowser::getSearchLink('<small>('.$num.')</small>', 'opt[typid]=is&val[typid][0]='.$typ['id']);

	echo('
		<tr class="a'.($i%2+1).($typ['id'] == -1 ? ' unimportant' : '').'">
			<td><input type="text" size="20" name="typ[name]['.$i.']" value="'.$typ['name'].'" /></td>
			<td><input type="text" size="3" name="typ[abk]['.$i.']" value="'.$typ['abk'].'" /></td>
			<td><input type="text" size="1" name="typ[RPE]['.$i.']" value="'.$typ['RPE'].'" /></td>
			<td><input type="checkbox" name="typ[splits]['.$i.']" '.Helper::Checked($typ['splits'] == 1).'/></td>
			<td>'.$delete.'</td>
		</tr>');
}
?>
	</table>
</div>

<div id="config_kleidung" class="change" style="display:none;">
	<h1>Kleidung:</h1>

	Die Kleidung kann wenn gew&uuml;nscht f&uuml;r weitere Statistiken bei jedem Training protokolliert werden.
	Alle Kleidungsst&uuml;cke werden nach Kategorie geordnet mit der Abk&uuml;rzung im Formular angezeigt.<br />

		<br />

	<table class="c">
		<tr class="b">
			<td>Name</td>
			<td>Abk&uuml;rzung</td>
			<td>Kategorie</td>
			<td>l&ouml;schen?</td>
		</tr>
		<tr class="space">
			<td colspan="4"></td>
		</tr>
<?php
$kleidungen = $Mysql->fetchAsArray('SELECT * FROM `'.PREFIX.'kleidung` ORDER BY `order`, `id` ASC');
$kleidungen[] = array('new' => true, 'name' => '', 'name_kurz' => '', 'order' => '');

foreach($kleidungen as $i => $kleidung) {
	if (isset($kleidung['new'])) {
		$delete = '';
	} else {
		$delete = '<input type="checkbox" name="kleidung[delete]['.$i.']" />';
	}
	echo('
		<tr class="a'.($i%2+1).($delete == '' ? ' unimportant' : '').'">
			<td><input type="text" size="30" name="kleidung[name]['.$i.']" value="'.$kleidung['name'].'" /></td>
			<td><input type="text" size="15" name="kleidung[name_kurz]['.$i.']" value="'.$kleidung['name_kurz'].'" /></td>
			<td><input type="text" size="4" name="kleidung[order]['.$i.']" value="'.$kleidung['order'].'" /></td>
			<td>'.$delete.'</td>
		</tr>');
}
?>
	</table>
</div>



		<div class="c">
			<input type="submit" value="Einstellungen speichern" />
		</div>
	</div>
</form>

<?php
$Frontend->displayFooter();
$Frontend->close();
?>