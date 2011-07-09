<?php
/**
 * File displaying the formular for adding a new training
 * Call:   inc/tpl/window.create.php
 */
$Mysql = Mysql::getInstance();
?>
<h1>Neues Training</h1>

<form id="newtraining" class="ajax" action="<?php echo $_SERVER['SCRIPT_NAME']; ?>" method="post">

	<input type="hidden" name="type" value="newtraining" />
	<input type="hidden" id="kalorien_stunde" name="kalorienprostunde" value="0" />

	<div class="c">
<?php
$sports = $Mysql->fetchAsArray('SELECT * FROM `ltb_sports` ORDER BY `id` ASC');
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
			<input type="text" size="10" name="datum" value="<?php echo Helper::Unknown($_POST['datum'], date("d.m.Y")); ?>" />
			<input type="text" size="4" name="zeit" value="<?php echo Helper::Unknown($_POST['zeit'], '00:00'); ?>" />
				<small>Datum</small><br />
			<input type="text" size="8" name="dauer" id="dauer" value="<?php echo Helper::Unknown($_POST['dauer'], '0:00:00'); ?>" onChange="paceberechnung(); kalorienberechnung(); kmhberechnung();" />
				<small style="margin-right: 75px;">Dauer</small>
			<input type="text" size="4" name="kalorien" id="kalorien" value="<?php echo Helper::Unknown($_POST['kalorien'], '0'); ?>" />
				<small>kcal</small><br />
			<input type="text" size="50" name="bemerkung" value="<?php echo Helper::Unknown($_POST['bemerkung'], ''); ?>" />
				<small>Bemerkung</small><br />
			<input type="text" size="50" name="trainingspartner" value="<?php echo Helper::Unknown($_POST['trainingspartner'], ''); ?>" />
				<small>Trainingspartner</small>
		</span>
	</div>

	<div style="float: right; width: 45%;"><br />
		<span id="typen" style="display: none;">
			<input type="hidden" name="count" id="count" value="1" />
			<select name="typid">
<?php
$typen = $Mysql->fetchAsArray('SELECT * FROM `ltb_typ` ORDER BY `id` ASC');
foreach($typen as $typ) {
	$onClick = '';
	if ($typ['count'] == 0)
		$onClick .= 'document.getElementById(\'count\').value=\'0\'';
	if ($typ['splits'] == 1)
		$onClick .= 'document.getElementById(\'splits\').style.display=\'block\'';
	else
		$onClick .= 'document.getElementById(\'splits\').style.display=\'none\'';

	$selected = isset($_POST) ? Helper::Selected($_POST['typid'], $typ['id']) : '';
	echo('
				<option value="'.$typ['id'].'" onClick="'.$onClick.'"'.$selected.'>'.$typ['name'].'</option>');
}
?>
			</select>

			<select name="schuhid">
<?php
$schuhe = $Mysql->fetchAsArray('SELECT * FROM `ltb_schuhe` WHERE `inuse`=1 ORDER BY `id` ASC');
foreach($schuhe as $schuh) {
	$selected = isset($_POST) ? Helper::Selected($_POST['schuhid'], $schuh['id']) : '';
	echo('
				<option value="'.$schuh['id'].'"'.$selected.'>'.$schuh['name'].'</option>');
}
?>
			</select>

			<input type="checkbox" name="laufabc"<?php echo Helper::Checked($_POST['laufabc']); ?> />
				<small>Lauf-ABC</small>
		</span>

		<span id="distanz" style="display: none;">
			<input type="text" size="4" name="distanz" id="dist" value="<?php echo Helper::Unknown($_POST['distanz'], '0.00'); ?>" onChange="paceberechnung(); kmhberechnung();" />
				<small>km</small>
			<input type="checkbox" name="bahn"<?php echo Helper::Checked($_POST['bahn']); ?> />
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
		<br />
		<input type="text" size="50" name="strecke" value="<?php echo Helper::Unknown($_POST['strecke'], ''); ?>" />
			<small style="margin-right: 100px;">Strecke</small>
		<select name="wetterid">
<?php
$wetter = $Mysql->fetchAsArray('SELECT * FROM `ltb_wetter` ORDER BY `order` ASC');
foreach($wetter as $dat) {
	$selected = isset($_POST) ? Helper::Selected($_POST['wetterid'], $dat['id']) : '';
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
$kleidungen = $Mysql->fetchAsArray('SELECT * FROM `ltb_kleidung` ORDER BY `name_kurz` ASC');
foreach($kleidungen as $kleidung)
	echo('
		<input type="checkbox" name="'.$kleidung['name_kurz'].'"'.Helper::Checked($_POST[$kleidung['name_kurz']]).' onClick="document.getElementById(\'kleidung\').value +=\''.$kleidung['id'].',\';" /> <small style="margin-right: 10px;">'.$kleidung['name_kurz'].'</small>');
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