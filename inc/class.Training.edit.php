<?php
/**
 * File displaying the formular with new sportler information
 * Call:   inc/class.Training.edit.php?id=
 */
require('class.Frontend.php');
$Frontend = new Frontend(true, __FILE__);
$Mysql = Mysql::getInstance();

$id = $_GET['id'];

if (isset($_POST) && $_POST['type'] == "training") {
	$sport = Helper::Sport($_POST['sportid'], true);

	$columns = array('sportid');
	$values = array($sport['id']);
	// Short version for $columns['var'] and $values[$_POST['var']]
	// Helper::Umlaute() and Helper::CommaToPoint will be called automatically
	$vars = array('kalorien', 'bemerkung', 'trainingspartner');

	// TODO error-handling if day and time are not in the right format
	// Timestamp
	$tag = explode('.', $_POST['datum']);
	$zeit = explode(':', $_POST['zeit']);
	$timestamp = mktime($zeit[0], $zeit[1], 0, $tag[1], $tag[0], $tag[2]);
	$columns[] = 'time';
	$values[] = $timestamp;
	// Time in seconds
	$dauer = explode(":", $_POST['dauer']);
	$time_in_s = 3600 * $dauer[0] + 60 * $dauer[1] + $dauer[2];
	$columns[] = 'dauer';
	$values[] = $time_in_s;
	// save difference for typ/schuh
	$distanz = Helper::CommaToPoint($_POST['distanz']);
	$dauer_dif = $time_in_s - $_POST['dauer_old'];
	$dist_dif = $distanz - $_POST['dist_old'];

	if ($sport['distanztyp'] == 1) {
		$vars[] = 'distanz';
		$vars[] = 'pace';
		$columns[] = 'bahn';
		$values[] = $_POST['bahn'] == 'on' ? 1 : 0;
	}

	if ($sport['outside'] == 1) {
		$vars[] = 'temperatur';
		$vars[] = 'wetterid';
		$vars[] = 'strecke';
		// Kleidung
		$kleidung = array();
		$kleidungen = $Mysql->fetch('SELECT `id`, `name_kurz` FROM `ltb_kleidung`');
		foreach ($kleidungen as $kl) {
			if ($_POST[$kl['name_kurz']] == 'on')
				$kleidung[] = $kl['id'];
		}
		$columns[] = 'kleidung';
		$values[] = count($kleidung) > 0 ? implode(',', $kleidung) : '';
	}

	if ($sport['distanztyp'] == 1 && $sport['outside'] == 1)
		$vars[] = 'hm';

	if ($sport['pulstyp'] == 1) {
		$vars[] = 'puls';
		$vars[] = 'puls_max';
	}

	if ($sport['typen'] == 1) {
		// Typid und Schuhid
		$vars[] = 'typid';
		$vars[] = 'schuhid';
		$columns[] = 'laufabc';
		$values[] = $_POST['laufabc'] == 'on' ? 1 : 0;
		if (Helper::TypeHasSplits($_POST['typid']) == 1)
			$vars[] = 'splits';
	}

	foreach($vars as $var)
		if (isset($_POST[$var])) {
			$columns[] = $var;
			$values[] = Helper::Umlaute(Helper::CommaToPoint($_POST[$var]));
		}
	$Mysql->update('ltb_training', $id, $columns, $values);


	if ($_POST['schuhid_old'] != $_POST['schuhid'] && $_POST['schuhid'] != 0) {
		$Mysql->query('UPDATE `ltb_schuhe` SET `km`=`km`-"'.$_POST['dist_old'].'", `dauer`=`dauer`-'.$_POST['dauer_old'].' WHERE `id`='.$_POST['schuhid_old'].' LIMIT 1');
		$Mysql->query('UPDATE `ltb_schuhe` SET `km`=`km`+"'.$distanz.'", `dauer`=`dauer`+'.$time_in_s.' WHERE `id`='.$_POST['schuhid'].' LIMIT 1');
	}
	if ($sport['typen'] == 1)
		$Mysql->query('UPDATE `ltb_schuhe` SET `km`=`km`+'.$dist_dif.', `dauer`=`dauer`+'.$dauer_dif.' WHERE `id`='.$_POST['schuhid'].' LIMIT 1');
	if ($sport['distanztyp'] == 1)
		$Mysql->query('UPDATE `ltb_sports` SET `distanz`=`distanz`+'.$dist_dif.', `dauer`=`dauer`+'.$dauer_dif.' WHERE `id`='.$_POST['sportid'].' LIMIT 1');

	$Mysql->update('ltb_training', $_POST['id'], 'trimp', Helper::TRIMP($_POST['id']));
	$Mysql->update('ltb_training', $_POST['id'], 'vdot', JD::Training2VDOT($_POST['id']));

	// TODO What is if a previously wrong training caused a higher config-value and this has to be corrected now?
	if (Helper::ATL($timestamp) > CONFIG_MAX_ATL)
		$Mysql->query('UPDATE `ltb_config` SET `max_atl`="'.Helper::ATL($timestamp).'"');
	if (Helper::CTL($timestamp) > CONFIG_MAX_CTL)
		$Mysql->query('UPDATE `ltb_config` SET `max_ctl`="'.Helper::CTL($timestamp).'"');
	if (Helper::TRIMP($_POST['id']) > CONFIG_MAX_TRIMP)
		$Mysql->query('UPDATE `ltb_config` SET `max_trimp`="'.Helper::TRIMP($_POST['id']).'"');

	$submit = '<em>Die Daten wurden gespeichert!</em><br /><br />';
}

