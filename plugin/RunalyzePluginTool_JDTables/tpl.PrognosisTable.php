<div style="width:700px;margin:0 auto;">
	<table id="jd-tables-prognosis">
		<thead>
			<tr class="small r">
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
			<tr class="small r <?php echo HTML::trClass($vdot); if (round(VDOT_FORM) == $vdot) echo ' highlight'; ?>">
				<td class="b"><?php echo $vdot; ?></td>
			<?php foreach ($this->config['pace_distances']['var'] as $km): ?>
			<?php if ($km >= 1): ?>
				<td><?php echo Time::toString(round(Running::Prognosis($km, $vdot, false))); ?></td>
			<?php endif; ?>
			<?php endforeach; ?>
			</tr>
	<?php endforeach; ?>
		</tbody>
	</table>
</div>

<p class="info">
	Diese Tabelle richtet sich nach den aus &quot;Die Laufformel&quot; abgeleiteten Gleichungen.
	Da die Berechnung in diesem Fall numerisch erfolgen muss, wird nur bis zu einer bestimmten Genauigkeit gerechnet
	(f&uuml;r 10 km auf 5 Sekunden, f&uuml;r Marathon auf 21 Sekunden).
</p>

<p class="info">
	In dieser Tabelle wird kein Korrekturfaktor f&uuml;r die Grundlagenausdauer verwendet.
</p>

<?php echo Ajax::wrapJSforDocumentReady('$("#jd-tables-prognosis").fixedHeaderTable({height:400});'); ?>