<?php
/**
 * File displaying the formular with new sportler information
 * Call:   inc/plugin/window.sportler.php
 */
require('../class.Frontend.php');
$Frontend = new Frontend(true, __FILE__);
$Mysql = Mysql::getInstance();
$Error = Error::getInstance();

$Plugin = Plugin::getInstanceFor('RunalyzePlugin_SportlerPanel');
$Plugin_conf = $Plugin->get('config');

if (isset($_POST['type']) && $_POST['type'] == "user") {
	$columns = array('time');
	$values = array(time());
	$vars = array('gewicht');
	if ($Plugin_conf['use_body_fat']) {
		$vars[] = 'fett';
		$vars[] = 'wasser';
		$vars[] = 'muskeln';
	}
	if ($Plugin_conf['use_pulse']) {
		$vars[] = 'puls_ruhe';
		$vars[] = 'puls_max';
	}
	foreach($vars as $var)
		if (isset($_POST[$var])) {
			$columns[] = $var;
			$values[] = Helper::CommaToPoint($_POST[$var]);
		}
	$id = $Mysql->insert(PREFIX.'user', $columns, $values);

	$submit = '<em>Die Daten wurden gespeichert!</em><br /><br />';
}

$Frontend->displayHeader();

$dat = $Mysql->fetch(PREFIX.'user','LAST');
if (empty($dat))
	$dat = array('gewicht' => '', 'fett' => '', 'wasser' => '', 'muskeln' => '', 'puls_ruhe' => '', 'puls_max' => '');
?>
<h1>K&ouml;rper-Daten eingeben</h1>

<?php
if (isset($submit))
	echo ('<div id="submit-info">'.$submit.'</div>');
?>

<form class="ajax" action="<?php echo $_SERVER['SCRIPT_NAME']; ?>" id="sportler" onsubmit="return false;" method="post">

<input type="hidden" name="type" value="user" />
<input type="hidden" name="time" value="<?php echo(time()); ?>" />
<label>
	<input type="text" name="gewicht" value="<?php echo $dat['gewicht']; ?>" size="5" />
	<small>Gewicht</small>
</label><br />

<?php if ($Plugin_conf['use_body_fat']): ?>
<label>
	<input type="text" name="fett" value="<?php echo $dat['fett']; ?>" size="5" />
	<small>&#37; Fett</small>
</label><br />
<label>
	<input type="text" name="wasser" value="<?php echo $dat['wasser']; ?>"	size="5" />
	<small>&#37; Wasser</small>
</label><br />
<label>
	<input type="text" name="muskeln" value="<?php echo $dat['muskeln']; ?>" size="5" />
	<small>&#37; Muskeln</small>
</label><br />
<?php endif; ?>

<?php if ($Plugin_conf['use_weight']): ?><br />
<label>
	<input type="text" name="puls_ruhe" value="<?php echo $dat['puls_ruhe']; ?>" size="5" />
	<small>Ruhepuls</small>
</label><br />
<label>
	<input type="text" name="puls_max" value="<?php echo $dat['puls_max']; ?>" size="5" />
	<small>Maximalpuls</small>
</label><br />
<?php endif; ?>

<input type="submit" value="Eintragen" />

</form>

<?php
$Frontend->displayFooter();
$Frontend->close();
?>