<h1>Sportarten</h1>

Fahre mit der Maus &uuml;ber die &Uuml;berschrift, falls dir die Bezeichnungen unklar sind.<br />

	<br />

<table class="c" style="width:100%;">
	<tr class="b">
		<td title="Diese Sportart wird verwendet" class="small">Aktiv</td>
		<td class="small" title="Es wird nur ein Symbol vor dem jeweiligen Tag angezeigt">Kurz</td>
		<td colspan="2">Sportart</td>
		<td title="Durchschnittlicher Energieumsatz in Kilokalorien pro Stunde">kcal/h</td>
		<td title="Die durchschnittliche Herzfrequenz (wird z.B. f&uuml;r TRIMP verwendet)">&Oslash; HF</td>
		<td title="Rating of Perceived Exertion (nach Borg) = durchschnittliche Anstrengung auf einer Skala von 1 (leicht) bis 10 (extrem hart)">RPE</td>
		<td title="Es wird eine Distanz zur&uuml;ckgelegt">km</td>
		<td title="Tempoanzeige in km/h statt min/km">kmh</td>
		<td title="Es werden Trainingstypen wie Intervalltraining verwendet">Typen</td>
		<td title="Der Puls wird dabei aufgezeichnet">Puls</td>
		<td title="Der Sport wird an der freien Luft betrieben (Strecke/Wetter)">Drau&szlig;en</td>
	</tr>
	<tr class="space">
		<td colspan="12"></td>
	</tr>
<?php
$sports = $Mysql->fetchAsArray('SELECT * FROM `'.PREFIX.'sport` ORDER BY `id` ASC');
$sports[] = array('new' => true, 'online' => 1, 'short' => 0, 'kcal' => '', 'HFavg' => '', 'RPE' => '', 'distances' => 0, 'kmh' => 0, 'types' => 0, 'pulse' => 0, 'outside' => '');
foreach($sports as $i => $sport) {
	if (isset($sport['new'])) {
		$icon = '?';
		$name = '<input type="text" name="sport[name]['.$i.']" value="" />';
	} else {
		$icon = Icon::getSportIcon($sport['id']);
		$name = '<input type="hidden" name="sport[name]['.$i.']" value="'.$sport['name'].'" />'.$sport['name'];
	}
	echo('
		<tr class="a'.($i%2+1).($icon == '?' ? ' unimportant' : '').'">
			<td><input type="checkbox" name="sport[online]['.$i.']" '.($sport['online'] == 1 ? 'checked="checked" ' : '').'/></td>
			<td><input type="checkbox" name="sport[short]['.$i.']" '.($sport['short'] == 1 ? 'checked="checked" ' : '').'/></td>
			<td>'.$icon.'</td>
			<td>'.$name.'</td>
			<td><input type="text" size="3" name="sport[kcal]['.$i.']" value="'.$sport['kcal'].'" /></td>
			<td><input type="text" size="3" name="sport[HFavg]['.$i.']" value="'.$sport['HFavg'].'" /></td>
			<td><input type="text" size="1" name="sport[RPE]['.$i.']" value="'.$sport['RPE'].'" /></td>
			<td><input type="checkbox" name="sport[distances]['.$i.']" '.($sport['distances'] == 1 ? 'checked="checked" ' : '').'/></td>
			<td><input type="checkbox" name="sport[kmh]['.$i.']" '.($sport['kmh'] == 1 ? 'checked="checked" ' : '').'/></td>
			<td><input type="checkbox" name="sport[types]['.$i.']" '.($sport['types'] == 1 ? 'checked="checked" ' : '').'/></td>
			<td><input type="checkbox" name="sport[pulse]['.$i.']" '.($sport['pulse'] == 1 ? 'checked="checked" ' : '').'/></td>
			<td><input type="checkbox" name="sport[outside]['.$i.']" '.($sport['outside'] == 1 ? 'checked="checked" ' : '').'/></td>
		</tr>');
}
?>
</table>