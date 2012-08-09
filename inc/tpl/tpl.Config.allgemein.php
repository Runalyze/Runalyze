<h1>Allgemeine Einstellungen</h1>

<?php
$Consts    = Config::getConsts();
$Fieldsets = Config::getFieldsets();
$first     = true;

foreach ($Fieldsets as $Label => $Fields) {
	$id = 'conf_'.mb_strtolower($Label);

	echo '<fieldset id="'.$id.'"'.(!$first ? ' class="collapsed"' : '').'>';
	echo '<legend onclick="Runalyze.toggleFieldset(this, \''.$id.'\', true);">'.$Label.'</legend>';

	foreach ($Fields as $i => $Key) {
		if ($Key == '') {
			echo '<div class="w50"></div>';
			continue;
		}

		$id = 'conf_field_'.$Key;

		echo '<div class="w50">';

			echo '<label for="'.$id.'">'.$Consts[$Key]['description'].'</label>';
			echo Config::getInputField($Consts[$Key], $id);
	
			if ($Consts[$Key]['type'] == 'array')
				echo ' <small>(kommagetrennt)</small>';

		echo '</div>';
	}

	echo '</fieldset>';

	$first = false;
}
?>