<?php
/**
 * File displaying the formular for shoes
 * Call:   inc/plugin/window.schuhe.php
 */
require('../class.Frontend.php');
$Frontend = new Frontend(true, __FILE__);
$Mysql = Mysql::getInstance();

if (isset($_POST) && $_POST['type'] == 'schuh' && $_POST['name'] != '') {
	$columns = array('inuse');
	$values = array(1);
	$vars = array('name', 'marke', 'kaufdatum');
	foreach($vars as $var)
		if (isset($_POST[$var])) {
			$columns[] = $var;
			$values[] = $_POST[$var];
		}
	$Mysql->insert('ltb_schuhe', $columns, $values);

	$submit = '<em>Der Schuh wurde gespeichert!</em><br /><br />';
}
elseif (isset($_POST) && $_POST['type'] == 'schuh_unuse') {
	$Mysql->update('ltb_schuhe', $_POST['schuhid'], 'inuse', 0);

	$submit = '<em>Der Schuh kann nun nicht mehr benutzt werden!</em><br /><br />';
}

$Frontend->displayHeader();
?>
<h1>Neuen Schuh erstellen</h1>

<?php
if (isset($submit))
	echo ('<div id="submit-info">'.$submit.'</div>');
?>

<form class="ajax" action="<?php echo $_SERVER['SCRIPT_NAME']; ?>" id="schuhe" onsubmit="return false;" method="post">
	<input type="hidden" name="type" value="schuh" />
	<input type="text" name="name" size="50" />
		<small>Name</small><br />
	<input type="text" name="marke" size="15" />
		<small>Marke</small><br />
	<input type="text" name="kaufdatum" value="<?php echo date("d.m.Y"); ?>" size="15" />
		<small>Kaufdatum</small><br />
	<input type="submit" value="Eintragen" />
</form>

<br />
<br />

<h1>Schuhe bearbeiten</h1>

<form class="ajax" action="<?php echo $_SERVER['SCRIPT_NAME']; ?>" id="schuhe_edit" onsubmit="return false;" method="post">
	<input type="hidden" name="type" value="schuh_unuse" />
	<select name="schuhid">
<?php
$schuhe = $Mysql->fetch('SELECT * FROM `ltb_schuhe` WHERE `inuse`=1 ORDER BY `id` ASC');
foreach($schuhe as $schuh)
	echo('
		<option value="'.$schuh['id'].'">'.$schuh['name'].'</option>');
?>
	</select>
	<input type="submit" value="Nicht mehr nutzen" />
</form>

<?php
$Frontend->displayFooter();
$Frontend->close();
?>