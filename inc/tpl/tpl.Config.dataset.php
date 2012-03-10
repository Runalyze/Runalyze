<h1>Dataset</h1>

Das Dataset bestimmt, welche Daten der Trainings in der &Uuml;bersicht angezeigt werden.<br />
	<br />

<table class="c">
	<tr>
		<td title="Die Information wird in der Tabelle direkt angezeigt">Anzeige</td>
		<td title="Die Daten werden f&uuml;r die Zusammenfassung der Sportart angezeigt">Zusammenfassung</td>
		<td style="width: 170px;" />
		<td title="Gibt die Reihenfolge der Anzeige vor">Position</td>
	</tr>
	<tr class="space">
		<td colspan="4"></td>
	</tr>
<?php
$datasets = $Mysql->fetchAsArray('SELECT *, (`position` = 0) as `hidden` FROM `'.PREFIX.'dataset` ORDER BY `hidden` ASC, ABS(2.5-`modus`) ASC, `position` ASC');
foreach($datasets as $i => $dataset) {
	// Modus=1 has been deleted
	$disabled = ($dataset['modus'] == 3) ? ' disabled="disabled"' : '';
	$checked_2 = ($dataset['modus'] >= 2) ? ' checked="checked"' : '';
	$checked_0 = ($dataset['modus'] == 0) ? ' checked="checked"' : '';
	$checked = ($dataset['summary'] == 1) ? ' checked="checked"' : '';
	if ($dataset['summary_mode'] == "YES" || $dataset['summary_mode'] == "NO")
		$checked .= ' disabled="disabled"';
	echo('
	<tr class="a'.($i%2+1).'">
		<td>
			<input type="hidden" name="'.$dataset['id'].'_modus_3" value="'.$dataset['modus'].'" />
			<input type="checkbox" name="'.$dataset['id'].'_modus"'.$checked_2.$disabled.' />
		</td>
		<td><input type="checkbox" name="'.$dataset['id'].'_summary"'.$checked.' /></td>
		<td title="'.$dataset['description'].'">'.$dataset['label'].'</td>
		<td><input type="text" name="'.$dataset['id'].'_position" value="'.$dataset['position'].'" size="2" /></td>
	</tr>');
}
?>
</table>