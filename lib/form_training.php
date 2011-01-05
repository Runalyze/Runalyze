<?php header('Content-type: text/html; charset=ISO-8859-1');
include_once('../config/functions.php');
connect();

if ($_GET['submit'] == "true") {

	$db = mysql_query('SELECT * FROM `ltb_sports` WHERE `id`='.$_POST['sportid'].' LIMIT 1');
	$sport = mysql_fetch_assoc($db);
	$send['sportid'] = $sport['id'];
	$send['kalorien'] = $_POST['kalorien'];
	$send['bemerkung'] = umlaute($_POST['bemerkung']);
	$send['trainingspartner'] = $_POST['trainingspartner'];

	$tag = explode(".", $_POST['datum']);
	$zeit = explode(":", $_POST['zeit']);
	$send['time'] = mktime($zeit[0], $zeit[1], 0, $tag[1], $tag[0], $tag[2]);
	$dauer = explode(":", $_POST['dauer']);
	$send['dauer'] = 3600 * $dauer[0] + 60 * $dauer[1] + $dauer[2];
	// Distanz- und Zeitunterschied
	$send['dauer_dif'] = $send['dauer'] - $_POST['dauer_old'];
	$send['dist_dif'] = komma($_POST['distanz']) - $_POST['dist_old'];

	if ($sport['distanztyp'] == 1) {
		$send['distanz'] = komma($_POST['distanz']);
		$send['bahn'] = $_POST['bahn']==1 ? 1 : 0;
		$send['pace'] = $_POST['pace'];
	}

	if ($sport['outside'] == 1) {
		$kleidung = array();
		$kleidung_db = mysql_query('SELECT * FROM `ltb_kleidung`');
		while ($kleidung_dat = mysql_fetch_array($kleidung_db))
		if ($_POST[$kleidung_dat['name_kurz']] == 1)
		$kleidung[] = '\''.$kleidung_dat['id'].'\'';

		$send['kleidung'] = (sizeof($kleidung) > 0) ? implode(',', $kleidung) : '';
		$send['temperatur'] = $_POST['temperatur'];
		$send['wetterid'] = $_POST['wetterid'];
		$send['strecke'] = umlaute($_POST['strecke']);
	}
	if ($sport['distanztyp'] == 1 && $sport['outside'] == 1)
	$send['hm'] = $_POST['hm'];

	if ($sport['pulstyp'] == 1) {
		$send['puls'] = $_POST['puls'];
		$send['puls_max'] = $_POST['puls_max'];
	}

	if ($sport['typen'] == 1) {
		// Typid und Schuhid
		$send['typid'] = $_POST['typid'];
		$send['schuhid'] = $_POST['schuhid'];
		$send['laufabc'] = $_POST['laufabc'] == 1 ? 1 : 0;
		if (typ($send['typid'],false,true) == 1)
		$send['splits'] = $_POST['splits'];
	}
	mysql_query('UPDATE `ltb_training` SET '.
'`typid`="'.$send['typid'].'", '.
'`time`="'.$send['time'].'", '.
'`distanz`="'.$send['distanz'].'", '.
'`bahn`="'.$send['bahn'].'", '.
'`dauer`="'.$send['dauer'].'", '.
'`pace`="'.$send['pace'].'", '.
'`hm`="'.$send['hm'].'", '.
'`kalorien`="'.$send['kalorien'].'", '.
'`kleidung`="'.$send['kleidung'].'", '.
'`temperatur`="'.$send['temperatur'].'", '.
'`puls`="'.$send['puls'].'", '.
'`puls_max`="'.$send['puls_max'].'", '.
'`wetterid`="'.$send['wetterid'].'", '.
'`strecke`="'.umlaute($send['strecke']).'", '.
'`splits`="'.$send['splits'].'", '.
'`bemerkung`="'.umlaute($send['bemerkung']).'", '.
'`trainingspartner`="'.umlaute($send['trainingspartner']).'", '.
'`laufabc`="'.$send['laufabc'].'", '.
'`schuhid`="'.$send['schuhid'].'" WHERE `id`='.$_POST['id'].' LIMIT 1') or die(mysql_error());

	if ($_POST['schuhid_old'] != $send['schuhid'] && $send['schuhid'] != 0) {
		mysql_query('UPDATE `ltb_schuhe` SET `km`=`km`-"'.$_POST['dist_old'].'", `dauer`=`dauer`-'.$_POST['dauer_old'].' WHERE `id`='.$_POST['schuhid_old'].' LIMIT 1') or die(mysql_error());
		mysql_query('UPDATE `ltb_schuhe` SET `km`=`km`+"'.$send['distanz'].'", `dauer`=`dauer`+'.$send['dauer'].' WHERE `id`='.$send['schuhid'].' LIMIT 1') or die(mysql_error());
	}
	if ($sport['typen'] == 1) mysql_query('UPDATE `ltb_schuhe` SET `km`=`km`+'.$send['dist_dif'].', `dauer`=`dauer`+'.$send['dauer_dif'].' WHERE `id`='.$send['schuhid'].' LIMIT 1') or die(mysql_error());
	mysql_query('UPDATE `ltb_sports` SET `distanz`=`distanz`+'.$send['dist_dif'].', `dauer`=`dauer`+'.$send['dauer_dif'].' WHERE `id`='.$send['sportid'].' LIMIT 1') or die(mysql_error());

	mysql_query('UPDATE `ltb_training` SET `trimp`="'.trimp($_POST['id']).'" WHERE `id`='.$_POST['id'].' LIMIT 1');
	mysql_query('UPDATE `ltb_training` SET `vdot`="'.jd_VDOT_bereinigt($_POST['id']).'" WHERE `id`='.$_POST['id'].' LIMIT 1');

	if (atl($send['time']) > $config['max_atl']) mysql_query('UPDATE `ltb_config` SET `max_atl`="'.atl($send['time']).'"');
	if (ctl($send['time']) > $config['max_ctl']) mysql_query('UPDATE `ltb_config` SET `max_ctl`="'.ctl($send['time']).'"');
	if (trimp($_POST['id']) > $config['max_trimp']) mysql_query('UPDATE `ltb_config` SET `max_trimp`="'.trimp($_POST['id']).'"');


	close();
	$_GET['id'] = $_POST['id'];
	include('training.php');
	exit();
}

