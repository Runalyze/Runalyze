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
<?php foreach ($this->Trainings as $Training): ?>
	<tr>
		<td class="small c"><?php echo date("d.m.Y", $Training['time']); ?></td>
		<td class="b l"><?php echo $Training['comment']; ?></td>
		<td><?php echo Running::Km($Training['distance']); ?></td>
		<td class="b"><?php echo Time::toString(round($Training['s']), false, true); ?></td>
		<td><?php echo round(JD::Competition2VDOT($Training['distance'], $Training['s']), 2); ?></td>
		<td><?php echo $Training['pulse_avg']; ?></td>

		<td><?php echo $Training['vdot']; ?></td>
		<td class="b"><?php echo Time::toString(round(JD::CompetitionPrognosis($Training['vdot'], $Training['distance'])), false, true); ?></td>

		<?php if (CONF_JD_USE_VDOT_CORRECTOR): ?>
		<?php $c_vdot = round(JD::correctVDOT($Training['vdot']),2); ?>
		<td><?php echo $c_vdot; ?></td>
		<td class="b"><?php echo Time::toString(round(JD::CompetitionPrognosis($c_vdot, $Training['distance'])), false, true); ?></td>
		<?php else: ?>
		<td>-</td>
		<td class="b">-</td>
		<?php endif; ?>

		<?php $shape     = round(JD::calculateVDOTform($Training['time']),2); ?>
		<?php $prognosis = JD::CompetitionPrognosis($shape, $Training['distance']); ?>
		<td><?php echo $shape; ?></td>
		<td class="b"><?php echo Time::toString(round($prognosis, false, true)); ?></td>
		<td><?php echo HTML::plusMinus(sprintf("%01.2f", 100*($prognosis - $Training['s'])/$Training['s']), 2); ?> &#37;</td>
		<td><?php echo sprintf("%1.4f", JD::VDOTcorrectorFor($Training['id'], $Training)); ?></td>
	</tr>
<?php endforeach; ?>
<?php if (empty($this->Trainings)): ?>
	<tr>
		<td colspan="12"><em><?php _e('You did not run any races.'); ?></td>
	</tr>
<?php endif; ?>
	</tbody>
</table>

<?php if (!empty($this->Trainings)) Ajax::createTablesorterWithPagerFor('#vdotAnalysisTable', true); ?>

<p class="info">
	<?php _e('<strong>VDOT/Time:</strong> by standard formulas derived from Jack Daniels\' Running formula<br>'); ?>
	<?php _e('The time is what you could have reached at your maximal possible heart rate over this distance.'); ?>
</p>

<?php if (CONF_JD_USE_VDOT_CORRECTOR): ?>
<p class="info">
	<?php printf( __('<strong>VDOT/Time (corrected):</strong> after individual VDOT correction (factor: %f)<br>'), JD::correctionFactor() ); ?>
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

<?php if (CONF_JD_USE_VDOT_CORRECTION_FOR_ELEVATION): ?>
<p class="warning">
	<?php _e('The distance correction for elevation is not used in this table.'); ?>
</p>
<?php endif; ?>

<?php echo Ajax::wrapJSforDocumentReady('$("#ajax").addClass("big-window");'); ?>