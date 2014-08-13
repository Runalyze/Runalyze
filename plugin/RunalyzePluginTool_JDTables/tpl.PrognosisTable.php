<?php
$Strategy = new RunningPrognosisDaniels;
$Strategy->adjustVDOT(false);

$Prognosis = new RunningPrognosis;
$Prognosis->setStrategy($Strategy);
?>

<table id="jd-tables-prognosis" class="zebra-style c" style="width: 700px;">
	<thead>
		<tr>
			<th>VDOT</th>
		<?php foreach ($this->Configuration()->value('pace_distances') as $km): ?>
		<?php if ($km >= 1): ?>
			<th><?php echo Running::Km($km, 1, ($km <= 3)); ?></th>
		<?php endif; ?>
		<?php endforeach; ?>
		</tr>
	</thead>
	<tbody>
<?php foreach ($this->Range as $vdot): ?>
		<?php $Strategy->setVDOT($vdot); ?>
		<tr<?php if (round(VDOT_FORM) == $vdot) echo ' class="highlight"'; ?>>
			<td class="b"><?php echo $vdot; ?></td>
		<?php foreach ($this->Configuration()->value('pace_distances') as $km): ?>
		<?php if ($km >= 1): ?>
			<td><?php echo Time::toString(round($Prognosis->inSeconds($km))); ?></td>
		<?php endif; ?>
		<?php endforeach; ?>
		</tr>
<?php endforeach; ?>
	</tbody>
</table>

<p class="info">
	<?php _e('This table is computed by some formulas, derived from the tables in Jack Daniels\' Running formula.'); ?>
	<?php _e('These values do not fit the original table one hundred percent.'); ?>
</p>

<p class="info">
	<?php _e('This table does <strong>not</strong> use a correction based on your current basic endurance.'); ?>
</p>