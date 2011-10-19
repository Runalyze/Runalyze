<?php
/**
 * File displaying the formular with new sportler information
 * Call:   plugin/window.sportler.table.php
 */
require '../inc/class.Frontend.php';

$Frontend = new Frontend(true, __FILE__);

$Plugin = Plugin::getInstanceFor('RunalyzePluginPanel_Sportler');
$Plugin_conf = $Plugin->get('config');

$Frontend->displayHeader();

$colspan     = 2;
$Fields      = array('time' => 'date', 'weight' => ' <small>kg</small>');
$FieldsPulse = array('pulse_rest' => ' <small>bpm</small>', 'pulse_max' => ' <small>bpm</small>');
$FieldsFat   = array('fat' => ' &#37;', 'water' => ' &#37;', 'muscles' => ' &#37;');
$Data        = array_reverse(User::getFullArray());
?>
<h1>K&ouml;rper-Daten</h1>

<table class="sortable pager">
	<thead>
		<tr>
			<th class="{sorter:'germandate'}">Datum</th>
			<th>Gewicht</th>
		<?php if ($Plugin_conf['use_pulse']['var']): ?>
		<?php $Fields = array_merge($Fields, $FieldsPulse); ?>
			<th>Ruhepuls</th>
			<th>Maximalpuls</th>
		<?php endif; ?>
		<?php if ($Plugin_conf['use_body_fat']['var']): ?>
		<?php $Fields = array_merge($Fields, $FieldsFat); ?>
			<th>&#37; Fett</th>
			<th>&#37; Wasser</th>
			<th>&#37; Muskeln</th>
		<?php endif; ?>
		</tr>
	</thead>
<?php $colspan = count($Fields); ?>
	<tbody>
	<?php if (empty($Data)): ?>
		<tr>
			<td colspan="<?php echo $colspan; ?>"><em>Keine Daten vorhanden.</em></td>
		</tr>
	<?php else: ?>
	<?php foreach ($Data as $i => $Info): ?>
		<tr class="<?php HTML::trClass($i); ?> c">
		<?php foreach ($Fields as $Key => $Unit): ?>
			<?php $Value = ($Unit == 'date') ? date('d.m.Y', $Info[$Key]) : $Info[$Key]; ?>
			<?php if ($Unit == 'date') $Unit = ''; ?>
			<td><?php echo (!is_numeric($Value) || $Value > 0) ? $Value.$Unit : '?'; ?></td>
		<?php endforeach; ?>
		</tr>
	<?php endforeach; ?>
	<?php endif; ?>
	</tbody>
</table>

<?php Ajax::printPagerDiv(); ?>
<?php echo Ajax::wrapJSforDocumentReady('jTablesorterWithPagination()'); ?>

<?php
$Frontend->displayFooter();
$Frontend->close();
?>