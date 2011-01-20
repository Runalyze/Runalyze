<?php
/**
 * File displaying the formular for adding a new training
 * Call:   inc/tpl/window.formular.php
 */
require('../class.Frontend.php');
$Frontend = new Frontend(true, __FILE__);
$Mysql = Mysql::getInstance();

if (isset($_POST) && $_POST['type'] == "newtraining") {
	$sport = $Mysql->fetch('ltb_sports', $_POST['sportid']);
	$distance = ($sport['distanztyp'] == 1) ? Helper::CommaToPoint($send['distanz']) : 0;

	$columns = array('sportid');
	$values = array($sport['id']);
	// Values beeing parsed with Helper::CommaToPoint();
	$vars = array('kalorien', 'bemerkung', 'trainingspartner');

	// Prepare "Time"
	$post_day = explode(".", $_POST['datum']);
	$post_time = explode(":", $_POST['zeit']);
	$time = mktime($post_time[0], $post_time[1], 0, $post_day[1], $post_day[0], $post_day[2]);
	$columns[] = 'time';
	$values[] = $time;
	// Prepare "Dauer"
	$post_dauer = explode(":", $_POST['dauer']);
	$time_in_s = 3600 * $post_dauer[0] + 60 * $post_dauer[1] + $post_dauer[2];
	$columns[] = 'dauer';
	$values[] = $time_in_s;
	// Prepare values for distances
	if ($sport['distanztyp'] == 1) {
		$columns[] = 'bahn';
		$values[] = $_POST['bahn']==true ? 1 : 0;
		$columns[] = 'pace';
		$values[] = Helper::Pace($distance, $time_in_s);
	}
	// Prepare values for outside-sport
	if ($sport['outside'] == 1) {
		$vars[] = 'hm';
		$vars[] = 'wetterid';
		$vars[] = 'strecke';
		$columns[] = 'kleidung';
		$values[] = substr($_POST['kleidung'],0,-1);
		$columns[] = 'temperatur';
		$values[] = is_numeric($_POST['temperatur']) ? $_POST['temperatur'] : NULL;
	} else {
		// Set NULL to temperatur otherwise
		$columns[] = 'temperatur';
		$values[] = NULL;
	}
	// Prepare values if using heartfrequence
	if ($sport['pulstyp'] == 1) {
		$vars[] = 'puls';
		$vars[] = 'puls_max';
	}
	// Prepare values for running (checked via "type")
	if ($sport['typen'] == 1) {
		$vars[] = 'typid';
		$vars[] = 'schuhid';
		$columns[] = 'laufabc';
		$values[] = $_POST['laufabc'] == true ? 1 : 0;
		if (Helper::Typ($_POST['typid'], false, true) == 1)
			$vars[] = 'splits';
	}

	foreach($vars as $var)
		if (isset($_POST[$var])) {
			$columns[] = $var;
			$values[] = Helper::CommaToPoint($_POST[$var]);
		}
	$id = $Mysql->insert('ltb_training', $columns, $values);

	$ATL = Helper::ATL($time);
	$CTL = Helper::CTL($time);
	$TRIMP = Helper::TRIMP($id);
	// Set TRIMP and VDOT
	$Mysql->query('UPDATE `ltb_training` SET `trimp`="'.$TRIMP.'" WHERE `id`='.$id.' LIMIT 1');
	$Mysql->query('UPDATE `ltb_training` SET `vdot`="'.JD::Training2VDOT($id).'" WHERE `id`='.$id.' LIMIT 1');
	// Update Maxima in config
	if ($ATL > CONFIG_MAX_ATL)
		$Mysql->query('UPDATE `ltb_config` SET `max_atl`="'.$ATL.'"');
	if ($CTL > CONFIG_MAX_CTL)
		$Mysql->query('UPDATE `ltb_config` SET `max_ctl`="'.$CTL.'"');
	if ($TRIMP > CONFIG_MAX_TRIMP)
		$Mysql->query('UPDATE `ltb_config` SET `max_trimp`="'.$TRIMP.'"');
	// Update 'Schuhe'
	if ($sport['typen'] == 1)
		$Mysql->query('UPDATE `ltb_schuhe` SET `km`=`km`+'.$distance.', `dauer`=`dauer`+'.$time_in_s.' WHERE `id`='.$_POST['schuhid'].' LIMIT 1');
	// Update 'Sports'
	$Mysql->query('UPDATE `ltb_sports` SET `distanz`=`distanz`+'.$distance.', `dauer`=`dauer`+'.$time_in_s.' WHERE `id`='.$_POST['sportid'].' LIMIT 1');

	header('Location: ?done');
}

$Frontend->displayHeader();
?>
<h1>Neues Training</h1>

<form class="ajax" action="<?php echo $_SERVER['SCRIPT_NAME']; ?>" method="post">

	<input type="hidden" name="type" value="newtraining" />
	<input type="hidden" id="kalorien_stunde" name="kalorienprostunde" value="0" />

	<center>
