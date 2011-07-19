<?php
/**
 * File displaying the formular for adding a new training
 * Call:   inc/tpl/window.create.php
 */
$Mysql = Mysql::getInstance();

$showUploader = empty($_POST);

if (isset($_POST['data']))
	$_POST = Training::parseTcx($_POST['data']);

if (!isset($_POST['datum']))
	$_POST['datum'] = date("d.m.Y");
if (!isset($_POST['zeit']))
	$_POST['zeit'] = '00:00';
if (!isset($_POST['dauer']))
	$_POST['dauer'] = '0:00:00';
if (!isset($_POST['kalorien']))
	$_POST['kalorien'] = '0';
if (!isset($_POST['bemerkung']))
	$_POST['bemerkung'] = '';
if (!isset($_POST['trainingspartner']))
	$_POST['trainingspartner'] = '';
if (!isset($_POST['distanz']))
	$_POST['distanz'] = '0.00';
if (!isset($_POST['pace']))
	$_POST['pace'] = '0:00';
if (!isset($_POST['kmh']))
	$_POST['kmh'] = '0,00';
if (!isset($_POST['hm']))
	$_POST['hm'] = '0';
if (!isset($_POST['puls']))
	$_POST['puls'] = '0';
if (!isset($_POST['puls_max']))
	$_POST['puls_max'] = '0';
if (!isset($_POST['strecke']))
	$_POST['strecke'] = '';
if (!isset($_POST['temperatur']))
	$_POST['temperatur'] = '';
if (!isset($_POST['splits']))
	$_POST['splits'] = '';
?>
<form id="newtraining" class="ajax" action="<?php echo $_SERVER['SCRIPT_NAME']; ?>" method="post">

	<input type="hidden" name="type" value="newtraining" />
	<input type="hidden" id="kalorien_stunde" name="kalorienprostunde" value="0" />

	<span class="right"><?php echo Ajax::toggle('<a href="#upload" title="Training hochladen">Uploader aus/einblenden</a>', 'upload'); ?></span>

	<div class="<?php if (!$showUploader) echo 'hide'; ?>" id="upload">
		<h1>Training vom Garmin Forerunner hochladen</h1>

		<div style="width:100%;text-align:center;">
			<iframe src="inc/tpl/tpl.garminCommunicator.php" id="GCapi" width="550px" height="180px"></iframe>
		</div>

	</div>

	<h1>Neues Training</h1>

	<div class="c">
