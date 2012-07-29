<h1>Allgemeine Einstellungen</h1>

<?php
$categories = $Mysql->fetch('SELECT `category` FROM `'.PREFIX.'conf` WHERE `category`!="'.Config::$HIDDEN_CAT.'" GROUP BY `category`');

foreach ($categories as $i => $cat) {
	$id = mb_strtolower($cat['category']);

	echo '<fieldset id="conf_'.$id.'"'.($i != 0 ? ' class="collapsed"' : '').'>';
	echo '<legend onclick="Runalyze.toggleFieldset(this, \'conf_'.$id.'\', true)">'.$cat['category'].'</legend>';

	$confs = $Mysql->fetchAsArray('SELECT * FROM `'.PREFIX.'conf` WHERE `category`="'.$cat['category'].'"');

	if (empty($confs))
		echo '<em>Keine Konfigurationsvariablen vorhanden.</em>';

	foreach ($confs as $i => $conf) {
		$id = 'conf_field_'.$conf['key'];

		echo '<div class="w50">';
		echo '<label for="'.$id.'">'.$conf['description'].'</label>';
		echo Config::getInputField($conf, $id);
		if ($conf['type'] == 'array')
			echo ' <small>(kommagetrennt)</small>';
		echo '</div>';
	}

	echo '</fieldset>';
}
echo '<fieldset id="conf_account" class="collapsed">';
echo '<legend onclick="Runalyze.toggleFieldset(this, \'conf_account\', true)">Account</legend>';
    echo '<div class="w50">';
    echo '<label for="conf_link_DELETE">Account l&ouml;schen</label>';
    echo '<a href="call/window.delete.php" class="window" id="conf_link_DELETE">Account l&ouml;schen</a>';
    echo ' <small>(Du bekommst eine Mail mit einem Best&auml;tigungslink zur Löschung.)</small>';
echo '</fieldset>';
?>