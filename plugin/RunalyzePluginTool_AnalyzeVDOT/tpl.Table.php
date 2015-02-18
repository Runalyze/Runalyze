<?php
use Runalyze\Configuration;
use Runalyze\Plugin\Tool\AnalyzeVDOT\TableRow;
?>
<table id="vdotAnalysisTable" class="fullwidth zebra-style">
	<thead>
		<tr>
			<th class="{sorter:'germandate'}"><?php _e('Date'); ?></th>
			<th><?php _e('Race'); ?></th>
			<th class="{sorter:'distance'}"><?php _e('km'); ?></th>
			<th><?php _e('Time'); ?></th>
			<th><?php _e('VDOT'); ?></th>
			<th><?php _e('HR'); ?></th>
			<th><?php _e('VDOT'); ?><br><small>(<?php _e('by&nbsp;HR'); ?>)</small></th>
			<th><?php _e('Time'); ?><br><small>(<?php _e('by&nbsp;HR'); ?>)</small></th>
			<th><?php _e('VDOT'); ?><br><small>(<?php _e('corrected'); ?>)</small></th>
			<th><?php _e('Time'); ?><br><small>(<?php _e('corrected'); ?>)</small></th>
			<th><?php _e('VDOT'); ?><br><small>(<?php _e('Shape'); ?>)</small></th>
			<th><?php _e('Time'); ?><br><small>(<?php _e('Shape'); ?>)</small></th>
			<th><?php _e('Deviation'); ?><br><small>(<?php _e('Shape'); ?>)</small></th>
			<th><?php _e('Corrector'); ?></th>
		</tr>
	</thead>
	<tbody class="r">
	<?php while ($data = $this->Query->fetch()): ?>
	<?php $Row = new TableRow($data); ?>
	<tr>
		<td class="small c"><?php echo $Row->date(); ?></td>
		<td class="b l"><?php echo $Row->name(); ?></td>
		<td><?php echo $Row->distance(); ?></td>
		<td class="b"><?php echo $Row->duration(); ?></td>
		<td><?php echo $Row->vdotByTime(); ?></td>
		<td><?php echo $Row->bpm(); ?></td>
		<td><?php echo $Row->vdotByHR(); ?></td>
		<td class="b"><?php echo $Row->prognosisByHR(); ?></td>
		<td><?php echo $Row->vdotByHRafterCorrection(); ?></td>
		<td class="b"><?php echo $Row->prognosisByHRafterCorrection(); ?></td>
		<td><?php echo $Row->vdotByShape(); ?></td>
		<td class="b"><?php echo $Row->prognosisByShape(); ?></td>
		<td><?php echo $Row->shapeDeviation(); ?></td>
		<td><?php echo $Row->correctionFactor(); ?></td>
	</tr>
	<?php endwhile; ?>
	<?php if (!isset($Row)): ?>
	<tr>
		<td colspan="12"><em><?php _e('You did not run any races.'); ?></td>
	</tr>
	<?php endif; ?>
	</tbody>
</table>

<?php if (isset($Row)) Ajax::createTablesorterWithPagerFor('#vdotAnalysisTable', true); ?>

<p class="info">
	<?php _e('<strong>VDOT/Time:</strong> by standard formulas derived from Jack Daniels\' Running formula<br>'); ?>
	<?php _e('The time is what you could have reached at your maximal possible heart rate over this distance.'); ?>
</p>

<?php if (Configuration::Vdot()->useCorrectionFactor()): ?>
<p class="info">
	<?php printf( __('<strong>VDOT/Time (corrected):</strong> after individual VDOT correction (factor: %f)<br>'), Configuration::Data()->vdotFactor() ); ?>
	<?php _e('The time is what you could have reached at your maximal possible heart rate over this distance.'); ?>
</p>
<?php else: ?>
<p class="warning">
	<?php _e('<strong>VDOT/Time (corrected):</strong> VDOT correction is deactivated. (see configuration)'); ?>
</p>
<?php endif; ?>

<p class="info">
	<?php _e('<strong>VDOT/Time (Shape):</strong> by your shape at that time<br>'); ?>
	<?php _e('The time is the prognosis by Runalyze.'); ?>
</p>

<p class="info">
	<?php _e('<strong>Corrector:</strong> Ratio between VDOT and VDOT (by HR)'); ?>
</p>

<?php if (Configuration::Vdot()->useElevationCorrection()): ?>
<p class="warning">
	<?php _e('The distance correction for elevation is not used in this table.'); ?>
</p>
<?php endif; ?>

<?php echo Ajax::wrapJSforDocumentReady('$("#ajax").addClass("big-window");'); ?>