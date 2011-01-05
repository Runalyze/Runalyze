<?php
header('Content-type: text/html; charset=ISO-8859-1');

include_once('../config/functions.php');
include_once('../config/dataset.php');
connect();
?>
<span class="r"><img id="close" src="img/cross.png"
	onClick="ajax_close()" /></span>
<h1>Eingabe</h1>
<form action="?action=do" method="post">
<center><?php
$db = mysql_query('SELECT * FROM `ltb_sports` ORDER BY `id` ASC');
while($sport = mysql_fetch_array($db)) {
	$onclick = 'kps('.$sport['kalorien'].');';
	if ($sport['distanztyp'] == 1) $onclick .= 'show(\'distanz\');';
	else $onclick .= 'unshow(\'distanz\');';
	if ($sport['typen'] == 1) $onclick .= 'show(\'typen\');';
	else $onclick .= 'unshow(\'typen\');unshow(\'splits\');';
	if ($sport['pulstyp'] == 1) $onclick .= 'show(\'puls\');';
	else $onclick .= 'unshow(\'puls\');';
	if ($sport['outside'] == 1) $onclick .= 'show(\'outside\');';
	else $onclick .= 'unshow(\'outside\');';
	echo('
		<input type="radio" name="sportid" value="'.$sport['id'].'" onClick="show(\'normal\');'.$onclick.'" /> '.$sport['name'].' &nbsp; '.NL);
}
?></center>

<input type="hidden" id="kalorien_stunde" name="kalorienprostunde"
	value="0" />

<div style="float: left;"><br />
<span id="normal" style="display: none;"> <input type="text" size="10"
	name="datum" value="<?php echo(date("d.m.Y")); ?>" /> <input
	type="text" size="4" name="zeit" value="00:00" /> <small>Datum</small><br />
<input type="text" size="8" name="dauer" id="dauer" value="0:00:00"
	onChange="paceberechnung(); kalorienberechnung(); kmhberechnung();" />
<small style="margin-right: 75px;">Dauer</small> <input type="text"
	size="4" name="kalorien" id="kalorien" value="0" /> <small>kcal</small><br />
<input type="text" size="50" name="bemerkung" /> <small>Bemerkung</small><br />
<input type="text" size="50" name="trainingspartner" /> <small>Trainingspartner</small>
</span></div>

<div style="float: right; width: 45%;"><br />
<span id="typen" style="display: none;"> <input type="hidden"
	name="count" id="count" value="1" /> <select name="typid">
	<?php
	$db = mysql_query('SELECT * FROM `ltb_typ` ORDER BY `id` ASC');
	while($typ = mysql_fetch_array($db)) {
		$onClick = '';
		if ($typ['count'] == 0) $onClick .= 'document.getElementById(\'count\').value=\'0\'';
		if ($typ['splits'] == 1) $onClick .= 'document.getElementById(\'splits\').style.display=\'block\'';
		else $onClick .= 'document.getElementById(\'splits\').style.display=\'none\'';
		echo('
				<option value="'.$typ['id'].'" onClick="'.$onClick.'">'.$typ['name'].'</option>');
	}
	?>
</select> <select name="schuhid">
<?php
$db = mysql_query('SELECT * FROM `ltb_schuhe` WHERE `inuse`=1 ORDER BY `id` ASC');
while($schuh = mysql_fetch_array($db)) {
	echo('
				<option value="'.$schuh['id'].'">'.$schuh['name'].'</option>');
}
?>
</select> <input type="checkbox" name="laufabc" /> <small>Lauf-ABC</small>
</span> <span id="distanz" style="display: none;"> <input type="text"
	size="4" name="distanz" id="dist" value="0.00"
	onChange="paceberechnung(); kmhberechnung();" /> <small>km</small> <input
	type="checkbox" name="bahn" /> <small style="margin-right: 25px;">Bahn</small>
<input type="text" size="4" name="pace" id="pace" value="0:00"
	disabled="disabled" /> <small>/km</small> <input type="text" size="4"
	name="kmh" id="kmh" value="0,00" disabled="disabled" /> <small>km/h</small>
<input type="text" size="3" name="hm" value="0" /> <small>HM</small> </span>

<span id="puls" style="display: none;"> <input type="text" size="3"
	name="puls" value="0" /> <small style="margin-right: 73px;">Puls</small>
<input type="text" size="3" name="puls_max" value="0" /> <small>max.
Puls</small> </span></div>

<br class="clear" />

<span id="outside" style="display: none;"> <br />
<input type="text" size="50" name="strecke" /> <small
	style="margin-right: 100px;">Strecke</small> <select name="wetterid">
	<?php for($i=1; $i<=sizeof($global['wetter']); $i++) { $selected = ($i==$dat['wetterid']) ? ' selected="selected"' : ''; echo('<option value="'.$i.'"'.$selected.'>'.$global['wetter'][$i]['name'].'</option>'); } ?>
</select> <small>Wetter</small> <input type="text" size="2"
	name="temperatur" /> <small style="margin-right: 40px;">&#176;C</small><br />
<br />
<input type="hidden" name="kleidung" id="kleidung" /> <?php
$db = mysql_query('SELECT * FROM `ltb_kleidung` ORDER BY `name_kurz` ASC');
while ($kleidung = mysql_fetch_array($db)) {
	echo('
		<input type="checkbox" name="'.$kleidung['name_kurz'].'" onClick="document.getElementById(\'kleidung\').value +=\''.$kleidung['id'].',\';" /> <small style="margin-right: 10px;">'.$kleidung['name_kurz'].'</small>');
}
?> <br />
</span> <span id="splits" style="display: none;"> <br />
<textarea name="splits" cols="70" rows="3"></textarea> <small>Splits</small><br />
</span>

<center><input style="margin-top: 10px;" type="submit"
	value="Eintragen!" /></center>
</form>
