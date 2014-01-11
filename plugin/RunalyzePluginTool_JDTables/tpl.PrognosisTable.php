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
		<?php foreach ($this->config['pace_distances']['var'] as $km): ?>
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
		<?php foreach ($this->config['pace_distances']['var'] as $km): ?>
		<?php if ($km >= 1): ?>
			<td><?php echo Time::toString(round($Prognosis->inSeconds($km))); ?></td>
		<?php endif; ?>
		<?php endforeach; ?>
		</tr>
<?php endforeach; ?>
	</tbody>
</table>

<p class="info">
	Diese Tabelle richtet sich nach den aus &quot;Die Laufformel&quot; abgeleiteten Gleichungen.
	Sie stimmt daher nicht hundertprozentig mit der originalen Tabelle von Jack Daniels &uuml;berein.
</p>

<p class="info">
	In dieser Tabelle wird kein Korrekturfaktor f&uuml;r die Grundlagenausdauer verwendet.
</p>