$db = mysql_query('SELECT * FROM `ltb_training` WHERE `id`='.$_GET['id'].' LIMIT 1');
$dat = mysql_fetch_assoc($db);
$datum = date("H:i", $dat['time']) != "00:00" ? date("d.m.Y H:i", $dat['time']).' Uhr' : date("d.m.Y", $dat['time']);
$db = mysql_query('SELECT * FROM `ltb_sports` WHERE `id`='.$dat['sportid'].' LIMIT 1');
$sport = mysql_fetch_assoc($db);
// 'distanztyp', 'typen', 'pulstyp', 'outside'
?>
<img
	id="close" src="img/cross.png" onClick="ajax_close()" />
<h1><img class="link"
	onClick="seite('training','<?php echo($dat['id']); ?>')"
	src="img/i.png" /> <?php echo(typ($dat['typid']).' ('.$datum.')'); ?></h1>
<form
	action="javascript:submit_form_training('<?php echo($dat['id']); ?>');"
	name="train" method="post"><input type="hidden" name="id"
	value="<?php echo($dat['id']); ?>" /> <a href="#"
	onClick="show('div-allg');unshow('div-train');unshow('div-out');">Allgemeines</a>
<?php if ($sport['distanztyp'] == 1): ?> | <a href="#"
	onClick="unshow('div-allg');show('div-train');unshow('div-out');">Training</a>
<?php endif; if ($sport['outside'] == 1) : ?> | <a href="#"
	onClick="unshow('div-allg');unshow('div-train');show('div-out');">Outside</a>
<?php endif; ?> <br />
<br />

<div id="div-allg"><input type="hidden" name="sportid"
	value="<?php echo($dat['sportid']); ?>" /> <input type="text"
	name="sport" value="<?php echo(sport($dat['sportid'])); ?>"
	disabled="disabled" /> <small>Sport</small><br />
<input type="text" size="10" name="datum"
	value="<?php echo(date("d.m.Y", $dat['time'])); ?>" /> <input
	type="text" size="4" name="zeit"
	value="<?php echo(date("H:i", $dat['time'])); ?>" /> <small>Datum</small><br />
<input type="hidden" id="kalorien_stunde" name="kalorienprostunde"
	value="<?php echo($sport['kalorien']); ?>" /> <input type="hidden"
	name="dauer_old" value="<?php echo($dat['dauer']); ?>" /> <input
	type="text" size="8" name="dauer" id="dauer"
	value="<?php echo(zeit($dat['dauer'],true)); ?>"
	onChange="paceberechnung(); kalorienberechnung();" /> <small>Dauer</small><br />