<?php
$sports = $Mysql->fetchAsArray('SELECT * FROM `'.PREFIX.'sports` ORDER BY `id` ASC');
foreach($sports as $sport) {
	$onclick = 'kps('.$sport['kalorien'].');';
	$onclick .= ($sport['distanztyp'] == 1) ? 'show(\'distanz\');' : 'unshow(\'distanz\');';
	$onclick .= ($sport['typen'] == 1) ? 'show(\'typen\');' : 'unshow(\'typen\');unshow(\'splits\');';
	$onclick .= ($sport['pulstyp'] == 1) ? 'show(\'puls\');' : 'unshow(\'puls\');';
	$onclick .= ($sport['outside'] == 1) ? 'show(\'outside\');' : 'unshow(\'outside\');';

	echo('
		<input type="radio" name="sportid" value="'.$sport['id'].'" onClick="show(\'normal\');'.$onclick.'" /> '.$sport['name'].' &nbsp; '.NL);
}

if (isset($_POST['sportid']))
	echo('<script type="text/javascript">$("input[value=\''.$_POST['sportid'].'\']").click();</script>');
?>
	</div>
		<br />

	<div style="float: left;">
		<span id="normal" style="display: none;">
			<input type="text" size="10" name="datum" value="<?php echo $_POST['datum']; ?>" />
			<input type="text" size="4" name="zeit" value="<?php echo $_POST['zeit']; ?>" />
				<small>Datum</small><br />
			<input type="text" size="8" name="dauer" id="dauer" value="<?php echo $_POST['dauer']; ?>" onChange="paceberechnung(); kalorienberechnung(); kmhberechnung();" />
				<small style="margin-right: 75px;">Dauer</small>
			<input type="text" size="4" name="kalorien" id="kalorien" value="<?php echo $_POST['kalorien']; ?>" />
				<small>kcal</small><br />
			<input type="text" size="50" name="bemerkung" value="<?php echo $_POST['bemerkung']; ?>" />
				<small>Bemerkung</small><br />
			<input type="text" size="50" name="trainingspartner" value="<?php echo $_POST['trainingspartner']; ?>" />
				<small>Trainingspartner</small>
		</span>
	</div>

	<div style="float: right; width: 45%;"><br />
		<span id="typen" style="display: none;">
			<input type="hidden" name="count" id="count" value="1" />
			<select name="typid">
				<option value="0">?</option>
<?php
$typen = $Mysql->fetchAsArray('SELECT * FROM `'.PREFIX.'typ` ORDER BY `id` ASC');
foreach($typen as $typ) {
	$onClick = '';
	if ($typ['count'] == 0)
		$onClick .= 'document.getElementById(\'count\').value=\'0\'';
	if ($typ['splits'] == 1)
		$onClick .= 'document.getElementById(\'splits\').style.display=\'block\'';
	else
		$onClick .= 'document.getElementById(\'splits\').style.display=\'none\'';

	$selected = isset($_POST['typid']) ? Helper::Selected($_POST['typid'], $typ['id']) : '';
	echo('
				<option value="'.$typ['id'].'" onClick="'.$onClick.'"'.$selected.'>'.$typ['name'].'</option>');
}
?>
			</select>

			<select name="schuhid">
				<option value="0">?</option>
<?php
$schuhe = $Mysql->fetchAsArray('SELECT * FROM `'.PREFIX.'schuhe` WHERE `inuse`=1 ORDER BY `id` ASC');
foreach($schuhe as $schuh) {
	$selected = isset($_POST['schuhid']) ? Helper::Selected($_POST['schuhid'], $schuh['id']) : '';
	echo('
				<option value="'.$schuh['id'].'"'.$selected.'>'.$schuh['name'].'</option>');
}
?>
			</select>

			<input type="checkbox" name="laufabc"<?php echo Helper::Checked(isset($_POST['laufabc']) ? $_POST['laufabc'] : false); ?> />
				<small>Lauf-ABC</small>
		</span>

		<span id="distanz" style="display: none;">
			<input type="text" size="4" name="distanz" id="dist" value="<?php echo Helper::Unknown($_POST['distanz'], '0.00'); ?>" onChange="paceberechnung(); kmhberechnung();" />
				<small>km</small>
			<input type="checkbox" name="bahn"<?php echo Helper::Checked(isset($_POST['bahn']) ? $_POST['bahn'] : false); ?> />
				<small style="margin-right: 25px;">Bahn</small>
			<input type="text" size="4" name="pace" id="pace" value="<?php echo Helper::Unknown($_POST['pace'], '0:00'); ?>" disabled="disabled" />
				<small>/km</small>
			<input type="text" size="4" name="kmh" id="kmh" value="<?php echo Helper::Unknown($_POST['kmh'], '0,00'); ?>" disabled="disabled" />
				<small>km/h</small>
			<input type="text" size="3" name="hm" value="<?php echo Helper::Unknown($_POST['hm'], '0'); ?>" />
				<small>HM</small>
		</span>

		<span id="puls" style="display: none;">
			<input type="text" size="3" name="puls" value="<?php echo Helper::Unknown($_POST['puls'], '0'); ?>" />
				<small style="margin-right: 73px;">Puls</small>
			<input type="text" size="3" name="puls_max" value="<?php echo Helper::Unknown($_POST['puls_max'], '0'); ?>" />
				<small>max. Puls</small>
		</span>
	</div>

		<br class="clear" />

	<span id="outside" style="display: none;">

		<input type="hidden" name="arr_time" value="<?php if (isset($_POST['arr_time'])) echo $_POST['arr_time']; ?>" />
		<input type="hidden" name="arr_lat" value="<?php if (isset($_POST['arr_lat'])) echo $_POST['arr_lat']; ?>" />
		<input type="hidden" name="arr_lon" value="<?php if (isset($_POST['arr_lon'])) echo $_POST['arr_lon']; ?>" />
		<input type="hidden" name="arr_alt" value="<?php if (isset($_POST['arr_alt'])) echo $_POST['arr_alt']; ?>" />
		<input type="hidden" name="arr_dist" value="<?php if (isset($_POST['arr_dist'])) echo $_POST['arr_dist']; ?>" />
		<input type="hidden" name="arr_heart" value="<?php if (isset($_POST['arr_heart'])) echo $_POST['arr_heart']; ?>" />
		<input type="hidden" name="arr_pace" value="<?php if (isset($_POST['arr_pace'])) echo $_POST['arr_pace']; ?>" />

		<br />
		<input type="text" size="50" name="strecke" value="<?php echo Helper::Unknown($_POST['strecke'], ''); ?>" />
			<small style="margin-right: 100px;">Strecke</small>
		<select name="wetterid">
<?php
$wetter = $Mysql->fetchAsArray('SELECT * FROM `'.PREFIX.'wetter` ORDER BY `order` ASC');
foreach($wetter as $dat) {
	$selected = isset($_POST['wetterid']) ? Helper::Selected($_POST['wetterid'], $dat['id']) : '';
	echo('<option value="'.$dat['id'].'"'.$selected.'>'.$dat['name'].'</option>');
}
?>
		</select>
			<small>Wetter</small>
		<input type="text" size="2" name="temperatur" value="<?php echo Helper::Unknown($_POST['temperatur'], ''); ?>" />
			<small style="margin-right: 40px;">&#176;C</small>
			<br />
			<br />
		<input type="hidden" name="kleidung" id="kleidung" />
<?php
$kleidungen = $Mysql->fetchAsArray('SELECT * FROM `'.PREFIX.'kleidung` ORDER BY `name_kurz` ASC');
foreach($kleidungen as $kleidung)
	echo('
		<input type="checkbox" name="'.$kleidung['name_kurz'].'"'.Helper::Checked(isset($_POST[$kleidung['name_kurz']]) ? $_POST[$kleidung['name_kurz']] : false).' onClick="document.getElementById(\'kleidung\').value +=\''.$kleidung['id'].',\';" /> <small style="margin-right: 10px;">'.$kleidung['name_kurz'].'</small>');
?>
			<br />
	</span>

	<span id="splits" style="display: none;">
		<br />
		<textarea name="splits" cols="70" rows="3"><?php echo Helper::Unknown($_POST['splits'], ''); ?></textarea>
			<small>Splits</small><br />
	</span>

	<div class="c">
		<input style="margin-top: 10px;" type="submit" value="Eintragen!" />
	</div>
</form>