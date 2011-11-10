<?php
require '../../inc/class.Frontend.php';

$Frontend = new Frontend(true, __FILE__);
$Mysql = Mysql::getInstance();
$Error = Error::getInstance();

$Plugin = Plugin::getInstanceFor('RunalyzePluginPanel_Sportler');
$Plugin_conf = $Plugin->get('config');

if (isset($_POST['type']) && $_POST['type'] == "user") {
	$columns = array('time');
	$values = array(time());
	$vars = array('weight');

	if ($Plugin_conf['use_body_fat']['var']) {
		$vars[] = 'fat';
		$vars[] = 'water';
		$vars[] = 'muscles';
	}
	if ($Plugin_conf['use_pulse']['var']) {
		$vars[] = 'pulse_rest';
		$vars[] = 'pulse_max';
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

$dat = User::getLastRow();
if (empty($dat))
	$dat = User::getDefaultArray();
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
	<input type="text" name="weight" value="<?php echo $dat['weight']; ?>" size="5" />
	<small>Gewicht</small>
</label><br />

<?php if ($Plugin_conf['use_body_fat']['var']): ?>
<label>
	<input type="text" name="fat" value="<?php echo $dat['fat']; ?>" size="5" />
	<small>&#37; Fett</small>
</label><br />
<label>
	<input type="text" name="water" value="<?php echo $dat['water']; ?>"	size="5" />
	<small>&#37; Wasser</small>
</label><br />
<label>
	<input type="text" name="muscles" value="<?php echo $dat['muscles']; ?>" size="5" />
	<small>&#37; Muskeln</small>
</label><br />
<?php endif; ?>

<?php if ($Plugin_conf['use_pulse']['var']): ?><br />
<label>
	<input type="text" name="pulse_rest" value="<?php echo $dat['pulse_rest']; ?>" size="5" />
	<small>Ruhepuls</small>
</label><br />
<label>
	<input type="text" name="pulse_max" value="<?php echo $dat['pulse_max']; ?>" size="5" />
	<small>Maximalpuls</small>
</label><br />
<?php endif; ?>

<input type="submit" value="Eintragen" />

</form>

<?php
$Frontend->displayFooter();
$Frontend->close();
?>