<?php if ($sport['distanztyp'] == 1): ?> <input type="checkbox" size="4"
	name="bahn"
	<?php echo $dat['bahn'] == 1 ? ' checked="checked"' : ''; ?> /> <input
	type="hidden" name="dist_old" value="<?php echo($dat['distanz']); ?>" />
<input type="text" size="4" name="distanz" id="dist"
	value="<?php echo($dat['distanz']); ?>" onChange="paceberechnung();" />
<small>km</small><br />
<input type="text" size="4" name="pace" id="pace"
	value="<?php echo($dat['pace']); ?>" /> <small>/km</small><br />
	<?php endif; ?> <input type="text" size="4" name="kalorien"
	id="kalorien" value="<?php echo($dat['kalorien']); ?>" /> <small>kcal</small><br />
<input type="text" size="50" name="bemerkung"
	value="<?php echo textarea($dat['bemerkung']); ?>" /> <small>Bemerkung</small><br />
<input type="text" size="50" name="trainingspartner"
	value="<?php echo textarea($dat['trainingspartner']); ?>" /> <small>Trainingspartner</small>
</div>

<div id="div-train" style="display: none;"><span
<?php echo $sport['typen'] == 1 ? '' : ' style="display:none;"'; ?>> <select
	name="typid">
	<?php
	$db = mysql_query('SELECT * FROM `ltb_typ` ORDER BY `id` ASC');
	while($typ = mysql_fetch_array($db)) {
		$selected = $typ['id'] == $dat['typid'] ? ' selected="selected"' : '';
		echo('<option value="'.$typ['id'].'"'.$selected.'>'.$typ['name'].'</option>');
	}
	?>
</select><br />

<input type="hidden" name="schuhid_old"
	value="<?php echo $dat['schuhid']; ?>" /> <select name="schuhid">
	<?php
	$db = mysql_query('SELECT * FROM `ltb_schuhe` ORDER BY `id` ASC');
	while($schuh = mysql_fetch_array($db)) {
		$selected = $schuh['id'] == $dat['schuhid'] ? ' selected="selected"' : '';
		echo('<option value="'.$schuh['id'].'"'.$selected.'>'.$schuh['name'].'</option>');
	}
	?>
</select><br />

<input type="checkbox" size="4" name="laufabc"
<?php echo $dat['laufabc'] == 1 ? ' checked="checked"' : ''; ?> />
Lauf-ABC<br />
</span> <span id="puls" style="display:<?php echo $sport['pulstyp'] == 1 ? 'block' : 'none'; ?>;">
<input type="text" size="3" name="puls"
	value="<?php echo($dat['puls']); ?>" /> <small>Puls</small><br />
<input type="text" size="3" name="puls_max"
	value="<?php echo($dat['puls_max']); ?>" /> <small>max. Puls</small><br />
</span> <span style="display:<?php echo typ($dat['typid'],false,true) == 1 ? 'block' : 'none'; ?>;">
<textarea name="splits" cols="70" rows="3"><?php echo($dat['splits']); ?></textarea>
<small>Splits</small><br />
</span></div>

<div id="div-out" style="display: none;"><input type="text" size="50"
	name="strecke" value="<?php echo textarea($dat['strecke']); ?>" /> <small
	style="margin-right: 73px;">Strecke</small> <input type="text" size="3"
	name="hm" value="<?php echo($dat['hm']); ?>" /> <small>HM</small><br />
<select name="wetterid">
<?php for($i=1; $i<=sizeof($global['wetter']); $i++) { $selected = ($i==$dat['wetterid']) ? ' selected="selected"' : ''; echo('<option value="'.$i.'"'.$selected.'>'.$global['wetter'][$i]['name'].'</option>'); } ?>
</select> <small>Wetter</small><br />
<input type="text" size="2" name="temperatur"
	value="<?php echo($dat['temperatur']); ?>" /> <small>&#176;C</small><br />
<br />
<?php
$db = mysql_query('SELECT * FROM `ltb_kleidung` ORDER BY `name_kurz` ASC');
while ($kleidung = mysql_fetch_array($db)) {
	$checked = in_array($kleidung['id'], explode(',', $dat['kleidung'])) ? ' checked="checked"' : '';
	echo('			<input type="checkbox" name="'.$kleidung['name_kurz'].'"'.$checked.' /> <small style="margin-right:12px;">'.$kleidung['name_kurz'].'</small>'.NL);
}
?></div>

<br />

<center><input type="submit" value="Bearbeiten" /></center>
</form>
<?php
close();
?>