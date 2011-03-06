<?php
/**
 * File displaying the config panel
 * Call:   inc/tpl/window.config.php
 */
require('../class.Frontend.php');
$Frontend = new Frontend(true, __FILE__);
$Mysql = Mysql::getInstance();
$Error = Error::getInstance();

$Error->addTodo('Formular abschicken funktioniert noch nicht vollständig', __FILE__, __LINE__);
if (isset($_POST) && $_POST['type'] == "config") {
	// die('FUNKTIONIERT NOCH NICHT!');

	// General config vars: 'ltb_config'
	$columns = array();
	$values = array();
	$vars = array('geschlecht', 'puls_mode');
	foreach($vars as $var)
		if (isset($_POST[$var])) {
			$columns[] = $var;
			$values[] = Helper::CommaToPoint($_POST[$var]);
		}
	$checkboxes = array('use_schuhe', 'use_splits', 'use_puls', 'use_kleidung', 'use_temperatur', 'use_wetter', 'use_strecke');
	foreach($checkboxes as $box) {
		$columns[] = $box;
		$values[] = (isset($_POST[$box]) && $_POST[$box] == 'on') ? 1 : 0;
	}
	$Mysql->update('ltb_config', 1, $columns, $values);

	// Plugin config vars: 'ltb_plugin'
	$plugins = $Mysql->fetch('SELECT `id` FROM `ltb_plugin`');
	foreach($plugins as $plugin) {
		$id = $plugin['id'];
		$Mysql->update('ltb_plugin', $id,
			array('active', 'order'),
			array($_POST['plugin_modus_'.$id], $_POST['plugin_order_'.$id]));
	}

	$submit = '<em>Die Einstellungen wurden gespeichert!</em><br /><br />';
}

// Because constants can't be redefinied, $config has to be used instead of CONFIG_...
$config = $Mysql->fetch('SELECT * FROM `ltb_config` LIMIT 1');

$Frontend->displayHeader();

if (isset($submit))
	echo ('<div id="submit-info">'.$submit.'</div>');
?>

<form class="ajax" action="<?php echo $_SERVER['SCRIPT_NAME']; ?>" id="config" onsubmit="return false;" method="post">
	<div id="config_all">
		<input type="hidden" name="type" value="config" />

		<span class="right">
			<a class="change" href="#config_allgemein" target="config_all">Allgemeines</a> |
			<a class="change" href="#config_plugins_panel" target="config_all">Panel-Plugins</a> |
			<a class="change" href="#config_plugins_stat" target="config_all">Stat-Plugins</a> |
			<a class="change" href="#config_dataset" target="config_all">Dataset</a> |
			<a class="change" href="#config_sport" target="config_all">Sportarten</a> |
			<a class="change" href="#config_typen" target="config_all">Trainingstypen</a> |
			<a class="change" href="#config_kleidung" target="config_all">Kleidung</a>
		</span>

		<br class="clear" />

<div id="config_allgemein" class="change">
	<h1>Allgemeine Einstellungen</h1>
<?php $Error->addTodo('Weitere Config-Variablen', __FILE__, __LINE__); ?>
<?php $Error->addTodo('Weitere Config-Variablen -&gt; nur entsprechende DIVs anzeigen', __FILE__, __LINE__); ?>
	<strong>Geschlecht:</strong><br />
		<input type="radio" name="geschlecht" value="m"<?php echo Helper::Checked($config['geschlecht'], 'm'); ?> />
			m&auml;nnlich<br />
		<input type="radio" name="geschlecht" value="w"<?php echo Helper::Checked($config['geschlecht'], 'w'); ?> />
			weiblich<br />
		<br />
	<strong>Herzfrequenz-Darstellung:</strong><br />
		<input type="radio" name="puls_mode" value="bpm"<?php echo Helper::Checked($config['puls_mode'], 'bpm'); ?> />
			absoluter Wert<br />
		<input type="radio" name="puls_mode" value="hfmax"<?php echo Helper::Checked($config['puls_mode'], 'hfmax'); ?> />
			&#37; <abbr title="maximale Herzfrequenz">HFmax</abbr><br />
		<br />
	<strong>W&auml;hle aus, welche der folgenden Daten du f&uuml;r jedes Training protokollieren m&ouml;chtest:</strong><br />
	<input type="checkbox" name="use_schuhe"<?php echo Helper::Checked($config['use_schuhe'], 1); ?> />
		Laufschuh<br />
	<input type="checkbox" name="use_splits"<?php echo Helper::Checked($config['use_splits'], 1); ?> />
		Zwischenzeiten<br />
	<input type="checkbox" name="use_puls"<?php echo Helper::Checked($config['use_puls'], 1); ?> />
		Puls<br />
	<input type="checkbox" name="use_kleidung"<?php echo Helper::Checked($config['use_kleidung'], 1); ?> />
		Kleidung<br />
	<input type="checkbox" name="use_temperatur"<?php echo Helper::Checked($config['use_temperatur'], 1); ?> />
		Temperatur<br />
	<input type="checkbox" name="use_wetter"<?php echo Helper::Checked($config['use_wetter'], 1); ?> />
		Wetter<br />
	<input type="checkbox" name="use_strecke"<?php echo Helper::Checked($config['use_strecke'], 1); ?> />
		Strecke<br />
