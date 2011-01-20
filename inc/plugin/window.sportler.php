<?php
/**
 * File displaying the formular with new sportler information
 * Call:   inc/plugin/window.sportler.php
 */
require('../class.Frontend.php');
$Frontend = new Frontend(true, __FILE__);
$Mysql = Mysql::getInstance();
$Error = Error::getInstance();

$Error->add('TODO', 'Config: use_koerperfett, use_ruhepuls, use_blutdruck');
$Error->add('TODO', 'Config: wunschgewicht');

if (isset($_POST) && $_POST['type'] == "user") {
	$columns = array('time');
	$values = array(time());
	$vars = array('gewicht');
	if (CONFIG_USE_KOERPERFETT == 1) {
		$vars[] = 'fett';
		$vars[] = 'wasser';
		$vars[] = 'muskeln';
	}
	if (CONFIG_USE_RUHEPULS == 1) {
		$vars[] = 'puls_ruhe';
		$vars[] = 'puls_max';
	}
	if (CONFIG_USE_BLUTDRUCK == 1) {
		$vars[] = 'blutdruck_min';
		$vars[] = 'blutdruck_max';
	}
	foreach($vars as $var)
		if (isset($_POST[$var])) {
			$columns[] = $var;
			$values[] = Helper::CommaToPoint($_POST[$var]);
		}
	$id = $Mysql->insert('ltb_user', $columns, $values);

	$submit = '<em>Die Daten wurden gespeichert!</em><br /><br />';
}

$Frontend->displayHeader();

$dat = $Mysql->fetch('ltb_user','LAST');
?>
<h1>K&ouml;rper-Daten eingeben</h1>

<?php
if (isset($submit))
	echo ('<div id="submit-info">'.$submit.'</div>');
?>

<form class="ajax" action="<?php echo $_SERVER['SCRIPT_NAME']; ?>" id="sportler" onsubmit="return false;" method="post">

<input type="hidden" name="type" value="user" />
<input type="hidden" name="time" value="<?php echo(time()); ?>" />
<input type="text" name="gewicht" value="<?php echo $dat['gewicht']; ?>" size="5" />
	<small>Gewicht</small><br />

<?php if (CONFIG_USE_KOERPERFETT == 1): ?>
<input type="text" name="fett" value="<?php echo $dat['fett']; ?>" size="5" />
	<small>&#37; Fett</small><br />
<input type="text" name="wasser" value="<?php echo $dat['wasser']; ?>"	size="5" />
	<small>&#37; Wasser</small><br />
<input type="text" name="muskeln" value="<?php echo $dat['muskeln']; ?>" size="5" />
	<small>&#37; Muskeln</small><br />
<?php endif; ?>

<?php if (CONFIG_USE_RUHEPULS == 1): ?><br />
<input type="text" name="puls_ruhe" value="<?php echo $dat['puls_ruhe']; ?>" size="5" />
	<small>Ruhepuls</small><br />
<input type="text" name="puls_max" value="<?php echo $dat['puls_max']; ?>" size="5" />
	<small>Maximalpuls</small><br />
<?php endif; ?>

<?php if (CONFIG_USE_BLUTDRUCK == 1): ?><br />
<input type="text" name="blutdruck_min" value="<?php echo $dat['blutdruck_min']; ?>" size="5" />
	<small>zu</small>
<input type="text" name="blutdruck_max" value="<?php echo $dat['blutdruck_max']; ?>" size="5" />
	<small>Blutdruck</small><br />
<?php endif; ?>

<input type="submit" value="Eintragen" />

</form>

<?php
$Frontend->displayFooter();
$Frontend->close();
?>