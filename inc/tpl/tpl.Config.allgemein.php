<h1>Allgemeine Einstellungen</h1>

<div class="c">
	<?php
	$categories = $Mysql->fetch('SELECT `category` FROM `'.PREFIX.'conf` WHERE `category`!="'.Config::$HIDDEN_CAT.'" GROUP BY `category`');
	foreach ($categories as $i => $cat)
		echo Ajax::change('<strong>'.$cat['category'].'</strong>', 'conf_div', strtolower($cat['category'])).($i < count($categories)-1 ? ' &nbsp; - &nbsp; ' : '').NL;
	?>
</div>

<hr />

<div id="conf_div">
	<?php
	foreach ($categories as $i => $cat) {
		echo '<div id="'.strtolower($cat['category']).'" class="change"'.($i == 0 ? '' : ' style="display:none;"').'>';
	
		$confs = $Mysql->fetchAsArray('SELECT * FROM `'.PREFIX.'conf` WHERE `category`="'.$cat['category'].'"');
	
		if (empty($confs))
			echo '<em>Keine Konfigurationsvariablen vorhanden vorhanden.</em>';
	
		foreach ($confs as $i => $conf) {
			echo '<label>';
			echo Config::getInputField($conf).NL;
			echo '<strong>'.$conf['description'].'</strong>';
			if ($conf['type'] == 'array')
				echo ' <small>(kommagetrennt)</small>';
			echo '</label><br />';
		}
	
		echo '</div>';
	}
	?>
</div>