</div>

<div id="config_plugins_panel" class="change" style="display:none;">
	<h1>Panel-Plugins</h1>

	Plugins erweitern den Funktionsumfang dieses Lauftagebuchs ganz nach deinem Belieben.
	Panel-Plugins werden durchgehend auf der rechten Seite angezeigt.
	W&auml;hle hier aus, welche Plugins du verwenden m&ouml;chtest.<br />
		<br />
	Beachte dabei bitte, dass einige Plugins die Nutzung gewisser Felder voraussetzen (z.B. den Puls).
	Die Einstellungen eines Plugins k&ouml;nnen nach einem Klick auf das Symbol ge&auml;ndert werden.
	Dabei werden aber alle &Auml;nderungen hier verworfen.<br />

	<br />

	<table cellspacing="0">
		<tr class="top b">
			<td colspan="3">Panels</td>
			<td class="small">Ja</td>
			<td class="small">eingeklappt</td>
			<td class="small">Nein</td>
			<td class="small">Pos.</td>
		</tr>
		<tr class="space">
			<td colspan="7"></td>
		</tr>
<?php
$plugins = $Mysql->fetch('SELECT * FROM `ltb_plugin` WHERE `type`="panel" ORDER BY `order` ASC');
foreach($plugins as $i => $plugin)
	echo('
		<tr class="top a'.($i%2+1).'">
			<td>'.Ajax::window('<a href="inc/class.Panel.config.php?id='.$plugin['id'].'" title="Plugin bearbeiten"><img src="img/confSettings.png" alt="Plugin bearbeiten" /></a>','small').'</td>
			<td class="b">'.$plugin['name'].'</td>
			<td class="small">'.$plugin['description'].'</td>
			<td><input type="radio" name="plugin_modus_'.$plugin['id'].'" value="1"'.Helper::Checked($plugin['active'], 1).' /></td>
			<td><input type="radio" name="plugin_modus_'.$plugin['id'].'" value="2"'.Helper::Checked($plugin['active'], 2).' /></td>
			<td><input type="radio" name="plugin_modus_'.$plugin['id'].'" value="0"'.Helper::Checked($plugin['active'], 0).' /></td>
			<td><input type="text" name="plugin_order_'.$plugin['id'].'" size="3" value="'.$plugin['order'].'" /></td>
		</tr>');
?>
	</table>
</div>

<div id="config_plugins_stat" class="change" style="display:none;">
	<h1>Stat-Plugins</h1>

	Plugins erweitern den Funktionsumfang dieses Lauftagebuchs ganz nach deinem Belieben.<br />
	Statistik-Plugins werden unterhalb der Trainingseinheiten angezeigt.
	Unwichtigere und kleinere Statistik-Plugins k&ouml;nnen unter dem Reiter &quot;Sonstiges&quot; zusammengefasst werden.
	W&auml;hle hier aus, welche Plugins du verwenden m&ouml;chtest.<br />
		<br />
	Beachte dabei bitte, dass einige Plugins die Nutzung gewisser Felder voraussetzen (z.B. den Puls).
	Die Einstellungen eines Plugins k&ouml;nnen nach einem Klick auf das Symbol ge&auml;ndert werden.
	Dabei werden aber alle &Auml;nderungen hier verworfen.<br />

	<br />

	<table cellspacing="0">
		<tr class="top b">
			<td colspan="3">Statistiken</td>
			<td class="small">Ja</td>
			<td class="small" title="Das Plugin wird unter dem Reiter Sonstiges gruppiert">Sonst.</td>
			<td class="small">Nein</td>
			<td class="small">Pos.</td>
		</tr>
		<tr class="space">
			<td colspan="7"></td>
		</tr>
<?php
$plugins = $Mysql->fetch('SELECT * FROM `ltb_plugin` WHERE `type`="stat" ORDER BY `order` ASC');
foreach($plugins as $i => $plugin)
	echo('
		<tr class="top a'.($i%2+1).'">
			<td>'.Ajax::window('<a href="inc/class.Stat.config.php?id='.$plugin['id'].'" title="Plugin bearbeiten"><img src="img/confSettings.png" alt="Plugin bearbeiten" /></a>','small').'</td>
			<td class="b">'.$plugin['name'].'</td>
			<td class="small">'.$plugin['description'].'</td>
			<td><input type="radio" name="plugin_modus_'.$plugin['id'].'" value="1"'.Helper::Checked($plugin['active'], 1).' /></td>
			<td><input type="radio" name="plugin_modus_'.$plugin['id'].'" value="2"'.Helper::Checked($plugin['active'], 2).' /></td>
			<td><input type="radio" name="plugin_modus_'.$plugin['id'].'" value="0"'.Helper::Checked($plugin['active'], 0).' /></td>
			<td><input type="text" name="plugin_order_'.$plugin['id'].'" size="3" value="'.$plugin['order'].'" /></td>
		</tr>');
?>
	</table>
</div>

<div id="config_dataset" class="change" style="display:none;">
	<h1>Dataset</h1>

	Das Dataset bestimmt, welche Daten der Trainings in der &Uuml;bersicht angezeigt werden.<br />
		<br />

	<table cellspacing="0" class="c">
		<tr>
			<td title="Die Information wird in der Tabelle direkt angezeigt">Anzeige</td>
			<td title="Die Daten werden für die Zusammenfassung der Sportart angezeigt">Zusammenfassung</td>
			<td style="width: 170px;" />
			<td title="Gibt die Reihenfolge der Anzeige vor">Position</td>
		</tr>
		<tr class="space">
			<td colspan="4"></td>
		</tr>
<?php
$datasets = $Mysql->fetch('SELECT * FROM `ltb_dataset` ORDER BY ABS(2.5-`modus`) ASC, `position` ASC');
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

	<table cellspacing="0" class="c" width="100%">
		<tr class="b">
			<td title="Diese Sportart wird verwendet" class="small">Aktiv</td>
			<td class="small" title="Es wird nur ein Symbol vor dem jeweiligen Tag angezeigt">Kurz</td>
			<td colspan="2">Sportart</td>
			<td title="Durchschnittlicher Energieumsatz in Kilokalorien pro Stunde">kcal/h</td>
			<td title="Die durchschnittliche Herzfrequenz (wird z.B. für TRIMP verwendet)">&Oslash; HF</td>
			<td title="Rating of Perceived Exertion (nach Borg) = durchschnittliche Anstrengung auf einer Skala von 1 (leicht) bis 10 (extrem hart)">RPE</td>
			<td title="Es wird eine Distanz zurückgelegt">km</td>
			<td title="Tempoanzeige in km/h statt min/km">kmh</td>
			<td title="Es werden Trainingstypen wie Intervalltraining verwendet">Typen</td>
			<td title="Der Puls wird dabei aufgezeichnet">Puls</td>
			<td title="Der Sport wird an der freien Luft betrieben (Strecke/Wetter)">Drau&szlig;en</td>
		</tr>
		<tr class="space">
			<td colspan="12"></td>
		</tr>
<?php
$Error->addTodo('Edit Sports', __FILE__, __LINE__);
// TODO ID=1 für Laufen sperren!

$sports = $Mysql->fetch('SELECT * FROM `ltb_sports` ORDER BY `id` ASC');
foreach($sports as $i => $sport) {
	echo('
		<tr class="a'.($i%2+1).'">
			<td><input type="checkbox" name="" disabled="disabled" '.($sport['online'] == 1 ? 'checked="checked" ' : '').'/></td>
			<td><input type="checkbox" name="" disabled="disabled" '.($sport['short'] == 1 ? 'checked="checked" ' : '').'/></td>
			<td><img src="img/sports/'.$sport['bild'].'" /></td>
			<td>'.$sport['name'].'</td>
			<td><input type="text" size="3" name="" disabled="disabled" value="'.$sport['kalorien'].'" /></td>
			<td><input type="text" size="3" name="" disabled="disabled" value="'.$sport['HFavg'].'" /></td>
			<td><input type="text" size="1" name="" disabled="disabled" value="'.$sport['RPE'].'" /></td>
			<td><input type="checkbox" name="" disabled="disabled" '.($sport['distanztyp'] == 1 ? 'checked="checked" ' : '').'/></td>
			<td><input type="checkbox" name="" disabled="disabled" '.($sport['kmh'] == 1 ? 'checked="checked" ' : '').'/></td>
			<td><input type="checkbox" name="" disabled="disabled" '.($sport['typen'] == 1 ? 'checked="checked" ' : '').'/></td>
			<td><input type="checkbox" name="" disabled="disabled" '.($sport['pulstyp'] == 1 ? 'checked="checked" ' : '').'/></td>
			<td><input type="checkbox" name="" disabled="disabled" '.($sport['outside'] == 1 ? 'checked="checked" ' : '').'/></td>
		</tr>');
}
?>
	</table>
</div>

<div id="config_typen" class="change" style="display:none;">
	<h1>Trainingstypen:</h1>

	<table cellspacing="0" class="c" width="100%">
		<tr class="b">
			<td>Trainingstyp</td>
			<td>Abk&uuml;rzung</td>
			<td title="Rating of Perceived Exertion (nach Borg) = durchschnittliche Anstrengung auf einer Skala von 1 (leicht) bis 10 (extrem hart)">RPE</td>
			<td title="Es werden einzelne Kilometerabschnitte aufgezeichnet">Splits</td>
		</tr>
		<tr class="space">
			<td colspan="4"></td>
		</tr>
<?php
$Error->addTodo('Edit Trainingstypen', __FILE__, __LINE__);
$Error->addTodo('Edit Trainingstypen: WK_TYPID', __FILE__, __LINE__);

$typen = $Mysql->fetch('SELECT * FROM `ltb_typ` ORDER BY `id` ASC');
foreach($typen as $i => $typ) {
	echo('
		<tr class="a'.($i%2+1).'">
			<td>'.$typ['name'].'</td>
			<td><input type="text" size="3" name="" disabled="disabled" value="'.$typ['abk'].'" /></td>
			<td><input type="text" size="1" name="" disabled="disabled" value="'.$typ['RPE'].'" /></td>
			<td><input type="checkbox" name="" disabled="disabled" '.($typ['splits'] == 1 ? 'checked="checked" ' : '').'/></td>
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

	<table cellspacing="0" class="c" width="80%">
		<tr class="b">
			<td>Name</td>
			<td>Abk&uuml;rzung</td>
			<td>Kategorie</td>
		</tr>
		<tr class="space">
			<td colspan="3"></td>
		</tr>
<?php
$Error->addTodo('Edit Kleidungen', __FILE__, __LINE__);

$kleidungen = $Mysql->fetch('SELECT * FROM `ltb_kleidung` ORDER BY `order`, `id` ASC');
foreach($kleidungen as $i => $kleidung) {
	echo('
		<tr class="a'.($i%2+1).'">
			<td>'.$kleidung['name'].'</td>
			<td>'.$kleidung['name_kurz'].'</td>
			<td>'.$kleidung['order'].'</td>
		</tr>');
}
?>
	</table>
</div>



		<center>
			<input type="submit" value="Einstellungen speichern" />
		</center>
	</div>
</form>

<?php
$Frontend->displayFooter();
$Frontend->close();
?>