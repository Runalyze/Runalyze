<?php
/**
 * File displaying the formular with new sportler information
 * Call:   inc/class.Training.edit.php?id=
 */
require('class.Frontend.php');
$Frontend = new Frontend(true, __FILE__);
$Mysql = Mysql::getInstance();

if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
	$Mysql->delete(PREFIX.'training', (int)$_GET['delete']);
	echo '<div id="submit-info" class="error">Das Training wurde gel&ouml;scht.</div>';
	echo '<script type="text/javascript">jReloadContent();</script>';
	exit();
}

$id = $_GET['id'];

if (isset($_POST['type']) && $_POST['type'] == "training") {
	$error = '';

	$sport = Helper::Sport($_POST['sportid'], true);

	$columns = array('sportid');
	$values = array($sport['id']);
	// Short version for $columns['var'] and $values[$_POST['var']]
	// Helper::Umlaute() and Helper::CommaToPoint will be called automatically
	$vars = array('kalorien', 'bemerkung', 'trainingspartner');

	// Timestamp
	$tag = explode('.', $_POST['datum']);
	$zeit = explode(':', $_POST['zeit']);

	if (count($tag) != 3 || count($zeit) != 2)
		$error = 'Das Datum konnte nicht gelesen werden.';
	else {
		$timestamp = mktime($zeit[0], $zeit[1], 0, $tag[1], $tag[0], $tag[2]);
		$columns[] = 'time';
		$values[]  = $timestamp;
	}

	// Time in seconds
	$ms        = explode(".", Helper::CommaToPoint($_POST['dauer']));
	$dauer     = explode(":", $ms[0]);
	if (!isset($ms[1]))
		$ms[1] = 0;
	$time_in_s = round(3600 * $dauer[0] + 60 * $dauer[1] + $dauer[2] + ($ms[1]/100), 2);
	$columns[] = 'dauer';
	$values[]  = $time_in_s;

	if ($time_in_s == 0)
		$error = 'Es muss eine Trainingszeit angegeben sein.';

	// save difference for typ/schuh
	$distanz = Helper::CommaToPoint($_POST['distanz']);
	$dauer_dif = $time_in_s - $_POST['dauer_old'];
	$dist_dif = $distanz - $_POST['dist_old'];

	if ($sport['distanztyp'] == 1) {
		$vars[]    = 'distanz';
		$vars[]    = 'pace';
		$columns[] = 'bahn';
		$values[]  = isset($_POST['bahn']) && $_POST['bahn'] == 'on' ? 1 : 0;
	}

	if ($sport['outside'] == 1) {
		if (strlen($_POST['temperatur']) > 0)
			$vars[] = 'temperatur';

		$vars[] = 'wetterid';
		$vars[] = 'strecke';

		// Kleidung
		$kleidung = array();
		$kleidungen = $Mysql->fetchAsArray('SELECT `id`, `name_kurz` FROM `'.PREFIX.'kleidung`');
		foreach ($kleidungen as $kl) {
			if (isset($_POST[$kl['name_kurz']]) && $_POST[$kl['name_kurz']] == 'on')
				$kleidung[] = $kl['id'];
		}

		$columns[] = 'kleidung';
		$values[]  = count($kleidung) > 0 ? implode(',', $kleidung) : '';
	}

	if ($sport['distanztyp'] == 1 && $sport['outside'] == 1)
		$vars[] = 'hm';

	if ($sport['pulstyp'] == 1) {
		$vars[] = 'puls';
		$vars[] = 'puls_max';
	}

	if ($sport['typen'] == 1) {
		// Typid und Schuhid
		$vars[]    = 'typid';
		$vars[]    = 'schuhid';
		$columns[] = 'laufabc';
		$values[]  = isset($_POST['laufabc']) && $_POST['laufabc'] == 'on' ? 1 : 0;

		if (Helper::TypeHasSplits($_POST['typid']) == 1)
			$vars[] = 'splits';
	}

	foreach($vars as $var)
		if (isset($_POST[$var])) {
			$columns[] = $var;
			$values[] = Helper::Umlaute(Helper::CommaToPoint($_POST[$var]));
		}

	if ($error == '') {
		$Mysql->update(PREFIX.'training', $id, $columns, $values);

		if (isset($_POST['schuhid_old']) && isset($_POST['dauer_old']) && isset($_POST['dist_old']) && isset($_POST['schuhid']) && $_POST['schuhid_old'] != $_POST['schuhid'] && $_POST['schuhid'] != 0) {
			$Mysql->query('UPDATE `'.PREFIX.'schuhe` SET `km`=`km`-"'.$_POST['dist_old'].'", `dauer`=`dauer`-'.$_POST['dauer_old'].' WHERE `id`='.$_POST['schuhid_old'].' LIMIT 1');
			$Mysql->query('UPDATE `'.PREFIX.'schuhe` SET `km`=`km`+"'.$distanz.'", `dauer`=`dauer`+'.$time_in_s.' WHERE `id`='.$_POST['schuhid'].' LIMIT 1');
		}
		if ($sport['typen'] == 1 && isset($_POST['schuhid']))
			$Mysql->query('UPDATE `'.PREFIX.'schuhe` SET `km`=`km`+'.$dist_dif.', `dauer`=`dauer`+'.$dauer_dif.' WHERE `id`='.$_POST['schuhid'].' LIMIT 1');
		if ($sport['distanztyp'] == 1 && isset($_POST['sportid']))
			$Mysql->query('UPDATE `'.PREFIX.'sports` SET `distanz`=`distanz`+'.$dist_dif.', `dauer`=`dauer`+'.$dauer_dif.' WHERE `id`='.$_POST['sportid'].' LIMIT 1');
	
		$Mysql->update(PREFIX.'training', $_POST['id'], 'trimp', Helper::TRIMP($_POST['id']));
		$Mysql->update(PREFIX.'training', $_POST['id'], 'vdot', JD::Training2VDOT($_POST['id']));

		if (Helper::ATL($timestamp) > CONFIG_MAX_ATL)
			$Mysql->query('UPDATE `'.PREFIX.'config` SET `max_atl`="'.Helper::ATL($timestamp).'"');
		if (Helper::CTL($timestamp) > CONFIG_MAX_CTL)
			$Mysql->query('UPDATE `'.PREFIX.'config` SET `max_ctl`="'.Helper::CTL($timestamp).'"');
		if (Helper::TRIMP($_POST['id']) > CONFIG_MAX_TRIMP)
			$Mysql->query('UPDATE `'.PREFIX.'config` SET `max_trimp`="'.Helper::TRIMP($_POST['id']).'"');
	
		$submit = '<em>Die Daten wurden gespeichert!</em><br /><br />';
	} else {
		$submit = '<em class="error">'.$error.'</em><br /><br />';
	}
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

<?php
echo Ajax::change(Icon::get(Icon::$CROSS, 'Training l&ouml;schen'), 'edit-div', '#delete', 'right').NL;

if ($Training->hasPositionData())
	echo '<span class="right">&nbsp;|&nbsp;</span> '.Ajax::change('GPS-Daten', 'edit-div', '#edit-gps', 'right').NL;

echo Ajax::change('Allgemeines', 'edit-div', '#edit-allg').NL;

if ($sport['distanztyp'] == 1)
	echo ' | '.Ajax::change('Training', 'edit-div', '#edit-train').NL;

if ($sport['outside'] == 1)
	echo ' | '.Ajax::change('Outside', 'edit-div', '#edit-out').NL;
?>

<hr />

<div id="edit-div">
	<div id="delete" class="change" style="display:none;">
		Bist du dir sicher, dass du dieses Training l&ouml;schen m&ouml;chtest?<br />
		<br />
<?php echo Ajax::link('<strong>Training wirklich l&ouml;schen</strong>', 'ajax', $_SERVER['SCRIPT_NAME'].'?delete='.$id); ?><br />
		<br />
		<small>Falls im Anschluss Probleme mit der Datenbank auftauchen, solltest du das Plugin-Tool zur Datenbankreinigung verwenden.</small>
	</div>

<?php // TODO: Check if elevationCorrection is already done ?>
	<div id="edit-gps" class="change" style="display:none;">
<?php if ($Training->hasPositionData()): ?>
		<a class="ajax" target="gps-results" href="inc/class.Training.elevationCorrection.php?id=<?php echo $id; ?>" title="H&ouml;hendaten korrigieren"><strong>ElevationCorrection</strong></a><br />
		<br />
		<small>
			Mit der ElevationCorrection k&ouml;nnen die H&ouml;hendaten korrigiert werden.<br />
			Vorsicht: Die Abfrage kann lange dauern, bitte nicht abbrechen, bevor das Laden beendet ist.
		</small><br />
		<br />

		<small id="gps-results"></small>
<?php else: ?>
		... hier soll ein Formular hin, um nachtr&auml;glich eine TCX-Datei hochzuladen und die GPS-Daten zu speichern.
<?php endif; ?>
	</div>


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
		<span<?php echo $sport['typen'] == 1 ? '' : ' style="display:none;"'; ?>>
			<select name="typid">
<?php
$typen = $Mysql->fetchAsArray('SELECT `id`, `name` FROM `'.PREFIX.'typ`');
echo('<option value="0">'.(empty($typen) ? 'keine Typen vorhanden' : '?').'</option>');
foreach ($typen as $typ)
	echo('<option value="'.$typ['id'].'"'.Helper::Selected($typ['id'] == $Training->get('typid')).'>'.$typ['name'].'</option>');
?>
			</select><br />

			<input type="hidden" name="schuhid_old" value="<?php echo $Training->get('schuhid'); ?>" />
			<select name="schuhid">
<?php
$schuhe = $Mysql->fetchAsArray('SELECT `id`, `name` FROM `'.PREFIX.'schuhe`');
echo('<option value="0">'.(empty($schuhe) ? 'keine Schuhe vorhanden' : '?').'</option>');
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
$wetter = $Mysql->fetchAsArray('SELECT `id`, `name` FROM `'.PREFIX.'wetter`');
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
$kleidungen = $Mysql->fetchAsArray('SELECT `id`, `name_kurz` FROM `'.PREFIX.'kleidung`');
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