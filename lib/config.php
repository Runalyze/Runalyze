<?php header('Content-type: text/html; charset=ISO-8859-1');
include_once('../config/functions.php');
connect();

if ($_GET['submit'] == "true") {
	mysql_query('UPDATE `ltb_config` SET
	`geschlecht`="'.$_POST['geschlecht'].'",
	`wunschgewicht`="'.$_POST['wunschgewicht'].'",
	`use_gewicht`="'.$_POST['use_gewicht'].'",
	`use_koerperfett`="'.$_POST['use_koerperfett'].'",
	`use_ruhepuls`="'.$_POST['use_ruhepuls'].'",
	`use_blutdruck`="'.$_POST['use_blutdruck'].'",
	`puls_mode`="'.$_POST['puls_mode'].'"') or die(mysql_error());

	$panel_db = mysql_query('SELECT * FROM `ltb_modules`');
	while ($panel = mysql_fetch_assoc($panel_db))
	mysql_query('UPDATE `ltb_modules` SET `use`='.$_POST[$panel['name']].' WHERE `name`="'.$panel['name'].'" LIMIT 1');

	for ($i = 1; $i <= mysql_num_rows(mysql_query('SELECT `id` FROM `ltb_dataset`')); $i++) {
		$modus = ($_POST[$i.'_modus_3'] == 3) ? '3' : $_POST[$i.'_modus'];
		mysql_query('UPDATE `ltb_dataset` SET
`modus`="'.$modus.'",
`zusammenfassung`="'.$_POST[$i.'_zusammenfassung'].'",
`position`="'.$_POST[$i.'_position'].'"
		WHERE `id`='.$i.' LIMIT 1') or die(mysql_error());
	}

	close();
	connect();
}

function check_sex($sex) {
	global $config;
	echo ($config['geschlecht'] == $sex) ? ' checked="checked"' : '';
}

function check_puls($mode) {
	global $config;
	echo ($config['puls_mode'] == $mode) ? ' checked="checked"' : '';
}

function check_config($name) {
	global $config;
	echo ($config[$name] == 1) ? ' checked="checked"' : '';
}
?>
<img
	id="close" src="img/cross.png" onClick="ajax_close()" />

<h1>Einstellungen</h1>

<form action="javascript:submit_config()" name="config" method="post">
<div class="right"><span class="link"
	onClick="show('div-allg');unshow('div-dataset');unshow('div-kleidung');unshow('div-sport');unshow('div-typen');">Allgemeines</span>
| <span class="link"
	onClick="unshow('div-allg');show('div-dataset');unshow('div-kleidung');unshow('div-sport');unshow('div-typen');">Dataset</span>
| <span class="link"
	onClick="unshow('div-allg');unshow('div-dataset');show('div-kleidung');unshow('div-sport');unshow('div-typen');">Kleidung</span>
| <span class="link"
	onClick="unshow('div-allg');unshow('div-dataset');unshow('div-kleidung');show('div-sport');unshow('div-typen');">Sportarten</span>
| <span class="link"
	onClick="unshow('div-allg');unshow('div-dataset');unshow('div-kleidung');unshow('div-sport');show('div-typen');">Typen</span>
</div>

<br class="clear" />

<div id="div-allg"><strong>Allgemeines:</strong> <br />

<small class="spacer">Puls-Anzeige:</small> <input type="radio"
	name="puls_mode" <?php check_puls('bpm');?> value="bpm" /> absoluter
Wert <input type="radio" name="puls_mode" <?php check_puls('hfmax');?>
	value="hfmax" class="spacer" /> &#37; HFmax <br />

<small class="spacer">Geschlecht:</small> <input type="radio"
	name="geschlecht" <?php check_sex('m');?> value="m" /> m&auml;nnlich <input
	type="radio" name="geschlecht" <?php check_sex('w');?> value="w"
	class="spacer" /> weiblich <br />

<small class="spacer">Protokoll:</small> <input type="checkbox"
	name="use_gewicht" <?php check_config('use_gewicht');?> /> Gewicht <input
	type="checkbox" name="use_koerperfett"
	<?php check_config('use_koerperfett');?> class="spacer" />
K&ouml;rperfett <input type="checkbox" name="use_ruhepuls"
<?php check_config('use_ruhepuls');?> class="spacer" /> Ruhepuls <input
	type="checkbox" name="use_blutdruck"
	<?php check_config('use_blutdruck');?> class="spacer" /> Blutdruck <br />

<small class="spacer"
	title="Wenn das Wunschgewicht aktiviert wird, wird es mit einer Linie im Diagramm angezeigt.">Wunschgewicht:</small>
<input type="text" size="3" name="wunschgewicht"
	value="<?php echo($config['wunschgewicht']); ?>"
	<?php if ($config['wunschgewicht'] == 0) echo(' disabled="disabled"'); ?> />
<input type="checkbox" name="use_wunschgewicht"
	onChange="check_wunschgewicht()"
	<?php if ($config['wunschgewicht'] != 0) echo(' checked="checked"'); ?> />
<br />

<strong>Infok&auml;sten:</strong> (rechts) <br />
	<?php
	$panel_db = mysql_query('SELECT * FROM `ltb_modules`');
	while ($panel = mysql_fetch_assoc($panel_db)) {
		$checked = ($panel['use'] == 1) ? ' checked="checked"' : '';
		echo('  <input class="spacer" type="checkbox" name="'.$panel['name'].'"'.$checked.' /> <strong title="'.$panel['beschreibung'].'">'.$panel['name'].'</strong><br />');
	}
	?></div>

<div id="div-dataset" style="display: none;"><strong>Dataset:</strong>

<table cellpadding="0" cellspacing="3px" class="nopadding c">
	<tr>
		<td title="Die Information wird in der Tabelle direkt angezeigt">Anzeige</td>
		<td title="Die Zusatzinformation wird erst nach einem Klick angezeigt">Extra</td>
		<td title="Die Information wird gar nicht angezeigt">Keine</td>
		<td
			title="Die Daten werden für die Zusammenfassung der Sportart angezeigt">Zusammenfassung</td>
		<td style="width: 170px;" />
		
		
		<td title="Gibt die Reihenfolge der Anzeige vor">Position</td>
	</tr>
	<?php
	$dataset_db = mysql_query('SELECT * FROM `ltb_dataset` ORDER BY ABS(2.5-`modus`) ASC, `position` ASC');
	while ($dataset = mysql_fetch_assoc($dataset_db)) {
		$disabled = ($dataset['modus'] == 3) ? ' disabled="disabled"' : '';
		$checked_2 = ($dataset['modus'] >= 2) ? ' checked="checked"' : '';
		$checked_1 = ($dataset['modus'] == 1) ? ' checked="checked"' : '';
		$checked_0 = ($dataset['modus'] == 0) ? ' checked="checked"' : '';
		$checked = ($dataset['zusammenfassung'] == 1) ? ' checked="checked"' : '';
		if ($dataset['zf_mode'] == "YES" || $dataset['zf_mode'] == "NO")
		$checked .= ' disabled="disabled"';
		echo('
			<tr>
				<td>
					<input type="hidden" name="'.$dataset['id'].'_modus_3" value="'.$dataset['modus'].'" />
					<input type="radio" value="2" name="'.$dataset['id'].'_modus"'.$checked_2.$disabled.' />
				</td>
				<td><input type="radio" value="1" name="'.$dataset['id'].'_modus"'.$checked_1.$disabled.' /></td>
				<td><input type="radio" value="0" name="'.$dataset['id'].'_modus"'.$checked_0.$disabled.' /></td>
				<td><input type="checkbox" name="'.$dataset['id'].'_zusammenfassung"'.$checked.' /></td>
				<td title="'.$dataset['beschreibung'].'">'.$dataset['name'].'</td>
				<td><input type="text" name="'.$dataset['id'].'_position" value="'.$dataset['position'].'" size="2" /></td>
			</tr>');
	}
	?>
</table>
</div>

<div id="div-kleidung" style="display: none;"><strong>Kleidung:</strong>

<table cellpadding="0" cellspacing="3px" class="nopadding c" width="80%">
	<tr class="b">
		<td class="small">#ID</td>
		<td>Name</td>
		<td>Abk&uuml;rzung</td>
		<td>Kategorie</td>
	</tr>
	<?php
	// TODO Änderungen der Kleidung
	/*
	 * Idee: Edit-Button
	 * Folge: <iframe> zur Bearbeitung
	 * Problem: Neuladen der Kleidungs-Info?
	 */
	$db = mysql_query('SELECT * FROM `ltb_kleidung` ORDER BY `order`, `id` ASC');
	while ($kleidung = mysql_fetch_assoc($db)) {
		echo('
			<tr>
				<td class="small l">#'.$kleidung['id'].'</td>
				<td>'.$kleidung['name'].'</td>
				<td>'.$kleidung['name_kurz'].'</td>
				<td>'.$kleidung['order'].'</td>
			</tr>');
	}
	?>
</table>

<small>Hinweis: &Auml;nderungen der Kleidung m&uuml;ssen derzeit noch
manuell in der Datenbank vorgenommen werden.</small></div>

<div id="div-sport" style="display: none;"><strong>Sportarten:</strong>

<table cellpadding="0" cellspacing="3px" class="nopadding c"
	width="100%">
	<tr class="b">
		<td class="small">Aktiv</td>
		<td class="small"
			title="Es wird nur ein Symbol vor dem jeweiligen Tag angezeigt">Kurz</td>
		<td colspan="2">Sportart</td>
		<td>kcal/h</td>
		<td>&Oslash; HF</td>
		<td
			title="Rating of Perceived Exertion (nach Borg) = durchschnittliche Anstrengung auf einer Skala von 1 (leicht) bis 10 (extrem hart)">RPE</td>
		<td title="Es wird eine Distanz zurückgelegt">km</td>
		<td title="Tempoanzeige in km/h statt min/km">kmh</td>
		<td title="Es werden Trainingstypen wie Intervalltraining verwendet">Typen</td>
		<td title="Der Puls wird dabei aufgezeichnet">Puls</td>
		<td title="Der Sport wird an der freien Luft betrieben">Drau&szlig;en</td>
	</tr>
	<?php
	// TODO Änderungen der Sportarten
	/*
	 * Idee: Create-Button?
	 * Folge: <iframe> zur Bearbeitung
	 */
	$db = mysql_query('SELECT * FROM `ltb_sports` ORDER BY `id` ASC');
	while ($sport = mysql_fetch_assoc($db)) {
		echo('
			<tr>
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

<small>Hinweis: &Auml;nderungen der Sportarten m&uuml;ssen derzeit noch
manuell in der Datenbank vorgenommen werden.</small></div>

<div id="div-typen" style="display: none;"><strong>Trainingstypen:</strong>

<table cellpadding="0" cellspacing="3px" class="nopadding c"
	width="100%">
	<tr class="b">
		<td class="small">#ID</td>
		<td>Trainingstyp</td>
		<td>Abk&uuml;rzung</td>
		<td
			title="Rating of Perceived Exertion (nach Borg) = durchschnittliche Anstrengung auf einer Skala von 1 (leicht) bis 10 (extrem hart)">RPE</td>
		<td title="Es werden einzelne Kilometerabschnitte aufgezeichnet">Splits</td>
	</tr>
	<?php
	// TODO Änderungen der Trainingstypen
	/*
	 * Idee: Create-Button?
	 * Folge: <iframe> zur Bearbeitung
	 * Problem: Kein Editieren!
	 */
	$db = mysql_query('SELECT * FROM `ltb_typ` ORDER BY `id` ASC');
	while ($typ = mysql_fetch_assoc($db)) {
		echo('
			<tr>
				<td class="small l">#'.$typ['id'].'</td>
				<td>'.$typ['name'].'</td>
				<td><input type="text" size="3" name="" disabled="disabled" value="'.$typ['abk'].'" /></td>
				<td><input type="text" size="1" name="" disabled="disabled" value="'.$typ['RPE'].'" /></td>
				<td><input type="checkbox" name="" disabled="disabled" '.($typ['splits'] == 1 ? 'checked="checked" ' : '').'/></td>
			</tr>');
	}
	?>
</table>

<small>Hinweis: &Auml;nderungen der Trainingstypen m&uuml;ssen derzeit
noch manuell in der Datenbank vorgenommen werden.</small></div>

<center><input type="submit" value="Bearbeiten" /></center>
</form>