$Frontend->displayHeader();

$Training = new Training($id);

$sport = Helper::Sport($Training->get('sportid'), true);
?>
<h1><?php $Training->displayTitle(true); ?>, <?php $Training->displayDate(); ?></h1>

<?php
if (isset($submit))
	echo ('<div id="submit-info">'.$submit.'</div>');
?>

<form class="ajax" action="<?php echo $_SERVER['SCRIPT_NAME']; ?>?id=<?php echo $id; ?>" id="training" onsubmit="return false;" method="post">

<input type="hidden" name="type" value="training" />
<input type="hidden" name="id" value="<?php echo $Training->get('id'); ?>" />

<?php Error::getInstance()->add('TODO','Use class:Ajax for these links', __FILE__, __LINE__); ?>

<?php if ($Training->hasPositionData()): ?>
<a class="right change" href="#edit-gps" target="edit-div">GPS-Daten</a>
<?php endif; ?>

<a class="change" href="#edit-allg" target="edit-div">Allgemeines</a>
<?php if ($sport['distanztyp'] == 1): ?> |
<a class="change" href="#edit-train" target="edit-div">Training</a>
<?php endif; if ($sport['outside'] == 1) : ?> |
<a class="change" href="#edit-out" target="edit-div">Outside</a>
<?php endif; ?> <br />
<br />

<div id="edit-div">
<?php if ($Training->hasPositionData()): ?>
	<div id="edit-gps" class="change" style="display:none;">
		<a class="ajax" target="gps-results" href="inc/class.Training.elevationCorrection.php?id=<?php echo $id; ?>" title="Höhendaten korrigieren">ElevationCorrection</a><br />
		<br />
		<small>
			Mit der ElevationCorrection k&ouml;nnen die H&ouml;hendaten korrigiert werden.<br />
			Vorsicht: Die Abfrage kann lange dauern, bitte nicht abbrechen, bevor das Laden beendet ist.
		</small><br />
		<br />

		<?php // TODO: Check if elevationCorrection is already done ?>
		<small id="gps-results"></small>
	</div>
<?php endif; ?>


	<div id="edit-allg" class="change">
		<input type="hidden" name="sportid" value="<?php echo $Training->get('sportid'); ?>" />
		<input type="text" name="sport" value="<?php echo Helper::Sport($Training->get('sportid')); ?>" disabled="disabled" />
			<small>Sport</small><br />
		<input type="text" size="10" name="datum" value="<?php echo date("d.m.Y", $Training->get('time')); ?>" />
		<input type="text" size="4" name="zeit" value="<?php echo date("H:i", $Training->get('time')); ?>" />
			<small>Datum</small><br />
		<input type="hidden" id="kalorien_stunde" name="kalorienprostunde" value="<?php echo $sport['kalorien']; ?>" />
		<input type="hidden" name="dauer_old" value="<?php echo $Training->get('dauer'); ?>" />
		<input type="text" size="8" name="dauer" id="dauer" value="<?php echo Helper::Time($Training->get('dauer'), false, true); ?>" onChange="paceberechnung(); kalorienberechnung();" />
			<small>Dauer</small><br />
