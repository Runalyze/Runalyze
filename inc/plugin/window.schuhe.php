<?php
/**
 * File displaying the formular for shoes
 * Call:   plugin/window.schuhe.php
 */
require '../inc/class.Frontend.php';
$Frontend = new Frontend(true, __FILE__);
$Mysql = Mysql::getInstance();

if (isset($_POST['type']) && $_POST['type'] == 'schuh') {
	$columns = array('inuse');
	$values = array(1);
	$vars = array('name', 'brand', 'since');
	foreach($vars as $var)
		if (isset($_POST[$var])) {
			$columns[] = $var;
			$values[] = $_POST[$var];
		}

	if (strlen($_POST['name']) > 1) {
		$Mysql->insert(PREFIX.'shoe', $columns, $values);
		$submit = '<em>Der Schuh wurde gespeichert!</em><br /><br />';
	} else {
		$submit = '<em class="error">Der Schuh muss einen Namen haben!</em><br /><br />';
	}
} elseif (isset($_POST['type']) && $_POST['type'] == 'shoe_unuse') {
	$Mysql->update(PREFIX.'shoe', $_POST['shoeid'], 'inuse', 0);

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
	<label>
		<input type="text" name="name" size="50" />
		<small>Name</small>
	</label><br />
	<label>
		<input type="text" name="brand" size="15" />
		<small>Marke</small>
	</label><br />
	<label>
		<input type="text" name="since" value="<?php echo date("d.m.Y"); ?>" size="15" />
		<small>Kaufdatum</small>
	</label><br />
	<input type="submit" value="Eintragen" />
</form>

<br />
<br />

<h1>Schuhe bearbeiten</h1>

<form class="ajax" action="<?php echo $_SERVER['SCRIPT_NAME']; ?>" id="schuhe_edit" onsubmit="return false;" method="post">
	<input type="hidden" name="type" value="shoe_unuse" />
	<?php echo Shoe::getSelectBox(true, false); ?>

	<input type="submit" value="Nicht mehr nutzen" />
</form>

<?php
$Frontend->displayFooter();
$Frontend->close();
?>