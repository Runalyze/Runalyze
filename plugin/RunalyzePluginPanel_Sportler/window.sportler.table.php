<?php
/**
 * Window: user table
 * @package Runalyze\Plugins\Panels
 */
require '../../inc/class.Frontend.php';

$Frontend = new Frontend();

$Factory = new PluginFactory();
$Plugin = $Factory->newInstance('RunalyzePluginPanel_Sportler');

$Fields      = array('time' => 'date', 'weight' => ' <small>kg</small>');
$FieldsPulse = array('pulse_rest' => ' <small>bpm</small>', 'pulse_max' => ' <small>bpm</small>');
$FieldsFat   = array('fat' => ' &#37;', 'water' => ' &#37;', 'muscles' => ' &#37;');
$Data        = array_reverse(UserData::getFullArray());

if (Request::param('reload') == 'true') {
	Ajax::setReloadFlag( Ajax::$RELOAD_ALL );
	echo Ajax::getReloadCommand();
}
?>
<div class="panel-heading">
	<div class="panel-menu"><ul><li><?php echo $Plugin->addLink(); ?></li></ul></div>
	<h1><?php _e('Body values'); ?></h1>
</div>
<div class="panel-content">
	<table id="sportlerTable" class="zebra-style">
		<thead>
			<tr>
				<th class="{sorter: false}">&nbsp;</th>
				<th class="{sorter: false}">&nbsp;</th>
				<th class="{sorter:'germandate'}"><?php _e('Date'); ?></th>
				<th><?php _e('Weight'); ?></th>
			<?php if ($Plugin->Configuration()->value('use_pulse')): ?>
			<?php $Fields = array_merge($Fields, $FieldsPulse); ?>
				<th><?php _e('Resting HR'); ?></th>
				<th><?php _e('Maximal HR'); ?></th>
			<?php endif; ?>
			<?php if ($Plugin->Configuration()->value('use_body_fat')): ?>
			<?php $Fields = array_merge($Fields, $FieldsFat); ?>
				<th>&#37; <?php _e('Fat'); ?></th>
				<th>&#37; <?php _e('Water'); ?></th>
				<th>&#37; <?php _e('Muscles'); ?></th>
			<?php endif; ?>
			</tr>
		</thead>
	<?php $colspan = 2 + count($Fields); ?>
		<tbody class="c">
		<?php if (empty($Data)): ?>
			<tr>
				<td colspan="<?php echo $colspan; ?>"><em><?php _e('No data available.'); ?></em></td>
			</tr>
		<?php else: ?>
		<?php foreach ($Data as $Info): ?>
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
</div>