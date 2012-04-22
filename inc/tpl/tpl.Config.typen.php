<h1>Trainingstypen:</h1>

Mit Trainingstypen k&ouml;nnen die Trainings bequem in Kategorien sortiert werden,
das dient vor allem der Trainingsanalyse.
Bestehende Trainingstypen k&ouml;nnen aber nur gel&ouml;scht werden, wenn keine Referenzen bestehen.
Daher sind die Trainingstypen mit ihren Trainings verlinkt.

<hr />

<table class="c">
	<thead>
		<tr class="b">
			<th>Trainingstyp</th>
			<th>Abk&uuml;rzung</th>
			<th title="Rating of Perceived Exertion (nach Borg) = durchschnittliche Anstrengung auf einer Skala von 1 (leicht) bis 10 (extrem hart)">RPE</th>
			<th title="Es werden einzelne Kilometerabschnitte aufgezeichnet">Splits</th>
			<th title="Ein Trainingstyp kann nur gel&ouml;scht werden, wenn keine Referenzen bestehen">l&ouml;schen?</th>
		</tr>
	</thead>
	<tbody>
<?php
$typen = $Mysql->fetchAsArray('SELECT * FROM `'.PREFIX.'type` ORDER BY `id` ASC');
$typen[] = array('id' => -1, 'name' => '', 'abbr' => '', 'RPE' => 5, 'splits' => 0);

foreach($typen as $i => $typ) {
	$num = $Mysql->num('SELECT `id` FROM `'.PREFIX.'training` WHERE `typeid`="'.$typ['id'].'"');
	if ($typ['id'] == -1)
		$delete = '';
	elseif ($num == 0)
		$delete = '<input type="checkbox" name="type[delete]['.$i.']" />';
	else
		$delete = DataBrowser::getSearchLink('<small>('.$num.')</small>', 'opt[typeid]=is&val[typeid][0]='.$typ['id']);

	echo('
		<tr class="a'.($i%2+1).($typ['id'] == -1 ? ' unimportant' : '').'">
			<td><input type="text" size="20" name="type[name]['.$i.']" value="'.$typ['name'].'" /></td>
			<td><input type="text" size="3" name="type[abbr]['.$i.']" value="'.$typ['abbr'].'" /></td>
			<td><input type="text" size="1" name="type[RPE]['.$i.']" value="'.$typ['RPE'].'" /></td>
			<td><input type="checkbox" name="type[splits]['.$i.']" '.HTML::Checked($typ['splits'] == 1).'/></td>
			<td>'.$delete.'</td>
		</tr>');
}
?>
	</tbody>
</table>