<?php if ($sport['distanztyp'] == 1): ?>
		<input type="checkbox" size="4" name="bahn" <?php echo Helper::Checked($Training->get('bahn') == 1); ?> />
			<small>Bahn</small>
		<input type="hidden" name="dist_old" value="<?php echo $Training->get('distanz'); ?>" />
		<input type="text" size="4" name="distanz" id="dist" value="<?php echo $Training->get('distanz'); ?>" onChange="paceberechnung();" />
			<small>km</small><br />
		<input type="text" size="4" name="pace" id="pace" value="<?php echo $Training->get('pace'); ?>" />
			<small>/km</small><br />
<?php endif; ?>
		<input type="text" size="4" name="kalorien" id="kalorien" value="<?php echo $Training->get('kalorien'); ?>" />
			<small>kcal</small><br />
		<input type="text" size="50" name="bemerkung" value="<?php echo Helper::Textarea($Training->get('bemerkung')); ?>" />
			<small>Bemerkung</small><br />
		<input type="text" size="50" name="trainingspartner" value="<?php echo Helper::Textarea($Training->get('trainingspartner')); ?>" />
			<small>Trainingspartner</small>
	</div>



	<div id="edit-train" class="change" style="display:none;">
		<span <?php echo $sport['typen'] == 1 ? '' : ' style="display:none;"'; ?>>
			<select name="typid">
<?php
$typen = $Mysql->fetch('SELECT `id`, `name` FROM `ltb_typ`', false, true);
foreach ($typen as $typ)
	echo('<option value="'.$typ['id'].'"'.Helper::Selected($typ['id'] == $Training->get('typid')).'>'.$typ['name'].'</option>');
?>
			</select><br />

			<input type="hidden" name="schuhid_old" value="<?php echo $Training->get('schuhid'); ?>" />
			<select name="schuhid">
<?php
$schuhe = $Mysql->fetch('SELECT `id`, `name` FROM `ltb_schuhe`', false, true);
foreach ($schuhe as $schuh)
	echo('<option value="'.$schuh['id'].'"'.Helper::Selected($schuh['id'] == $Training->get('schuhid')).'>'.$schuh['name'].'</option>');
?>
			</select><br />

			<input type="checkbox" size="4" name="laufabc" <?php echo Helper::Checked($Training->get('laufabc') == 1); ?> />
				Lauf-ABC<br />
		</span>

		<span id="puls" style="display:<?php echo $sport['pulstyp'] == 1 ? 'block' : 'none'; ?>;">
			<input type="text" size="3" name="puls" value="<?php echo $Training->get('puls'); ?>" />
				<small>Puls</small><br />
			<input type="text" size="3" name="puls_max" value="<?php echo $Training->get('puls_max'); ?>" />
				<small>max. Puls</small><br />
		</span>

		<span style="display:<?php echo Helper::TypeHasSplits($Training->get('typid')) ? 'block' : 'none'; ?>;">
			<textarea name="splits" cols="70" rows="3"><?php echo $Training->get('splits'); ?></textarea>
				<small>Splits</small><br />
		</span>
	</div>



	<div id="edit-out" class="change" style="display:none;">
		<input type="text" size="50" name="strecke" value="<?php echo Helper::Textarea($Training->get('strecke')); ?>" />
			<small style="margin-right: 63px;">Strecke</small>
		<input type="text" size="3" name="hm" value="<?php echo $Training->get('hm'); ?>" />
			<small>HM</small><br />
		<select name="wetterid">
<?php
$wetter = $Mysql->fetch('SELECT `id`, `name` FROM `ltb_wetter`');
foreach ($wetter as $wetter_dat)
	echo('<option value="'.$wetter_dat['id'].'"'.Helper::Selected($wetter_dat['id'] == $Training->get('wetterid')).'>'.$wetter_dat['name'].'</option>');
?>
		</select>
			<small>Wetter</small><br />
		<input type="text" size="2" name="temperatur" value="<?php echo $Training->get('temperatur'); ?>" />
			<small>&#176;C</small><br />
		<br />
		<small>Kleidung</small><br />
<?php
$kleidungen = $Mysql->fetch('SELECT `id`, `name_kurz` FROM `ltb_kleidung`');
foreach ($kleidungen as $kleidung) {
	$checked = Helper::Checked(in_array($kleidung['id'], explode(',', $Training->get('kleidung'))));
	echo('<input type="checkbox" name="'.$kleidung['name_kurz'].'"'.$checked.' />&nbsp;<small style="margin-right:12px;">'.$kleidung['name_kurz'].'</small>'.NL);
}
?>
	</div>
</div>

<br />

<input type="submit" value="Bearbeiten" />

</form>

<?php
$Frontend->displayFooter();
$Frontend->close();
?>