<?php
$sports = $Mysql->fetch('SELECT * FROM `ltb_sports` ORDER BY `id` ASC', false, true);
foreach($sports as $sport) {
	$onclick = 'kps('.$sport['kalorien'].');';
	$onclick .= ($sport['distanztyp'] == 1) ? 'show(\'distanz\');' : 'unshow(\'distanz\');';
	$onclick .= ($sport['typen'] == 1) ? 'show(\'typen\');' : 'unshow(\'typen\');unshow(\'splits\');';
	$onclick .= ($sport['pulstyp'] == 1) ? 'show(\'puls\');' : 'unshow(\'puls\');';
	$onclick .= ($sport['outside'] == 1) ? 'show(\'outside\');' : 'unshow(\'outside\');';

	echo('
		<input type="radio" name="sportid" value="'.$sport['id'].'" onClick="show(\'normal\');'.$onclick.'" /> '.$sport['name'].' &nbsp; '.NL);
}
?>
	</center>
		<br />

	<div style="float: left;">
		<span id="normal" style="display: none;">
			<input type="text" size="10" name="datum" value="<?php echo(date("d.m.Y")); ?>" />
			<input type="text" size="4" name="zeit" value="00:00" />
				<small>Datum</small><br />
			<input type="text" size="8" name="dauer" id="dauer" value="0:00:00" onChange="paceberechnung(); kalorienberechnung(); kmhberechnung();" />
				<small style="margin-right: 75px;">Dauer</small>
			<input type="text" size="4" name="kalorien" id="kalorien" value="0" />
				<small>kcal</small><br />
			<input type="text" size="50" name="bemerkung" />
				<small>Bemerkung</small><br />
			<input type="text" size="50" name="trainingspartner" />
				<small>Trainingspartner</small>
		</span>
	</div>

	<div style="float: right; width: 45%;"><br />
		<span id="typen" style="display: none;">
			<input type="hidden" name="count" id="count" value="1" />
			<select name="typid">
<?php
$typen = $Mysql->fetch('SELECT * FROM `ltb_typ` ORDER BY `id` ASC', false, true);
foreach($typen as $typ) {
	$onClick = '';
	if ($typ['count'] == 0)
		$onClick .= 'document.getElementById(\'count\').value=\'0\'';
	if ($typ['splits'] == 1)
		$onClick .= 'document.getElementById(\'splits\').style.display=\'block\'';
	else
		$onClick .= 'document.getElementById(\'splits\').style.display=\'none\'';
	echo('
				<option value="'.$typ['id'].'" onClick="'.$onClick.'">'.$typ['name'].'</option>');
}
?>
			</select>

			<select name="schuhid">
<?php
$schuhe = $Mysql->fetch('SELECT * FROM `ltb_schuhe` WHERE `inuse`=1 ORDER BY `id` ASC', false, true);
foreach($schuhe as $schuh)
	echo('
				<option value="'.$schuh['id'].'">'.$schuh['name'].'</option>');
?>
			</select>

			<input type="checkbox" name="laufabc" />
				<small>Lauf-ABC</small>
		</span>

		<span id="distanz" style="display: none;">
			<input type="text" size="4" name="distanz" id="dist" value="0.00" onChange="paceberechnung(); kmhberechnung();" />
				<small>km</small>
			<input type="checkbox" name="bahn" />
				<small style="margin-right: 25px;">Bahn</small>
			<input type="text" size="4" name="pace" id="pace" value="0:00" disabled="disabled" />
				<small>/km</small>
			<input type="text" size="4" name="kmh" id="kmh" value="0,00" disabled="disabled" />
				<small>km/h</small>
			<input type="text" size="3" name="hm" value="0" />
				<small>HM</small>
		</span>

		<span id="puls" style="display: none;">
			<input type="text" size="3" name="puls" value="0" />
				<small style="margin-right: 73px;">Puls</small>
			<input type="text" size="3" name="puls_max" value="0" />
				<small>max. Puls</small>
		</span>
	</div>

		<br class="clear" />

	<span id="outside" style="display: none;">
		<br />
		<input type="text" size="50" name="strecke" />
			<small style="margin-right: 100px;">Strecke</small>
		<select name="wetterid">
<?php
$wetter = $Mysql->fetch('SELECT * FROM `ltb_wetter` ORDER BY `order` ASC');
foreach($wetter as $dat)
	echo('<option value="'.$dat['id'].'">'.$dat['name'].'</option>');
?>
		</select>
			<small>Wetter</small>
		<input type="text" size="2" name="temperatur" />
			<small style="margin-right: 40px;">&#176;C</small>
			<br />
			<br />
		<input type="hidden" name="kleidung" id="kleidung" />
<?php
$kleidungen = $Mysql->fetch('SELECT * FROM `ltb_kleidung` ORDER BY `name_kurz` ASC');
foreach($kleidungen as $kleidung)
	echo('
		<input type="checkbox" name="'.$kleidung['name_kurz'].'" onClick="document.getElementById(\'kleidung\').value +=\''.$kleidung['id'].',\';" /> <small style="margin-right: 10px;">'.$kleidung['name_kurz'].'</small>');
?>
			<br />
	</span>

	<span id="splits" style="display: none;">
		<br />
		<textarea name="splits" cols="70" rows="3"></textarea>
			<small>Splits</small><br />
	</span>

	<center>
		<input style="margin-top: 10px;" type="submit" value="Eintragen!" />
	</center>
</form>

<?php
$Frontend->displayFooter();
$Frontend->close();
?>