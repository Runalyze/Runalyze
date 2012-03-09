<?php
require '../../inc/class.Frontend.php';
$Frontend = new Frontend(true, __FILE__);
$Mysql = Mysql::getInstance();

$isEditingMode = false;

if (isset($_POST['type']) && $_POST['type'] == 'schuh') {
	$columns = array('inuse');
	$values = array(1);
	$vars = array('name', 'brand', 'since');
	foreach($vars as $var)
		if (isset($_POST[$var])) {
			$columns[]   = $var;
			$values[]    = $_POST[$var];
		}

	if (strlen($_POST['name']) > 1) {
		if (isset($_POST['shoeid'])) {
			$isEditingMode = true;
			$Mysql->update(PREFIX.'shoe', $_POST['shoeid'], $columns, $values);
			$submit = '<em>Der Schuh wurde ge&auml;ndert!</em><br /><br />';
		} else {
			$Mysql->insert(PREFIX.'shoe', $columns, $values);
			$submit = '<em>Der Schuh wurde gespeichert!</em><br /><br />';
			unset($_POST);
		}
	} else {
		$submit = '<em class="error">Der Schuh muss einen Namen haben!</em><br /><br />';
	}
} elseif (isset($_POST['type']) && $_POST['type'] == 'shoe_unuse') {
	$Mysql->update(PREFIX.'shoe', $_POST['shoeid'], 'inuse', 0);

	$submit = '<em>Der Schuh kann nun nicht mehr benutzt werden!</em><br /><br />';
} elseif (isset($_POST['type']) && $_POST['type'] == 'shoe_edit') {
	$isEditingMode = true;
	$Shoe = $Mysql->fetch(PREFIX.'shoe', $_POST['shoeid']);
	$_POST['name'] = $Shoe['name'];
	$_POST['brand'] = $Shoe['brand'];
	$_POST['since'] = $Shoe['since'];
}

$Frontend->displayHeader();
?>
<h1>Neuen Schuh erstellen</h1>

<?php
if (isset($submit))
	echo ('<div id="submit-info">'.$submit.'</div>');
?>

<form class="ajax" action="<?php echo $_SERVER['SCRIPT_NAME']; ?>" id="schuhe" onsubmit="return false;" method="post">
	<?php echo HTML::hiddenInput('type', 'schuh'); ?>
	<?php if ($isEditingMode) echo HTML::hiddenInput('shoeid'); ?>

	<label>
		<?php echo HTML::simpleInputField('name', 20); ?>
		<small>Name</small>
	</label><br />
	<label>
		<?php echo HTML::simpleInputField('brand', 15); ?>
		<small>Marke</small>
	</label><br />
	<label>
		<?php echo HTML::simpleInputField('since', 15, date("d.m.Y")); ?>
		<small>Kaufdatum</small>
	</label><br />
	<input type="submit" value="<?php echo ($isEditingMode) ? 'Bearbeiten' : 'Eintragen'; ?>" />
</form>

<br />
<br />

<h1>Schuh bearbeiten</h1>

<form class="ajax" action="<?php echo $_SERVER['SCRIPT_NAME']; ?>" id="schuhe_edit" onsubmit="return false;" method="post">
	<input type="hidden" name="type" value="shoe_edit" />
	<?php echo Shoe::getSelectBox(true, false); ?>

	<input type="submit" value="bearbeiten" />
</form>

<br />
<br />

<h1>Schuh &quot;wegwerfen&quot;</h1>

<form class="ajax" action="<?php echo $_SERVER['SCRIPT_NAME']; ?>" id="schuhe_edit" onsubmit="return false;" method="post">
	<input type="hidden" name="type" value="shoe_unuse" />
	<?php echo Shoe::getSelectBox(true, false); ?>

	<input type="submit" value="Nicht mehr nutzen" />
</form>

<?php
$Frontend->displayFooter();
$Frontend->close();
?>