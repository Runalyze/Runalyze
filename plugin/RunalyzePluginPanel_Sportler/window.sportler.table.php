<?php
/**
 * Window: user table
 * @package Runalyze\Plugins\Panels
 */
require '../../inc/class.Frontend.php';

$Frontend = new Frontend();

$Plugin = Plugin::getInstanceFor('RunalyzePluginPanel_Sportler');
$Plugin_conf = $Plugin->get('config');

$colspan     = 3;
$Fields      = array('time' => 'date', 'weight' => ' <small>kg</small>');
$FieldsPulse = array('pulse_rest' => ' <small>bpm</small>', 'pulse_max' => ' <small>bpm</small>');
$FieldsFat   = array('fat' => ' &#37;', 'water' => ' &#37;', 'muscles' => ' &#37;');
$Data        = array_reverse(UserData::getFullArray());

if (Request::param('reload') == 'true') {
	Ajax::setReloadFlag( Ajax::$RELOAD_ALL );
	echo Ajax::getReloadCommand();
}
?>
<h1>K&ouml;rper-Daten</h1>

<table id="sportlerTable" class="zebra-style">
	<thead>
		<tr>
			<th class="{sorter: false}">&nbsp;</th>
			<th class="{sorter: false}">&nbsp;</th>
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
<?php $colspan = 2 + count($Fields); ?>
	<tbody class="c">
	<?php if (empty($Data)): ?>
		<tr>
			<td colspan="<?php echo $colspan; ?>"><em>Keine Daten vorhanden.</em></td>
		</tr>
	<?php else: ?>
	<?php foreach ($Data as $i => $Info): ?>
		<tr>
			<td><?php echo RunalyzePluginPanel_Sportler::getDeleteLinkFor($Info['id']); ?></td>
			<td><?php echo RunalyzePluginPanel_Sportler::getEditLinkFor($Info['id']); ?></td>
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

<?php Ajax::createTablesorterWithPagerFor('#sportlerTable', true); ?>

<p class="c">
	<?php echo Ajax::window('<a href="plugin/'.$Plugin->get('key').'/window.sportler.php">'.Icon::$ADD.' Einen neuen Eintrag hinzuf&uuml;gen</a>'); ?>
</p>