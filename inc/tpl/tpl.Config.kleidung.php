<h1>Kleidung:</h1>

Die Kleidung kann wenn gew&uuml;nscht f&uuml;r weitere Statistiken bei jedem Training protokolliert werden.
Alle Kleidungsst&uuml;cke werden nach Kategorie geordnet mit der Abk&uuml;rzung im Formular angezeigt.<br />

	<br />

<table class="c">
	<thead>
		<tr>
			<th>Name</th>
			<th>Abk&uuml;rzung</th>
			<th>Kategorie</th>
			<th>l&ouml;schen?</th>
		</tr>
	</thead>
	<tbody>
<?php
$kleidungen = $Mysql->fetchAsArray('SELECT * FROM `'.PREFIX.'clothes` ORDER BY `order`, `id` ASC');
$kleidungen[] = array('new' => true, 'name' => '', 'short' => '', 'order' => '', 'id' => -1);

foreach($kleidungen as $i => $kleidung) {
	$id = $kleidung['id'];
	if (isset($kleidung['new'])) {
		$delete = '';
	} else {
		$delete = '<input type="checkbox" name="clothes[delete]['.$id.']" />';
	}
	echo('
			<tr class="a'.($i%2+1).($delete == '' ? ' unimportant' : '').'">
				<td><input type="text" size="30" name="clothes[name]['.$id.']" value="'.$kleidung['name'].'" /></td>
				<td><input type="text" size="15" name="clothes[short]['.$id.']" value="'.$kleidung['short'].'" /></td>
				<td><input type="text" size="4" name="clothes[order]['.$id.']" value="'.$kleidung['order'].'" /></td>
				<td>'.$delete.'</td>
			</tr>');
}
?>
	</tbody>
</table>