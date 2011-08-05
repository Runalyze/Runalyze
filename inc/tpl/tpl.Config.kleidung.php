<h1>Kleidung:</h1>

Die Kleidung kann wenn gew&uuml;nscht f&uuml;r weitere Statistiken bei jedem Training protokolliert werden.
Alle Kleidungsst&uuml;cke werden nach Kategorie geordnet mit der Abk&uuml;rzung im Formular angezeigt.<br />

	<br />

<table class="c">
	<tr class="b">
		<td>Name</td>
		<td>Abk&uuml;rzung</td>
		<td>Kategorie</td>
		<td>l&ouml;schen?</td>
	</tr>
	<tr class="space">
		<td colspan="4"></td>
	</tr>
<?php
$kleidungen = $Mysql->fetchAsArray('SELECT * FROM `'.PREFIX.'clothes` ORDER BY `order`, `id` ASC');
$kleidungen[] = array('new' => true, 'name' => '', 'short' => '', 'order' => '');

foreach($kleidungen as $i => $kleidung) {
	if (isset($kleidung['new'])) {
		$delete = '';
	} else {
		$delete = '<input type="checkbox" name="clothes[delete]['.$i.']" />';
	}
	echo('
		<tr class="a'.($i%2+1).($delete == '' ? ' unimportant' : '').'">
			<td><input type="text" size="30" name="clothes[name]['.$i.']" value="'.$kleidung['name'].'" /></td>
			<td><input type="text" size="15" name="clothes[short]['.$i.']" value="'.$kleidung['short'].'" /></td>
			<td><input type="text" size="4" name="clothes[order]['.$i.']" value="'.$kleidung['order'].'" /></td>
			<td>'.$delete.'</td>
		</tr>');
}
?>
</table>